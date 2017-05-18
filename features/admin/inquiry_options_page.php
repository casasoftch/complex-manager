<?php
namespace casasoft\complexmanager;


class inquiry_options_page extends Feature {

	public function __construct() {
		if( function_exists('acf_add_local_field_group') ):
			acf_add_options_sub_page(array(
				'title' => 'Anfrage Optionen',
				'parent' => 'edit.php?post_type=complex_inquiry'
			));




			$fields = array();
			if (isset($_GET['csv-download'])) {
				$fields[] =  array (
					'key' => 'field_as9d78fg9as8df9as8gdf',
					'label' => 'csv-download',
					'name' => '',
					'type' => 'message',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => 'CSV DOWNLOADED',
					'new_lines' => 'wpautop',
					'esc_html' => 0,
				);
			} else {
				$fields[] =  array (
					'key' => 'field_lsjdijfijdifjidjif',
					'label' => 'csv-download',
					'name' => '',
					'type' => 'message',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '<a href="?page=acf-options-anfrage-optionen&csv-download=1">CSV Herunterladen</a>',
					'new_lines' => 'wpautop',
					'esc_html' => 0,
				);
			}


			acf_add_local_field_group(array (
				'key' => 'group_5919659ee3192',
				'title' => 'Anfrage Optionen',
				'fields' => $fields,
				'location' => array (
					array (
						array (
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'acf-options-anfrage-optionen',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => 1,
				'description' => '',
			));



		endif;

	}

}
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\inquiry_options_page', 'init' ));
