<?php

function get_cxm($object_id = false, $key = false, $label = false, $type = false){
	$fm = new \casasoft\complexmanager\field_manager;
	$post = false;
	if ($object_id) {
		$post = get_post($object_id);
		$type = $post->post_type;
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

function render_cxm_form(){
	$render = new \casasoft\complexmanager\render;
	return $render->renderForm(array());
}