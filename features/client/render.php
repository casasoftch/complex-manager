<?php
namespace casasoft\complexmanager;


class render extends Feature {

	private $buildingsStore = null;

	public $formSendHasAlreadyOccuredDuringThisRequest = false;

	public function __construct() {
		$this->add_action( 'init', 'set_shortcodes' );
		$this->fieldMessages = array(
			'first_name' => __('First name is required', 'complexmanager'),
			'last_name' => __('Last name is required', 'complexmanager'),
			'legal_name' => __('The company is required', 'complexmanager'),
			'email' => __('Email is not valid', 'complexmanager'),
			'phone' => __('A phone number is required', 'complexmanager'),
			'street' => __('A street address is required', 'complexmanager'),
			'postal_code' => __('ZIP is required', 'complexmanager'),
			'locality' =>  __('City is required', 'complexmanager'),
			'message' => __('Message is required', 'complexmanager'),
			'post' => __('Ivalid post', 'complexmanager'),
			'gender' => 'That should not be possible',
			'unit_id' => __('Please choose a unit', 'complexmanager'),//'Bitte wählen Sie eine Wohnung'
		);
		if ($this->get_option('recaptcha') && !$this->get_option('honeypot')) {
			$lang = substr(get_bloginfo('language'), 0, 2);
			wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?hl='.$lang, array(), false, true );
		}
	}

	public function set_shortcodes() {
		add_shortcode( 'CXM-list', array($this, 'shortcode_list'));
		add_shortcode( 'CXM-list-unit', array($this, 'shortcode_list_unit'));
		add_shortcode( 'CXM-graphic', array($this, 'shortcode_graphic'));
		add_shortcode( 'CXM-filter', array($this, 'shortcode_filter'));
	}

	private $formLoaded = false;
	public function isFormLoaded(){
		return $this->formLoaded;
	}
	public function setFormLoaded(){
		$this->formLoaded = true;
	}

	// [CXM-list cols="name,price,rent" labels="Name,Preis,Mietpreis" unit="13" sales="rent" type="3"]
	function shortcode_list( $atts ) {
	    $a = shortcode_atts( array(
	        'cols' => '',
	        'labels' => '',
	        'integrate_form' => "1",
	        'collapsible' => "1",
			'show_image' => "1",
	        'building_id' => false,
	        'class' => '',
	    ), $atts );

	    $a['integrate_form'] = (bool) $a['integrate_form'];
	    $a['collapsible'] = (bool) $a['collapsible'];
		$a['show_image'] = (bool) $a['show_image'];


	    $cols = array();
	    $a_cols = explode(',', $a['cols']);
	    $a_labels = explode(',', $a['labels']);
	    $i = 0; foreach ($a_cols as $a_col) {
	    	foreach (get_default_cxm('unit') as $key => $col) {
	    		if ($key == trim($a_col)) {
	    			if (isset($a_labels[$i]) && trim($a_labels[$i]) != '{T}' && trim($a_labels[$i]) != '') {
	    				$col['label'] = trim($a_labels[$i]);
	    			}
	    			$cols[] = array('field' => $key, 'label' => $col['label']);
	    			break;
	    		}
	    	}
	    $i++; }

	    $cols = maybe_unserialize((maybe_unserialize($this->get_option("list_cols"))));
	   	if (!$cols || !is_array($cols)) {
	   		$cols = array();
	   	} else {
	   		//sort
			uasort($cols, function($a, $b){
				if (is_numeric($a["order"]) && is_numeric($b["order"])) {
					return $a["order"] - $b["order"];
				}
			});
	   	}

	    return $this->renderTable($cols, $a['integrate_form'], $a['collapsible'], $a['show_image'], ($a['building_id'] ? $a['building_id'] : false), $a['class']);
	}

	// [CXM-list-unit cols="name,price,rent" labels="Name,Preis,Mietpreis" sales="rent" type="3"]
	function shortcode_list_unit( $atts ) {
		//render single unit list
	}


	// [CXM-graphic]
	function shortcode_graphic( $atts ) {
	    $a = shortcode_atts( array(
	    	'building_id' => false
	    ), $atts );

	    $cols = maybe_unserialize((maybe_unserialize($this->get_option("list_cols"))));
	   	if (!$cols || !is_array($cols)) {
	   		$cols = array();
	   	} else {
	   		//sort
			uasort($cols, function($a, $b){
				if (is_numeric($a["order"]) && is_numeric($b["order"])) {
					return $a["order"] - $b["order"];
				}
			});
	   	}

	    return $this->renderGraphic($cols, ($a['building_id'] ? $a['building_id'] : false));
	}

	// [CXM-filter]
	function shortcode_filter( $atts ) {
	    $a = shortcode_atts( array(
	    	'filters' => 'rooms, status',
	    	'building_id' => '',
	    ), $atts );

	    $filters = array();
	    if ($a['filters']) {
	    	$filters = explode(',', $a['filters']);
	    	$filters = array_map('trim',$filters);
	    }


	    return $this->renderFilter($filters, $a['building_id']);
	}

	private function loadBuildings($building_id){
		$unit_args = array(
			'posts_per_page' => 250,
			'post_type' => 'complex_unit',
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);
		$building_args = array('orderby' => 'slug');
		if ($building_id) {
			$building_args['include'] = $building_id;
		}

		$a_buildings = array();
		$building_terms = get_terms( 'building', $building_args);


		if ( !empty( $building_terms ) && !is_wp_error( $building_terms ) ){
			foreach ( $building_terms as $term ) {
				$unit_args['building'] = $term->slug;
				$a_buildings[] = array(
					'term' => $term,
					'units' => get_posts( $unit_args )
				);
			}
		}

		//moves certain units to the end of the list
		$buildings = array();
		$ending_buildings = array();
		foreach ($a_buildings as $abuilding) {
			if (in_array($abuilding['term']->slug, array('garage', 'garagen', 'tiefgarage', 'parkplaetze', 'parkplatz')) ) {
				$ending_buildings[] = $abuilding;
			} else {
				$buildings[] = $abuilding;
			}
		}
		foreach ($ending_buildings as $ebuilding) {
			$buildings[] = $ebuilding;
		}

		return $buildings;
	}

	private function store($key, $data){
		if($this->get_option('cache_renders', false)){;
			$dir = wp_upload_dir(null, true, false);
			if (!is_dir($dir['basedir'] . '/cmx_cache')) {
				mkdir($dir['basedir'] . '/cmx_cache', 0777);
			} else if (is_file($dir['basedir'] . '/cmx_cache/' . $key . '.php')) {
				unlink($dir['basedir'] . '/cmx_cache/' . $key . '.php');
			}
			$serializedData = serialize($data);
			$myfile = fopen($dir['basedir'] . '/cmx_cache/' . $key . '.php', "w");
			fwrite($myfile, $serializedData);
			fclose($myfile);

			return true;
		}
		return false;
	}

