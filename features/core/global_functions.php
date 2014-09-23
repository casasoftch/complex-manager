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

function get_default_cxm($type){
	$fm = new \casasoft\complexmanager\field_manager;
	if ($type == 'unit') {
		return $fm->getUnitItems();
	} elseif ($type == 'inquiry') {
		return $fm->getInquiryItems();
	}
	return array();
}