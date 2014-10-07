<?php
namespace casasoft\complexmanager;


class unit_archive_cols extends Feature {

	public function __construct() {
		//add_filter( 'manage_edit-complex_unit_columns', array( $this, 'editColumns' )) ;
	}

	public function editColumns($columns){
		/*$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Movie' ),
			'duration' => __( 'Duration' ),
			'genre' => __( 'Genre' ),
			'date' => __( 'Date' )
		);

		die('hello');

		return $columns;*/
	}
}
//add_action( 'manage_complex_unit_posts_custom_column', array( 'casasoft\complexmanager\unit_archive_cols', 'init' ));