	private function getFromStorage($key){
		if($this->get_option('cache_renders', false)){;
			$dir = wp_upload_dir(null, true, false);
			if (is_file($dir['basedir'] . '/cmx_cache/' . $key . '.php')) {
				$serializedData = file_get_contents($dir['basedir'] . '/cmx_cache/' . $key . '.php');
				$data = unserialize($serializedData);
				return $data;
			}
		}
		return false;
	}

	private function getBuildings($building_id = false){
		$storeKey = ($building_id ? $building_id : 'all');
		if (!isset($this->buildingsStore[$storeKey])) {
			$storedData = $this->getFromStorage('building_' . $storeKey);
			if ($storedData) {
				$this->buildingsStore[$storeKey] = $storedData; //$this->loadBuildings($building_id);
			} else {
				$this->buildingsStore[$storeKey] = $this->loadBuildings($building_id);
				$this->store('building_' . $storeKey, $this->buildingsStore[$storeKey]);
			}

		}
		return $this->buildingsStore[$storeKey];
	}

	private function prepareUnit($unit, $cols){
		$fromStorage  = $this->getFromStorage('the_unit_' . $unit->ID);
		if ($fromStorage) {
			return $fromStorage;
		}

		$the_unit = array('post' => $unit);
		$status = get_cxm($unit, 'status');
		$state = 'default';
		$lang = substr(get_bloginfo('language'), 0, 2);
		switch ($status) {
			case 'available': $state = 'default'; break;
			case 'pre-reserved': $state = 'warning'; break;
			case 'reserved': $state = 'danger'; break;
			case 'rented': $state = 'danger'; break;
			case 'sold': $state = 'danger'; break;
		}
		$the_unit['state'] = $state;
		$the_unit['status'] = $status;

		$data = array();
		foreach ($cols as $field => $col) {
			$value = get_cxm($unit, $field);
			$data[$field] = htmlentities($value);
			if (($field == 'r_link' && $col['hidden-reserved'] == 1) || ($field == 'quick-download' && $col['hidden-reserved'] == 1)) {
				$data[$field] = 'hidden-reserved';
			}
		}
		$the_unit['data'] = $data;

		$the_unit['displayItems'] = array();
		$i = 0;
		foreach ($cols as $field => $col) {

			
			$i++;
			if ($col['active']){
				$displayItem = array(
					'field' => $field,
					'label' => '',
					'value' => '',
					'td_classes' => '',
					'hidden-xs' => $col['hidden-xs']
				);

				//==label==

				// check for lingustic alternatives
				$label_text = (isset($col['label_'.$lang]) ? $col['label_'.$lang] : (isset($col['label']) ? $col['label'] : ''));
				$displayItem['label'] = nl2br(str_replace('\n', "\n", ($label_text ? $label_text : get_cxm_label(false, $field, 'complex_unit') ) ) );


				$Rfield = get_cxm_item($unit, $field);
				$displayItem['item'] = $Rfield;
				//==therest==
				switch ($field) {
					case 'status':
						$value = '';
						switch ($status) {
							case 'available': $value = '<span class="text-success">'.strtolower(__('Available', 'complexmanager')).'</span>'; break;
							case 'pre-reserved': $value = '<span class="text-'.$state.'">'.strtolower(__('pre-reserved', 'complexmanager')).'</span>'; break;
							case 'reserved': $value = '<span class="text-'.$state.'">'.strtolower(__('Reserved', 'complexmanager')).'</span>'; break;
							case 'rented': $value = '<span class="text-'.$state.'">'.strtolower(__('Rented', 'complexmanager')).'</span>'; break;
							case 'sold': $value = '<span class="text-'.$state.'">'.strtolower(__('Sold', 'complexmanager')).'</span>'; break;
							default: $value = $status;
						}
						$displayItem['value'] = '<span class="text-'.$state.'">' . $value . '</span>';
						//$displayItem['td_classes'] = 'hidden-sm hidden-xs col-status';
						//$displayItem['hidden-xs'] = $col['hidden-xs'];

						$displayItem['td_classes'] = ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field;
						$displayItem['hidden-xs'] = $col['hidden-xs'];

						break;
					case 'r_purchase_price':
					case 'r_rent_net':
					case 'r_rent_gross':
						$currency = false;
						if (
							$col['hidden-reserved'] == 0
							||
							!in_array($status, array('reserved', 'sold', 'rented'))
						) {
							if (get_cxm($unit, 'unit_currency')) {
								$currency = get_cxm($unit, 'unit_currency');
							}
							$displayItem['value'] = ($currency ? $currency . ' ' : '') . $Rfield['value'];
						} else {
							$displayItem['value'] = '';
						}

						$displayItem['td_classes'] = ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field;
						$displayItem['hidden-xs'] = $col['hidden-xs'];

						break;
					case 'r_link':
						if (
							$col['hidden-reserved'] == 0
							||
							!in_array($status, array('reserved', 'sold', 'rented'))
						) {
							if (get_cxm($unit, 'link_url')) {
								$value = '<a target="'. (get_cxm($unit, 'link_target') ? get_cxm($unit, 'link_target') : '_self') . '" href="' . get_cxm($unit, 'link_url') . '">' . (get_cxm($unit, 'link_label') ? get_cxm($unit, 'link_label') : 'Link') . '</a>';
							} else {
								$value = '';
							}

						} else {
							$value = '';
						}
						$displayItem['value'] = $value;
						$displayItem['td_classes'] = ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field;
						$displayItem['hidden-xs'] = $col['hidden-xs'];
						break;
					case 'quick-download':
						if (
							$col['hidden-reserved'] == 0
							||
							!in_array($status, array('reserved', 'sold', 'rented'))
						) {
							if (get_cxm($unit, 'download_file')) {
								$value = '<a target="_blank" class="btn btn-xs btn-default" href="' . get_cxm($unit, 'download_file') . '">' . (get_cxm($unit, 'download_label') ? get_cxm($unit, 'download_label') : 'Download') . '</a>';
							} else {
								$value = '';
							}

						} elseif(
							$col['hidden-reserved'] == 1
							&& in_array($status, array('reserved', 'sold', 'rented'))
						) {
							$value = '';

							//show availability instead if deactivated?
							$statustext = '';
							switch ($status) {
								case 'pre-reserved': $statustext = '<span class="text-'.$state.'">'.strtolower(__('pre-reserved', 'complexmanager')).'</span>'; break;
								case 'reserved': $statustext = '<span class="text-'.$state.'">'.strtolower(__('Reserved', 'complexmanager')).'</span>'; break;
								case 'rented': $statustext = '<span class="text-'.$state.'">'.strtolower(__('Rented', 'complexmanager')).'</span>'; break;
								case 'sold': $statustext = '<span class="text-'.$state.'">'.strtolower(__('Sold', 'complexmanager')).'</span>'; break;
							}
							if ($statustext) {
								$value = $statustext;
							}
						} else {
							$value = '';
						}
						$displayItem['value'] = $value;
						$displayItem['td_classes'] = ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field;
						$displayItem['hidden-xs'] = $col['hidden-xs'];

						break;
					default:
						if (
							$col['hidden-reserved'] == 0
							||
							!in_array($status, array('reserved', 'sold', 'rented'))
						) {
							$value = $Rfield['value'];
						} else {
							$value = '';
						}
						if ($value !== '') {
							$displayItem['value'] = '<span class="text-'.$state.'">' . ($i == 1 ? '<strong>' : '') . $value . ($i == 1 ? '</strong>' : '') . '</span>';
						} else {
							$displayItem['value'] = '';
						}
						$displayItem['td_classes'] = ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field;
						$displayItem['hidden-xs'] = $col['hidden-xs'];

						break;
				}

				$the_unit['displayItems'][] = $displayItem;
			}
		}
		$this->store('the_unit_' . $unit->ID, $the_unit);

		return $the_unit;
	}

