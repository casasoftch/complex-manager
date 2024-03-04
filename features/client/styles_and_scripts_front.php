<?php
namespace casasoft\complexmanager;


class styles_and_scripts_front extends Feature {


	public function __construct() {
		wp_enqueue_style( 'complex-manager-front', PLUGIN_URL . 'assets/css/complex-manager-front.css', array(), '11', 'screen' );
		wp_enqueue_script( 'complex-manager-front', PLUGIN_URL . 'assets/js/complex-manager-front.js', array('jquery'), '29', true);
		if ($this->get_option('recaptcha') && $this->get_option('recaptcha_v3') && !$this->get_option('honeypot')) {
			wp_enqueue_script('recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render=' . $this->get_option('recaptcha'), array(), false, true );
		} elseif ($this->get_option('recaptcha') && !$this->get_option('honeypot')) {
			$lang = substr(get_bloginfo('language'), 0, 2);
			wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?hl='.$lang, array(), false, true );
		}
	}

}

add_action( 'wp_enqueue_scripts', array( 'casasoft\complexmanager\styles_and_scripts_front', 'init' )  );
