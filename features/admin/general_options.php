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
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_menu', array( $this, 'set_standard_terms' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_external_scripts' ) );

        
    }

    public function load_external_scripts(){   
         wp_register_script('complex-manager-options', PLUGIN_URL . 'assets/js/min/complex-manager-options-min.js',  array('jquery') );
 
        wp_enqueue_media();
        //wp_enqueue_script('media-upload');
        wp_enqueue_script('complex-manager-options');
 
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Complex Manager Settings', 
            'manage_options', 
            'complexmanager-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {



        // Set class property
        $this->options = get_option( 'complex_manager' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Complex Manager</h2>           
            <form method="post" action="options.php">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'cxm_general_options' );   
                    do_settings_sections( 'my-setting-admin' );
                    submit_button(); 
                ?>

                <button class="button button-default" type="submit" name="generate_defaults" value="true">Regenerate Default Terms</button>
            </form>
        </div>
        <?php
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

        add_settings_section(
            'setting_section_id', // ID
            'My Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        /*add_settings_field(
            'id_number', // ID
            'ID Number', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );    */  

        add_settings_field(
            'project_image', 
             __( 'Project Image', 'complexmanager' ), 
             array( $this, 'project_image_callback' ),
             'my-setting-admin', 
             'setting_section_id'
        );

        add_settings_field(
            'emails', 
             __( 'Emails', 'complexmanager' ), 
            array( $this, 'emails_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );    

        add_settings_field(
            'remcat', 
             __( 'Remcat', 'complexmanager' ), 
            array( $this, 'remcat_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );  




    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['emails'] ) )
            $new_input['emails'] = sanitize_text_field( $input['emails'] );

        if( isset( $input['remcat'] ) )
            $new_input['remcat'] = sanitize_text_field( $input['remcat'] );

        if( isset( $input['project_image'] ) )
            $new_input['project_image'] = sanitize_text_field( $input['project_image'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
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

    public function remcat_callback()
    {
        printf(
            '<input type="text" id="remcat" name="complex_manager[remcat]" value="%s" />',
            isset( $this->options['remcat'] ) ? esc_attr( $this->options['remcat']) : ''
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

        if (!$set) {
            echo "<strong>Sehr Wichtig!!!</strong>";
        }

        printf(
            '
            <img src="%s" id="complex_upload_project_image_src" /><br>
            <input type="hidden" id="complex_upload_project_image" name="complex_manager[project_image]" value="%s" />
            <input id="complex_upload_project_image_button" type="button" class="complex_image_upload button" value="' . __( 'Upload Image', 'complexmanager' ) . '" />
            ',
            $image_src,
            $value
        );
    }

    public function set_standard_terms(){
        if (isset($_GET['generate_defaults']) || isset($_POST['generate_defaults'])) {
            wp_insert_term( 'Suchmaschinen', 'inquiry_reason', $args = array() );
            wp_insert_term( 'Immobilienplattform', 'inquiry_reason', $args = array() );
            wp_insert_term( 'Events / Anzeigen', 'inquiry_reason', $args = array() );
            wp_insert_term( 'Persönlich vorgeschlagen', 'inquiry_reason', $args = array() );
        }
    }
}

add_action( 'complexmanager_init', array( 'casasoft\complexmanager\general_options', 'init' ) );