	private function prepareBuildings($buildings, $cols){




		$the_buildings = array();
		foreach ($buildings as $building) {
			$fromStorage  = $this->getFromStorage('the_building_' . $building['term']->term_id);
			if ($fromStorage) {
				$the_buildings[] = $fromStorage;
				continue;
			}

			$building['description'] = ($building['term']->description ? $building['term']->description : '');
			$building['the_units'] = array();
			$col_options = get_term_meta( $building['term']->term_id, 'building_col_options', true );
			$hide_building = get_term_meta( $building['term']->term_id, 'hide_building', true );
			/*if ($hide_building) {
				continue;
			}*/
			$building['hidden'] = $hide_building;

			$building_cols = $cols;
			foreach ($building_cols as $col => $b_col) {
				$hidden = (isset($col_options[$col]) && isset($col_options[$col]['hide']) && $col_options[$col]['hide'] ? true : false);
				$alt_label = (isset($col_options[$col]) && isset($col_options[$col]['alternate_label']) && $col_options[$col]['alternate_label'] ? $col_options[$col]['alternate_label'] : false);
				if ($hidden) {
					$building_cols[$col]['active'] = false;
				} elseif ($alt_label) {
					$building_cols[$col]['label'] = $alt_label;
				}
			}

			foreach ($building['units'] as $unit) {
				$the_unit = $this->prepareUnit($unit, $building_cols);
				$building['the_units'][] = $the_unit;
			}

			//add total row

			$show_total = get_term_meta( $building['term']->term_id, 'show_total', true );
			if ($show_total == 1) {

				$building['totals'] = array();
				foreach ($building['the_units'] as $unit) {
					foreach ($unit['displayItems'] as $displayItem) {
						if (!isset($building['totals'][$displayItem['field']])) {
							$building['totals'][$displayItem['field']] = 0;
						}
						if (isset($displayItem['item']['pure_value']) && $displayItem['item']['pure_value']) {

							//areas
							if (is_numeric($displayItem['item']['pure_value'])) {
								$building['totals'][$displayItem['field']] = $building['totals'][$displayItem['field']] + $displayItem['item']['pure_value'];
							}

						}
					}
				}
				$decimal_spaces = $this->get_option('space_decimal', 1);
				foreach ($building['totals'] as $key => $value) {
					if (in_array($key, array(
						'r_balcony_space',
						'r_living_space',
						'r_usable_space',
						'r_terrace_space',
						'r_balcony_space',
					))) {
						$building['totals'][$key] = number_format($value, $decimal_spaces ,".", $this->get_option('thousands_seperator', "'")) . '&nbsp;m<sup>2</sup>';
					}
				}
			}

			$this->store('the_building_' . $building['term']->term_id, $building);

			$the_buildings[] = $building;
		}
		return $the_buildings;
	}

	public function renderTable($cols, $integrate_form = true, $collapsible = true, $show_image = true, $building_id = false, $className = ''){
		/*if (!$cols) {
			$cols = array();
			foreach (get_default_cxm('unit') as $key => $col) {
				if (in_array($key, array('name', 'status'))) {
					$cols[] = array('field' => $key, 'label' => $col['label']);
				}
			}
		}*/

		$template = $this->get_template();
		$template->set( 'cols', $cols );
		
		$theBuildingsKey = 'renderTableTheBuildings_' . $integrate_form . '_' . $collapsible . '_' . $show_image . '_' . $building_id;
		$fromtheBuildingsStorage = $this->getFromStorage($theBuildingsKey);
		if ($fromtheBuildingsStorage) {
			$theBuildings = $fromtheBuildingsStorage;
			$this->store($theBuildingsKey, $fromtheBuildingsStorage);	
		} else {
			$theBuildings = $this->prepareBuildings($this->getBuildings($building_id), $cols);
		}
		$template->set( 'the_buildings', $theBuildings, $cols);

		$buildingsKey = 'renderTablebuildings_' . $integrate_form . '_' . $collapsible . '_' . $show_image . '_' . $building_id;
		$frombuildingsStorage = $this->getFromStorage($buildingsKey);
		if ($frombuildingsStorage) {
			$buildings = $frombuildingsStorage;
			$this->store($buildingsKey, $frombuildingsStorage);	
		} else {
			$buildings = $this->getBuildings($building_id);
		}
		$template->set( 'buildings', $buildings, $cols);

		$template->set( 'collapsible', $collapsible );
		$template->set( 'show_image', $show_image );
		$template->set( 'integrate_form', $integrate_form );
		$template->set( 'class', $className );

		if ($integrate_form) {
			if (!$this->isFormLoaded()) {
				$template->set( 'form', $this->renderForm(array('building_id' => $building_id)));
				$this->setFormLoaded();
			} else {
				$template->set( 'form', false );
			}
		} else {
			$template->set( 'form', false );
		}


		$message = $template->apply( 'list.php' );

		

		return $message;
	}

	public function renderSingleTable($cols){
		//render single table for a unit
	}

