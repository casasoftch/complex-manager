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

	public function getInquiryItems($inquiry = false, $specials = true){
		$prefix = '_complexmanager_inquiry_';
		$metas = array();
		if ($inquiry) {
			foreach (get_post_meta($inquiry->ID) as $le_key => $le_value) {
				if (strpos($le_key, $prefix) === 0) {
					$metas[str_replace($prefix, '', $le_key)] = $le_value[0];
				}
			}
		}
		$metas = array_merge($this->getInquiryDefaults(), $metas);

		$datas = array(
			'first_name' => array(
				'label' => __('First name', 'complexmanager'),
				'value' => $metas['first_name']
			),
			'last_name' => array(
				'label' => __('Last name', 'complexmanager'),
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
			'gender' => array(
				'label' => __('Gender', 'complexmanager'),
				'value' => $metas['gender']
			),
			'unit_id' => array(
				'label' => __('Unit', 'complexmanager'),
				'value' => $metas['unit_id']
			),
		);
		if ($specials) {
			$datas['name'] = array(
				'label' => __('Name', 'complexmanager'),
				'value' => ''
			);
			$datas['address_html'] = array(
				'label' => __('Address', 'complexmanager'),
				'value' => ''
			);
			$datas['address_text'] = array(
				'label' => __('Address', 'complexmanager'),
				'value' => ''
			);
			$datas['unit'] = array(
				'label' => __('Unit', 'complexmanager'),
				'value' => ''
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

			//unit special
			if ($metas['unit_id']) {
				$unit = get_post($metas['unit_id']);
				if ($unit) {
					$datas['unit']['value'] = $unit;
				}
			}
		}

		return $datas;

	}

	public function getInquiryItem($inquiry, $key){
		$datas = $this->getInquiryItems($inquiry);

		if (isset($datas[$key])) {
			return $datas[$key];
		}
		return false;
	}

	public function getInquiryField($inquiry = false, $key, $label = false){
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

	public function getUnitDefaults(){
		return array(
			'name' => '',
			'purchase_price' => '',
			'rent_net' => '',
			'number_of_rooms' => '',
			'story' => '',
			'status' => 'available',
			'currency' => 'CHF',
			'living_space' => ''
		);
	}

	public function render_money($value, $currency = ''){
		$before = true;
		$space = '&nbsp;';
		switch ($currency) {
			case 'EUR': $currency = '€'; $before = false; $space = ''; break;
			case 'USD': $currency = '$'; break;
			case 'GBP': $currency = '£'; break;
			case 'CHF': $currency = '.–'; $before = false; $space = ''; break;
		}
		return ($before ? $currency . $space : '') . number_format($value, 0 ,".", "'")  . (!$before ? $space . $currency : '');
	}

	public function getUnitItems($unit = false, $specials = false){
		$prefix = '_complexmanager_unit_';
		$metas = array();
		if ($unit) {
			foreach (get_post_meta($unit->ID) as $le_key => $le_value) {
				if (strpos($le_key, $prefix) === 0) {
					$metas[str_replace($prefix, '', $le_key)] = $le_value[0];
				}
			}
		}
		$metas = array_merge($this->getUnitDefaults(), $metas);

		$datas = array(
			'name' => array(
				'label' => __('Unit', 'complexmanager'),
				'value' => ($unit ? $unit->post_title : '')
			),
			'purchase_price' => array(
				'label' => __('Purchase price', 'complexmanager'),
				'value' => $metas['purchase_price']
			),
			'currency' => array(
				'label' => __('Currency', 'complexmanager'),
				'value' => $metas['currency']
			),
			'rent_net' => array(
				'label' => __('Rent', 'complexmanager'),
				'value' => $metas['rent_net']
			),
			'number_of_rooms' => array(
				'label' => __('Rooms', 'complexmanager'),
				'value' => $metas['number_of_rooms']
			),
			'story' => array(
				'label' => __('Floor', 'complexmanager'),
				'value' => $metas['story']
			),
			'status' => array(
				'label' => __('Status', 'complexmanager'),
				'value' => $metas['status']
			),

			'living_space' => array(
				'label' => __('Living space', 'complexmanager'),
				'value' => $metas['living_space']
			),
		);
		if ($specials) {
			$datas['rendered_purchase_price'] = array(
					'label' => __('Purchase price', 'complexmanager'),
					'value' => ''
				);
			$datas['rendered_rent_net'] = array(
				'label' => __('Rent', 'complexmanager'),
				'value' => ''
			);
			$datas['rendered_living_space'] = array(
				'label' => __('Living space', 'complexmanager'),
				'value' => ''
			);

			if ((int) $metas['purchase_price']) {
				$value = (int) $metas['purchase_price'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['rendered_purchase_price']['value'] = $this->render_money($value, $currency);
				}
			}
			if ((int) $metas['rent_net']) {
				$value = (int) $metas['rent_net'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['rendered_rent_net']['value'] = $this->render_money($value, $currency);
				}
			}
			if ((float) $metas['living_space']) {
				$value = (float) $metas['living_space'];
				$datas['rendered_living_space']['value'] = number_format($value, 1 ,".", "'") . '&nbsp;m<sup>2</sup>';
			}
		}

		

		return $datas;
	}

	public function getUnitItem($unit = false, $key){
		$datas = $this->getUnitItems($unit);

		if (isset($datas[$key])) {
			return $datas[$key];
		}
		return false;
	}

	public function getUnitField($unit = false, $key, $label = false){
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