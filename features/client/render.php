<?php
namespace casasoft\complexmanager;

class render extends Feature {

	private $buildingsStore = null;

	public function __construct() {
		$this->add_action( 'init', 'set_shortcodes' );
		
	}

	public function set_shortcodes() {
		add_shortcode( 'CXM-list', array($this, 'shortcode_list'));
		add_shortcode( 'CXM-list-unit', array($this, 'shortcode_list_unit'));
		add_shortcode( 'CXM-graphic', array($this, 'shortcode_graphic'));
		add_shortcode( 'CXM-filter', array($this, 'shortcode_filter'));
	}

	// [CXM-list cols="name,price,rent" labels="Name,Preis,Mietpreis" unit="13" sales="rent" type="3"]
	function shortcode_list( $atts ) {
	    $a = shortcode_atts( array(
	        'cols' => '',
	        'labels' => '',
	        'integrate_form' => "1",
	        'collapsible' => "1",
	        'building_id' => false
	    ), $atts );

	    $a['integrate_form'] = (bool) $a['integrate_form'];
	    $a['collapsible'] = (bool) $a['collapsible'];


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
				return $a["order"] - $b["order"];
			});
	   	}

	    return $this->renderTable($cols, $a['integrate_form'], $a['collapsible'], ($a['building_id'] ? $a['building_id'] : false));
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
				return $a["order"] - $b["order"];
			});
	   	}

	    return $this->renderGraphic($cols, ($a['building_id'] ? $a['building_id'] : false));
	}

	// [CXM-filter]
	function shortcode_filter( $atts ) {
	    $a = shortcode_atts( array(
	    ), $atts );

	    return $this->renderFilter();
	}

	private function loadBuildings($building_id){
		$unit_args = array(
			'posts_per_page' => 99,
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

	private function getBuildings($building_id = false){
		$storeKey = ($building_id ? $building_id : 'all');
		if (!isset($this->buildingsStore[$storeKey])) {
			$this->buildingsStore[$storeKey] = $this->loadBuildings($building_id);
		}
		return $this->buildingsStore[$storeKey];
	}

	private function prepareUnit($unit, $cols){
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
				$label_text = (isset($col['label_'.$lang]) ? $col['label_'.$lang] : $col['label']);
				$displayItem['label'] = nl2br(str_replace('\n', "\n", ($label_text ? $label_text : get_cxm_label(false, $field, 'complex_unit') ) ) );

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
						$displayItem['td_classes'] = 'hidden-sm hidden-xs col-status';
						$displayItem['hidden-xs'] = $col['hidden-xs'];
						break;
					case 'r_purchase_price':
					case 'r_rent_net':
					case 'r_rent_gross':
						$currency = false;
						if (
							$col['hidden-reserved'] == 0
							||
							!in_array($status, array('pre-reserved', 'reserved', 'sold', 'rented'))
						) {
							$value = get_cxm($unit, $field);	
							if (get_cxm($unit, 'unit_currency')) {
								$currency = get_cxm($unit, 'unit_currency');
							}
						} else {
							$value = '';
						}
						$displayItem['value'] = ($currency ? $currency . ' ' : '') . $value;
						$displayItem['td_classes'] = ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field;
						$displayItem['hidden-xs'] = $col['hidden-xs'];

						break;
					case 'quick-download':
						if (
							$col['hidden-reserved'] == 0
							||
							!in_array($status, array('pre-reserved', 'reserved', 'sold', 'rented'))
						) {
							if (get_cxm($unit, 'download_file')) {
								$value = '<a target="_blank" class="btn btn-xs btn-default" href="' . get_cxm($unit, 'download_file') . '">' . (get_cxm($unit, 'download_label') ? get_cxm($unit, 'download_label') : 'Download') . '</a>';
							} else {
								$value = '';
							}
							
						} elseif(
							$col['hidden-reserved'] == 1 
							&& in_array($status, array('pre-reserved', 'reserved', 'sold', 'rented'))
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
							!in_array($status, array('pre-reserved', 'reserved', 'sold', 'rented'))
						) {
							$value = get_cxm($unit, $field);	
						} else {
							$value = '';
						}
						if ($value) {
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

		return $the_unit;
	}

	private function prepareBuildings($buildings, $cols){

		$the_buildings = array();
		foreach ($buildings as $building) {
			$building['description'] = ($building['term']->description ? '<p class="unit-description">' . $building['term']->description . '</p>' : '');
			$building['the_units'] = array();
			$col_options = get_term_meta( $building['term']->term_id, 'building_col_options', true );
			$hide_building = get_term_meta( $building['term']->term_id, 'hide_building', true );
			if ($hide_building) {
				continue;
			}

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

			$the_buildings[] = $building;
		}
		return $the_buildings;
	}

	public function renderTable($cols, $integrate_form = true, $collapsible = true, $building_id = false){
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
		$template->set( 'buildings', $this->getBuildings($building_id) );
		$template->set( 'the_buildings', $this->prepareBuildings($this->getBuildings($building_id), $cols));
		$template->set( 'collapsible', $collapsible );

		if ($integrate_form) {
			$template->set( 'form', $this->renderForm($this->getBuildings()));	
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
		$image = PLUGIN_URL.'assets/img/example-project-bg.png';
		$width = 1152;
	    $height = 680;
	    $set = false;

	    //building specific base image
	    if ($building_id) {
	    	$project_image_alt_id = get_field('alternate-base-image', 'building_'.$building_id);
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
		return $message;
	}

	public function renderFilter(){
		$unit_args = array(
			'post_type' => 'complex_unit',
			'posts_per_page' => 99
		);

		$image = PLUGIN_URL.'assets/img/example-project-bg.png';
		$width = 1152;
	    $height = 680;

		$buildings = array();
		$building_terms = get_terms( 'building', array() );
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
		$template->set( 'image', $image );
		$template->set( 'width', $width );
		$template->set( 'height', $height );

		$roomfilters = array();
		foreach ($buildings as $building) {
			foreach ($building['units'] as $unit) {
				$number_of_rooms = get_cxm($unit, 'number_of_rooms');
				if (!in_array($number_of_rooms, $roomfilters)) {
					$roomfilters[] = $number_of_rooms;
				}
			}
		}
		$roomfilters = array_filter($roomfilters);
		asort($roomfilters);

		$template->set( 'roomfilters', $roomfilters );
		
		$message = $template->apply( 'filter.php' );
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


		return $formData;
	}

	public function getFormMessages(){
		$defaults = array(
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

		$messages = array();
		foreach ($this->getFormData() as $col => $value) {
			switch ($col) {
				case 'first_name':
				case 'last_name':
				//case 'legal_name':
				case 'phone':
				case 'street':
				case 'postal_code':
				case 'locality':
				case 'unit_id':
					if (!$value || $value == '–') {
						$messages[$col] = $defaults[$col];
					}
					break;
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

		return $messages;
	}

	public function formValid(){
		if (count($this->getFormMessages())) {
			return false;
		}
		return true;
	}

	public function sendRemcat($to = false, $inquiry){
		if ($to) {
			$remcat = $to;
		} else {
			$remcat = $this->get_option("remcat");	
		}
		if ($remcat) {
			$unit_id = get_cxm($inquiry->ID, 'unit_id');

			$remcat_arr = array(
				'' => '',
				'Immobilienportalname' => 'wp_complex_manager', //homegate
				'Immobilienverwaltung Name' => '', //PSP Management AG
				'Immobilienverwaltung Adresse' => '', //Baslerstrasse 44
				'Immobilienverwaltung PLZ' => '', //4600
				'Immobilienverwaltung Ort' => '', //Olten
				'Immobilienverwaltung Sachbearbeiter' => '', //Urben Michael 
				'Immobilienverwaltung Sachbearbeiter Emailadresse' => '', //remcat@psp.info
				'Objektreferenz' => $this->get_option("idx_ref_property").'.'.get_cxm($unit_id, 'idx_ref_house').'.'.get_cxm($unit_id, 'idx_ref_object'), //*** 6045.01.0202 
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

	public function sendEmail($to = false, $inquiry){
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

	public function sendCasamail($provider = false, $publisher = false, $inquiry, $formData){
		if (!$provider) {
			$provider = $this->get_option("provider_slug");	
		}
		if (!$publisher) {
			$publisher = $this->get_option("publisher_slug");	
		}
		if ($provider && $publisher) {
			$unit_id = get_cxm($inquiry->ID, 'unit_id');
			$unit = get_post($unit_id);

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
			//$data['mobile']       = '000 000 00 00';
			//$data['fax']       = '000 000 00 00';
			$data['email']       = get_cxm($inquiry->ID, 'email');
			$data['message']     = get_cxm($inquiry->ID, 'message');

			$data['provider']               = $provider; //must be registered at CASAMAIL
			$data['publisher']              = $publisher; //must be registered at CASAMAIL
			$data['lang']                   = 'de';
			$data['property_reference']     = $this->get_option("idx_ref_property").'.'.get_cxm($unit_id, 'idx_ref_house').'.'.get_cxm($unit_id, 'idx_ref_object');
			//$data['property_street']        = 'musterstrasse 17';
			//$data['property_postal_code']   = '3291';
			//$data['property_locality']      = 'Ortschaft';
			//$data['property_category']      = 'house';
			//$data['property_country']       = 'CH';
			//$data['property_rooms']         = '3.2';
			//$data['property_type']          = 'rent';
			//$data['property_price']         = '123456';
			//$data['direct_recipient_email'] = 'directemail@domain.ch';

			$term = get_term($formData['reason'], 'inquiry_reason', OBJECT);
			$extra_data = array();
			if ($term) {
				$extra_data['acquiredThrough'] = $term->name;	
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
				$unit_infos = '';
				foreach ($the_unit['displayItems'] as $displayItem) {
					$unit_infos .= $displayItem['label'] . ': ' . $displayItem['value'] . "<br>";
				}
				if ($unit_infos) {
					$extra_data['cmx_unit'] = $unit_infos;
				}

			}

			$data['extra_data'] = json_encode($extra_data);


			$data_string = json_encode($data);                                                                                   
			                                                                                                                     
			$ch = curl_init('http://onemail.ch/api/msg');
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


	public function renderForm($args){
		$template = $this->get_template();

		$reasons = array();

		$unit_args = array(
			'post_type' => 'complex_unit',
			'posts_per_page' => 99,
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
			if ($this->formValid()) {
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

				$this->sendEmail(false, $inquiry);
				$this->sendRemcat(false, $inquiry);
				$casamail_msgs = $this->sendCasamail(false, false, $inquiry, $formData);
				if ($casamail_msgs) {
					$msg .= 'CASAMAIL Fehler: '. print_r($casamail_msgs);
					$state = 'danger';
				}

				do_action('cxm_after_inquirysend', $formData);

				//empty form
				$formData = $this->getFormData(true);
				
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

}



// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\render', 'init' ) );