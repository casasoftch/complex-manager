<?php

namespace casasoft\complexmanager;

class general_options extends Feature
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'redirect_legacy_settings_page' ), 1 );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_menu', array( $this, 'set_standard_terms' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_external_scripts' ) );


    }

    public function load_external_scripts(){
        wp_register_script('complex-manager-options', PLUGIN_URL . 'assets/js/complex-manager-options.js',  array('jquery'), '2' );

        wp_enqueue_media();
        //wp_enqueue_script('media-upload');
        wp_enqueue_script('complex-manager-options');

    }

    /**
     * Tab slugs are UI state only. All settings remain in complex_manager.
     */
    private function get_tabs()
    {
        return array(
            'general' => __( 'General', 'complexmanager' ),
            'lists' => __( 'Lists & Filters', 'complexmanager' ),
            'contact' => __( 'Contact Form', 'complexmanager' ),
            'delivery' => __( 'Inquiry Delivery', 'complexmanager' ),
            'import' => __( 'Import', 'complexmanager' ),
        );
    }

    private function get_tab_page( $tab )
    {
        return 'complex-manager-admin-' . $tab;
    }

    private function get_current_tab()
    {
        $tabs = $this->get_tabs();
        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

        return isset( $tabs[ $tab ] ) ? $tab : 'general';
    }

    private function get_submitted_tab()
    {
        $tabs = $this->get_tabs();
        $tab = isset( $_POST['_cxm_settings_tab'] ) ? sanitize_key( wp_unslash( $_POST['_cxm_settings_tab'] ) ) : '';

        return isset( $tabs[ $tab ] ) ? $tab : false;
    }

    private function get_tab_url( $tab, $args = array() )
    {
        $args = array_merge( array(
            'page' => 'complexmanager-admin',
            'tab' => $tab,
        ), $args );

        return add_query_arg( $args, admin_url( 'admin.php' ) );
    }

    /**
     * Preserve existing Settings-menu bookmarks and action links after moving
     * Complex Manager to the top-level WordPress admin navigation.
     */
    public function redirect_legacy_settings_page()
    {
        global $pagenow;

        if (
            'options-general.php' !== $pagenow
            || ! isset( $_GET['page'] )
            || 'complexmanager-admin' !== sanitize_key( wp_unslash( $_GET['page'] ) )
            || ! current_user_can( 'manage_options' )
        ) {
            return;
        }

        $args = wp_unslash( $_GET );

        /*
         * Do not carry retired legacy action parameters to the new URL.
         */
        unset( $args['emonitorupdate'], $args['force_all_properties'], $args['force_last_import'], $args['generate_defaults'] );

        $args['page'] = 'complexmanager-admin';

        wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Maps the stored setting keys to their owning tab. The map is also the
     * allowlist used when saving a tab, so unrelated settings cannot be lost.
     */
    private function get_tab_option_keys()
    {
        return array(
            'general' => array(
                'project_image', 'cache_renders', 'thousands_seperator',
                'space_decimal', 'flex_list',
            ),
            'lists' => array(
                'list_cols', 'list_filters', 'translate_labels', 'filter_income_max',
            ),
            'contact' => array(
                'contactform_mandatory_firstname', 'contactform_mandatory_lastname',
                'contactform_mandatory_legalname', 'contactform_mandatory_phone',
                'contactform_mandatory_mobile', 'contactform_mandatory_street',
                'contactform_mandatory_zip', 'contactform_mandatory_locality',
                'contactform_mandatory_message', 'recaptcha', 'recaptcha_secret',
                'recaptcha_v3', 'recaptcha_score', 'honeypot',
            ),
            'delivery' => array(
                'emails', 'global_direct_recipient_email', 'provider_slug',
                'publisher_slug', 'remcat', 'remcat_website', 'remcat_company',
                'remcat_company_street', 'remcat_company_postal_code',
                'remcat_company_locality', 'remcat_company_person_name',
                'remcat_company_email', 'remcat_general_property_ref',
                'idx_ref_property', 'gap_id',
            ),
            'import' => array(
                'cxm_flatfox_project_id', 'cxm_flatfox_organization_slug',
                'cxm_emonitor_api', 'cxm_force_property_update',
                'cxm_exclude_buildings', 'cxm_emonitor_custom1_matching',
                'cxm_emonitor_custom2_matching', 'cxm_emonitor_custom3_matching',
                'separate_building_property_type', 'squaremeterprices',
                'propertytype', 'virtualtour',
                'cxm_emonitor_rewrite_download_label',
                'cxm_emonitor_rewrite_link_label',
            ),
        );
    }

    /**
     * New import sources can register an additional entry here without adding
     * another top-level settings tab.
     */
    private function get_import_sources()
    {
        return array(
            'flatfox' => array(
                'id' => 'import_flatfox',
                'title' => __( 'Flatfox', 'complexmanager' ),
                'callback' => 'import_flatfox_callback',
            ),
            'emonitor' => array(
                'id' => 'import_emonitor',
                'title' => __( 'eMonitor', 'complexmanager' ),
                'callback' => 'import_emonitor_callback',
            ),
            'clear' => array(
                'id' => 'import_clear_units',
                'title' => __( 'Clear units', 'complexmanager' ),
                'callback' => 'import_clear_units_callback',
            ),
        );
    }

    private function add_tabbed_settings_field( $tab, $id, $title, $callback )
    {
        add_settings_field(
            $id,
            $title,
            array( $this, $callback ),
            $this->get_tab_page( $tab ),
            'cxm_' . $tab
        );
    }

    /**
     * The original settings registrations pre-date tabs. Move their existing
     * fields to the appropriate Settings API page without changing callbacks,
     * field IDs, or HTML input names.
     */
    private function distribute_legacy_settings_fields()
    {
        global $wp_settings_fields;

        foreach ( $this->get_tabs() as $tab => $title ) {
            add_settings_section(
                'cxm_' . $tab,
                '',
                array( $this, 'print_section_info' ),
                $this->get_tab_page( $tab )
            );
        }

        if ( empty( $wp_settings_fields['complex-manager-admin']['cxm_1'] ) ) {
            return;
        }

        $tab_fields = $this->get_tab_option_keys();
        $tab_fields['contact'][] = 'print_form_info';
        unset( $tab_fields['import'] );

        foreach ( $tab_fields as $tab => $field_ids ) {
            foreach ( $field_ids as $field_id ) {
                if ( isset( $wp_settings_fields['complex-manager-admin']['cxm_1'][ $field_id ] ) ) {
                    $wp_settings_fields[ $this->get_tab_page( $tab ) ][ 'cxm_' . $tab ][ $field_id ] = $wp_settings_fields['complex-manager-admin']['cxm_1'][ $field_id ];
                }
            }
        }

        unset( $wp_settings_fields['complex-manager-admin']['cxm_1'] );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        add_menu_page(
            'Complex Manager',
            'Complex Manager',
            'manage_options',
            'complexmanager-admin',
            array( $this, 'create_admin_page' ),
            'dashicons-admin-generic'
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {

        if (isset($_GET['cxm_clear_cache'])) {
            $removed = 0;
            $dir = wp_upload_dir(null, true, false);
            if (is_dir($dir['basedir'] . '/cmx_cache')) {
                $files = glob($dir['basedir'] . '/cmx_cache/*');
                foreach($files as $file){ // iterate files
                  if(is_file($file))
                    unlink($file); // delete file
                    $removed++;
                }
            }

            $removed_message = sprintf(
                _n( 'Removed %d cache file.', 'Removed %d cache files.', $removed, 'complexmanager' ),
                $removed
            );
            echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>' . esc_html( $removed_message ) . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html( $removed_message ) . '</span></button></div>';


        }

        // Set class property
        $this->options = get_option( 'complex_manager' );
        ?>
        <div class="wrap">
            <h2>Complex Manager</h2>
            <?php
                $tabs = $this->get_tabs();
                $current_tab = $this->get_current_tab();
            ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab => $label ) : ?>
                    <a class="nav-tab <?php echo $tab === $current_tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>"><?php echo esc_html( $label ); ?></a>
                <?php endforeach; ?>
            </h2>
            <?php if ( 'import' === $current_tab ) : ?>
                <?php $this->print_import_action_notices(); ?>
                <?php $this->print_import_action_forms(); ?>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'cxm_general_options' );
                    printf( '<input type="hidden" name="_cxm_settings_tab" value="%s" />', esc_attr( $current_tab ) );
                    do_settings_sections( $this->get_tab_page( $current_tab ) );
                    submit_button();
                ?>

                <?php if ( 'contact' === $current_tab ) : ?>
                    <button class="button button-default" type="submit" name="generate_defaults" value="true"><?php esc_html_e( 'Regenerate default terms', 'complexmanager' ); ?></button>
                <?php endif; ?>
            </form>
        </div>
        <?php
    }

    /** Displays results returned by the nonce-protected Flatfox import actions. */
    private function print_import_action_notices()
    {
        $result = isset( $_GET['cxm_flatfox_result'] ) ? sanitize_key( wp_unslash( $_GET['cxm_flatfox_result'] ) ) : '';
        if ( 'success' === $result ) {
            $processed = isset( $_GET['cxm_flatfox_processed'] ) ? absint( $_GET['cxm_flatfox_processed'] ) : 0;
            $trashed = isset( $_GET['cxm_flatfox_trashed'] ) ? absint( $_GET['cxm_flatfox_trashed'] ) : 0;
            $message = sprintf(
                __( 'Flatfox import completed: %1$d units imported, %2$d units moved to Trash.', 'complexmanager' ),
                $processed,
                $trashed
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        } elseif ( 'no_project' === $result ) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Enter a Flatfox project ID before running the import.', 'complexmanager' ) . '</p></div>';
        } elseif ( 'request_failed' === $result ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Flatfox import failed. Existing Flatfox units were left untouched.', 'complexmanager' ) . '</p></div>';
        } elseif ( 'processing_failed' === $result ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Flatfox import could not save every unit. Existing Flatfox units were left untouched.', 'complexmanager' ) . '</p></div>';
        }

        if ( isset( $_GET['cxm_clear_error'] ) && 'invalid_request' === sanitize_key( wp_unslash( $_GET['cxm_clear_error'] ) ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'The clear request could not be verified. Please reload the page and try again.', 'complexmanager' ) . '</p></div>';
        }

        $source = isset( $_GET['cxm_clear_source'] ) ? sanitize_key( wp_unslash( $_GET['cxm_clear_source'] ) ) : '';
        $sources = array(
            'manual' => __( 'manually created', 'complexmanager' ),
            'emonitor' => __( 'eMonitor', 'complexmanager' ),
            'flatfox' => __( 'Flatfox', 'complexmanager' ),
        );
        if ( isset( $sources[ $source ] ) && isset( $_GET['cxm_clear_count'] ) ) {
            $count = absint( $_GET['cxm_clear_count'] );
            $message = sprintf(
                _n( '%1$d %2$s unit permanently deleted.', '%1$d %2$s units permanently deleted.', $count, 'complexmanager' ),
                $count,
                $sources[ $source ]
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        }
    }

    /**
     * Keep import and destructive actions out of the Settings API form. Their
     * buttons use the form attribute to submit these nonce-protected forms.
     */
    private function print_import_action_forms()
    {
        foreach ( array( 'standard' => 0, 'overwrite' => 1 ) as $mode => $force_overwrite ) {
            ?>
            <form id="cxm-run-emonitor-import-<?php echo esc_attr( $mode ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none">
                <input type="hidden" name="action" value="cxm_run_emonitor_import" />
                <input type="hidden" name="cxm_emonitor_force_overwrite" value="<?php echo esc_attr( $force_overwrite ); ?>" />
                <?php wp_nonce_field( 'cxm_run_emonitor_import' ); ?>
            </form>
            <?php
        }

        foreach ( array( 'manual', 'emonitor', 'flatfox' ) as $source ) {
            ?>
            <form id="cxm-clear-units-<?php echo esc_attr( $source ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none">
                <input type="hidden" name="action" value="cxm_clear_units" />
                <input type="hidden" name="cxm_clear_source" value="<?php echo esc_attr( $source ); ?>" />
                <?php wp_nonce_field( 'cxm_clear_units', 'cxm_clear_units_nonce' ); ?>
            </form>
            <?php
        }
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'cxm_general_options', // Option group
            'complex_manager', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        /*add_settings_field(
            'id_number', // ID
            'ID Number', // Title
            array( $this, 'id_number_callback' ), // Callback
            'complex-manager-admin', // Page
            'cxm_1' // Section
        );    */

        add_settings_field(
            'project_image',
             __( 'Project Image', 'complexmanager' ),
             array( $this, 'project_image_callback' ),
             'complex-manager-admin',
             'cxm_1'
        );

        add_settings_field(
            'emails',
             __( 'Emails', 'complexmanager' ),
            array( $this, 'emails_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'cache_renders',
             __( 'Use File-Cache for Renders', 'complexmanager' ),
            array( $this, 'cache_renders_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );



        add_settings_field(
            'global_direct_recipient_email',
             __( 'CASAMAIL direct email', 'complexmanager' ),
            array( $this, 'global_direct_recipient_email_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'provider_slug',
             __( 'CASAMAIL provider ID', 'complexmanager' ),
            array( $this, 'provider_slug_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'publisher_slug',
             __( 'CASAMAIL publisher ID', 'complexmanager' ),
            array( $this, 'publisher_slug_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'thousands_seperator',
             __( 'Thousands separator', 'complexmanager' ),
            array( $this, 'thousands_seperator_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'space_decimal',
             __( 'Space/Area Decimal', 'complexmanager' ),
            array( $this, 'space_decimal_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'flex_list',
             __( 'Use Flexbox layout for lists', 'complexmanager' ),
            array( $this, 'flex_list_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );



        add_settings_field(
            'remcat',
             __( 'REMCat', 'complexmanager' ),
            array( $this, 'remcat_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_website',
             __( 'REMCat website', 'complexmanager' ),
            array( $this, 'remcat_website_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_company',
             __( 'REMCat company', 'complexmanager' ),
            array( $this, 'remcat_company_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_company_street',
             __( 'REMCat company street', 'complexmanager' ),
            array( $this, 'remcat_company_street_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_company_postal_code',
             __( 'REMCat company postal code', 'complexmanager' ),
            array( $this, 'remcat_company_postal_code_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_company_locality',
             __( 'REMCat company locality', 'complexmanager' ),
            array( $this, 'remcat_company_locality_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_company_person_name',
             __( 'REMCat company contact person', 'complexmanager' ),
            array( $this, 'remcat_company_person_name_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_company_email',
             __( 'REMCat company email', 'complexmanager' ),
            array( $this, 'remcat_company_email_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'remcat_general_property_ref',
             __( 'REMCat general ID', 'complexmanager' ),
            array( $this, 'remcat_general_property_ref_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'idx_ref_property',
             __( 'IDX / REMCat property reference', 'complexmanager' ),
            array( $this, 'idx_ref_property_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'print_form_info',
             __( 'Contact form mandatory fields', 'complexmanager' ),
             array( $this, 'print_form_info' ),
             'complex-manager-admin',
             'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_firstname',
             __( 'First name', 'complexmanager' ),
            array( $this, 'contactform_mandatory_firstname_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_lastname',
             __( 'Last name', 'complexmanager' ),
            array( $this, 'contactform_mandatory_lastname_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_legalname',
             __( 'Company', 'complexmanager' ),
            array( $this, 'contactform_mandatory_legalname_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_phone',
             __( 'Phone', 'complexmanager' ),
            array( $this, 'contactform_mandatory_phone_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_mobile',
             __( 'Mobile', 'complexmanager' ),
            array( $this, 'contactform_mandatory_mobile_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_street',
             __( 'Street', 'complexmanager' ),
            array( $this, 'contactform_mandatory_street_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_zip',
             __( 'ZIP', 'complexmanager' ),
            array( $this, 'contactform_mandatory_zip_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_locality',
             __( 'City', 'complexmanager' ),
            array( $this, 'contactform_mandatory_locality_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'contactform_mandatory_message',
             __( 'Message', 'complexmanager' ),
            array( $this, 'contactform_mandatory_message_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'list_cols',
             __( 'Columns displayed in lists', 'complexmanager' ),
            array( $this, 'list_cols_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'list_filters',
             __( 'Filter translations', 'complexmanager' ),
            array( $this, 'list_filters_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'translate_labels',
             __( 'Label translations', 'complexmanager' ),
            array( $this, 'translate_labels_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        foreach ( $this->get_import_sources() as $source ) {
            $this->add_tabbed_settings_field(
                'import',
                $source['id'],
                $source['title'],
                $source['callback']
            );
        }

        add_settings_field(
            'gap_id',
             __( 'Google Analytics Code', 'complexmanager' ),
            array( $this, 'google_analytics_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );


        //filter settings
        add_settings_field(
            'filter_income_max',
             __( 'Income maximum', 'complexmanager' ),
            array( $this, 'filter_income_max_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );


        add_settings_field(
            'recaptcha',
             __( 'reCAPTCHA site key', 'complexmanager' ),
            array( $this, 'recaptcha_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'recaptcha_secret',
             __( 'reCAPTCHA secret key', 'complexmanager' ),
            array( $this, 'recaptcha_secret_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'recaptcha_v3',
            __( 'Enable reCAPTCHA v3', 'complexmanager' ),
            array( $this, 'recaptcha_v3_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'recaptcha_score',
            __( 'reCAPTCHA v3 score', 'complexmanager' ),
            array( $this, 'recaptcha_score_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        add_settings_field(
            'honeypot',
            __( 'Honeypot', 'complexmanager' ),
            array( $this, 'honeypot_callback' ),
            'complex-manager-admin',
            'cxm_1'
        );

        $this->distribute_legacy_settings_fields();

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $input = is_array( $input ) ? $input : array();
        $stored_options = get_option( 'complex_manager', array() );
        $new_input = is_array( $stored_options ) ? $stored_options : array();
        $tab_keys = $this->get_tab_option_keys();
        $submitted_tab = $this->get_submitted_tab();

        if ( $submitted_tab ) {
            $keys_to_save = $tab_keys[ $submitted_tab ];
        } else {
            // Retain compatibility for a legacy or programmatic settings post.
            $keys_to_save = array();
            foreach ( $tab_keys as $keys ) {
                $keys_to_save = array_merge( $keys_to_save, $keys );
            }
            $keys_to_save = array_merge( $keys_to_save, array( 'id_number', 'cxm_api_key', 'cxm_private_key' ) );
        }

        foreach ( $keys_to_save as $key ) {
            if ( array_key_exists( $key, $input ) ) {
                $new_input[ $key ] = $this->sanitize_setting_value( $key, $input[ $key ] );
            }
        }

        return $new_input;
    }

    private function sanitize_setting_value( $key, $value )
    {
        if ( in_array( $key, array( 'list_cols', 'list_filters', 'translate_labels' ), true ) ) {
            return maybe_serialize( $value );
        }

        if ( in_array( $key, array( 'id_number', 'space_decimal' ), true ) ) {
            return absint( $value );
        }

        if ( 'cxm_flatfox_project_id' === $key ) {
            return absint( $value );
        }

        if ( 'cxm_force_property_update' === $key ) {
            return ! empty( $value ) ? 1 : 0;
        }

        $raw_values = array(
            'thousands_seperator',
            'cxm_api_key',
            'cxm_private_key',
            'cxm_emonitor_api',
            'cxm_exclude_buildings',
            'cxm_emonitor_custom1_matching',
            'cxm_emonitor_custom2_matching',
            'cxm_emonitor_custom3_matching',
            'cxm_emonitor_rewrite_download_label',
            'cxm_emonitor_rewrite_link_label',
            'filter_income_max',
            'recaptcha',
            'recaptcha_secret',
        );

        return in_array( $key, $raw_values, true ) ? $value : sanitize_text_field( $value );
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
    }

    /**
     * Print the Section text
     */
    public function print_form_info()
    {
      
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="complex_manager[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function emails_callback()
    {
        printf(
            '<input type="text" id="emails" name="complex_manager[emails]" value="%s" />',
            isset( $this->options['emails'] ) ? esc_attr( $this->options['emails']) : ''
        );
    }


    public function global_direct_recipient_email_callback()
    {
        printf(
            '<input type="text" id="global_direct_recipient_email" name="complex_manager[global_direct_recipient_email]" value="%s" />',
            isset( $this->options['global_direct_recipient_email'] ) ? esc_attr( $this->options['global_direct_recipient_email']) : ''
        );
    }

    public function cache_renders_callback()
    {
        echo
            '<input type="hidden" name="complex_manager[cache_renders]" value="0" />
            <input type="checkbox" ' . (isset( $this->options['cache_renders']) && $this->options['cache_renders'] ? 'CHECKED' : '') . ' id="cache_renders" name="complex_manager[cache_renders]" value="1" />'
        ;

        echo '&nbsp;&nbsp;<a href="' . esc_url( $this->get_tab_url( 'general', array( 'cxm_clear_cache' => 1 ) ) ) . '" type="button" class="button">' . esc_html__( 'Remove cache files', 'complexmanager' ) . '</a>';
    }

    public function contactform_mandatory_firstname_callback()
    {
        $checked = false;
        if (($this->options['contactform_mandatory_firstname'] ?? TRUE) || (($this->options['contactform_mandatory_firstname'] ?? TRUE) && isset( $this->options['contactform_mandatory_firstname']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_firstname]" value="0" />
            <input type="checkbox" value="1" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_firstname" name="complex_manager[contactform_mandatory_firstname]" /></div>'

        ;

    }

    public function contactform_mandatory_lastname_callback()
    {
        $checked = false;
        if (($this->options['contactform_mandatory_lastname'] ?? TRUE) || (($this->options['contactform_mandatory_lastname'] ?? TRUE) && isset( $this->options['contactform_mandatory_lastname']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_lastname]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_lastname" name="complex_manager[contactform_mandatory_lastname]" value="1" /></div>'

        ;


    }

    public function contactform_mandatory_legalname_callback()
    {
        $checked = false;
        if ((($this->options['contactform_mandatory_legalname'] ?? TRUE) && isset( $this->options['contactform_mandatory_legalname']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_legalname]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_legalname" name="complex_manager[contactform_mandatory_legalname]" value="1" /></div>'

        ;


    }

    public function recaptcha_v3_callback()
    {
        $checked = false;
        if ((($this->options['recaptcha_v3'] ?? TRUE) && isset( $this->options['recaptcha_v3']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[recaptcha_v3]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="recaptcha_v3" name="complex_manager[recaptcha_v3]" value="1" /></div>'

        ;
    }

    public function recaptcha_score_callback()
    {
        printf(
            '<input type="number" step="0.1" id="recaptcha_score" name="complex_manager[recaptcha_score]" value="%s" />',
            isset( $this->options['recaptcha_score'] ) ? esc_attr( $this->options['recaptcha_score']) : '0.4'
        );

    }

    public function honeypot_callback()
    {
        $checked = false;
        if ((($this->options['honeypot'] ?? TRUE) && isset( $this->options['honeypot']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[honeypot]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="honeypot" name="complex_manager[honeypot]" value="1" /></div>'

        ;

    }

    public function contactform_mandatory_phone_callback()
    {
        $checked = false;
        if (($this->options['contactform_mandatory_phone'] ?? TRUE) || (($this->options['contactform_mandatory_phone'] ?? TRUE) && isset( $this->options['contactform_mandatory_phone']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_phone]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_phone" name="complex_manager[contactform_mandatory_phone]" value="1" /></div>'

        ;


    }

    public function contactform_mandatory_mobile_callback()
    {
        $checked = false;
        if ((($this->options['contactform_mandatory_mobile'] ?? TRUE) && isset( $this->options['contactform_mandatory_mobile']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_mobile]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_mobile" name="complex_manager[contactform_mandatory_mobile]" value="1" /></div>'

        ;


    }

    public function contactform_mandatory_street_callback()
    {
        $checked = false;
        if (($this->options['contactform_mandatory_street'] ?? TRUE) || (($this->options['contactform_mandatory_street'] ?? TRUE) && isset( $this->options['contactform_mandatory_street']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_street]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_street" name="complex_manager[contactform_mandatory_street]" value="1" /></div>'

        ;


    }

    public function contactform_mandatory_zip_callback()
    {
        $checked = false;
        if (($this->options['contactform_mandatory_zip'] ?? TRUE) || (($this->options['contactform_mandatory_zip'] ?? TRUE) && isset( $this->options['contactform_mandatory_zip']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_zip]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_zip" name="complex_manager[contactform_mandatory_zip]" value="1" /></div>'

        ;


    }

    public function contactform_mandatory_locality_callback()
    {
        $checked = false;
        if (($this->options['contactform_mandatory_locality'] ?? TRUE) || (($this->options['contactform_mandatory_locality'] ?? TRUE) && isset( $this->options['contactform_mandatory_locality']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_locality]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_locality" name="complex_manager[contactform_mandatory_locality]" value="1" /></div>'

        ;


    }

    public function contactform_mandatory_message_callback()
    {
        $checked = false;
        if ((($this->options['contactform_mandatory_message'] ?? TRUE) && isset( $this->options['contactform_mandatory_message']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[contactform_mandatory_message]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="contactform_mandatory_message" name="complex_manager[contactform_mandatory_message]" value="1" /></div>'

        ;


    }

    public function provider_slug_callback()
    {
        printf(
            '<input type="text" id="provider_slug" name="complex_manager[provider_slug]" value="%s" />',
            isset( $this->options['provider_slug'] ) ? esc_attr( $this->options['provider_slug']) : ''
        );
    }

    public function publisher_slug_callback()
    {
        printf(
            '<input type="text" id="publisher_slug" name="complex_manager[publisher_slug]" value="%s" />',
            isset( $this->options['publisher_slug'] ) ? esc_attr( $this->options['publisher_slug']) : ''
        );
    }

    public function thousands_seperator_callback()
    {
        printf(
            '<input type="text" id="thousands_seperator" name="complex_manager[thousands_seperator]" value="%s" />',
            isset( $this->options['thousands_seperator'] ) ? esc_attr( $this->options['thousands_seperator']) : ''
        );
    }

    public function space_decimal_callback()
    {
        printf(
            '<input type="number" id="space_decimal" name="complex_manager[space_decimal]" value="%s" />',
            isset( $this->options['space_decimal'] ) ? esc_attr( $this->options['space_decimal']) : ''
        );
    }

    public function flex_list_callback()
    {

        $checked = false;
        if ((($this->options['flex_list'] ?? TRUE) && isset( $this->options['flex_list']))) {
            $checked = true;
        }
        echo
            '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[flex_list]" value="0" />
            <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="flex_list" name="complex_manager[flex_list]" value="1" /></div>'

        ;

    }

    public function remcat_callback()
    {
        printf(
            '<input type="text" id="remcat" name="complex_manager[remcat]" value="%s" />',
            isset( $this->options['remcat'] ) ? esc_attr( $this->options['remcat']) : ''
        );
    }

    public function remcat_website_callback()
    {
        printf(
            '<input type="text" id="remcat_website" name="complex_manager[remcat_website]" value="%s" />',
            isset( $this->options['remcat_website'] ) ? esc_attr( $this->options['remcat_website']) : ''
        );
    }

    public function remcat_company_callback()
    {
        printf(
            '<input type="text" id="remcat_company" name="complex_manager[remcat_company]" value="%s" />',
            isset( $this->options['remcat_company'] ) ? esc_attr( $this->options['remcat_company']) : ''
        );
    }

    public function remcat_company_street_callback()
    {
        printf(
            '<input type="text" id="remcat_company_street" name="complex_manager[remcat_company_street]" value="%s" />',
            isset( $this->options['remcat_company_street'] ) ? esc_attr( $this->options['remcat_company_street']) : ''
        );
    }

    public function remcat_company_postal_code_callback()
    {
        printf(
            '<input type="text" id="remcat_company_postal_code" name="complex_manager[remcat_company_postal_code]" value="%s" />',
            isset( $this->options['remcat_company_postal_code'] ) ? esc_attr( $this->options['remcat_company_postal_code']) : ''
        );
    }

    public function remcat_company_locality_callback()
    {
        printf(
            '<input type="text" id="remcat_company_locality" name="complex_manager[remcat_company_locality]" value="%s" />',
            isset( $this->options['remcat_company_locality'] ) ? esc_attr( $this->options['remcat_company_locality']) : ''
        );
    }

    public function remcat_company_person_name_callback()
    {
        printf(
            '<input type="text" id="remcat_company_person_name" name="complex_manager[remcat_company_person_name]" value="%s" />',
            isset( $this->options['remcat_company_person_name'] ) ? esc_attr( $this->options['remcat_company_person_name']) : ''
        );
    }

    public function remcat_company_email_callback()
    {
        printf(
            '<input type="text" id="remcat_company_email" name="complex_manager[remcat_company_email]" value="%s" />',
            isset( $this->options['remcat_company_email'] ) ? esc_attr( $this->options['remcat_company_email']) : ''
        );
    }

    public function remcat_general_property_ref_callback()
    {
        printf(
            '<input type="text" id="remcat_general_property_ref" name="complex_manager[remcat_general_property_ref]" value="%s" />',
            isset( $this->options['remcat_general_property_ref'] ) ? esc_attr( $this->options['remcat_general_property_ref']) : ''
        );
    }

    public function idx_ref_property_callback()
    {
        printf(
            '<input type="text" id="idx_ref_property" name="complex_manager[idx_ref_property]" value="%s" />',
            isset( $this->options['idx_ref_property'] ) ? esc_attr( $this->options['idx_ref_property']) : ''
        );
    }

    public function google_analytics_callback()
    {
        printf(
            '<input type="text" id="gap_id" name="complex_manager[gap_id]" placeholder="UA-######-#" value="%s" /> %s',
            isset( $this->options['gap_id'] ) ? esc_attr( $this->options['gap_id']) : '',
            esc_html__( 'Used to send events.', 'complexmanager' )
        );
    }

    public function project_image_callback()
    {
        $image_src = false;
        $set = false;
        $value = (isset( $this->options['project_image'] ) ? esc_attr( $this->options['project_image']) : '');
        if ($value) {
            $image_attributes = wp_get_attachment_image_src( $value, 'medium' ); // returns an array
            if ($image_attributes) {
                $set = true;
                $image_src = $image_attributes[0];
            }
        }

        /*if (!$set) {
            echo "<strong>Sehr Wichtig!!!</strong>";
        }*/

        printf(
            '
            <img src="%s" id="complex_upload_project_image_src" /><br>
            <input type="hidden" id="complex_upload_project_image" name="complex_manager[project_image]" value="%s" />
            <input id="complex_upload_project_image_button" type="button" class="complex_image_upload button" value="' . esc_attr__( 'Upload Image', 'complexmanager' ) . '" />
            ',
            $image_src,
            $value
        );
    }

    public function list_cols_callback()
    {
        //$cols = $this->get_list_col_defaults();
        $cols = cxm_get_list_col_defaults();


        //add linguistical attribute defaults?
        $extra_langs = array();
        $defaultlang = get_bloginfo('language');
        if (function_exists('icl_get_languages')) {
            $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($langs as $iso => $options) {
                $extra_langs[$iso] = $options;
            }
        } else {
        }
        foreach ($cols as $key => $data) {
            foreach ($extra_langs as $iso => $options) {
                $cols[$key]['label_'.$iso] = '';
            }
        }


        $cur_array = maybe_unserialize( $this->options['list_cols'] ?? NULL);
        if ($cur_array && is_array($cur_array)) {
            foreach ($cur_array as $col => $options) {
                if (isset($cols[$col])) {
                    foreach ($options as $option_key => $option_value) {
                        if (isset($cols[$col][$option_key])) {
                            $cols[$col][$option_key] = $option_value;
                        }
                    }

                }
            }
        }

            /*if (isset( $this->options['list_cols'] ) && is_array($this->options['list_cols'])) {
                print_r($this->options['list_cols']);
            }*/

        $extra_langs = array();
        $defaultlang = get_bloginfo('language');
        if (function_exists('icl_get_languages')) {
            $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($langs as $iso => $options) {
                $extra_langs[$iso] = $options;
            }
        } else {
            $extra_langs[substr($defaultlang, 0, 2)] = array(
                'code' => substr($defaultlang, 0, 2),
                'id' => '',
                'native_name' => '',
                'major' => '1',
                'active' => '',
                'default_locale' => $defaultlang,
                'encode_url' => '',
                'tag' => $defaultlang,
                'translated_name' => '',
                'url' => '',
                'country_flag_url' => '',
                'language_code' => substr($defaultlang, 0, 2),
            );
        }

        $th_langs = '';
        foreach ($extra_langs as $iso => $options) {
             if (substr($defaultlang, 0, 2)== $iso) {
                $th_langs .= '<th>' . esc_html__( 'Display name', 'complexmanager' ) . '</th>';
            } else {
                $th_langs .= '<th>' . sprintf( esc_html__( 'Display name (%s)', 'complexmanager' ), esc_html( $options['code'] ) ) . '</th>';
            }
        }

        echo '<table class="table">';
            echo '<thead><tr>
                <th>' . esc_html__( 'Field name', 'complexmanager' ) . '</th>
                <th>' . esc_html__( 'Active', 'complexmanager' ) . '</th>
                <th>' . esc_html__( 'Hide on mobile', 'complexmanager' ) . '</th>
                <th>' . esc_html__( 'Hide when reserved', 'complexmanager' ) . '</th>
                '.$th_langs.'
                <th>' . esc_html__( 'Sort order', 'complexmanager' ) . '</th>
            </tr></thead>';

            echo "<tbody>";
            foreach ($cols as $col => $col_options) {
                $td_inputs = '';
                foreach ($extra_langs as $iso => $options) {
                    if (substr($defaultlang, 0, 2)== $iso) {
                        $td_inputs .= '<td><input type="text" style="width:125px" placeholder="'.$col_options['o_label'].'" name="complex_manager[list_cols]['.$col.'][label]" value="'.$col_options['label'].'" /></td>';
                    } else {
                        $td_inputs .= '<td><input type="text" style="width:125px" placeholder="'.$iso.'" name="complex_manager[list_cols]['.$col.'][label_'.$iso.']" value="'.$col_options['label_'.$iso.''].'" /></td>';
                    }
                }
                echo '<tr>
                    <th>'.$col_options['o_label'].'</th>
                    <td><input type="hidden" name="complex_manager[list_cols]['.$col.'][active]" value="0"><input type="checkbox" value="1" name="complex_manager[list_cols]['.$col.'][active]" '.($col_options['active'] ? 'checked="checked"' : '').' /></td>
                    <td><input type="hidden" name="complex_manager[list_cols]['.$col.'][hidden-xs]" value="0"><input type="checkbox" value="1" name="complex_manager[list_cols]['.$col.'][hidden-xs]" '.($col_options['hidden-xs'] ? 'checked="checked"' : '').' /></td>
                    <td><input type="hidden" name="complex_manager[list_cols]['.$col.'][hidden-reserved]" value="0"><input type="checkbox" value="1" name="complex_manager[list_cols]['.$col.'][hidden-reserved]" '.($col_options['hidden-reserved'] ? 'checked="checked"' : '').' /></td>
                    ' . $td_inputs . '
                    <td><input type="number" style="width:75px" name="complex_manager[list_cols]['.$col.'][order]" value="'.$col_options['order'].'" /></td>
                </tr>';
            }
            echo "</tbody>";

        echo "</table>";

        /*printf(
            '<input type="text" id="list_cols" name="complex_manager[list_cols]" value="%s" />',
            isset( $this->options['list_cols'] ) ? esc_attr( $this->options['list_cols']) : ''
        );*/
    }

    public function list_filters_callback(){
        $filters = cxm_get_filter_label_defaults();

        //add linguistical attribute defaults?
        $extra_langs = array();
        $defaultlang = get_bloginfo('language');
        if (function_exists('icl_get_languages')) {
            $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($langs as $iso => $options) {
                $extra_langs[$iso] = $options;
            }
        } else {
        }
        foreach ($filters as $key => $data) {
            foreach ($extra_langs as $iso => $options) {
                $filters[$key]['label_'.$iso] = '';
            }
        }

        $cur_array = maybe_unserialize( $this->options['list_filters'] ?? NULL);
        if ($cur_array && is_array($cur_array)) {
            foreach ($cur_array as $col => $options) {
                if (isset($filters[$col])) {
                    foreach ($options as $option_key => $option_value) {
                        if (isset($filters[$col][$option_key])) {
                            $filters[$col][$option_key] = $option_value;
                        }
                    }
                }
            }
        }

        $extra_langs = array();
        $defaultlang = get_bloginfo('language');
        if (function_exists('icl_get_languages')) {
            $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($langs as $iso => $options) {
                $extra_langs[$iso] = $options;
            }
        } else {
            $extra_langs[substr($defaultlang, 0, 2)] = array(
                'code' => substr($defaultlang, 0, 2),
                'id' => '',
                'native_name' => '',
                'major' => '1',
                'active' => '',
                'default_locale' => $defaultlang,
                'encode_url' => '',
                'tag' => $defaultlang,
                'translated_name' => '',
                'url' => '',
                'country_flag_url' => '',
                'language_code' => substr($defaultlang, 0, 2),
            );
        }

        $th_langs = '';
        foreach ($extra_langs as $iso => $options) {
             if (substr($defaultlang, 0, 2)== $iso) {
                $th_langs .= '<th>' . esc_html__( 'Filter label', 'complexmanager' ) . '</th>';
            } else {
                $th_langs .= '<th>' . sprintf( esc_html__( 'Filter label (%s)', 'complexmanager' ), esc_html( $options['code'] ) ) . '</th>';
            }
        }

        echo '<table class="table">';
            echo '<thead><tr>
                <th>' . esc_html__( 'Filter', 'complexmanager' ) . '</th>
                '.$th_langs.'
            </tr></thead>';

            echo "<tbody>";
            foreach ($filters as $col => $col_options) {
                $td_inputs = '';
                foreach ($extra_langs as $iso => $options) {
                    if (substr($defaultlang, 0, 2)== $iso) {
                        $td_inputs .= '<td><input type="text" style="width:200px" placeholder="'.$col_options['o_label'].'" name="complex_manager[list_filters]['.$col.'][label]" value="'.$col_options['label'].'" /></td>';
                    } else {
                        $td_inputs .= '<td><input type="text" style="width:200px" placeholder="'.$iso.'" name="complex_manager[list_filters]['.$col.'][label_'.$iso.']" value="'.$col_options['label_'.$iso.''].'" /></td>';
                    }
                }
                echo '<tr>
                    <th>'.$col_options['o_label'].'</th>
                    ' . $td_inputs . '
                </tr>';
            }
            echo "</tbody>";

        echo "</table>";
    }

    public function translate_labels_callback(){

        $labels = [
            'download_file' => [
                'o_label' => __( 'Download', 'complexmanager' ),
                'label' => '',
                'order' => 1
            ],
            'link' => [
                'o_label' => __( 'Link', 'complexmanager' ),
                'label' => '',
                'order' => 2
            ],
            'virtual_tour' => [
                'o_label' => __( 'Virtual tour', 'complexmanager' ),
                'label' => '',
                'order' => 3
            ]
        ];

        //add linguistical attribute defaults?
        $extra_langs = array();
        $defaultlang = get_bloginfo('language');
        if (function_exists('icl_get_languages')) {
            $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($langs as $iso => $options) {
                $extra_langs[$iso] = $options;
            }
        }

        foreach ($labels as $key => $data) {
            foreach ($extra_langs as $iso => $options) {
                $labels[$key]['label_'.$iso] = '';
            }
        }

        $cur_array = maybe_unserialize( $this->options['translate_labels'] ?? NULL);
        if ($cur_array && is_array($cur_array)) {
            foreach ($cur_array as $col => $options) {
                if (isset($labels[$col])) {
                    foreach ($options as $option_key => $option_value) {
                        if (isset($labels[$col][$option_key])) {
                            $labels[$col][$option_key] = $option_value;
                        }
                    }
                }
            }
        }

        $extra_langs = array();
        $defaultlang = get_bloginfo('language');
        if (function_exists('icl_get_languages')) {
            $langs = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($langs as $iso => $options) {
                $extra_langs[$iso] = $options;
            }
        } else {
            $extra_langs[substr($defaultlang, 0, 2)] = array(
                'code' => substr($defaultlang, 0, 2),
                'id' => '',
                'native_name' => '',
                'major' => '1',
                'active' => '',
                'default_locale' => $defaultlang,
                'encode_url' => '',
                'tag' => $defaultlang,
                'translated_name' => '',
                'url' => '',
                'country_flag_url' => '',
                'language_code' => substr($defaultlang, 0, 2),
            );
        }

        $th_langs = '';
        foreach ($extra_langs as $iso => $options) {
             if (substr($defaultlang, 0, 2)== $iso) {
                $th_langs .= '<th>' . esc_html__( 'Label', 'complexmanager' ) . '</th>';
            } else {
                $th_langs .= '<th>' . sprintf( esc_html__( 'Label (%s)', 'complexmanager' ), esc_html( $options['code'] ) ) . '</th>';
            }
        }

        echo '<table class="table">';
            echo '<thead><tr>
                <th>' . esc_html__( 'Field', 'complexmanager' ) . '</th>
                '.$th_langs.'
            </tr></thead>';

            echo "<tbody>";
            foreach ($labels as $col => $col_options) {
                $td_inputs = '';
                foreach ($extra_langs as $iso => $options) {
                    if (substr($defaultlang, 0, 2)== $iso) {
                        $td_inputs .= '<td><input type="text" style="width:200px" placeholder="'.$col_options['o_label'].'" name="complex_manager[translate_labels]['.$col.'][label]" value="'.$col_options['label'].'" /></td>';
                    } else {
                        $td_inputs .= '<td><input type="text" style="width:200px" placeholder="'.$iso.'" name="complex_manager[translate_labels]['.$col.'][label_'.$iso.']" value="'.$col_options['label_'.$iso.''].'" /></td>';
                    }
                }
                echo '<tr>
                    <th>'.$col_options['o_label'].'</th>
                    ' . $td_inputs . '
                </tr>';
            }
            echo "</tbody>";

        echo "</table>";
    }

    public function import_flatfox_callback(){
        $project_id = absint( $this->options['cxm_flatfox_project_id'] ?? 0 );
		$organization_slug = $this->options['cxm_flatfox_organization_slug'] ?? '';
        $import_url = wp_nonce_url(
            admin_url( 'admin-post.php?action=cxm_run_flatfox_import' ),
            'cxm_run_flatfox_import'
        );
        $confirmation = __( 'This permanently deletes the selected units. Shared buildings and media will be kept. Continue?', 'complexmanager' );
        ?>
        <div class="cxm-import-source cxm-import-source--flatfox">
            <h3><?php esc_html_e( 'General settings', 'complexmanager' ); ?></h3>
            <fieldset>
                <legend class="screen-reader-text"><span><?php esc_html_e( 'Flatfox project ID', 'complexmanager' ); ?></span></legend>
                <p><label for="cxm_flatfox_project_id"><?php esc_html_e( 'Flatfox project ID', 'complexmanager' ); ?></label></p>
                <p>
                    <input type="number" min="1" step="1" id="cxm_flatfox_project_id" name="complex_manager[cxm_flatfox_project_id]" value="<?php echo esc_attr( $project_id ); ?>" class="small-text" />
                    <a class="button button-secondary" href="<?php echo esc_url( $import_url ); ?>"><?php esc_html_e( 'Run Flatfox import', 'complexmanager' ); ?></a>
                </p>
                <p><label for="cxm_flatfox_organization_slug"><?php esc_html_e( 'Flatfox organization slug', 'complexmanager' ); ?></label></p>
                <p>
                    <input type="text" id="cxm_flatfox_organization_slug" name="complex_manager[cxm_flatfox_organization_slug]" value="<?php echo esc_attr( $organization_slug ); ?>" class="regular-text" placeholder="xaver-meyer-ag64" />
                </p>
                <p class="description"><?php esc_html_e( 'Use the organization slug from Flatfox’s exported submit links. It creates the canonical Flatfox contact URL and further limits the public listing query.', 'complexmanager' ); ?></p>
                <p class="description"><?php esc_html_e( 'Uses Flatfox’s public listing API. The configured project is synchronized hourly; no API key is required.', 'complexmanager' ); ?></p>
            </fieldset>

        </div>
        <?php
    }

    public function import_emonitor_callback(){
        ?>
        

        

        <div class="cxm-import-source cxm-import-source--emonitor">
            <h3><?php esc_html_e( 'General settings', 'complexmanager' ); ?></h3>
                <fieldset>
                    <table>
                        <tr>
                            <?php if (($this->options['cxm_emonitor_api'] ?? FALSE)): ?>
                                <td><code><strong>eMonitor</strong></code></td>
                            <?php else: ?>
                                <td><strike><code><strong>eMonitor</strong></code></strike></td>
                            <?php endif ?>
                            <td><button type="submit" class="button button-secondary" form="cxm-run-emonitor-import-standard"><?php esc_html_e( 'Run import', 'complexmanager' ); ?></button></td>
                        </tr>
                        <tr>
                            <?php $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml'; if (file_exists($file)) : ?>
                                <td><code>data.xml</code></td>
                            <?php else: ?>
                                <td><strike><code>data.xml</code></strike></td>
                            <?php endif ?>
                            <td><button type="submit" class="button button-secondary" form="cxm-run-emonitor-import-overwrite"><?php esc_html_e( 'Run import and overwrite units', 'complexmanager' ); ?></button></td>
                        </tr>
                         <tr>
                            <td><label for="cxm_force_property_update"><?php esc_html_e( 'Always overwrite units during automatic updates', 'complexmanager' ); ?></label></td>
                            <td> 
                                <?php
                                $checked = !empty( $this->options['cxm_force_property_update'] );
                                echo
                                    '<div class="form-field-mandatory">
                                        <input type="hidden" name="complex_manager[cxm_force_property_update]" value="0" />
                                        <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="cxm_force_property_update" name="complex_manager[cxm_force_property_update]" value="1" />
                                    </div>'

                                ;

                                ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                
                <fieldset>
                    <legend class="screen-reader-text"><span><?php esc_html_e( 'eMonitor API endpoint', 'complexmanager' ); ?></span></legend>
                    <?php $name = 'cxm_emonitor_api'; ?>
                    <p><?php esc_html_e( 'eMonitor API endpoint', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'Disabled', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>" class="large-text code" rows="2" cols="50"  />
                    </p>
                </fieldset>

                <fieldset>
                    <legend class="screen-reader-text"><span><?php esc_html_e( 'Exclude existing buildings from import cleanup (comma-separated)', 'complexmanager' ); ?></span></legend>
                    <?php $name = 'cxm_exclude_buildings'; ?>
                    <p><?php esc_html_e( 'Exclude existing buildings from import cleanup (comma-separated)', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'Building IDs (comma-separated)', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>" class="large-text code" rows="2" cols="50"  />
                    </p>
                </fieldset>

                <h3><?php esc_html_e( 'Version 2 settings', 'complexmanager' ); ?></h3>
                <fieldset>
                    <?php $name = 'cxm_emonitor_custom1_matching'; ?>
                    <p><?php esc_html_e( 'Map Custom 1 from an eMonitor field', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'eMonitor field key', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>"  />
                    </p>
                </fieldset>

                <fieldset>
                    <?php $name = 'cxm_emonitor_custom2_matching'; ?>
                    <p><?php esc_html_e( 'Map Custom 2 from an eMonitor field', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'eMonitor field key', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>"  />
                    </p>
                </fieldset>

                <fieldset>
                    <?php $name = 'cxm_emonitor_custom3_matching'; ?>
                    <p><?php esc_html_e( 'Map Custom 3 from an eMonitor field', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'eMonitor field key', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>"  />
                    </p>
                </fieldset>

                <h3><?php esc_html_e( 'Version 1 settings', 'complexmanager' ); ?></h3>
                <fieldset>
                    <table>
                        <tr>
                            <td><code><strong><?php esc_html_e( 'Separate buildings by property type', 'complexmanager' ); ?></strong></code></td>
                            <td>
                                <?php

                                $checked = false;
                                if ((($this->options['separate_building_property_type'] ?? TRUE) && isset( $this->options['separate_building_property_type']))) {
                                    $checked = true;
                                }
                                echo
                                    '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[separate_building_property_type]" value="0" />
                                    <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="separate_building_property_type" name="complex_manager[separate_building_property_type]" value="1" /></div>'

                                ; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><code><strong><?php esc_html_e( 'Store commercial price/m² and ancillary costs/m² in Custom 2 and Custom 3', 'complexmanager' ); ?></strong></code></td>
                            <td>
                                <?php

                                $checked = false;
                                if ((($this->options['squaremeterprices'] ?? TRUE) && isset( $this->options['squaremeterprices']))) {
                                    $checked = true;
                                }
                                echo
                                    '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[squaremeterprices]" value="0" />
                                    <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="squaremeterprices" name="complex_manager[squaremeterprices]" value="1" /></div>'

                                ; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><code><strong><?php esc_html_e( 'Store property type in Custom 1', 'complexmanager' ); ?></strong></code></td>
                            <td>
                                <?php

                                $checked = false;
                                if ((($this->options['propertytype'] ?? TRUE) && isset( $this->options['propertytype']))) {
                                    $checked = true;
                                }
                                echo
                                    '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[propertytype]" value="0" />
                                    <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="propertytype" name="complex_manager[propertytype]" value="1" /></div>'

                                ; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><code><strong><?php esc_html_e( 'Store virtual tour in Custom 2', 'complexmanager' ); ?></strong></code></td>
                            <td>
                                <?php

                                $checked = false;
                                if ((($this->options['virtualtour'] ?? TRUE) && isset( $this->options['virtualtour']))) {
                                    $checked = true;
                                }
                                echo
                                    '<div class="form-field-mandatory"><input type="hidden" name="complex_manager[virtualtour]" value="0" />
                                    <input type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' id="virtualtour" name="complex_manager[virtualtour]" value="1" /></div>'

                                ; ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset>
                    <legend class="screen-reader-text"><span><?php esc_html_e( 'Override eMonitor download label', 'complexmanager' ); ?></span></legend>
                    <?php $name = 'cxm_emonitor_rewrite_download_label'; ?>
                    <p><?php esc_html_e( 'Override eMonitor download label', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'Floor plan', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>" class=""  />
                    </p>
                </fieldset>

                <fieldset>
                    <legend class="screen-reader-text"><span><?php esc_html_e( 'Override eMonitor link label', 'complexmanager' ); ?></span></legend>
                    <?php $name = 'cxm_emonitor_rewrite_link_label'; ?>
                    <p><?php esc_html_e( 'Override eMonitor link label', 'complexmanager' ); ?></p>
                    <p>
                        <input type="text" placeholder="<?php echo esc_attr__( 'Apply online now', 'complexmanager' ); ?>" name="complex_manager[<?php echo $name ?>]" value="<?= $this->options[$name] ?? NULL ?>" id="<?php echo $name; ?>" class=""  />
                    </p>
                </fieldset>
        </div>
        <?php
    }

    public function import_clear_units_callback(){
        $confirmation = __( 'This permanently deletes the selected units. Shared buildings and media will be kept. Continue?', 'complexmanager' );
        ?>
        <div class="cxm-import-source cxm-import-source--clear">
            <p class="description"><?php esc_html_e( 'These actions permanently delete only the selected source’s units. Shared buildings and media are kept.', 'complexmanager' ); ?></p>
            <p>
                <button
                    type="submit"
                    class="button button-secondary button-link-delete"
                    form="cxm-clear-units-manual"
                    onclick="return confirm('<?php echo esc_js( $confirmation ); ?>');"
                ><?php esc_html_e( 'Clear manually created units', 'complexmanager' ); ?></button>
                <button
                    type="submit"
                    class="button button-secondary button-link-delete"
                    form="cxm-clear-units-emonitor"
                    onclick="return confirm('<?php echo esc_js( $confirmation ); ?>');"
                ><?php esc_html_e( 'Clear eMonitor units', 'complexmanager' ); ?></button>
                <button
                    type="submit"
                    class="button button-secondary button-link-delete"
                    form="cxm-clear-units-flatfox"
                    onclick="return confirm('<?php echo esc_js( $confirmation ); ?>');"
                ><?php esc_html_e( 'Clear Flatfox units', 'complexmanager' ); ?></button>
            </p>
        </div>
        <?php
    }


    //filter
    public function filter_income_max_callback()
    {
        printf(
            '<input type="text" id="filter_income_max" name="complex_manager[filter_income_max]" value="%s" />',
            isset( $this->options['filter_income_max'] ) ? esc_attr( $this->options['filter_income_max']) : ''
        );
    }

    
    public function recaptcha_callback()
    {
        printf(
            '<input type="text" id="recaptcha" name="complex_manager[recaptcha]" value="%s" />',
            isset( $this->options['recaptcha'] ) ? esc_attr( $this->options['recaptcha']) : ''
        );

    }

    public function recaptcha_secret_callback()
    {
        printf(
            '<input type="text" id="recaptcha_secret" name="complex_manager[recaptcha_secret]" value="%s" />',
            isset( $this->options['recaptcha_secret'] ) ? esc_attr( $this->options['recaptcha_secret']) : ''
        );

    }

    public function set_standard_terms(){
        if (isset($_GET['generate_defaults']) || isset($_POST['generate_defaults'])) {
            wp_insert_term( __( 'Search engines', 'complexmanager' ), 'inquiry_reason', $args = array() );
            wp_insert_term( __( 'Real-estate platform', 'complexmanager' ), 'inquiry_reason', $args = array() );
            wp_insert_term( __( 'Events / advertisements', 'complexmanager' ), 'inquiry_reason', $args = array() );
            wp_insert_term( __( 'Suggested personally', 'complexmanager' ), 'inquiry_reason', $args = array() );
        }
    }
}

add_action( 'complexmanager_init', array( 'casasoft\complexmanager\general_options', 'init' ) );