	public function renderGraphic($cols, $building_id = false){
		$thekey = 'renderGraphic_' . md5(implode(',', array_keys($cols) ) ) . '_' . $building_id;
		$fromStorage = $this->getFromStorage($thekey);
		if ($fromStorage) {
			return $fromStorage;
		}

		$image = PLUGIN_URL.'assets/img/example-project-bg.png';
		$width = 1152;
	    $height = 680;
	    $set = false;

	    //building specific base image
	    if ($building_id) {
	    	$project_image_alt_id = get_term_meta($building_id, 'alternate-base-image', true);
	    	if ($project_image_alt_id) {
		        $image_attributes = wp_get_attachment_image_src( $project_image_alt_id, 'full' ); // returns an array
		        if ($image_attributes) {
		            $set = true;
		            $image = $image_attributes[0];
		            $width = $image_attributes[1];
		            $height = $image_attributes[2];
		        }
		    }
	    }

	    //default base image
	    if (!$set) {
		    $project_image_id = $this->get_option("project_image");
		    if ($project_image_id) {
		        $image_attributes = wp_get_attachment_image_src( $project_image_id, 'full' ); // returns an array
		        if ($image_attributes) {
		            $set = true;
		            $image = $image_attributes[0];
		            $width = $image_attributes[1];
		            $height = $image_attributes[2];
		        }
		    }
	    }



		$template = $this->get_template();
		$template->set( 'buildings', $this->getBuildings($building_id) );
		$template->set( 'the_buildings', $this->prepareBuildings($this->getBuildings($building_id), $cols));
		$template->set( 'image', $image );
		$template->set( 'width', $width );
		$template->set( 'height', $height );

		$message = $template->apply( 'project-graphic.php' );

		$this->store($thekey, $message);

		return $message;
	}

	public function renderFilter($filters, $building_ids = false){

		$defaultLabels = cxm_get_filter_label_defaults();

	    $labels = maybe_unserialize((maybe_unserialize($this->get_option("list_filters"))));
	   	if (!$labels || !is_array($labels)) {
	   		$labels = array();
	   	}

	   	//print_r($defaultLabels);

	   	//print_r($labels);
	   	$labelArray = array();
	   	$defaultLabelArray = array();

	   	foreach ($defaultLabels as $key => $label) {
	   		$defaultLabelArray[$key] = $label['o_label'];
	   	}

	   	//print_r($defaultLabelArray);

		$lang = get_locale();

	   	foreach ($labels as $key => $label) {
			   //print_r($label);
	   		if ($key == 'r_living_space') {
	   			$key = 'livingspace';
	   		}
	   		if ($key == 'r_usable_space') {
	   			$key = 'usablespace';
	   		}
	   		if ($key == 'r_rent_net') {
	   			$key = 'rentnet';
	   		}
	   		if ($key == 'r_rent_gross') {
	   			$key = 'rentgross';
	   		}
	   		if ($key == 'r_purchase_price') {
	   			$key = 'purchaseprice';
	   		}
	   		if ($label['label'] && $lang == 'de_DE') {
	   			$labelArray[$key] = $label['label'];
	   		} elseif($label['label_en'] && $lang == 'en_US') {
				$labelArray[$key] = $label['label_en'];
			} elseif($label['label_fr'] && $lang == 'fr_FR') {
				$labelArray[$key] = $label['label_fr'];
			}
	   	}

	   	$filterLabelArray = array_merge($defaultLabelArray, $labelArray);

	   	//print_r($filterLabelArray);
	   	$filterArray = array();

	   	foreach ($filters as $filter => $keyFilter) {
	   		foreach ($filterLabelArray as $keyLabel => $value) {
	   			if ($keyFilter == $keyLabel) {
	   				$filterArray[$keyFilter] = $value;
	   			}
	   		}
	   	}

	   	$filters = $filterArray;

		$thekey = 'renderFilters_' . md5( implode(',', array_keys($filters)) );
		$fromStorage = $this->getFromStorage($thekey);
		if ($fromStorage) {
			return $fromStorage;
		}


		$unit_args = array(
			'post_type' => 'complex_unit',
			'posts_per_page' => 250
		);

		$image = PLUGIN_URL.'assets/img/example-project-bg.png';
		$width = 1152;
	    $height = 680;

		$buildings = array();
		if ($building_ids) {
			$building_terms = get_terms( 'building', array('include' => $building_ids) );
		} else {
			$building_terms = get_terms( 'building', array() );	
		}

		if ( !empty( $building_terms ) && !is_wp_error( $building_terms ) ){
			foreach ( $building_terms as $term ) {
				$unit_args['building'] = $term->slug;
				$buildings[] = array(
					'term' => $term,
					'units' => get_posts( $unit_args )
				);
			}
		}

	    $project_image_id = $this->get_option("project_image");
	    if ($project_image_id) {
	        $image_attributes = wp_get_attachment_image_src( $project_image_id, 'full' ); // returns an array
	        if ($image_attributes) {
	            $set = true;
	            $image = $image_attributes[0];
	            $width = $image_attributes[1];
	            $height = $image_attributes[2];
	        }
	    }

		$template = $this->get_template();
		$template->set( 'buildings', $buildings );
		//$template->set( 'the_buildings', $this->prepareBuildings($buildings));
		$template->set( 'image', $image );
		$template->set( 'width', $width );
		$template->set( 'height', $height );

		$roomfilters = array();
		$type_filters = array();
		$custom_3_filters = array();
		$custom_2_filters = array();
		$custom_1_filters = array();
		$story_filters = array();
		$minLivingSpace = false;
		$maxLivingSpace = false;
		$minUsableSpace = false;
		$maxUsableSpace = false;
		$minRentNet = false;
		$maxRentNet = false;
		$minRentGross = false;
		$maxRentGross = false;
		$minPurchasePrice = false;
		$maxPurchasePrice = false;

		foreach ($buildings as $building) {
			foreach ($building['units'] as $unit) {
				if (get_cxm($unit, 'living_space')) {
					if ($minLivingSpace && $maxLivingSpace) {
						if (get_cxm($unit, 'living_space') < $minLivingSpace) {
							$minLivingSpace = get_cxm($unit, 'living_space');
						} elseif (get_cxm($unit, 'living_space') > $maxLivingSpace) {
							$maxLivingSpace = get_cxm($unit, 'living_space');
						}
					} else {
						$minLivingSpace = get_cxm($unit, 'living_space');
						$maxLivingSpace = get_cxm($unit, 'living_space');
					}
				}

				if (get_cxm($unit, 'usable_space')) {
					if ($minUsableSpace && $maxUsableSpace) {
						if (get_cxm($unit, 'usable_space') < $minUsableSpace) {
							$minUsableSpace = get_cxm($unit, 'usable_space');
						} elseif (get_cxm($unit, 'usable_space') > $maxUsableSpace) {
							$maxUsableSpace = get_cxm($unit, 'usable_space');
						}
					} else {
						$minUsableSpace = get_cxm($unit, 'usable_space');
						$maxUsableSpace = get_cxm($unit, 'usable_space');
					}
				}

				if (get_cxm($unit, 'rent_net')) {
					if ($minRentNet && $maxRentNet) {
						if (get_cxm($unit, 'rent_net') < $minRentNet) {
							$minRentNet = get_cxm($unit, 'rent_net');
						} elseif (get_cxm($unit, 'rent_net') > $maxRentNet) {
							$maxRentNet = get_cxm($unit, 'rent_net');
						}
					} else {
						$minRentNet = get_cxm($unit, 'rent_net');
						$maxRentNet = get_cxm($unit, 'rent_net');
					}
				}

				if (get_cxm($unit, 'rent_gross')) {
					if ($minRentGross && $maxRentGross) {
						if (get_cxm($unit, 'rent_gross') < $minRentGross) {
							$minRentGross = get_cxm($unit, 'rent_gross');
						} elseif (get_cxm($unit, 'rent_gross') > $maxRentGross) {
							$maxRentGross = get_cxm($unit, 'rent_gross');
						}
					} else {
						$minRentGross = get_cxm($unit, 'rent_gross');
						$maxRentGross = get_cxm($unit, 'rent_gross');
					}
				}

				if (get_cxm($unit, 'purchase_price')) {
					if ($minPurchasePrice && $maxPurchasePrice) {
						if (get_cxm($unit, 'purchase_price') < $minPurchasePrice) {
							$minPurchasePrice = get_cxm($unit, 'purchase_price');
						} elseif (get_cxm($unit, 'purchase_price') > $maxPurchasePrice) {
							$maxPurchasePrice = get_cxm($unit, 'purchase_price');
						}
					} else {
						$minPurchasePrice = get_cxm($unit, 'purchase_price');
						$maxPurchasePrice = get_cxm($unit, 'purchase_price');
					}
				}




				//complexmanager_unit_
				$number_of_rooms = get_cxm($unit, 'number_of_rooms');
				if (!in_array($number_of_rooms, $roomfilters)) {
					if ($number_of_rooms) {
						$roomfilters[] = number_format(round($number_of_rooms, 1), 1, '.', $this->get_option('thousands_seperator', "'")) ;
					}
				}

				//types
				$types = get_terms('unit_type', array('hide_empty' => true));
				if ($types) {
					foreach ($types as $type) {
						if (!in_array($type, $type_filters)) {
							$type_filters[] = $type;
						}
					}
				}
				

				//custom_3
				$custom_3 = get_cxm($unit, 'custom_3');
				if (!in_array($custom_3, $custom_3_filters)) {
					if ($custom_3) {
						$custom_3_filters[] = $custom_3 ;
						//asort($custom_3_filters);
					}
				}

				//custom_2
				$custom_2 = get_cxm($unit, 'custom_2');
				if (!in_array($custom_2, $custom_2_filters)) {
					if ($custom_2) {
						$custom_2_filters[] = $custom_2 ;
						//asort($custom_2_filters);
					}
				}

				//custom_1
				$custom_1 = get_cxm($unit, 'custom_1');
				if (!in_array($custom_1, $custom_1_filters)) {
					if ($custom_1) {
						$custom_1_filters[] = $custom_1 ;
						//asort($custom_1_filters);
					}
				}


				//story
				$story = get_cxm($unit, 'story');
				if (!in_array($story, $story_filters)) {
					if ($story) {
						$story_filters[] = $story ;
					}
				}
			}
		}

		$roomfilters = array_filter($roomfilters);
		asort($roomfilters);

		$template->set( 'roomfilters', $roomfilters );

		$template->set( 'type_filters', $type_filters );

		$template->set( 'custom_3_filters', $custom_3_filters );

		$template->set( 'custom_2_filters', $custom_2_filters );

		$template->set( 'custom_1_filters', $custom_1_filters );

		$template->set( 'story_filters', $story_filters );

		$template->set( 'maxlivingspace', $maxLivingSpace );

		$template->set( 'minlivingspace', $minLivingSpace );

		$template->set( 'maxusablespace', $maxUsableSpace );

		$template->set( 'minusablespace', $minUsableSpace );

		$template->set( 'maxrentnet', $maxRentNet );

		$template->set( 'minrentnet', $minRentNet );

		$template->set( 'maxrentgross', $maxRentGross );

		$template->set( 'minrentgross', $minRentGross );

		$template->set( 'maxpurchaseprice', $maxPurchasePrice );

		$template->set( 'minpurchaseprice', $minPurchasePrice );

		$template->set( 'filters', $filters );

		$template->set( 'filter_income_max', $this->get_option('filter_income_max', "250000") );


		$message = $template->apply( 'filter.php' );

		$this->store($thekey, $message);

		return $message;
	}

