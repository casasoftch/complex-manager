<?php
namespace casasoft\complexmanager;


class unit_metabox extends Feature {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'js_enqueue' ));

	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
        $post_types = array('complex_unit'); //limit meta box to certain post types
        if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				'complexmanager_unit_box'
				,__( 'Unit Settings', 'complexmanager' )
				,array( $this, 'render_meta_box_content' )
				,$post_type
				,'normal'
				,'core'
			);
        }
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['complexmanager_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['complexmanager_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'complexmanager_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		/* OK, its safe for us to save the data now. */

		if ('complex_unit' == $_POST['post_type']) {
			$mydata = sanitize_text_field( $_POST['complexmanager_unit_number_of_rooms'] );
			update_post_meta( $post_id, '_complexmanager_unit_number_of_rooms', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_story'] );
			update_post_meta( $post_id, '_complexmanager_unit_story', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_status'] );
			update_post_meta( $post_id, '_complexmanager_unit_status', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_purchase_price'] );
			update_post_meta( $post_id, '_complexmanager_unit_purchase_price', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_rent_net'] );
			update_post_meta( $post_id, '_complexmanager_unit_rent_net', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_currency'] );
			update_post_meta( $post_id, '_complexmanager_unit_currency', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_document'] );
			update_post_meta( $post_id, '_complexmanager_unit_document', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_document'] );
			update_post_meta( $post_id, '_complexmanager_unit_document', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_graphic_hover_color'] );
			update_post_meta( $post_id, '_complexmanager_unit_graphic_hover_color', $mydata );

			$mydata = sanitize_text_field( $_POST['complexmanager_unit_graphic_poly'] );
			update_post_meta( $post_id, '_complexmanager_unit_graphic_poly', $mydata );

		}
	}

	public function js_enqueue() {
		    global $typenow;
		    if( $typenow == 'complex_unit' ) {
		        wp_enqueue_media();
		 		
		 		wp_enqueue_script( 'jquery-canvasareadraw', PLUGIN_URL . 'assets/js/jquery.canvasAreaDraw.min.js', array('jquery'));

		        // Registers and enqueues the required javascript.
		        wp_register_script( 'complexmanager-meta-box', PLUGIN_URL.'/assets/js/complexmanager-meta-box.js' , array( 'jquery', 'wp-color-picker', 'jquery-canvasareadraw' ) );
		        wp_localize_script( 'complexmanager-meta-box', 'i18n',
		            array(
		                'title' => __( 'Choose or Upload a Document', 'complexmanager' ),
		                'button' => __( 'Use this document', 'complexmanager' ),
		            )
		        );
		        wp_enqueue_script( 'complexmanager-meta-box' );

		        wp_enqueue_style( 'wp-color-picker' ); 

		    }
		}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'complexmanager_inner_custom_box', 'complexmanager_inner_custom_box_nonce' );
		

        $value = get_post_meta( $post->ID, '_complexmanager_unit_graphic_hover_color', true );
		echo '<p><label for="complexmanager_unit_graphic_hover_color">';
		_e( 'Hover color', 'complexmanager' );
		echo '</label><br>';
		echo '<input type="text" id="complexmanager_unit_graphic_hover_color" name="complexmanager_unit_graphic_hover_color"';
                echo ' value="' . esc_attr( $value ) . '" size="25" />';
        echo '</p>';

       	$value = get_post_meta( $post->ID, '_complexmanager_unit_graphic_poly', true );
        $image_src = PLUGIN_URL.'assets/img/example-project-bg.png';
        $project_image_id = $this->get_option("project_image");
        if ($project_image_id) {
            $image_attributes = wp_get_attachment_image_src( $project_image_id, 'full' ); // returns an array
            if ($image_attributes) {
                $set = true;
                $image_src = $image_attributes[0];
            }
        }
        
        echo '<div class="comlexmanager-polyhelper">
        		<textarea id="complexmanager_unit_graphic_poly" name="complexmanager_unit_graphic_poly" data-image-url="'.$image_src.'">
        		'.$value.'
        		</textarea>
        	</div>';

        echo "<hr>";

		echo '<div class="complexmanager-meta-row">';
			echo '<div class="complexmanager-meta-col">';

				$value = get_post_meta( $post->ID, '_complexmanager_unit_status', true );
		        echo '<p><label for="complexmanager_unit_status">';
				_e( 'Status', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="complexmanager_unit_status" name="complexmanager_unit_status">';
					echo '<option value="available" ' . ($value == 'available' ? 'selected' : '') . '>' . __('Available', 'complexmanager') . '</option>';
					echo '<option value="reserved" '  . ($value == 'reserved' ? 'selected' : '') . '>'  . __('Reserved', 'complexmanager') . '</option>';
					echo '<option value="sold" '      . ($value == 'sold' ? 'selected' : '') . '>'      . __('Sold', 'complexmanager') . '</option>';
		        echo '</select>';
		        echo '</p>';

				$value = get_post_meta( $post->ID, '_complexmanager_unit_number_of_rooms', true );
				echo '<p><label for="complexmanager_unit_number_of_rooms">';
				_e( 'Number of Rooms', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="0.5" min="0" id="complexmanager_unit_number_of_rooms" name="complexmanager_unit_number_of_rooms"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';


		        $value = get_post_meta( $post->ID, '_complexmanager_unit_story', true );
		        echo '<p><label for="complexmanager_unit_story">';
				_e( 'Apartment Story', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text" id="complexmanager_unit_story" name="complexmanager_unit_story" placeholder="1. OG"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        //echo '<br> (' . __('0 = EG, +1 = 1. OG, -1 = 1. UG', 'complexmanager' ) . ')';
		        echo '</p>';

		    echo "</div>";
		    echo '<div class="complexmanager-meta-col">';

		       

		        $value = get_post_meta( $post->ID, '_complexmanager_unit_currency', true );
		        echo '<p><label for="complexmanager_unit_currency">';
				_e( 'Currency', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="complexmanager_unit_currency" name="complexmanager_unit_currency">';
					echo '<option value="CHF" ' . ($value == 'CHF' ? 'selected' : '') . '>CHF</option>';
					echo '<option value="EUR" ' . ($value == 'EUR' ? 'selected' : '') . '>€</option>';
					echo '<option value="USD" ' . ($value == 'USD' ? 'selected' : '') . '>$</option>';
					echo '<option value="GBP" ' . ($value == 'GBP' ? 'selected' : '') . '>£</option>';
		        echo '</select>';
		        echo '</p>';

		        $value = get_post_meta( $post->ID, '_complexmanager_unit_purchase_price', true );
		        echo '<p><label for="complexmanager_unit_purchase_price">';
				_e( 'Purchase Price', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" id="complexmanager_unit_purchase_price" name="complexmanager_unit_purchase_price"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '<br> (' . __('"" = not for sale, 0 = upon request', 'complexmanager') . ')';
		        echo '</p>';

		        $value = get_post_meta( $post->ID, '_complexmanager_unit_rent_net', true );
		        echo '<p><label for="complexmanager_unit_rent_net">';
				_e( 'Rent Net Price', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" id="complexmanager_unit_rent_net" name="complexmanager_unit_rent_net"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '<br> (' . __('"" = not for rent, 0 = upon request', 'complexmanager') . ')';
		        echo '</p>';

		    echo "</div>";
		    echo '<div style="clear:both">';
        echo "</div>"; 	


        $value = get_post_meta( $post->ID, '_complexmanager_unit_document', true );
        echo '<p>
		    <label for="complexmanager_unit_document">'.__( 'No file chosen', 'complexmanager' ).'</label>
		    <input type="button" id="complexmanager_unit_document-button" class="button" value="' . __( 'Add file', 'complexmanager' ).'" />
		</p>';
		echo '
			<div class="cxm-file-uploader clearfix  active" data-library="all">
				<input type="text" name="complexmanager_unit_document" id="complexmanager_unit_document-file" value="' . $value . '" />
				<div class="has-file">
					<ul class="hl clearfix">
						<li>
							<img class="cxm-file-icon" src="/wp-includes/images/media/default.png" alt="">
							<div class="hover">
								<ul class="bl">
									<li><a href="#" class="cxm-button-delete"><i></i></a></li>
									<li><a href="#" class="cxm-button-edit"><i></i></a></li>
								</ul>
							</div>
						</li>
						<li>
							<p>
								<strong class="cxm-file-title">Bristle Grass</strong>
							</p>
							<p>
								<strong>Name:</strong>
								<a class="cxm-file-name" href="http://wordpress.local/wp-content/uploads/2014/09/Bristle-Grass.jpg" target="_blank">Bristle-Grass.jpg</a>
							</p>
							<p>
								<strong>Size:</strong>
								<span class="cxm-file-size">4 MB</span>
							</p>
							
						</li>
					</ul>
				</div>
			</div>
		';







	

		

		//complexmanager-unit-document-upload.js


	}
}

add_action( 'load-post.php', array( 'casasoft\complexmanager\unit_metabox', 'init' )  );
add_action( 'load-post-new.php', array( 'casasoft\complexmanager\unit_metabox', 'init' ) );

