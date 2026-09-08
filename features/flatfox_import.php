<?php

namespace casasoft\complexmanager;

require_once( 'silence.php' );

/**
 * Imports the public listings of one Flatfox project.
 *
 * Flatfox does not need an API token for this endpoint. The importer deliberately
 * owns only posts marked with SOURCE so it can live alongside eMonitor and
 * manually maintained units.
 */
class FlatfoxImport extends Feature {

	const SOURCE          = 'flatfox';
	const SOURCE_META     = '_complexmanager_import_source';
	const LISTING_ID_META = '_complexmanager_flatfox_listing_id';
	const REFERENCE_META  = '_complexmanager_flatfox_reference';
	const PROJECT_META    = '_complexmanager_flatfox_project_id';
	const AVAILABLE_META  = '_complexmanager_flatfox_availability_date';
	const ADDRESS_META    = '_complexmanager_flatfox_address';
	const CRON_HOOK       = 'cxm_flatfox_hourly_import';

	/** @var string[] */
	private $listing_statuses = array( 'pre', 'act', 'dis', 'arc', 'rem' );

	public function __construct() {
		add_action( 'init', array( $this, 'schedule_import' ) );
		add_action( self::CRON_HOOK, array( $this, 'run_scheduled_import' ) );
		add_action( 'admin_post_cxm_run_flatfox_import', array( $this, 'handle_manual_import' ) );
		add_action( 'admin_post_cxm_clear_units', array( $this, 'handle_clear_units' ) );
	}

