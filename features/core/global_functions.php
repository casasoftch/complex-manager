<?php

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

function render_cxm_form($atts){
	/*$a = shortcode_atts( array(
        'unit_id' => ''
    ), $atts );*/

	$render = new \casasoft\complexmanager\render;
	return '<div class="complex-contact-form-wrapper" id="complexContactForm" style="display: block;">' . $render->renderForm($atts) . '</div>';

}
add_shortcode( 'contactform-complex', 'render_cxm_form' );