	public function getFormData($empty = false){
		$defaults_inq = get_default_cxm('inquiry', false);
		$defaults = array();
		foreach ($defaults_inq as $key => $inq_item) {
			$defaults[$key] = $inq_item['value'];
		}
		$defaults['post'] = 0;

		$request = array();
		if (!$empty) {
			$request = array_merge($_GET, $_POST);
		}

		if (isset($request['complex-unit-inquiry'])) {
			$formData = array_merge($defaults, $request['complex-unit-inquiry']);
		} else {
			$formData = $defaults;
		}

		if (isset($request['extra_data'])) {
			$formData['extra_data'] = $request['extra_data'];
		}

		if (isset($request['g-recaptcha-response'])) {
		    $formData['captcha_response'] = $request['g-recaptcha-response'];
		}

		if (isset($request['auth'])) {
		    $this->auth = $request['auth'];
		}

		return $formData;
	}

	public $fieldMessages = array();
	public function addFieldValidationMessage($col, $message){
		$this->fieldMessages = $fieldMessages;
		$this->fieldMessages[$col] = $message;
	}
	public $requiredFields = array(
		'first_name',
		'last_name',
		'unit_id'
	);
	public function setFieldRequired($col){
		$this->requiredFields[] = $col;
	}

	public function getFormMessages(){
		
		$defaults = $this->fieldMessages;
		$messagesReturn = apply_filters('cxm_filter_form_required_messages', array("messages" => $this->fieldMessages, "formData" => $this->getFormData()));
		if ($messagesReturn && is_array($messagesReturn)) {
			$defaults = $messagesReturn["messages"];
		}

		$required = $this->requiredFields;	
		$requiredReturn = apply_filters('cxm_filter_form_required', array("fields" => $this->requiredFields, "formData" => $this->getFormData()));
		if ($requiredReturn && is_array($requiredReturn)) {
			$required = $requiredReturn["fields"];
		}

		$messages = array();
		foreach ($this->getFormData() as $col => $value) {
			if (in_array($col, $required)) {
				if (!$value || $value == '–') {
					if (isset($defaults[$col])) {
						$messages[$col] = $defaults[$col];
					} else {
						$messages[$col] = $col . ' required';
					}

				}
			} else {
				switch ($col) {
					case 'email':
						$valid = filter_var( $value, FILTER_VALIDATE_EMAIL );
						if (!$valid) {
							$messages[$col] = $defaults[$col];
						}
						break;
					case 'post':
						if (!$value) {
							//silent but deadly
							$messages[$col] = 'Your message has been sent!?';
						}
						break;

				}
			}
		}
		$returndata = apply_filters('cxm_filter_form_messages', array("messages" => $messages, "formData" => $this->getFormData()));

		if (is_array($returndata)) {
			if (isset($returndata['messages'])) {
				$returndata = $returndata['messages'];
			}
			$messages = $returndata;
		}

		return $messages;
	}

