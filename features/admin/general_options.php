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
         wp_register_script('complex-manager-options', PLUGIN_URL . 'assets/js/complex-manager-options.js',  array('jquery') );
 
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
            __( 'Complex Manager Settings', 'complexmanager' ), 
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
                    do_settings_sections( 'complex-manager-admin' );
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
            'cxm_1', // ID
            __( 'Main Settings', 'complexmanager' ), // Title
            array( $this, 'print_section_info' ), // Callback
            'complex-manager-admin' // Page
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
            'global_direct_recipient_email', 
             __( '<strong>CASA</strong><span style="font-weight:100">MAIL</span> direkte E-Mail', 'complexmanager' ), 
            array( $this, 'global_direct_recipient_email_callback' ), 
            'complex-manager-admin', 
            'cxm_1'
        );  

        add_settings_field(
            'provider_slug', 
             __( '<strong>CASA</strong><span style="font-weight:100">MAIL</span> Provider ID', 'complexmanager' ), 
            array( $this, 'provider_slug_callback' ), 
            'complex-manager-admin', 
            'cxm_1'
        );   

        add_settings_field(
            'publisher_slug', 
             __( '<strong>CASA</strong><span style="font-weight:100">MAIL</span> Publisher ID', 'complexmanager' ), 
            array( $this, 'publisher_slug_callback' ), 
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
            'remcat', 
             __( 'Remcat', 'complexmanager' ), 
            array( $this, 'remcat_callback' ), 
            'complex-manager-admin', 
            'cxm_1'
        );  

        add_settings_field(
            'idx_ref_property', 
             __( 'IDX / REMCat Property Ref.', 'complexmanager' ), 
            array( $this, 'idx_ref_property_callback' ), 
            'complex-manager-admin', 
            'cxm_1'
        );  


        add_settings_field(
            'list_cols', 
             __( 'Column values to show in Lists', 'complexmanager' ), 
            array( $this, 'list_cols_callback' ), 
            'complex-manager-admin', 
            'cxm_1'
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
        if( isset( $input['id_number'] ) ) {
            $new_input['id_number'] = absint( $input['id_number'] );
        }

        if( isset( $input['emails'] ) ) {
            $new_input['emails'] = sanitize_text_field( $input['emails'] );
        }

        if( isset( $input['global_direct_recipient_email'] ) ) {
            $new_input['global_direct_recipient_email'] = sanitize_text_field( $input['global_direct_recipient_email'] );
        }

        if( isset( $input['provider_slug'] ) ) {
            $new_input['provider_slug'] = sanitize_text_field( $input['provider_slug'] );
        }

        if( isset( $input['publisher_slug'] ) ) {
            $new_input['publisher_slug'] = sanitize_text_field( $input['publisher_slug'] );
        }

        if( isset( $input['space_decimal'] ) ) {
            $new_input['space_decimal'] = absint( $input['space_decimal'] );
        }

        if( isset( $input['remcat'] ) ) {
            $new_input['remcat'] = sanitize_text_field( $input['remcat'] );
        }

        if( isset( $input['project_image'] ) ) {
            $new_input['project_image'] = sanitize_text_field( $input['project_image'] );
        }

        if( isset( $input['idx_ref_property'] ) ) {
            $new_input['idx_ref_property'] = sanitize_text_field( $input['idx_ref_property'] );
        }

        if( isset( $input['list_cols'] ) ) {
            $new_input['list_cols'] = maybe_serialize( $input['list_cols'] );
        }

        

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print __( 'Enter your settings below:', 'complexmanager' ); // Title;
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

    public function space_decimal_callback()
    {
        printf(
            '<input type="number" id="space_decimal" name="complex_manager[space_decimal]" value="%s" />',
            isset( $this->options['space_decimal'] ) ? esc_attr( $this->options['space_decimal']) : ''
        );
    }

    public function remcat_callback()
    {
        printf(
            '<input type="text" id="remcat" name="complex_manager[remcat]" value="%s" />',
            isset( $this->options['remcat'] ) ? esc_attr( $this->options['remcat']) : ''
        );
    }

    public function idx_ref_property_callback()
    {   
        printf(
            '<input type="text" id="idx_ref_property" name="complex_manager[idx_ref_property]" value="%s" />',
            isset( $this->options['idx_ref_property'] ) ? esc_attr( $this->options['idx_ref_property']) : ''
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
            <input id="complex_upload_project_image_button" type="button" class="complex_image_upload button" value="' . __( 'Upload Image', 'complexmanager' ) . '" />
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

        
        $cur_array = maybe_unserialize( $this->options['list_cols']);
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
                $th_langs .= '<th>Anzeigename</th>';
            } else {
                $th_langs .= '<th>Anzeigename ' . $options['code'] . '</th>';
            }
        }

        echo '<table class="table">';
            echo '<thead><tr>
                <th>Feldname</th>
                <th>Aktiv</th>
                <th>Mobil verstecken</th>
                <th>Bei reserviert verstecken</th>
                '.$th_langs.'
                <th>Anordnung</th>
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
