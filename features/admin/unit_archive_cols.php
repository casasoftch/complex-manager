<?php
namespace casasoft\complexmanager;


class unit_archive_cols extends Feature {

	public function __construct() {
		add_filter( 'manage_complex_unit_posts_columns', array($this,'set_columns'), 4);
		add_action( 'manage_complex_unit_posts_custom_column', array($this,'set_columns_content'), 4, 2);
	}

	// Create an ID Column in the News post type
	
	public function set_columns($defaults){
		/*[cb] => <input type="checkbox" />
	    [title] => Titel
	    [taxonomy-building] => Gebäude
	    [taxonomy-unit_type] => Typen
	    [date] => Datum
	    [custom_header] => custom header*/
    	unset($defaults['date']);
    	unset($defaults['taxonomy-unit_type']);

    	$cols = maybe_unserialize((maybe_unserialize($this->get_option("list_cols"))));
	   	if (!$cols || !is_array($cols)) {
	   		$cols = array();
	   	} else {
	   		//sort
			uasort($cols, function($a, $b){
				return $a["order"] - $b["order"];
			});
	   	}
	   	unset($cols['name']);
    	foreach ($cols as $field => $col){
			if ($col['active']){
				$defaults[$field] = nl2br(str_replace('\n', "\n", ($col['label'] ? $col['label'] : get_cxm_label(false, $field, 'complex_unit') ) ) );
			}
		}

		$defaults['files'] = 'Dateien';

		return $defaults;
	}

	
	public function set_columns_content($column_name, $id){
		if ($column_name == 'files') {
			$download_file = get_cxm($id, 'download_file');
			echo basename(get_cxm($id, 'download_file'));
		} else{


			$cols = maybe_unserialize((maybe_unserialize($this->get_option("list_cols"))));
		   	if (!$cols || !is_array($cols)) {
		   		$cols = array();
		   	} else {
		   		//sort
				uasort($cols, function($a, $b){
					return $a["order"] - $b["order"];
				});
		   	}
		   	unset($cols['name']);
	    	foreach ($cols as $field => $col){
	    		if($column_name === $field){
					if ($col['active']){
						switch ($field) {
							case 'status':
								$value = '';
								$status = get_cxm($id, 'status');
								switch ($status) {
									case 'available': $value = '<span class="text-success">'.strtolower(__('Available', 'complexmanager')).'</span>'; break;
									case 'reserved': $value = '<span class="text-'.$status.'">'.strtolower(__('Reserved', 'complexmanager')).'</span>'; break;
									case 'rented': $value = '<span class="text-'.$status.'">'.strtolower(__('Rented', 'complexmanager')).'</span>'; break;
									case 'sold': $value = '<span class="text-'.$status.'">'.strtolower(__('Sold', 'complexmanager')).'</span>'; break;
									default: $value = $status;
								}
								echo $value;
								break;
							default:
								
								echo get_cxm($id, $field);	
								break;
						}
					}
				}
			}
		}
	}

}
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\unit_archive_cols', 'init' ));
//add_action( 'manage_complex_unit_posts_custom_column', array( 'casasoft\complexmanager\unit_archive_cols', 'init' ));