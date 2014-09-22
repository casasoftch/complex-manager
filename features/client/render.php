<?php
namespace casasoft\complexmanager;

class render extends Feature {

	public $cols = array();
	public $buildings = array();

	public function __construct() {
		$this->add_action( 'init', 'set_shortcodes' );
		$this->cols = array(
			array(
				'field' => 'name',
				'label' => __('Name', 'complexmanager')
			),
			array(
				'field' => 'purchase_price',
				'label' => __('Price', 'complexmanager')
			),
			array(
				'field' => 'rent_net',
				'label' => __('Rent', 'complexmanager')
			),
			array(
				'field' => 'number_of_rooms',
				'label' => __('Rooms', 'complexmanager')
			),
			array(
				'field' => 'story',
				'label' => __('Story', 'complexmanager')
			),
		);
	}

	public function set_shortcodes() {
		add_shortcode( 'CXM-list', array($this, 'shortcode_list'));
		add_shortcode( 'CXM-graphic', array($this, 'shortcode_graphic'));
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
	    	foreach ($this->cols as $col) {
	    		if ($col['field'] == trim($a_col)) {
	    			if (isset($a_labels[$i]) && trim($a_labels[$i]) != '{T}' && trim($a_labels[$i]) != '') {
	    				$col['label'] = trim($a_labels[$i]);
	    			}
	    			$cols[] = $col;
	    			break;
	    		}
	    	}
	    $i++; }

	    return $this->renderTable($cols);
	}


	// [CXM-graphic]
	function shortcode_graphic( $atts ) {
	    $a = shortcode_atts( array(
	    ), $atts );

	    return $this->renderGraphic();
	}

	public function renderTable($cols = array()){
		if (!$cols) {
			$cols = array();
			foreach ($this->cols as $col) {
				if (in_array($col['field'], array('name', 'purchase_price'))) {
					$cols[] = $col;
				}
			}
		}

		$unit_args = array(
			'post_type' => 'complex_unit'
		);
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

		$template = $this->get_template();
		$template->set( 'cols', $cols );
		$template->set( 'buildings', $buildings );
		$template->set( 'form', $this->renderForm($buildings) );
		
		$message = $template->apply( 'list.php' );
		return $message;
	}

	public function renderGraphic(){
		$unit_args = array(
			'post_type' => 'complex_unit'
		);

		$image = PLUGIN_URL.'assets/img/example-project-bg.png';
		$width = 1152;
	    $height = 680;


	    $unit_args = array(
			'post_type' => 'complex_unit'
		);
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

	public function getFormData(){
		$defaults = array(
			'first_name' => '',
			'last_name' => '',
			'email' => '',
			'phone' => '',
			'street' => '',
			'postal_code' => '',
			'locality' => '',
			'subject' => 'habe Intresse an: ***',
			'message' => 'Bitte senden Sie mir Informationsunterlagen und registrieren sie mich als potenzieller käufer/vermierter',
			'unit_id' => '',
			'post' => 0,
			'gender' => 'male'
		);

		$request = array_merge($_GET, $_POST);
		if (isset($request['complex-unit-inquiry'])) {
			$formData = array_merge($defaults, $request['complex-unit-inquiry']);	
		} else {
			$formData = $defaults;
		}

		return $formData;
	}

	public function renderForm(){
		$template = $this->get_template();
		
		$unit_args = array(
			'post_type' => 'complex_unit'
		);
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

		if ($formData['post']) {
			print_r('posted');
		}
		
		$template->set('data', $formData);
		$template->set( 'buildings', $buildings );
		$message = $template->apply( 'contact-form.php' );
		return $message;	
	}

} // End Class



// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\render', 'init' ) );