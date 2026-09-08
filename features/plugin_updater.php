<?php

namespace casasoft\complexmanager;

/**
 * Integrates Complex Manager with Casasoft's self-hosted WordPress update API.
 *
 * The endpoint uses the same POST/serialized-response contract as CASAWP, while
 * the updater itself is namespaced so both plugins can be active together.
 */
class PluginUpdater {

	/** @var string */
	private $current_version;

	/** @var string */
	private $update_path;

	/** @var string */
	private $plugin_slug;

	/** @var string */
	private $slug;

	/** @var string */
	private $license_user;

	/** @var string */
	private $license_key;

	/**
	 * @param string $current_version Installed plugin version.
	 * @param string $update_path     Self-hosted update endpoint.
	 * @param string $plugin_slug     Plugin basename, e.g. complex-manager/complex-manager.php.
	 * @param string $license_user    Compatibility value for the Casasoft update API.
	 * @param string $license_key     Compatibility value for the Casasoft update API.
	 */
	public function __construct( $current_version, $update_path, $plugin_slug, $license_user = '', $license_key = '' ) {
		$this->current_version = $current_version;
		$this->update_path     = $update_path;
		$this->plugin_slug     = $plugin_slug;
		$this->slug            = pathinfo( basename( $plugin_slug ), PATHINFO_FILENAME );
		$this->license_user    = $license_user;
		$this->license_key     = $license_key;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );
	}

	/**
	 * Adds an available self-hosted release to WordPress' update transient.
	 *
	 * @param object $transient WordPress' plugin update data.
	 * @return object
	 */
	public function check_update( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		$remote_version = $this->get_remote_response( 'version' );
		if ( ! is_object( $remote_version ) || empty( $remote_version->new_version ) ) {
			return $transient;
		}

		if ( version_compare( $this->current_version, $remote_version->new_version, '<' ) ) {
			$update              = new \stdClass();
			$update->id          = isset( $remote_version->id ) ? $remote_version->id : $this->slug;
			$update->slug        = $this->slug;
			$update->plugin      = $this->plugin_slug;
			$update->new_version = $remote_version->new_version;
			$update->url         = isset( $remote_version->url ) ? $remote_version->url : '';
			$update->package     = isset( $remote_version->package ) ? $remote_version->package : '';

			if ( isset( $remote_version->tested ) ) {
				$update->tested = $remote_version->tested;
			}
			if ( isset( $remote_version->requires ) ) {
				$update->requires = $remote_version->requires;
			}
			if ( isset( $remote_version->requires_php ) ) {
				$update->requires_php = $remote_version->requires_php;
			}

			$transient->response[ $this->plugin_slug ] = $update;
		}

		return $transient;
	}

	/**
	 * Supplies the WordPress update-details modal with the hosted release data.
	 *
	 * @param mixed  $result Existing response.
	 * @param string $action Requested API action.
	 * @param object $args   Requested plugin details.
	 * @return mixed
	 */
	public function check_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || $this->slug !== $args->slug ) {
			return $result;
		}

		$information = $this->get_remote_response( 'info' );
		return is_object( $information ) ? $information : $result;
	}

	/**
	 * Requests and validates one response from the Casasoft update endpoint.
	 *
	 * @param string $action Endpoint action.
	 * @return object|false
	 */
	private function get_remote_response( $action ) {
		$response = wp_remote_post(
			$this->update_path,
			array(
				'timeout' => 10,
				'body'    => array(
					'action'       => $action,
					'license_user' => $this->license_user,
					'license_key'  => $this->license_key,
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( ! is_string( $body ) || '' === $body ) {
			return false;
		}

		// The legacy Casasoft endpoint returns serialized stdClass objects.
		if ( PHP_VERSION_ID >= 70000 ) {
			$remote = @unserialize( $body, array( 'allowed_classes' => array( 'stdClass' ) ) );
		} else {
			$remote = @unserialize( $body );
		}

		return is_object( $remote ) ? $remote : false;
	}
}
