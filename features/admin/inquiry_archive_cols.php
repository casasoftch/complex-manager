<?php
namespace casasoft\complexmanager;


class inquiry_archive_cols extends Feature {

	public function __construct() {
		add_filter( 'manage_edit-complex_inquiry_columns', array( $this, 'editColumns' )) ;
		add_action( 'manage_complex_inquiry_posts_custom_column', array( $this, 'manageColumns' ));
	}

	public function editColumns($columns){
		$columns = array(
			'cb' 		=> '<input type="checkbox" />',
			'title' 	=> __( 'From', 'complexmanager' ),
			'address' 	=> __( 'Address', 'complexmanager' ),
			'email' 	=> __( 'Email', 'complexmanager' ),
			'phone' 	=> __( 'Telephone', 'complexmanager' ),
			'date' => __( 'Date')
		);

		return $columns;
	}

	public function manageColumns( $column ) {
		global $post;


		switch( $column ) {
			case 'address' :
				echo get_cxm($post->ID, 'address_html');
				break;
			case 'email' :
				echo get_cxm($post->ID, 'email');
				break;
			case 'phone' :
				echo get_cxm($post->ID, 'phone');
				break;
			default :
				break;
		}
	}
}
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\inquiry_archive_cols', 'init' ));