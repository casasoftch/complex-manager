<?php
namespace casasoft\complexmanager;


class inquiry_options_page extends Feature {

	public $prefix = 'complexmanager_inquiry_';

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



		if (isset($_GET['csv-download'])) {


			$inquiries = get_posts(array(
		    'post_type' => 'complex_inquiry',
		    'posts_per_page'   => -1,
		    'post_status' => 'any',
		    'post_parent' => 0,
		    //'orderby' => 'menu_order',
		    //'order' => 'asc'
		  ));


			$rows = [];
			foreach ($inquiries as $inq) {
	      $row = array();

        $row['ID'] = $inq->ID;
				$row['Titel'] = $inq->post_title;





				$fields = array(
					'reason',
					'unit_id',
					'gender',
					'message',
					'email',
					'locality',
					'postal_code',
					'street',
					'mobile',
					'phone',
					'legal_name',
					'last_name',
					'first_name'
				);
				foreach ($fields as $field) {
					$key = $this->prefix.$field;
					$value = get_post_meta( $inq->ID, '_'.$key, true );
					$row[get_cxm_label(false, $field, 'complex_inquiry')] = str_replace("\r", "", str_replace("\n", '' , nl2br($value)));
				}

				$key = $this->prefix.'extra_data';
				$value = get_post_meta( $inq->ID, '_'.$key, true );
				$extra_content = '';
				foreach ($value as $key => $value) {
					$extra_content .= str_replace("'", "'", $key) . ': ' . str_replace("\r", "", str_replace("\n", '' , nl2br($value))) ;  //\n 
				}
				$row['Extra'] = $extra_content;



        $rows[] = $row;
			}


			$fp = fopen(wp_upload_dir() . 'anfragen.csv', 'w');
			$BOM = "\xEF\xBB\xBF"; // UTF-8 BOM
			fwrite($export); //, "sep=\t".PHP_EOL
			fwrite($fp, $BOM);
			if ($rows) {
			  fputcsv($fp, array_keys($rows[0]));

			  foreach ($rows as $fields) {
			      fputcsv($fp, $fields);
			  }
			}

		  fclose($fp);

	    header('Content-Type: application/octet-stream');
	    header("Content-Transfer-Encoding: Binary");
	    header("Content-disposition: attachment; filename=\"" . 'anfragen.csv' . "\"");
	    echo file_get_contents(wp_upload_dir() . 'anfragen.csv');

		  die();
		}


	}



}
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\inquiry_options_page', 'init' ));