	public function formValid(){
		if (count($this->getFormMessages())) {
			return false;
		}
		return true;
	}

	public function sendRemcat($inquiry, $to = false){
		if ($to) {
			$remcat = $to;
		} else {
			$remcat = $this->get_option("remcat");
		}
		if ($remcat) {
			$unit_id = get_cxm($inquiry->ID, 'unit_id');

			$propertyReference = $this->get_option("idx_ref_property").'.'.get_cxm($unit_id, 'idx_ref_house').'.'.get_cxm($unit_id, 'idx_ref_object');
			if ($this->get_option('remcat_general_property_ref')) {
				$propertyReference = '..' . $this->get_option('remcat_general_property_ref');
			}

			$portalName = 'wp_complex_manager';
			if ($this->get_option('remcat_website')) {
				$portalName = $this->get_option('remcat_website');
			}

			$companyName = '';
			if ($this->get_option('remcat_company')) {
				$companyName = $this->get_option('remcat_company');
			}

			$companyStreet = '';
			if ($this->get_option('remcat_company_street')) {
				$companyStreet = $this->get_option('remcat_company_street');
			}

			$companyPostalCode = '';
			if ($this->get_option('remcat_company_postal_code')) {
				$companyPostalCode = $this->get_option('remcat_company_postal_code');
			}

			$companyLocality = '';
			if ($this->get_option('remcat_company_locality')) {
				$companyLocality = $this->get_option('remcat_company_locality');
			}

			$companyPersonName = '';
			if ($this->get_option('remcat_company_person_name')) {
				$companyPersonName = $this->get_option('remcat_company_person_name');
			}

			$companyEmail = '';
			if ($this->get_option('remcat_company_email')) {
				$companyEmail = $this->get_option('remcat_company_email');
			}

			$remcat_arr = array(
				'Hash davor' => '', //Ein valides Remcat beinhaltet genau 27 Hashes. Startet mit # und endet mit ##
				'Immobilienportalname' => $portalName, //homegate
				'Immobilienverwaltung Name' => $companyName, //PSP Management AG
				'Immobilienverwaltung Adresse' => $companyStreet, //Baslerstrasse 44
				'Immobilienverwaltung PLZ' => $companyPostalCode, //4600
				'Immobilienverwaltung Ort' => $companyLocality, //Olten
				'Immobilienverwaltung Sachbearbeiter' => $companyPersonName, //Urben Michael
				'Immobilienverwaltung Sachbearbeiter Emailadresse' => $companyEmail, //remcat@psp.info
				'Objektreferenz' => $propertyReference, //*** 6045.01.0202
				'Advertisement ID' => $inquiry->ID, //*** 101688233
				'Objekt Adresse' => '', //Stalden 35
				'Objekt PLZ Ort' => '', //4500 Solothurn
				'Objekt Art' => '', //Wohnung
				'Interessent Sprache' => '', //d
				'Interessent Anredecode ' => '', //
				'Interessent Vorname' => get_cxm($inquiry->ID, 'first_name'), //*** Fritz
				'Interessent Name' => get_cxm($inquiry->ID, 'last_name'), //*** Muster
				'Interessent Firma ' => get_cxm($inquiry->ID, 'legal_name'), //
				'Interessent Strasse' => get_cxm($inquiry->ID, 'street'), //***
				'Interessent PLZ' => get_cxm($inquiry->ID, 'postal_code'), //***
				'Interessent Ort' => get_cxm($inquiry->ID, 'locality'), //***
				'Interessent Telefon' => get_cxm($inquiry->ID, 'phone'), //*** 044 444 00 00
				'Interessent Mobile' => '', //
				'Interessent Fax' => '', //
				'Interessent e-Mail' => get_cxm($inquiry->ID, 'email'), //*** fritz.muster@muster.ch
				'Interessent Bemerkungen' => get_cxm($inquiry->ID, 'message'), //Dies ist ein Beispielmail
			);
			$remcat_arr = str_replace('#', 'Nr.', $remcat_arr);
			$remhash = implode('#',$remcat_arr);
			$remhash = strip_tags($remhash);

			$multiple_to_recipients = array(
			    $remcat,
			);

			
			add_filter( 'wp_mail_content_type', function($content_type){
				return 'text/plain';
			});

			wp_mail( $multiple_to_recipients, __('Unit inquiry'), $remhash );

			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', function($content_type){
				return 'text/plain';
			});
		}
	}

	public function sendEmail($inquiry, $to = false){
		if ($to) {
			$emails = $to;
		} else {
			$emails = $this->get_option("emails");
		}


		if ($emails) {
			$html_contact_data = '';
			if (get_cxm($inquiry->ID, 'name')) {
				$html_contact_data .= '<strong>' . get_cxm($inquiry->ID, 'name') . '</strong><br>';
			}
			if (get_cxm($inquiry->ID, 'address_html')) {
				$html_contact_data .= '' . get_cxm($inquiry->ID, 'address_html') . '<br>';
			}

			$html_contact_data .= '<br>';

			if (get_cxm($inquiry->ID, 'email')) {
				$html_contact_data .= '<a href="mailto:'.get_cxm($inquiry->ID, 'email').'">' . get_cxm($inquiry->ID, 'email') . '</a><br>';
			}

			if (get_cxm($inquiry->ID, 'phone')) {
				$html_contact_data .= __('Phone:', 'complexmanager') . ' ' . get_cxm($inquiry->ID, 'phone'). '<br>';
			}

			if (get_cxm($inquiry->ID, 'mobile')) {
				$html_contact_data .= __('Mobile:', 'complexmanager') . ' ' . get_cxm($inquiry->ID, 'mobile'). '<br>';
			}

			$html_contact_data .= '<br>';

			if (get_cxm($inquiry->ID, 'subject')) {
				$html_contact_data .= '<strong>'.get_cxm($inquiry->ID, 'subject') . '</strong><br>';
			}

			if (get_cxm($inquiry->ID, 'message')) {
				$html_contact_data .= get_cxm($inquiry->ID, 'message');
			}


			if (get_cxm($inquiry->ID, 'unit')) {
				$unit = get_cxm($inquiry->ID, 'unit');
				$html_contact_data .= '<br><br><strong>'.__('Unit', 'complexmanager').':</strong> '. get_cxm($unit->ID, 'name');
			}


			$multiple_to_recipients = array(
			    $emails,
			);

			add_filter( 'wp_mail_content_type', function($content_type){
				return 'text/html';
			});

			wp_mail( $multiple_to_recipients, __('Unit inquiry'), $html_contact_data );

			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', function($content_type){
				return 'text/html';
			});
		}

	}

	public function sendCasamail($inquiry, $formData, $provider = false, $publisher = false){

		if (!$provider) {
			$provider = $this->get_option("provider_slug");
		}
		if (!$publisher) {
			$publisher = $this->get_option("publisher_slug");
		}
		if ($provider && $publisher) {
			$unit_id = get_cxm($inquiry->ID, 'unit_id');
			$unit = get_post($unit_id);

			$lang = substr(get_bloginfo('language'), 0, 2);

			//CASAMAIL
			$data                = array();
			$data['firstname']   = get_cxm($inquiry->ID, 'first_name');
			$data['lastname']    = get_cxm($inquiry->ID, 'last_name');
			$gender = get_cxm($inquiry->ID, 'gender');
			if ($gender == 'female') {
				$data['gender']      = 2;
			} elseif ($gender == 'male') {
				$data['gender']      = 1;
			}
			$data['street']      = get_cxm($inquiry->ID, 'street');
			$data['legal_name']  = get_cxm($inquiry->ID, 'legal_name');;
			$data['postal_code'] = get_cxm($inquiry->ID, 'postal_code');
			$data['locality']    = get_cxm($inquiry->ID, 'locality');
			//$data['country']     = 'CH';
			$data['phone']       = get_cxm($inquiry->ID, 'phone');
			$data['mobile']       = get_cxm($inquiry->ID, 'mobile');
			//$data['mobile']       = '000 000 00 00';
			//$data['fax']       = '000 000 00 00';
			$data['email']       = get_cxm($inquiry->ID, 'email');
			$data['message']     = get_cxm($inquiry->ID, 'message');

			$data['provider']               = $provider; //must be registered at CASAMAIL
			$data['publisher']              = $publisher; //must be registered at CASAMAIL
			$data['lang']                   = $lang;
			$data['property_reference']     = $this->get_option("idx_ref_property").'.'.get_cxm($unit_id, 'idx_ref_house').'.'.get_cxm($unit_id, 'idx_ref_object');
			//$data['property_street']        = 'musterstrasse 17';
			//$data['property_postal_code']   = '3291';
			//$data['property_locality']      = 'Ortschaft';
			//$data['property_category']      = 'house';
			//$data['property_country']       = 'CH';
			//$data['property_rooms']         = '3.2';
			//$data['property_type']          = 'rent';
			//$data['property_price']         = '123456';
			$data['direct_recipient_email'] = get_cxm($inquiry->ID, 'direct_recipient_email');

			if (!$data['direct_recipient_email']) {
				$data['direct_recipient_email'] = $this->get_option("global_direct_recipient_email");
			}
			

			$term = get_term($formData['reason'], 'inquiry_reason', OBJECT);
			$extra_data = array();
			if ($term) {
				if (get_class( $term) == 'WP_Error') {
					#echo '<pre>'.$term->get_error_message().'</pre>';
				} else{
						$extra_data['acquiredThrough'] = $term->name;
				}
			}

			//unitdata
			if ($unit) {
				$cols = maybe_unserialize((maybe_unserialize($this->get_option("list_cols"))));
			   	if (!$cols || !is_array($cols)) {
			   		$cols = array();
			   	} else {
			   		//sort
					uasort($cols, function($a, $b){
						return $a["order"] - $b["order"];
					});
			   	}
				$the_unit = $this->prepareUnit($unit, $cols);
				$unit_infos = '<br>';
				foreach ($the_unit['displayItems'] as $displayItem) {
					if ($displayItem['label'] && $displayItem['value']) {
						$unit_infos .= $displayItem['label'] . ': ' . $displayItem['value'] . "<br>";
					}

				}
				if ($unit_infos) {
					$extra_data['infos'] = $unit_infos;
				}
			}

			//custom extra data
			if (isset($formData['extra_data'])) {
				foreach ($formData['extra_data'] as $key => $value) {
					$extra_data[$key] = $value;
				}
			}

			//files as extra_data
			if (isset($formData['files']['url']) && $formData['files']['url']) {
				foreach ($formData['files']['url'] as $filekey => $url) {
					$extra_data[$filekey] = $url;
				}
			}

			$data['extra_data'] = json_encode($extra_data);

			$returndata = apply_filters('cxm_filter_casamail_data', $data, $formData);
			if ($returndata) {
				$data = $returndata;
			}

			$data_string = json_encode($data);

			$ch = curl_init('https://message.casasoft.com/api/msg');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($data_string))
			);
			curl_setopt($ch, CURLOPT_USERPWD,  "matchcom:bbYzzYEBmZJ9BDumrqPKBHM");

			$result = curl_exec($ch);
			$json = json_decode($result, true);
			if (isset($json['validation_messages'])) {
				return $json['validation_messages'];
			} else {
				return null;
			}
		}
	}

	private function storeInquiry($args, $formData){
		$inq_post_id = wp_insert_post( $args );
		if ($inq_post_id) {
			foreach ($formData as $key => $value) {
				add_post_meta( $inq_post_id, '_complexmanager_inquiry_'.$key, $value , true);
			}
			if (isset($formData['reason']) && $formData['reason']) {
				wp_set_post_terms( $inq_post_id, $formData['reason'], 'inquiry_reason' );
			}

		}

		return get_post($inq_post_id);
	}

	

	public function sendGaEvent($action = 'inquiry-sent', $label = 'Anfrage Versand', $value = 1){
		cxm_send_ga_event($action, $label, $value);
	}

	public function renderForm($args){
		$template = $this->get_template();
		$reasons = array();

		$unit_args = array(
			'post_type' => 'complex_unit',
			'posts_per_page' => 250,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'unit_id' => false
		);
		

		if (isset($args['unit_id']) && $args['unit_id']) {
			$unit_args['include'] = $args['unit_id'];
		}



		$a_buildings = array();
		$building_terms = get_terms( 'building', array() );
		if ( !empty( $building_terms ) && !is_wp_error( $building_terms ) ){
			foreach ( $building_terms as $term ) {
				$unit_args['building'] = $term->slug;
				$a_buildings[] = array(
					'term' => $term,
					'units' => get_posts( $unit_args )
				);
			}
		}


		//moves certain units to the end of the list
		$buildings = array();
		$ending_buildings = array();
		foreach ($a_buildings as $abuilding) {
			if (in_array($abuilding['term']->slug, array('garage', 'garagen', 'tiefgarage', 'parkplaetze', 'parkplatz')) ) {
				$ending_buildings[] = $abuilding;
			} else {
				$buildings[] = $abuilding;
			}
		}
		foreach ($ending_buildings as $ebuilding) {
			$buildings[] = $ebuilding;
		}


		$formData =  $this->getFormData();
		$msg = '';
		$state = '';
		$messages = array();
		if ($formData['post']) {
			#print_r('rendered_form');
			if ($this->formValid()) {
				if (!$this->formSendHasAlreadyOccuredDuringThisRequest) {
				 	$this->formSendHasAlreadyOccuredDuringThisRequest = true;
					if (wp_verify_nonce( $_REQUEST['_wpnonce'], 'send-inquiry')) {

						$validCaptcha = null;

						if ($this->get_option('honeypot')) {

							$honeypot = $formData['firstname'];

							if (! empty($honeypot)) {
								return;
							} else {
								$msg = __('Inquiry has been sent. Thank you!', 'complexmanager');
								$state = 'success';

								$inq_post = array(
									'post_content'   => '',
									'post_title'     => $formData['first_name'] . ' ' . $formData['last_name'],
									'post_status'    => 'publish',
									'post_type'      => 'complex_inquiry',
									'ping_status'    => 'closed',
									'comment_status' => 'closed',
								);

								do_action('cxm_before_inquirystore', $formData);

								$inquiry = $this->storeInquiry($inq_post, $formData);

								do_action('cxm_before_inquirysend', $formData);
								$this->sendEmail($inquiry, false);
								$this->sendRemcat($inquiry, false);
								$casamail_msgs = $this->sendCasamail($inquiry, $formData, false, false);
								if ($casamail_msgs) {
									$msg .= 'CASAMAIL Fehler: '. print_r($casamail_msgs, true);
									$state = 'danger';
								}

								$this->sendGaEvent('inquiry-sent', get_cxm($inquiry->ID, 'email'), 1);

								do_action('cxm_after_inquirysend', $formData);

								//empty form
								$formData = $this->getFormData(true);
								
							}
						} elseif($this->get_option('recaptcha')) { 

							if (isset($formData['captcha_response'])) {
								$validCaptcha = $this->verifyCaptcha($formData['captcha_response']);
							}
							if ($validCaptcha &&  $validCaptcha === 'success') {
								$msg = __('Inquiry has been sent. Thank you!', 'complexmanager');
								$state = 'success';

								$inq_post = array(
									'post_content'   => '',
									'post_title'     => $formData['first_name'] . ' ' . $formData['last_name'],
									'post_status'    => 'publish',
									'post_type'      => 'complex_inquiry',
									'ping_status'    => 'closed',
									'comment_status' => 'closed',
								);

								do_action('cxm_before_inquirystore', $formData);

								$inquiry = $this->storeInquiry($inq_post, $formData);

								do_action('cxm_before_inquirysend', $formData);

								$this->sendEmail($inquiry, false);
								$this->sendRemcat($inquiry, false);
								$casamail_msgs = $this->sendCasamail($inquiry, $formData, false, false);
								if ($casamail_msgs) {
									$msg .= 'CASAMAIL Fehler: '. print_r($casamail_msgs, true);
									$state = 'danger';
								}

								$this->sendGaEvent('inquiry-sent', get_cxm($inquiry->ID, 'email'), 1);

								do_action('cxm_after_inquirysend', $formData);

								//empty form
								$formData = $this->getFormData(true);
							}
						} else {
							$msg = __('Inquiry has been sent. Thank you!', 'complexmanager');
							$state = 'success';

							$inq_post = array(
								'post_content'   => '',
								'post_title'     => $formData['first_name'] . ' ' . $formData['last_name'],
								'post_status'    => 'publish',
								'post_type'      => 'complex_inquiry',
								'ping_status'    => 'closed',
								'comment_status' => 'closed',
							);

							do_action('cxm_before_inquirystore', $formData);

							$inquiry = $this->storeInquiry($inq_post, $formData);

							do_action('cxm_before_inquirysend', $formData);

							$this->sendEmail($inquiry, false);
							$this->sendRemcat($inquiry, false);
							$casamail_msgs = $this->sendCasamail($inquiry, $formData, false, false);
							if ($casamail_msgs) {
								$msg .= 'CASAMAIL Fehler: '. print_r($casamail_msgs, true);
								$state = 'danger';
							}

							$this->sendGaEvent('inquiry-sent', get_cxm($inquiry->ID, 'email'), 1);

							do_action('cxm_after_inquirysend', $formData);

							//empty form
							$formData = $this->getFormData(true);
						}					
					}
				} 		

			} else {
				$msg = __('Please check the following and try again:', 'complexmanager');
				$msg .= '<ul>';
				foreach ($this->getFormMessages() as $col => $message) {
					$msg .= '<li>' . $message . '</li>';
				}
				$msg .= '</ul>';
				$state = 'danger';
				$messages = $this->getFormMessages();
			}

		}

		$terms = get_terms( 'inquiry_reason', array('hide_empty'        => false ));
		if ($terms) {
			$reasons = $terms;
		}

		/* $disableajax = false;
		if (isset($args['disable_ajax'])) {
			$disableajax = true;
		} */

		$template->set('messages', $messages);
		$template->set('message', $msg);
		$template->set('state', $state);
		$template->set('data', $formData);
		$template->set('buildings', $buildings);
		$template->set('reasons', $reasons);
		$template->Set('beforeFormParts', '');
		$template->Set('afterFormParts', '');
		$message = $template->apply( 'contact-form.php' );
		return $message;
	}

	private function verifyCaptcha($captchaResponse) {
	   $post_data = http_build_query(
	       array(
	           'secret' => $this->get_option('recaptcha_secret'),
	           'response' => $_POST['g-recaptcha-response'],
	           'remoteip' => $_SERVER['REMOTE_ADDR']
	       )
	   );
	   $opts = array('http' =>
	       array(
	           'method'  => 'POST',
	           'header'  => 'Content-type: application/x-www-form-urlencoded',
	           'content' => $post_data
	       )
	   );
	   $context  = stream_context_create($opts);
	  // print_r($opts);
	   $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
	   $result = json_decode($response, true);
	 //  print_r($result);
	   
	   if (!$result['success']) {
	       throw new \Exception('Gah! CAPTCHA verification failed. Please email me directly at: jstark at jonathanstark dot com', 1);
	   } else {
	   		return 'success';
	   }
	}
}



// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\render', 'init' ) );
