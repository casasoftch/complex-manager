<?php
namespace casasoft\complexmanager;

class field_manager extends Feature {

	public function __construct() {
	}

	public function getInquiryDefaults(){
		return array(
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
			'gender' => 'male'
		);
	}

	public function getInquiryItem($inquiry, $key){
		$prefix = '_complexmanager_inquiry_';
		$metas = array();
		foreach (get_post_meta($inquiry->ID) as $le_key => $le_value) {
			if (strpos($le_key, $prefix) === 0) {
				$metas[str_replace($prefix, '', $le_key)] = $le_value[0];
			}
		}
		$metas = array_merge($this->getInquryDefaults(), $metas);

		$datas = array(
			'first_name' => array(
				'label' => __('First name', 'complexmanager'),
				'value' => $metas['first_name']
			),
			'last_name' => array(
				'label' => __('First name', 'complexmanager'),
				'value' => $metas['last_name']
			),
			'phone' => array(
				'label' => __('Phone', 'complexmanager'),
				'value' => $metas['phone']
			),
			'street' => array(
				'label' => __('Street', 'complexmanager'),
				'value' => $metas['street']
			),
			'postal_code' => array(
				'label' => __('ZIP', 'complexmanager'),
				'value' => $metas['postal_code']
			),
			'locality' => array(
				'label' => __('City', 'complexmanager'),
				'value' => $metas['locality']
			),
			'email' => array(
				'label' => __('Email', 'complexmanager'),
				'value' => $metas['email']
			),
			'subject' => array(
				'label' => __('Subject', 'complexmanager'),
				'value' => $metas['subject']
			),
			'message' => array(
				'label' => __('Message', 'complexmanager'),
				'value' => $metas['message']
			),

			//specials
			'name' => array(
				'label' => __('Name', 'complexmanager'),
				'value' => ''
			),
			'address_html' => array(
				'label' => __('Address', 'complexmanager'),
				'value' => ''
			),
			'address_text' => array(
				'label' => __('Address', 'complexmanager'),
				'value' => ''
			)
		);

		//name special
		if ($metas['first_name'].$metas['last_name']) {
			$salutation = ($metas['gender'] ? ($metas['gender'] == 'male' ? __('Mr.', 'complexmanager') : __('Mrs.', 'complexmanager')) : '' );
			$datas['name']['value'] = trim($salutation . " " . $metas['first_name'] . ' ' . $metas['last_name']);
		}

		//address special
		$lines = array();
		$lines[] = $metas['street'];
		$lines[] = trim($metas['postal_code'] . ' ' . $metas['locality']);
		$lines = array_filter($lines);
		if (count($lines)) {
			$datas['address_html']['value'] = implode("<br>", $lines);
			$datas['address_text']['value'] = implode("\n", $lines);
		}

	

		if (isset($datas[$key])) {
			return $datas[$key];
		}
		return false;
	}

	public function getInquiryField($inquiry, $key, $label = false){
		$item = $this->getInquiryItem($inquiry, $key);
		if ($item) {
			if ($label) {
				return $item['label'];
			} else {
				return $item['value'];	
			}
		}
		return '';
	}

	public function getInquiryItem($inquiry, $key){
		return false;
	}

	public function getUnitField($unit, $key, $label = false){
		$item = $this->getUnitItem($unit, $key);
		if ($item) {
			if ($label) {
				return $item['label'];
			} else {
				return $item['value'];	
			}
		}
		return '';
	}

}

function get_cxm($object_id, $key, $label = false){
	$fm = new field_manager;
	$post = get_post($object_id);
	if ($post) {
		if ($post->post_type == 'complex_unit') {
			return $fm->getUnitField($post, $key);
		} elseif ($post->post_type == 'complex_inquiry') {
			return $fm->getInquiryField($post, $key);
		}
	}
	return '';
}

function get_cxm_label($object_id, $key){
	return get_cxm($object_id, $key, true);
}