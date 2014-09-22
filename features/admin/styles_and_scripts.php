<?php
namespace casasoft\complexmanager;


class styles_and_scripts extends Feature {


	public function __construct() {
		wp_enqueue_style( 'complex-manager-admin', PLUGIN_URL . 'assets/css/complex-manager-admin.css', array(), '1', 'screen' );
	}

}

add_action( 'load-post.php', array( 'casasoft\complexmanager\styles_and_scripts', 'init' )  );

