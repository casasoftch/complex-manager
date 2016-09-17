<?php
namespace casasoft\complexmanager;

class import extends Feature {

	private $buildingsStore = null;

	public function __construct() {
		
	}

	

}



// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\import', 'init' ) );