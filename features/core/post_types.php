<?php
namespace casasoft\complexmanager;

class post_types extends Feature {

	public function __construct() {
		$this->add_action( 'init', 'set_posttypes', 10 );


		/**
		 * Add the scheduling if it doesnt already exist
		 */
		$this->add_action('wp', 'setup_complex_inquiry_schedule');
		
		/**
		 * Add the function that takes care of removing all rows with post_type=complex_inquiry that are older than 6 Months
		 */
		$this->add_action('cxm_posttype_daily_pruning', 'remove_old_posts');		

		if (is_admin()) {
			$this->add_filter( 'dashboard_glance_items', 'glance_items', 10, 1 );
		}
	}

	public function setup_complex_inquiry_schedule() {
	  if (!wp_next_scheduled('cxm_posttype_daily_pruning') ) {
	    wp_schedule_event( time(), 'daily', 'cxm_posttype_daily_pruning');
	  }
	}
	public function remove_old_posts() {
	  global $wpdb;
	  $wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->prefix}posts` WHERE post_type=%s AND post_date < DATE_SUB(NOW(), INTERVAL 182 DAY);",
	  	'complex_inquiry'
	  ));
	}

	public function set_posttypes() {

		$labels = array(
			'name'               => _x( 'Apartment Units', 'post type general name', 'complexmanager' ),
			'singular_name'      => _x( 'Unit', 'post type singular name', 'complexmanager' ),
			'menu_name'          => _x( 'Apartment Units', 'admin menu', 'complexmanager' ),
			'name_admin_bar'     => _x( 'Unit', 'add new on admin bar', 'complexmanager' ),
			'add_new'            => _x( 'Add New', 'unit', 'complexmanager' ),
			'add_new_item'       => __( 'Add New Unit', 'complexmanager' ),
			'new_item'           => __( 'New Unit', 'complexmanager' ),
			'edit_item'          => __( 'Edit Unit', 'complexmanager' ),
			'view_item'          => __( 'View Unit', 'complexmanager' ),
			'all_items'          => __( 'All Units', 'complexmanager' ),
			'search_items'       => __( 'Search Units', 'complexmanager' ),
			'parent_item_colon'  => __( 'Parent Unit:', 'complexmanager' ),
			'not_found'          => __( 'No units found.', 'complexmanager' ),
			'not_found_in_trash' => __( 'No units found in Trash.', 'complexmanager' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'unit' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => null,
			'supports'           => array( 'title', 'thumbnail', 'page-attributes', 'editor' ) // 'editor', 'author', 
		);

		register_post_type( 'complex_unit', $args );

		if (is_admin() && function_exists('pti_set_post_type_icon')) {
			pti_set_post_type_icon( 'complex_unit', 'home' );
		}

		$labels = array(
			'name'              => _x( 'Buildings', 'taxonomy general name', 'complexmanager'  ),
			'singular_name'     => _x( 'Building', 'taxonomy singular name', 'complexmanager'  ),
			'search_items'      => __( 'Search Buildings', 'complexmanager'  ),
			'all_items'         => __( 'All Buildings', 'complexmanager'  ),
			'parent_item'       => __( 'Parent Building', 'complexmanager'  ),
			'parent_item_colon' => __( 'Parent Building:', 'complexmanager'  ),
			'edit_item'         => __( 'Edit Building', 'complexmanager'  ),
			'update_item'       => __( 'Update Building', 'complexmanager'  ),
			'add_new_item'      => __( 'Add New Building', 'complexmanager'  ),
			'new_item_name'     => __( 'New Building Name', 'complexmanager'  ),
			'menu_name'         => __( 'Building', 'complexmanager'  ),
		);
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'cm-building' ),
		);
		register_taxonomy( 'building', array( 'complex_unit' ), $args );

		$labels = array(
			'name'              => _x( 'Types', 'taxonomy general name', 'complexmanager'  ),
			'singular_name'     => _x( 'Type', 'taxonomy singular name', 'complexmanager'  ),
			'search_items'      => __( 'Search Types', 'complexmanager'  ),
			'all_items'         => __( 'All Types', 'complexmanager'  ),
			'parent_item'       => __( 'Parent Type', 'complexmanager'  ),
			'parent_item_colon' => __( 'Parent Type:', 'complexmanager'  ),
			'edit_item'         => __( 'Edit Type', 'complexmanager'  ),
			'update_item'       => __( 'Update Type', 'complexmanager'  ),
			'add_new_item'      => __( 'Add New Type', 'complexmanager'  ),
			'new_item_name'     => __( 'New Type Name', 'complexmanager'  ),
			'menu_name'         => __( 'Types', 'complexmanager'  ),
		);
		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'cm-unit-type' ),
		);
		register_taxonomy( 'unit_type', array( 'complex_unit' ), $args );

		$labels = array(
			'name'               => _x( 'Inquiries', 'post type general name', 'complexmanager' ),
			'singular_name'      => _x( 'Inquiry', 'post type singular name', 'complexmanager' ),
			'menu_name'          => _x( 'Inquiries', 'admin menu', 'complexmanager' ),
			'name_admin_bar'     => _x( 'Inquiry', 'add new on admin bar', 'complexmanager' ),
			'add_new'            => _x( 'Add New', 'inquiry', 'complexmanager' ),
			'add_new_item'       => __( 'Add New Inquiry', 'complexmanager' ),
			'new_item'           => __( 'New Inquiry', 'complexmanager' ),
			'edit_item'          => __( 'Edit Inquiry', 'complexmanager' ),
			'view_item'          => __( 'View Inquiry', 'complexmanager' ),
			'all_items'          => __( 'All Inquiries', 'complexmanager' ),
			'search_items'       => __( 'Search Inquiries', 'complexmanager' ),
			'parent_item_colon'  => __( 'Parent Inquiries:', 'complexmanager' ),
			'not_found'          => __( 'No inquiries found.', 'complexmanager' ),
			'not_found_in_trash' => __( 'No inquiries found in Trash.', 'complexmanager' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'cm-inquiry' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title')
		);

		register_post_type( 'complex_inquiry', $args );

		$labels = array(
			'name'              => _x( 'Reasons', 'taxonomy general name', 'complexmanager'  ),
			'singular_name'     => _x( 'Reason', 'taxonomy singular name', 'complexmanager'  ),
			'search_items'      => __( 'Search Reasons', 'complexmanager'  ),
			'all_items'         => __( 'All Reasons', 'complexmanager'  ),
			'parent_item'       => __( 'Parent Reason', 'complexmanager'  ),
			'parent_item_colon' => __( 'Parent Reason:', 'complexmanager'  ),
			'edit_item'         => __( 'Edit Reason', 'complexmanager'  ),
			'update_item'       => __( 'Update Reason', 'complexmanager'  ),
			'add_new_item'      => __( 'Add New Reason', 'complexmanager'  ),
			'new_item_name'     => __( 'New Reason Name', 'complexmanager'  ),
			'menu_name'         => __( 'Reasons', 'complexmanager'  ),
		);
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'cm-reason' ),
		);
		register_taxonomy( 'inquiry_reason', array( 'complex_inquiry' ), $args );

			/*----------  attachments  ----------*/

		    $labels = array(
		      'name'              => __( 'Property Attachment Types', 'complexmanager' ),
		        'singular_name'     => __( 'Attachment Type', 'complexmanager' ),
		        'search_items'      => __( 'Search Attachment Types', 'complexmanager' ),
		        'all_items'         => __( 'All Attachment Types', 'complexmanager' ),
		        'parent_item'       => __( 'Parent Attachment Type', 'complexmanager' ),
		        'parent_item_colon' => __( 'Parent Attachment Type:', 'complexmanager' ),
		        'edit_item'         => __( 'Edit Attachment Type', 'complexmanager' ),
		        'update_item'       => __( 'Update Attachment Type', 'complexmanager' ),
		        'add_new_item'      => __( 'Add New Attachment Type', 'complexmanager' ),
		        'new_item_name'     => __( 'New Attachment Type Name', 'complexmanager' ),
		        'menu_name'         => __( 'Attachment Type', 'complexmanager' )
		    );
		    $args = array(
		        'hierarchical'      => true,
		        'labels'            => $labels,
		        'show_ui'           => true,
		        'show_admin_column' => true,
		        'query_var'         => true,
		        'rewrite'           => array( 'slug' => 'cm-anhangstyp' )
		    );

		    register_taxonomy( 'cxm_attachment_type', array( 'complex_unit' ), $args );
		    register_taxonomy_for_object_type('cxm_attachment_type', 'attachment');
		    add_post_type_support('attachment', 'cxm_attachment_type');
		    $id1 = wp_insert_term('Image', 'cxm_attachment_type', array('slug' => 'image'));
		    $id2 = wp_insert_term('Plan', 'cxm_attachment_type', array('slug' => 'plan'));
		    $id3 = wp_insert_term('Document', 'cxm_attachment_type', array('slug' => 'document'));
		    $id3 = wp_insert_term('Sales Brochure', 'cxm_attachment_type', array('slug' => 'sales-brochure'));

		if (is_admin() && function_exists('pti_set_post_type_icon')) {
			pti_set_post_type_icon( 'complex_inquiry', 'inbox' );
		}

	}

	public function glance_items( $items = array() ) {
	    $post_types = array( 'complex_unit', 'complex_inquiry' );
	    foreach( $post_types as $type ) {
	        if( ! post_type_exists( $type ) ) continue;
	        $num_posts = wp_count_posts( $type );
	        if( $num_posts ) {
	            $published = intval( $num_posts->publish );
	            $post_type = get_post_type_object( $type );
	            $text = _n( '%s ' . $post_type->labels->name, '%s ' . $post_type->labels->name, $published, 'complexmanager' );
	            $text = sprintf( $text, number_format_i18n( $published ) );
	            if ( current_user_can( $post_type->cap->edit_posts ) ) {
	            	$output = '<a href="edit.php?post_type=' . $post_type->name . '">' . $text . '</a>';
	                	echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
	            } else {
	           	 $output = '<span>' . $text . '</span>';
	                echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
	            }
	        }

	        
	    }
	    echo '<style type="text/css">
		    #dashboard_right_now li.complex_unit-count a::before, #dashboard_right_now li.complex_unit-count span::before{
		    	font-family: FontAwesome;
		    	content: \'\f015\' !important; 
		    }
		    #dashboard_right_now li.complex_inquiry-count a::before, #dashboard_right_now li.complex_complex_inquiry-count span::before{
		    	font-family: FontAwesome;
		    	content: \'\f01c\' !important; 
		    }
	    </style>';
	    return $items;
	}

} // End Class


// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\post_types', 'init' ), 10 );



