<?php
/*
 *	Plugin Name: 	Complex Manager
 *	Description:    Plugin for managing and presenting real-estate building project sales.
 *	Author:         Casasoft AG
 *	Author URI:     http://casasoft.ch
 *	Version: 		0.0.1
 *	Text Domain: 	complexmanager
 *	Domain Path: 	languages/
 */

namespace casasoft\complexmanager;
require_once( 'features/silence.php' );

define( 'casasoft\complexmanager\VERSION', '0.0.1' );
define( 'casasoft\complexmanager\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'casasoft\complexmanager\PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/*
 * The following includes add features to the plugin
 */
require_once( 'features/feature.php' );
require_once( 'features/template.php' );
require_once( 'features/options.php' );
require_once( 'features/class-loader.php' );
require_once( 'features/kit.php' );

/**
 * The central plugin class and bootstrap for the application.
 *
 * While this class is primarily boilerplate code and can be used without alteration,
 * there are a few things you need to edit to get the most out of this kit:
 *  * Add any initialization code that must run *during* the plugins_loaded action in the constructor.
 *  * Edit the return value of the defaults function so that the array contains all your default plugin values.
 *  * Add any plugin activation code to the activate_plugin method.
 *  * Add any plugin deactivation code to the deactivate_plugin method.
 *      - If you don't have any activation code, be sure to comment-out register_deactivation_hook
 */
class ComplexManager extends Kit {

	private static $__instance;

	public static function init() {
		if ( !self::$__instance ) {
			$plugin_dir = basename( dirname( __FILE__ ) );
			load_plugin_textdomain( 'complexmanager', FALSE, $plugin_dir . '/languages/' );
			self::$__instance = new ComplexManager();
			parent::initialize();
		}
		return self::$__instance;
	}

	/**
	 * Constructor: Main entry point for your plugin. Runs during the plugins_loaded action.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Plugin activation hook
	 *
	 * Add any activation code you need to do here, like building tables and such.
	 * You won't need to worry about your options so long as you updated them using the defaults method.
	 *
	 * @static
	 * @hook register_activation_hook
	 */
	public static function activate_plugin() {
		//generate standard inquiry-reasons

		/*$terms = get_terms( 'inquiry_reason', array() );
		print_r($terms);
		die();*/

	}

	/**
	 * Plugin deactivation hook
	 *
	 * Need to clean up your plugin when it's deactivated?  Do that here.
	 * Remember, this isn't when your plugin is uninstalled, just deactivated
	 * ( so it happens when the plugin is updated too ).
	 *
	 * @static
	 * @hook register_deactivation_hook
	 */
	public static function deactivate_plugin() {

	}

} // End Class


//...and away we go!
add_action( 'plugins_loaded', array( 'casasoft\complexmanager\ComplexManager', 'init' ) );
register_activation_hook( __FILE__, array( 'casasoft\complexmanager\ComplexManager', 'activate_plugin' ) );
register_deactivation_hook( __FILE__, array( 'casasoft\complexmanager\ComplexManager', 'deactivate_plugin' ) );