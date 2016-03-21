<?php
namespace casasoft\complexmanager;

class render extends Feature {

	public function __construct() {
		$this->add_action( 'init', 'set_shortcodes' );
		
	}

	public function set_shortcodes() {
		add_shortcode( 'CXM-list', array($this, 'shortcode_list'));
		add_shortcode( 'CXM-list-unit', array($this, 'shortcode_list_unit'));
		add_shortcode( 'CXM-graphic', array($this, 'shortcode_graphic'));
		add_shortcode( 'CXM-filter', array($this, 'shortcode_filter'));
	}

	// [CXM-list cols="name,price,rent" labels="Name,Preis,Mietpreis" sales="rent" type="3"]
	function shortcode_list( $atts ) {
	    $a = shortcode_atts( array(
	        'cols' => '',
	        'labels' => '',
	    ), $atts );

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

	    return $this->renderTable($cols);
	}

	// [CXM-list-unit cols="name,price,rent" labels="Name,Preis,Mietpreis" sales="rent" type="3"]
	function shortcode_list_unit( $atts ) {
		//render single unit list
	}


	// [CXM-graphic]
	function shortcode_graphic( $atts ) {
	    $a = shortcode_atts( array(
	    ), $atts );

	    return $this->renderGraphic();
	}

	// [CXM-filter]
	function shortcode_filter( $atts ) {
	    $a = shortcode_atts( array(
	    ), $atts );

	    return $this->renderFilter();
	}

	public function renderTable($cols){
		/*if (!$cols) {
			$cols = array();
			foreach (get_default_cxm('unit') as $key => $col) {
				if (in_array($key, array('name', 'status'))) {
					$cols[] = array('field' => $key, 'label' => $col['label']);
				}
			}
		}*/

		$unit_args = array(
			'posts_per_page' => 99,
			'post_type' => 'complex_unit',
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);
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


		$template = $this->get_template();
		$template->set( 'cols', $cols );
		$template->set( 'buildings', $buildings );
		$template->set( 'form', $this->renderForm($buildings) );
		
		$message = $template->apply( 'list.php' );
		return $message;
	}

	public function renderSingleTable($cols){
		//render single table for a unit
	}

	public function renderGraphic(){
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

		return $formData;
	}

	public function getFormMessages(){
		$defaults = array(
			'first_name' => __('First name is required', 'complexmanager'),
			'last_name' => __('Last name is required', 'complexmanager'),
			'email' => __('Email is not valid', 'complexmanager'),
			'phone' => __('A phone number is required', 'complexmanager'),
			'street' => __('A street address is required', 'complexmanager'),
			'postal_code' => __('ZIP is required', 'complexmanager'),
			'locality' =>  __('City is required', 'complexmanager'),
			'subject' => '',
			'message' => '',
			'unit_id' => '',
			'post' => __('Ivalid post', 'complexmanager'),
			'gender' => ''
		);

		$messages = array();
		foreach ($this->getFormData() as $col => $value) {
			switch ($col) {
				case 'first_name':
				case 'last_name':
				case 'phone':
				case 'street':
				case 'postal_code':
				case 'locality':
					if (!$value) {
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
				'Interessent Firma ' => '', //
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


			//CASAMAIL
			$data                = array();
			$data['firstname']   = get_cxm($inquiry->ID, 'first_name');
			$data['lastname']    = get_cxm($inquiry->ID, 'last_name');
			//$data['gender']      = 1;
			$data['street']      = get_cxm($inquiry->ID, 'street');
			//$data['legal_name']  = 'Firma';
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
			if ($term) {
				$data['extra_data'] = json_encode(array("acquiredThrough" => $term->name));
			}

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


	public function renderForm(){
		$template = $this->get_template();
		
		$unit_args = array(
			'post_type' => 'complex_unit',
			'posts_per_page' => 99,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);
		$reasons = array();
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

				$inquiry = $this->storeInquiry($inq_post, $formData);

				$this->sendEmail(false, $inquiry);
				$this->sendRemcat(false, $inquiry);
				$casamail_msgs = $this->sendCasamail(false, false, $inquiry, $formData);
				if ($casamail_msgs) {
					$msg .= 'CASAMAIL Fehler: '. print_r($casamail_msgs);
					$state = 'danger';
				}

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
		$message = $template->apply( 'contact-form.php' );
		return $message;	
	}

}



// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\render', 'init' ) );