	public static function activate_plugin() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::CRON_HOOK );
		}
	}

	public static function deactivate_plugin() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/** Ensures existing installations receive the schedule after updating. */
	public function schedule_import() {
		self::activate_plugin();
	}

	public function run_scheduled_import() {
		$this->run_import();
	}

	/**
	 * Runs the public API import. Cleanup runs only after every page was fetched
	 * and parsed successfully, including a successful empty result.
	 *
	 * @return array{code:string, processed:int, trashed:int}
	 */
	public function run_import() {
		$project_id = absint( PluginOptions::get_option( 'cxm_flatfox_project_id', 0 ) );
		if ( ! $project_id ) {
			return array( 'code' => 'no_project', 'processed' => 0, 'trashed' => 0 );
		}

		$listings = $this->fetch_listings( $project_id );
		if ( is_wp_error( $listings ) ) {
			return array( 'code' => 'request_failed', 'processed' => 0, 'trashed' => 0 );
		}

		$listings = $this->deduplicate_listings( $listings );
		$posts_pool = $this->get_posts_pool();
		$found_posts = array();
		$menu_order = 0;

		foreach ( $listings as $listing ) {
			$menu_order++;
			$post_id = $this->upsert_listing( $listing, $project_id, $posts_pool, $menu_order );
			if ( $post_id ) {
				$found_posts[] = $post_id;
			} else {
				// A local write failure is not proof that a listing disappeared.
				// Leave every existing Flatfox unit in place until a later full run.
				return array( 'code' => 'processing_failed', 'processed' => count( $found_posts ), 'trashed' => 0 );
			}
		}

		$trashed = $this->trash_missing_units( $found_posts );

		return array(
			'code'      => 'success',
			'processed' => count( $found_posts ),
			'trashed'   => $trashed,
		);
	}

	public function handle_manual_import() {
		$this->require_manage_options();
		check_admin_referer( 'cxm_run_flatfox_import' );

		$result = $this->run_import();
		$this->redirect_to_import_settings( array(
			'cxm_flatfox_result'    => $result['code'],
			'cxm_flatfox_processed' => $result['processed'],
			'cxm_flatfox_trashed'   => $result['trashed'],
		) );
	}

	public function handle_clear_units() {
		$this->require_manage_options();
		$nonce = isset( $_REQUEST['cxm_clear_units_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cxm_clear_units_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'cxm_clear_units' ) ) {
			$this->redirect_to_import_settings( array( 'cxm_clear_error' => 'invalid_request' ) );
		}

		$source = isset( $_POST['cxm_clear_source'] ) ? sanitize_key( wp_unslash( $_POST['cxm_clear_source'] ) ) : '';
		if ( ! in_array( $source, array( 'manual', 'emonitor', self::SOURCE ), true ) ) {
			wp_die( esc_html__( 'Invalid unit source.', 'complexmanager' ), 400 );
		}

		$post_ids = $this->get_post_ids_for_source( $source );
		$deleted = 0;
		foreach ( $post_ids as $post_id ) {
			if ( wp_delete_post( $post_id, true ) ) {
				$deleted++;
			}
		}

		$this->redirect_to_import_settings( array(
			'cxm_clear_source' => $source,
			'cxm_clear_count'  => $deleted,
		) );
	}

	private function require_manage_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage Complex Manager imports.', 'complexmanager' ), 403 );
		}
	}

	private function redirect_to_import_settings( $args ) {
		$url = add_query_arg(
			$args,
			add_query_arg(
				array( 'page' => 'complexmanager-admin', 'tab' => 'import' ),
				admin_url( 'admin.php' )
			)
		);

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Retrieves every Flatfox result page. A malformed page is a failed import,
	 * not an empty source response, so cleanup remains safely disabled.
	 *
	 * @return array|\WP_Error
	 */
	private function fetch_listings( $project_id ) {
		$status_query = '';
		foreach ( $this->listing_statuses as $status ) {
			$status_query .= '&status=' . rawurlencode( $status );
		}
		$organization_slug = $this->organization_slug();
		if ( $organization_slug ) {
			$status_query .= '&organization__slug=' . rawurlencode( $organization_slug );
		}

		$url = 'https://flatfox.ch/api/v1/public-listing/?project=' . absint( $project_id ) . $status_query;
		$listings = array();
		$pages = 0;

		while ( $url ) {
			$pages++;
			if ( $pages > 500 || ! $this->is_flatfox_listing_url( $url ) ) {
				return new \WP_Error( 'flatfox_invalid_pagination' );
			}

			$response = wp_remote_get( $url, array(
				'timeout' => 30,
				'headers' => array( 'Accept' => 'application/json' ),
			) );
			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				return new \WP_Error( 'flatfox_request_failed' );
			}

			$payload = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( ! is_array( $payload ) || ! array_key_exists( 'results', $payload ) || ! is_array( $payload['results'] ) ) {
				return new \WP_Error( 'flatfox_invalid_response' );
			}

			foreach ( $payload['results'] as $listing ) {
				if ( ! is_array( $listing ) || ! isset( $listing['pk'] ) || ! is_numeric( $listing['pk'] ) ) {
					return new \WP_Error( 'flatfox_invalid_response' );
				}
				$listings[] = $listing;
			}

			$next = isset( $payload['next'] ) ? $payload['next'] : null;
			if ( null !== $next && ! is_string( $next ) ) {
				return new \WP_Error( 'flatfox_invalid_pagination' );
			}
			$url = $next ? $this->normalise_next_url( $next ) : false;
		}

		return $listings;
	}

	private function normalise_next_url( $url ) {
		if ( 0 === strpos( $url, '/' ) ) {
			return 'https://flatfox.ch' . $url;
		}

		return $url;
	}

	private function is_flatfox_listing_url( $url ) {
		$parts = wp_parse_url( $url );

		return is_array( $parts )
			&& isset( $parts['scheme'], $parts['host'], $parts['path'] )
			&& 'https' === strtolower( $parts['scheme'] )
			&& 'flatfox.ch' === strtolower( $parts['host'] )
			&& 0 === strpos( $parts['path'], '/api/v1/public-listing/' );
	}

	/** Retains the highest listing PK for listings sharing the same reference. */
	private function deduplicate_listings( $listings ) {
		$unique = array();
		foreach ( $listings as $listing ) {
			$key = $this->listing_reference( $listing );
			if ( ! $key ) {
				$key = 'pk:' . absint( isset( $listing['pk'] ) ? $listing['pk'] : 0 );
			}

			if ( ! isset( $unique[ $key ] ) || $this->listing_pk( $listing ) > $this->listing_pk( $unique[ $key ] ) ) {
				$unique[ $key ] = $listing;
			}
		}

		ksort( $unique, SORT_NATURAL | SORT_FLAG_CASE );

		return array_values( $unique );
	}

	private function listing_pk( $listing ) {
		return absint( isset( $listing['pk'] ) ? $listing['pk'] : 0 );
	}

	private function listing_reference( $listing ) {
		if ( ! empty( $listing['reference'] ) ) {
			return (string) $listing['reference'];
		}

		$references = array_filter( array(
			isset( $listing['ref_property'] ) ? $listing['ref_property'] : '',
			isset( $listing['ref_house'] ) ? $listing['ref_house'] : '',
			isset( $listing['ref_object'] ) ? $listing['ref_object'] : '',
		) );

		return implode( '.', $references );
	}

	private function get_posts_pool() {
		$posts = get_posts( array(
			'post_type'      => 'complex_unit',
			'post_status'    => $this->all_unit_statuses(),
			'posts_per_page' => -1,
			'meta_key'       => self::SOURCE_META,
			'meta_value'     => self::SOURCE,
		) );
		$pool = array();

		foreach ( $posts as $post ) {
			$reference = get_post_meta( $post->ID, self::REFERENCE_META, true );
			if ( $reference ) {
				$pool[ $reference ] = $post;
			}
		}

		return $pool;
	}

	private function upsert_listing( $listing, $project_id, $posts_pool, $menu_order ) {
		$reference = $this->listing_reference( $listing );
		$pk = $this->listing_pk( $listing );
		if ( ! $reference ) {
			$reference = 'listing-' . $pk;
		}

		$post = isset( $posts_pool[ $reference ] ) ? $posts_pool[ $reference ] : false;
		$title = $this->listing_title( $listing, $reference );
		$post_data = array(
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => 'complex_unit',
			'menu_order'  => $menu_order,
		);

		if ( $post ) {
			$post_data['ID'] = $post->ID;
			$post_id = wp_update_post( $post_data, true );
		} else {
			$post_data['post_name'] = sanitize_title( 'flatfox-' . $project_id . '-' . $reference );
			$post_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		$this->update_listing_meta( $post_id, $listing, $project_id, $reference, $title );
		if ( ! $this->set_listing_building( $post_id, $listing, $project_id, $pk ) ) {
			return 0;
		}

		return (int) $post_id;
	}

	private function listing_title( $listing, $fallback ) {
		foreach ( array( 'alternative_reference', 'ref_object', 'short_title', 'public_title', 'reference' ) as $key ) {
			if ( isset( $listing[ $key ] ) && '' !== trim( (string) $listing[ $key ] ) ) {
				return (string) $listing[ $key ];
			}
		}

		return $fallback;
	}

	private function update_listing_meta( $post_id, $listing, $project_id, $reference, $title ) {
		$rent_net = $this->numeric_value( isset( $listing['rent_net'] ) ? $listing['rent_net'] : '' );
		$charges = $this->numeric_value( isset( $listing['rent_charges'] ) ? $listing['rent_charges'] : '' );
		$gross = $this->numeric_value( isset( $listing['rent_gross'] ) ? $listing['rent_gross'] : '' );
		if ( '' === $gross && '' !== $rent_net && '' !== $charges ) {
			$gross = $rent_net + $charges;
		}

		$submit_url = $this->submit_url( $listing );
		$availability = $this->listing_availability( $listing );
		$meta = array(
			self::SOURCE_META                              => self::SOURCE,
			self::LISTING_ID_META                          => $this->listing_pk( $listing ),
			self::REFERENCE_META                           => $reference,
			self::PROJECT_META                             => $project_id,
			self::AVAILABLE_META                           => $availability,
			self::ADDRESS_META                             => $this->listing_address( $listing ),
			'_complexmanager_unit_name'                    => $title,
			'_complexmanager_unit_idx_ref_house'           => isset( $listing['ref_house'] ) ? $listing['ref_house'] : '',
			'_complexmanager_unit_idx_ref_object'          => isset( $listing['ref_object'] ) ? $listing['ref_object'] : '',
			'_complexmanager_unit_number_of_rooms'         => $this->numeric_value( isset( $listing['number_of_rooms'] ) ? $listing['number_of_rooms'] : '' ),
			'_complexmanager_unit_story'                   => isset( $listing['floor'] ) ? sanitize_text_field( $listing['floor'] ) : '',
			'_complexmanager_unit_living_space'            => $this->numeric_value( isset( $listing['surface_living'] ) ? $listing['surface_living'] : '' ),
			'_complexmanager_unit_rent_net'                => $rent_net,
			'_complexmanager_unit_rent_gross'              => $gross,
			'_complexmanager_unit_extra_costs'             => $charges,
			'_complexmanager_unit_rent_timesegment'        => $this->rent_time_segment( isset( $listing['price_unit'] ) ? $listing['price_unit'] : '' ),
			'_complexmanager_unit_currency'                => isset( $listing['currency'] ) && $listing['currency'] ? sanitize_text_field( $listing['currency'] ) : 'CHF',
			'_complexmanager_unit_status'                  => $this->listing_status( $listing ),
			'_complexmanager_unit_link_url'                => $submit_url,
			'_complexmanager_unit_link_label'              => $submit_url ? 'Registration form' : '',
			'_complexmanager_unit_link_target'             => $submit_url ? '_blank' : '',
			'_complexmanager_flatfox_ref_property'         => isset( $listing['ref_property'] ) ? sanitize_text_field( $listing['ref_property'] ) : '',
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	private function numeric_value( $value ) {
		return is_numeric( $value ) ? $value + 0 : '';
	}

	/**
	 * The public API exposes a generic, numeric submit URL. When an organisation
	 * slug is configured, use Flatfox's canonical organisation/reference route,
	 * which is also the form supplied in Flatfox's export spreadsheet.
	 */
	private function submit_url( $listing ) {
		$organization_slug = $this->organization_slug();
		$reference = $this->listing_reference( $listing );
		if ( $organization_slug && $reference ) {
			$locale = function_exists( 'get_locale' ) ? strtolower( substr( get_locale(), 0, 2 ) ) : 'en';
			if ( ! in_array( $locale, array( 'de', 'en', 'fr', 'it' ), true ) ) {
				$locale = 'en';
			}

			return esc_url_raw(
				'https://flatfox.ch/' . $locale . '/' . rawurlencode( $organization_slug ) . '/listing/' . rawurlencode( $reference ) . '/submit/'
			);
		}

		$url = isset( $listing['submit_url'] ) ? trim( (string) $listing['submit_url'] ) : '';
		if ( '' === $url ) {
			return '';
		}

		if ( 0 === strpos( $url, '//' ) ) {
			$url = 'https:' . $url;
		} elseif ( 0 === strpos( $url, '/' ) ) {
			$url = 'https://flatfox.ch' . $url;
		} elseif ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://flatfox.ch/' . ltrim( $url, '/' );
		}

		return esc_url_raw( $url );
	}

	private function organization_slug() {
		return sanitize_title( (string) PluginOptions::get_option( 'cxm_flatfox_organization_slug', '' ) );
	}

	private function listing_availability( $listing ) {
		foreach ( array( 'moving_date', 'available_from', 'availability_date' ) as $key ) {
			if ( ! empty( $listing[ $key ] ) ) {
				return sanitize_text_field( $listing[ $key ] );
			}
		}

		return '';
	}

	private function rent_time_segment( $price_unit ) {
		$price_unit = strtolower( trim( (string) $price_unit ) );
		if ( in_array( $price_unit, array( 'monthly', 'month', 'm' ), true ) ) {
			return 'M';
		}
		if ( in_array( $price_unit, array( 'yearly', 'annual', 'year', 'y' ), true ) ) {
			return 'Y';
		}
		if ( in_array( $price_unit, array( 'weekly', 'week', 'w' ), true ) ) {
			return 'W';
		}

		return '';
	}

	private function listing_status( $listing ) {
		$status = isset( $listing['status'] ) ? strtolower( (string) $listing['status'] ) : '';
		if ( in_array( $status, array( 'arc', 'rem' ), true ) ) {
			return 'rented';
		}

		return ! empty( $listing['reserved'] ) ? 'reserved' : 'available';
	}

	private function set_listing_building( $post_id, $listing, $project_id, $pk ) {
		$ref_property = isset( $listing['ref_property'] ) ? (string) $listing['ref_property'] : '';
		$ref_house = isset( $listing['ref_house'] ) ? (string) $listing['ref_house'] : '';
		$slug_parts = array_filter( array( 'flatfox', $project_id, $ref_property, $ref_house ) );
		if ( count( $slug_parts ) < 4 ) {
			$slug_parts = array( 'flatfox', $project_id, 'listing', $pk );
		}
		$slug = sanitize_title( implode( '-', $slug_parts ) );
		$name = $this->listing_address( $listing );
		if ( ! $name ) {
			$name = trim( $ref_property . ' ' . $ref_house );
		}
		if ( ! $name ) {
			$name = sprintf( __( 'Flatfox building %d', 'complexmanager' ), $pk );
		}

		$term = get_term_by( 'slug', $slug, 'building' );
		if ( ! $term ) {
			$created_term = wp_insert_term( $name, 'building', array(
				'slug'        => $slug,
				'description' => $name,
			) );
			if ( is_wp_error( $created_term ) ) {
				return false;
			}
			$term = get_term( (int) $created_term['term_id'], 'building' );
		} elseif ( $term->name !== $name || $term->description !== $name ) {
			$updated_term = wp_update_term( (int) $term->term_id, 'building', array(
				'name'        => $name,
				'description' => $name,
			) );
			if ( is_wp_error( $updated_term ) ) {
				return false;
			}
		}

		if ( $term && ! is_wp_error( $term ) ) {
			$result = wp_set_object_terms( $post_id, (int) $term->term_id, 'building' );
			return ! is_wp_error( $result );
		}

		return false;
	}

	private function listing_address( $listing ) {
		foreach ( array( 'address', 'public_address' ) as $key ) {
			if ( ! empty( $listing[ $key ] ) && is_string( $listing[ $key ] ) ) {
				return sanitize_text_field( $listing[ $key ] );
			}
		}

		$street = isset( $listing['street'] ) ? trim( (string) $listing['street'] ) : '';
		$zip = isset( $listing['zipcode'] ) ? trim( (string) $listing['zipcode'] ) : '';
		$city = isset( $listing['city'] ) ? trim( (string) $listing['city'] ) : '';
		$address = trim( $street . ( $zip || $city ? ', ' : '' ) . trim( $zip . ' ' . $city ) );

		return sanitize_text_field( $address );
	}

	/** Only Flatfox-owned units are eligible for import cleanup. */
	private function trash_missing_units( $found_posts ) {
		$missing_posts = get_posts( array(
			'post_type'      => 'complex_unit',
			'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
			'posts_per_page' => -1,
			'exclude'         => $found_posts,
			'meta_key'       => self::SOURCE_META,
			'meta_value'     => self::SOURCE,
		) );
		$trashed = 0;

		foreach ( $missing_posts as $post ) {
			if ( wp_trash_post( $post->ID ) ) {
				$trashed++;
			}
		}

		return $trashed;
	}

	private function get_post_ids_for_source( $source ) {
		$args = array(
			'post_type'      => 'complex_unit',
			'post_status'    => $this->all_unit_statuses(),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( self::SOURCE === $source ) {
			$args['meta_key'] = self::SOURCE_META;
			$args['meta_value'] = self::SOURCE;
		} elseif ( 'emonitor' === $source ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array( 'key' => self::SOURCE_META, 'value' => 'emonitor' ),
				array( 'key' => 'casawp_id', 'compare' => 'EXISTS' ),
			);
		} else {
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array( 'key' => self::SOURCE_META, 'compare' => 'NOT EXISTS' ),
					array( 'key' => self::SOURCE_META, 'value' => array( self::SOURCE, 'emonitor' ), 'compare' => 'NOT IN' ),
				),
				array( 'key' => 'casawp_id', 'compare' => 'NOT EXISTS' ),
			);
		}

		return get_posts( $args );
	}

	private function all_unit_statuses() {
		return array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' );
	}
}
