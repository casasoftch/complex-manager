<?php
//namespace casasoft\complexmanager;

function cxm_send_ga_event($action = 'inquiry-sent', $label = 'Anfrage Versand', $value = 1){

	$gap_id = casasoft\complexmanager\PluginOptions::get_option( 'gap_id', false );

	if ($gap_id && is_string($gap_id)) {
		$data = array(
		'v' => 1,
		'tid' => $gap_id,
		'cid' => sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		),
		't' => 'event'
		);


		$data['ec'] = "complex-manager";
		$data['ea'] = $action;
		$data['el'] = $label;
		$data['ev'] = $value;

		//json_encode($formData)

		$url = 'https://www.google-analytics.com/collect';
		$content = http_build_query($data);
		$content = utf8_encode($content);
		//$user_agent = 'Example/1.0 (http://example.com/)';


		//die('sending to:' . $url . '; with data: ' . print_r($data, true));



		$ch = curl_init();
		//curl_setopt($ch,CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded'));
		curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $content);
		$response = curl_exec($ch);
		curl_close($ch);
	}
}

function get_cxm($object_id = false, $key = false, $label = false, $type = false){
	$fm = new \casasoft\complexmanager\field_manager;
	$post = false;
	if ($object_id) {
		$post = get_post($object_id);
		if ($post) {
			$type = $post->post_type;
		} else {
			return '';
		}

	}

	if ($type == 'complex_unit') {
		return $fm->getUnitField($post, $key, $label);
	} elseif ($type == 'complex_inquiry') {
		return $fm->getInquiryField($post, $key, $label);
	}
	return '';
}

function get_cxm_item($object_id = false, $key = false, $type = false){
    $fm = new \casasoft\complexmanager\field_manager;
    $post = false;
    if ($object_id) {
        $post = get_post($object_id);
        if ($post) {
            $type = $post->post_type;
        } else {
            return '';
        }

    }

    if ($type == 'complex_unit') {
        return $fm->getUnitItem($post, $key);
    } elseif ($type == 'complex_inquiry') {
        return $fm->getInquiryItem($post, $key);
    }
    return '';
}

function get_cxm_label($object_id, $key, $type = false){
	return get_cxm($object_id, $key, true, $type);
}

function get_default_cxm($type, $specials = true){
	$fm = new \casasoft\complexmanager\field_manager;
	if ($type == 'unit') {
		return $fm->getUnitItems(false, $specials);
	} elseif ($type == 'inquiry') {
		return $fm->getInquiryItems(false, $specials);
	}
	return array();
}

function render_cxm_form($atts = array()){
	/*$a = shortcode_atts( array(
        'unit_id' => ''
    ), $atts );*/

	$render = new \casasoft\complexmanager\render;
	return '<div class="complex-contact-form-wrapper" id="complexContactForm" style="display: block;">' . $render->renderForm($atts) . '</div>';

}
add_shortcode( 'contactform-complex', 'render_cxm_form' );

add_shortcode( 'CXM-form', 'render_cxm_form' );


function cxm_get_list_col_defaults(){
	return array(
        'name' => array(
            'o_label' => __( 'Name', 'complexmanager' ),
            'active' => 1,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 1,
        ),
        'number_of_rooms' => array(
            'o_label' => __( 'Number of Rooms', 'complexmanager' ),
            'active' => 1,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 2,
        ),
        'story' => array(
            'o_label' => __( 'Apartment Story', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 3,
        ),
        'r_living_space' => array(
            'o_label' => __( 'Living Space', 'complexmanager' ),
            'active' => 1,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 4,
        ),

        'r_usable_space' => array(
            'o_label' => __( 'Usable Space', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 1,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 5,
        ),
        'r_purchase_price' => array(
            'o_label' => __( 'Purchase Price', 'complexmanager' ),
            'active' => 1,
            'hidden-xs' => 0,
            'hidden-reserved' => 1,
            'label' => '',
            'order' => 6,
        ),

        'r_rent_net' => array(
            'o_label' => __( 'Rent Net Price', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 1,
            'label' => '',
            'order' => 7,
        ),

        'r_rent_gross' => array(
            'o_label' => __( 'Rent gross', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 1,
            'label' => '',
            'order' => 8,
        ),



        'r_extra_costs' => array(
            'o_label' => __( 'Extra Costs', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 1,
            'hidden-reserved' => 1,
            'label' => '',
            'order' => 9,
        ),

        'r_terrace_space' => array(
            'o_label' => __( 'Terrace Space', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 1,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 10,
        ),

        'r_balcony_space' => array(
            'o_label' => __( 'Balcony Space', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 1,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 11,
        ),




        'status' => array(
            'o_label' => __( 'Status', 'complexmanager' ),
            'active' => 1,
            'hidden-xs' => 1,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 12,
        ),

        'currency' => array(
            'o_label' => __( 'Currency', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => '',
            'hidden-reserved' => '',
            'label' => '',
            'order' => 13,
        ),

        'custom_1' => array(
            'o_label' => sprintf(__( 'Custom %d', 'complexmanager' ), 1),
            'active' => 0,
            'hidden-xs' => '',
            'hidden-reserved' => '',
            'label' => '',
            'order' => 14,
        ),

        'custom_2' => array(
            'o_label' => sprintf(__( 'Custom %d', 'complexmanager' ), 2),
            'active' => 0,
            'hidden-xs' => '',
            'hidden-reserved' => '',
            'label' => '',
            'order' => 15,
        ),

        'custom_3' => array(
            'o_label' => sprintf(__( 'Custom %d', 'complexmanager' ), 3),
            'active' => 0,
            'hidden-xs' => '',
            'hidden-reserved' => '',
            'label' => '',
            'order' => 16,
        ),

        'quick-download' => array(
            'o_label' => __( 'Download', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 17,
        ),

       

        'min_persons' => array(
            'o_label' => __( 'Number of people (min)', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 18,
        ),
        'max_persons' => array(
            'o_label' => __( 'Number of people (max)', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 19,
        ),
        'min_income' => array(
            'o_label' => __( 'Income (min)', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 20,
        ),
        'max_income' => array(
            'o_label' => __( 'Income (max)', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 21,
        ),

         'r_link' => array(
            'o_label' => __( 'Link', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => 0,
            'hidden-reserved' => 0,
            'label' => '',
            'order' => 22,
        ),


        /*'idx_ref_house' => array(
            'o_label' => __( 'Number of Rooms', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => '',
            'hidden-reserved' => '',
            'label' => '',
            'order' => 1,
        ),
        'idx_ref_object' => array(
            'o_label' => __( 'Number of Rooms', 'complexmanager' ),
            'active' => 0,
            'hidden-xs' => '',
            'hidden-reserved' => '',
            'label' => '',
            'order' => 1,
        ),*/


    );
}
