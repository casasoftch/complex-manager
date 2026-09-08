<?php
/**
 * The remote host file to process Complex Manager update requests.
 *
 * Deploy this file to https://wp.casasoft.com/complex-manager/update.php.
 * The update ZIP is always https://wp.casasoft.com/complex-manager/latest.zip.
 */

if ( ! isset( $_POST['action'] ) ) {
	echo '0';
	exit;
}

// Set up the properties shared by update-check and details requests.
$obj              = new stdClass();
$obj->slug        = 'complex-manager';
$obj->name        = 'Complex Manager';
$obj->plugin_name = 'complex-manager';
$obj->version     = '1.1.0';
$obj->new_version = '1.1.0';
$obj->url         = 'https://casasoft.ch';
$obj->package     = 'https://wp.casasoft.com/complex-manager/latest.zip';
$obj->requires_php = '8.0';

switch ( $_POST['action'] ) {
	case 'version':
		echo serialize( $obj );
		break;

	case 'info':
		$obj->requires      = '4.0';
		$obj->tested        = '6.9.4';
		$obj->last_updated  = '2026-09-01';
		$obj->download_link = $obj->package;
		$obj->sections      = array(
			'description' => 'Complex Manager manages and presents real-estate building project sales.',
			'changelog'   => '<h4>1.1.0</h4><ul><li>Adds the Flatfox public-listing importer with hourly synchronization and source-safe cleanup.</li></ul><h4>1.0.0</h4><ul><li>Introduces the reorganised, translated settings interface.</li></ul>',
		);
		echo serialize( $obj );
		break;

	case 'license':
		echo serialize( $obj );
		break;
}
