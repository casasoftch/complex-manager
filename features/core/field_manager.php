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
			//'subject' => '',
			'message' => '',
			'unit_id' => '',
			'gender' => 'male',
			'reason' => '',
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
			/*'subject' => array(
				'label' => __('Subject', 'complexmanager'),
				'value' => $metas['subject']
			),*/
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
			'reason' => array(
				'label' => __('Reason', 'complexmanager'),
				'value' => $metas['reason']
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
			'rent_gross' => '',
			'number_of_rooms' => '',
			'story' => '',
			'status' => 'available',
			'currency' => 'CHF',
			'living_space' => '',
			'usable_space' => '',
			'terrace_space' => '',
			'balcony_space' => '',
			'idx_ref_house' => '',
			'idx_ref_object' => '',
			'extra_costs' => '',

			'r_purchase_price' => '',
			'r_rent_net' => '',
			'r_rent_gross' => '',
			'r_living_space' => '',
			'r_usable_space' => '',
			'r_terrace_space' => '',
			'r_balcony_space' => '',
			'r_extra_costs' => '',

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

	public function getUnitItems($unit = false, $specials = true){
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
			'idx_ref_house' => array(
				'label' => __('IDX / REMCat House Ref.', 'complexmanager'),
				'value' => $metas['idx_ref_house']
			),
			'idx_ref_object' => array(
				'label' => __('IDX / REMCat Object Ref.', 'complexmanager'),
				'value' => $metas['idx_ref_object']
			),
			'living_space' => array(
				'label' => __('Living space', 'complexmanager'),
				'value' => $metas['living_space']
			),
			'usable_space' => array(
				'label' => __('Usable space', 'complexmanager'),
				'value' => $metas['usable_space']
			),
			'terrace_space' => array(
				'label' => __('Terrace space', 'complexmanager'),
				'value' => $metas['living_space']
			),
			'balcony_space' => array(
				'label' => __('Balcony space', 'complexmanager'),
				'value' => $metas['living_space']
			),
			'extra_costs' => array(
				'label' => __('Extra Costs', 'complexmanager'),
				'value' => $metas['living_space']
			),
		);
		if ($specials) {
			$datas['r_purchase_price'] = array(
					'label' => sprintf(__('Purchase price%s in %s%s', 'complexmanager'), '<span class="hidden-sm hidden-xs">', $datas['currency']['value'], '</span>'),
					'value' => ''
				);
			$datas['r_rent_net'] = array(
				'label' => sprintf(__('Rent%s in %s%s', 'complexmanager'), '<span class="hidden-sm hidden-xs">', $datas['currency']['value'], '</span>'),
				'value' => ''
			);
			$datas['rent_gross'] = array(
				'label' => __('Rent gross', 'complexmanager'),
				'value' => ''
			);
			$datas['r_rent_gross'] = array(
				'label' => __('Rent gross', 'complexmanager'),
				'value' => ''
			);
			$datas['r_living_space'] = array(
				'label' => __('Living space', 'complexmanager'),
				'value' => ''
			);
			$datas['r_usable_space'] = array(
				'label' => __('Living space', 'complexmanager'),
				'value' => ''
			);
			$datas['r_terrace_space'] = array(
				'label' => __('Terrace space', 'complexmanager'),
				'value' => ''
			);
			$datas['r_balcony_space'] = array(
				'label' => __('Balcony space', 'complexmanager'),
				'value' => ''
			);
			$datas['r_extra_costs'] = array(
				'label' => sprintf(__('Extra Costs%s in %s%s', 'complexmanager'), '<span class="hidden-sm hidden-xs">', $datas['currency']['value'], '</span>'),
				'value' => ''
			);

			if ((int) $metas['purchase_price']) {
				$value = (int) $metas['purchase_price'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['r_purchase_price']['value'] = $this->render_money($value, $currency);
				}
			}
			if ((int) $metas['rent_net']) {
				$value = (int) $metas['rent_net'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['r_rent_net']['value'] = $this->render_money($value, $currency);
				}
			}
			
			if ((int) $metas['rent_net'] && (int) $metas['extra_costs']) {
				$value = $metas['rent_net']+$metas['extra_costs'];
				$datas['rent_gross']['value'] = $value;
			}

			if ((int) $datas['rent_gross']['value']) {
				$value = $datas['rent_gross']['value'];
				$currency = $metas['currency'];
				$datas['r_rent_gross']['value'] = $this->render_money($value, $currency);
			}
			
			if ((float) $metas['living_space']) {
				$value = (float) $metas['living_space'];
				$datas['r_living_space']['value'] = number_format($value, 1 ,".", "'") . '&nbsp;m<sup>2</sup>';
			}
			if ((float) $metas['usable_space']) {
				$value = (float) $metas['usable_space'];
				$datas['r_usable_space']['value'] = number_format($value, 1 ,".", "'") . '&nbsp;m<sup>2</sup>';
			}
			if ((float) $metas['terrace_space']) {
				$value = (float) $metas['terrace_space'];
				$datas['r_terrace_space']['value'] = number_format($value, 1 ,".", "'") . '&nbsp;m<sup>2</sup>';
			}
			if ((float) $metas['balcony_space']) {
				$value = (float) $metas['balcony_space'];
				$datas['r_balcony_space']['value'] = number_format($value, 1 ,".", "'") . '&nbsp;m<sup>2</sup>';
			}
			if ((int) $metas['extra_costs']) {
				$value = (int) $metas['extra_costs'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['r_extra_costs']['value'] = $this->render_money($value, $currency);
				}
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