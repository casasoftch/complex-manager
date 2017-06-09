<?php
namespace casasoft\complexmanager;


class inquiry_options_page extends Feature {

	public function __construct() {
		acf_add_options_sub_page(array(
			'title' => 'Anfrage Optionen',
			'parent' => 'edit.php?post_type=complex_inquiry'
		));
	}

}
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\inquiry_options_page', 'init' ));
