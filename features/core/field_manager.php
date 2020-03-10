<?php
namespace casasoft\complexmanager;

class field_manager extends Feature {

	public function __construct() {
	}

	public function getInquiryDefaults(){
		return array(
			'first_name' => '',
			'last_name' => '',
			'legal_name' => '',
			'email' => '',
			'phone' => '',
			'mobile' => '',
			'street' => '',
			'postal_code' => '',
			'locality' => '',
			//'subject' => '',
			'message' => '',
			'unit_id' => '',
			'gender' => 'female',
			'reason' => '',
			'direct_recipient_email' => ''
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
			'legal_name' => array(
				'label' => __('Company', 'complexmanager'),
				'value' => $metas['legal_name']
			),
			'phone' => array(
				'label' => __('Phone', 'complexmanager'),
				'value' => $metas['phone']
			),
			'mobile' => array(
				'label' => __('Mobile', 'complexmanager'),
				'value' => $metas['mobile']
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
			'direct_recipient_email' => array(
				'label' => __('direct_recipient_email', 'complexmanager'),
				'value' => $metas['direct_recipient_email']
			),
		);
		if ($specials) {
			$datas['name'] = array(
				'label' => __('Name', 'complexmanager'),
				'value' => '',
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
			'min_income' => '',
			'max_income' => '',
			'min_persons' => '',
			'max_persons' => '',
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
			'download_file' => '',
			'download_label' => '',

			'r_purchase_price' => '',
			'r_rent_net' => '',
			'r_rent_gross' => '',
			'r_living_space' => '',
			'r_usable_space' => '',
			'r_terrace_space' => '',
			'r_balcony_space' => '',
			'r_extra_costs' => '',
			'custom_1' => '',
			'custom_2' => '',
			'custom_3' => '',
			'quick-download' => '',
			'r_link' => '',
			'link' => '',
			'link_target' => '',
			'link_url' => '',
			'link_label' => '',

		);
	}

	public function render_money($value, $currency = ''){
		$before = true;
		$space = '&nbsp;';
		switch ($currency) {
			case 'EUR': $currency = '€'; $before = false; $space = ''; break;
			case 'USD': $currency = '$'; break;
			case 'GBP': $currency = '£'; break;
			//case 'CHF': $currency = '.–'; $before = false; $space = ''; break;
			case 'CHF': $currency = 'CHF'; $before = true; $space = ' '; break;
		}
		return ($before ? $currency . $space : '') . number_format($value, 0 ,".", $this->get_option('thousands_seperator', "'"))  . (!$before ? $space . $currency : '');
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
				'value' => ($unit ? $unit->post_title : ''),
				'pure_value' => ''
			),
			'purchase_price' => array(
				'label' => __('Purchase price', 'complexmanager'),
				'value' => $metas['purchase_price'],
				'pure_value' => $metas['purchase_price']
			),
			'currency' => array(
				'label' => __('Currency', 'complexmanager'),
				'value' => $metas['currency'],
				'pure_value' => $metas['currency']
			),
			'rent_net' => array(
				'label' => __('Rent', 'complexmanager'),
				'value' => $metas['rent_net'],
				'pure_value' => $metas['rent_net']
			),
			'number_of_rooms' => array(
				'label' => __('Rooms', 'complexmanager'),
				'value' => $metas['number_of_rooms'],
				'pure_value' => $metas['number_of_rooms']
			),


			'min_income' => array(
				'label' => __('Income (min)', 'complexmanager'),
				'value' => $metas['min_income'],
				'pure_value' => $metas['min_income']
			),
			'max_income' => array(
				'label' => __('Income (max)', 'complexmanager'),
				'value' => $metas['max_income'],
				'pure_value' => $metas['max_income']
			),
			'min_persons' => array(
				'label' => __('Number of people (min)', 'complexmanager'),
				'value' => $metas['min_persons'],
				'pure_value' => $metas['min_persons']
			),
			'max_persons' => array(
				'label' => __('Number of people (max)', 'complexmanager'),
				'value' => $metas['max_persons'],
				'pure_value' => $metas['max_persons']
			),

			'story' => array(
				'label' => __('Floor', 'complexmanager'),
				'value' => $metas['story'],
				'pure_value' => $metas['story'],
			),
			'status' => array(
				'label' => __('Status', 'complexmanager'),
				'value' => $metas['status'],
				'pure_value' => $metas['status'],
			),
			'idx_ref_house' => array(
				'label' => __('IDX / REMCat House Ref.', 'complexmanager'),
				'value' => $metas['idx_ref_house'],
				'pure_value' => $metas['idx_ref_house'],
			),
			'idx_ref_object' => array(
				'label' => __('IDX / REMCat Object Ref.', 'complexmanager'),
				'value' => $metas['idx_ref_object'],
				'pure_value' => $metas['idx_ref_object'],
			),
			'living_space' => array(
				'label' => __('Living space', 'complexmanager'),
				'value' => $metas['living_space'],
				'pure_value' => $metas['living_space'],
			),
			'usable_space' => array(
				'label' => __('Usable space', 'complexmanager'),
				'value' => $metas['usable_space'],
				'pure_value' => $metas['usable_space'],
			),
			'terrace_space' => array(
				'label' => __('Terrace space', 'complexmanager'),
				'value' => $metas['living_space'],
				'pure_value' => $metas['living_space'],
			),
			'balcony_space' => array(
				'label' => __('Balcony space', 'complexmanager'),
				'value' => $metas['living_space'],
				'pure_value' => $metas['living_space'],
			),
			'extra_costs' => array(
				'label' => __('Extra Costs', 'complexmanager'),
				'value' => $metas['living_space'],
				'pure_value' => $metas['living_space'],
			),
			'download_file' => array(
				'label' => __('Download file', 'complexmanager'),
				'value' => $metas['download_file'],
				'pure_value' => $metas['download_file'],
			),
			'download_label' => array(
				'label' => __('Download label', 'complexmanager'),
				'value' => $metas['download_label'],
				'pure_value' => $metas['download_label'],
			),
			'custom_1' => array(
				'label' => sprintf(__( 'Custom %d', 'complexmanager' ), 1),
				'value' => str_replace(' m2', ' m<sup>2</sup>', $metas['custom_1']),
				'pure_value' => $metas['custom_1'],
			),
			'custom_2' => array(
				'label' => sprintf(__( 'Custom %d', 'complexmanager' ), 2),
				'value' => str_replace(' m2', ' m<sup>2</sup>', $metas['custom_2']),
				'pure_value' => $metas['custom_2'],
			),
			'custom_3' => array(
				'label' => sprintf(__( 'Custom %d', 'complexmanager' ), 3),
				'value' => str_replace(' m2', ' m<sup>2</sup>', $metas['custom_3']),
				'pure_value' => $metas['custom_3'],
			),
			'link_target' => array(
				'label' => __('Link target', 'complexmanager'),
				'value' => $metas['link_target'],
				'pure_value' => $metas['link_target'],
			),
			'link_url' => array(
				'label' => __('Link url', 'complexmanager'),
				'value' => $metas['link_url'],
				'pure_value' => $metas['link_url'],
			),
			'link_label' => array(
				'label' => __('Link label', 'complexmanager'),
				'value' => $metas['link_label'],
				'pure_value' => $metas['link_label'],
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
			$datas['r_link'] = array(
				'label' => __('Link', 'complexmanager'),
				'value' => ''
			);

			$value = $metas['purchase_price'];
			if (is_numeric($value) && $value !== '0') {
				$currency = $metas['currency'];
				$datas['r_purchase_price']['value'] = $this->render_money($value, $currency);
				$datas['r_purchase_price']['pure_value'] = $value;
			} elseif ($value === '0') {
				$datas['r_purchase_price']['value'] = __('upon request', 'complexmanager');
				$datas['r_purchase_price']['pure_value'] = $value;
			}
			if ((int) $metas['rent_net']) {
				$value = (int) $metas['rent_net'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['r_rent_net']['value'] = $this->render_money($value, $currency);
					$datas['r_rent_net']['pure_value'] = $value;
				}
			}
			
			if ((int) $metas['rent_net'] && (int) $metas['extra_costs']) {
				$value = $metas['rent_net']+$metas['extra_costs'];
				$datas['rent_gross']['value'] = $value;
				$datas['rent_gross']['pure_value'] = $value;
			}

			if ((int) $datas['rent_gross']['value']) {
				$value = $datas['rent_gross']['value'];
				$currency = $metas['currency'];
				$datas['r_rent_gross']['value'] = $this->render_money($value, $currency);
				$datas['r_rent_gross']['pure_value'] = $value;
			}
			
			$decimal_spaces = $this->get_option('space_decimal', 1);

			if ((float) $metas['living_space']) {
				$value = (float) $metas['living_space'];
				$datas['r_living_space']['value'] = number_format($value, $decimal_spaces ,".", $this->get_option('thousands_seperator', "'")) . '&nbsp;m<sup>2</sup>';
				$datas['r_living_space']['pure_value'] = $value;
			}
			if ((float) $metas['usable_space']) {
				$value = (float) $metas['usable_space'];
				$datas['r_usable_space']['value'] = number_format($value, $decimal_spaces ,".", $this->get_option('thousands_seperator', "'")) . '&nbsp;m<sup>2</sup>';
				$datas['r_usable_space']['pure_value'] = $value;
			}
			if ((float) $metas['terrace_space']) {
				$value = (float) $metas['terrace_space'];
				$datas['r_terrace_space']['value'] = number_format($value, $decimal_spaces ,".", $this->get_option('thousands_seperator', "'")) . '&nbsp;m<sup>2</sup>';
				$datas['r_terrace_space']['pure_value'] = $value;
			}
			if ((float) $metas['balcony_space']) {
				$value = (float) $metas['balcony_space'];
				$datas['r_balcony_space']['value'] = number_format($value, $decimal_spaces ,".", $this->get_option('thousands_seperator', "'")) . '&nbsp;m<sup>2</sup>';
				$datas['r_balcony_space']['pure_value'] = $value;
			}
			if ((int) $metas['extra_costs']) {
				$value = (int) $metas['extra_costs'];
				if ($value) {
					$currency = $metas['currency'];
					$datas['r_extra_costs']['value'] = $this->render_money($value, $currency);
					$datas['r_extra_costs']['pure_value'] = $value;
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