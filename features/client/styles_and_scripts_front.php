<?php
namespace casasoft\complexmanager;


class styles_and_scripts_front extends Feature {


	public function __construct() {
		wp_enqueue_style( 'complex-manager-front', PLUGIN_URL . 'assets/css/complex-manager-front.css', array(), 3, 'screen' );
		wp_enqueue_script( 'complex-manager-front', PLUGIN_URL . 'assets/js/complex-manager-front.js', array('jquery'), '8');

	}

}

add_action( 'wp_enqueue_scripts', array( 'casasoft\complexmanager\styles_and_scripts_front', 'init' )  );
