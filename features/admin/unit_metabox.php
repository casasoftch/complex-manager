<?php
namespace casasoft\complexmanager;


class unit_metabox extends Feature {

	public $prefix = 'complexmanager_unit_';

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		$this->add_action( 'add_meta_boxes', 'add_meta_box' );
		$this->add_action( 'save_post', 'save' );
		$this->add_action( 'admin_enqueue_scripts', 'js_enqueue');
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
			add_meta_box(
				'complexmanager_unit_graphic_box'
				,__( 'Unit Graphic Settings', 'complexmanager' )
				,array( $this, 'render_meta_graphic_box_content' )
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

		/* clear the CXM cache */
		$removed = 0;
		$dir = wp_upload_dir(null, true, false);
		if (is_dir($dir['basedir'] . '/cmx_cache')) {
				$files = glob($dir['basedir'] . '/cmx_cache/*');
				foreach($files as $file){ // iterate files
					if(is_file($file))
						unlink($file); // delete file
						$removed++;
				}
		}

		//echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Removed ' . $removed . ' files.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Removed ' . $removed . ' files.</span></button></div>';


		if ('complex_unit' == $_POST['post_type']) {
			$texts = array(
				$this->prefix.'number_of_rooms',
				$this->prefix.'min_persons',
				$this->prefix.'max_persons',
				$this->prefix.'min_income',
				$this->prefix.'story',
				$this->prefix.'status',
				$this->prefix.'purchase_price',
				$this->prefix.'purchase_price_propertysegment',
				$this->prefix.'rent_net',
				$this->prefix.'rent_timesegment',
				$this->prefix.'rent_propertysegment',
				$this->prefix.'currency',
				$this->prefix.'document',
				//$this->prefix.'graphic_hover_color',
				$this->prefix.'graphic_poly',
				$this->prefix.'custom_overlay',
				$this->prefix.'download_file',
				$this->prefix.'download_label',
				$this->prefix.'living_space',
				$this->prefix.'usable_space',
				$this->prefix.'terrace_space',
				$this->prefix.'balcony_space',
				$this->prefix.'idx_ref_house',
				$this->prefix.'idx_ref_object',
				$this->prefix.'extra_costs',
				$this->prefix.'custom_1',
				$this->prefix.'custom_2',
				$this->prefix.'custom_3',
			);

			foreach ($texts as $key) {
				if (isset($_POST[$key] )) {
					$mydata = sanitize_text_field( $_POST[$key] );
					update_post_meta( $post_id, '_'.$key, $mydata );
				}
			}

		}
	}

	public function js_enqueue() {
	    global $typenow;
	    if( $typenow == 'complex_unit' ) {
	        wp_enqueue_media();

	 		wp_register_script( 'jquery-canvasareadraw', PLUGIN_URL . 'assets/js/jquery.canvasAreaDraw.min.js', array('jquery'));

	        // Registers and enqueues the required javascript.
	        wp_register_script( 'complexmanager-meta-box', PLUGIN_URL.'/assets/js/complexmanager-meta-box.js' , array( 'jquery', 'wp-color-picker', 'jquery-canvasareadraw' ) );
	        wp_localize_script( 'complexmanager-meta-box', 'i18n',
	            array(
	                'title' => __( 'Choose or Upload a Document', 'complexmanager' ),
	                'button' => __( 'Use this document', 'complexmanager' ),
	            )
	        );
	        wp_enqueue_script( 'jquery-canvasareadraw' );
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

		echo '<p>';


        echo '<p><label for="complexmanager_unit_download_label">';
		_e( 'Download file', 'complexmanager' );
		echo '</label><br>';

		$labelvalue = get_post_meta( $post->ID, '_complexmanager_unit_download_label', true );
        $value = get_post_meta( $post->ID, '_complexmanager_unit_download_file', true );
        echo '<div class="uploader">
        		<input type="text" id="complexmanager_unit_download_label" name="complexmanager_unit_download_label" placeholder="Button name" value="' . esc_attr( $labelvalue ) . '"  />
				<input id="complexmanager_unit_download_file" name="complexmanager_unit_download_file" value="'.$value.'" type="text" placeholder="Datei" />
				<input id="complexmanager_unit_download_file_button" class="button" name="complexmanager_unit_download_file_button" type="button" value="Download-Datei auswählen" />
			</div>';
		echo "</p>";

        echo "<hr>";

		echo '<div class="complexmanager-meta-row">';
			echo '<div class="complexmanager-meta-col">';
				echo "<h3>". __('General', 'complexmanager'). "</h3>";

				$value = get_post_meta( $post->ID, '_complexmanager_unit_status', true );
		        echo '<p><label for="complexmanager_unit_status">';
				_e( 'Status', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="complexmanager_unit_status" name="complexmanager_unit_status">';
					echo '<option value="available" ' . ($value == 'available' ? 'selected' : '') . '>' . __('Available', 'complexmanager') . '</option>';
					echo '<option value="pre-reserved" '  . ($value == 'pre-reserved' ? 'selected' : '') . '>'  . __('pre-reserved', 'complexmanager') . '</option>';
					echo '<option value="reserved" '  . ($value == 'reserved' ? 'selected' : '') . '>'  . __('Reserved', 'complexmanager') . '</option>';
					echo '<option value="sold" '      . ($value == 'sold' ? 'selected' : '') . '>'      . __('Sold', 'complexmanager') . '</option>';
					echo '<option value="rented" '    . ($value == 'rented' ? 'selected' : '') . '>'      . __('Rented', 'complexmanager') . '</option>';
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


		        $key = $this->prefix.'idx_ref_house';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'IDX / REMCat House Ref.', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" min="0"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'idx_ref_object';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'IDX / REMCat Object Ref.', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" min="0"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $value = get_post_meta( $post->ID, '_complexmanager_unit_min_persons', true );
				echo '<p><label for="complexmanager_unit_min_persons">';
				_e( 'Number of people (min)', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" min="1" id="complexmanager_unit_min_persons" name="complexmanager_unit_min_persons"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $value = get_post_meta( $post->ID, '_complexmanager_unit_max_persons', true );
				echo '<p><label for="complexmanager_unit_max_persons">';
				_e( 'Number of people (max)', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" min="1" id="complexmanager_unit_max_persons" name="complexmanager_unit_max_persons"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $value = get_post_meta( $post->ID, '_complexmanager_unit_min_income', true );
				echo '<p><label for="complexmanager_unit_min_income">';
				_e( 'Income (min)', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" min="0" id="complexmanager_unit_min_income" name="complexmanager_unit_min_income"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';



		    echo "</div>";
		    echo '<div class="complexmanager-meta-col">';
		    	echo "<h3>". __('Spaces', 'complexmanager'). " m<sup>2</sup></h3>";

		        $key = $this->prefix.'living_space';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Living Space', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text" step="0.1" min="0"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />&nbsp;m<sup>2</sup>';
		        echo '</p>';

		        $key = $this->prefix.'usable_space';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Usable Space', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text" step="0.1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />&nbsp;m<sup>2</sup>';
		        echo '</p>';


		        $key = $this->prefix.'terrace_space';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Terrace Space', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text" step="0.1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />&nbsp;m<sup>2</sup>';
		        echo '</p>';

		        $key = $this->prefix.'balcony_space';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Balcony Space', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text" step="0.1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />&nbsp;m<sup>2</sup>';
		        echo '</p>';

		    echo "</div>";
		    echo '<div style="clear:both"></div>';
        echo "</div>";

        echo "<hr>";

		echo '<div class="complexmanager-meta-row">';
			echo '<div class="complexmanager-meta-col">';
				echo "<h3>". __('Buy', 'complexmanager'). "</h3>";

				$value = get_post_meta( $post->ID, '_complexmanager_unit_currency', true );
		        echo '<p><label for="complexmanager_unit_currency">';
				_e( 'Currency', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="complexmanager_unit_currency" name="complexmanager_unit_currency">';
					echo '<option value="CHF" ' . ($value == 'CHF' ? 'selected' : '') . '>CHF</option>';
					echo '<option value="" ' . ($value == 'CHF' ? '' : '') . '>–</option>';
					echo '<option value="EUR" ' . ($value == 'EUR' ? 'selected' : '') . '>€</option>';
					echo '<option value="USD" ' . ($value == 'USD' ? 'selected' : '') . '>$</option>';
					echo '<option value="GBP" ' . ($value == 'GBP' ? 'selected' : '') . '>£</option>';
		        echo '</select>';
		        echo '</p>';

        		$key = $this->prefix.'purchase_price';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				_e( 'Purchase Price', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '<br> (' . __('"" = not for sale, 0 = upon request', 'complexmanager') . ')';
		        echo '</p>';

		        $key = $this->prefix.'purchase_price_propertysegment';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				_e( 'Purchase scope', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="'.$key.'" name="'.$key.'">';
					echo '<option value="full" ' . ($value == 'full' ? 'selected' : '') . '>Full price</option>';
					echo '<option value="M2" ' . ($value == 'M2' ? 'selected' : '') . '>per M2</option>';
		        echo '</select>';
		        echo '</p>';

		    echo "</div>";
		   	echo '<div class="complexmanager-meta-col">';
		   		echo "<h3>". __('Rent', 'complexmanager'). "</h3>";

		   		$key = $this->prefix.'rent_net';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				_e( 'Rent Net Price', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '<br> (' . __('"" = not for rent, 0 = upon request', 'complexmanager') . ')';
		        echo '</p>';

		        $key = $this->prefix.'rent_timesegment';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				_e( 'Rent Time segment', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="'.$key.'" name="'.$key.'">';
					echo '<option value="M" ' . ($value == 'M' ? 'selected' : '') . '>Month</option>';
					echo '<option value="W" ' . ($value == 'W' ? 'selected' : '') . '>Week</option>';
		        echo '</select>';
		        echo '</p>';

		        $key = $this->prefix.'rent_propertysegment';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				_e( 'Rental scope', 'complexmanager' );
				echo '</label><br>';
				echo '<select id="'.$key.'" name="'.$key.'">';
					echo '<option value="full" ' . ($value == 'full' ? 'selected' : '') . '>Full price</option>';
					echo '<option value="M2" ' . ($value == 'M2' ? 'selected' : '') . '>per M2</option>';
		        echo '</select>';
		        echo '</p>';

		        $key = $this->prefix.'extra_costs';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				_e( 'Extra Costs', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="number" step="1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';




		 	echo "</div>";
		    echo '<div style="clear:both"></div>';
        echo "</div>";

        echo "<hr>";

		echo '<div class="complexmanager-meta-row">';
			echo '<div class="complexmanager-meta-col">';
				echo "<h3>". __('Custom Fields', 'complexmanager'). "</h3>";

				$key = $this->prefix.'custom_1';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				echo sprintf(__( 'Custom %d', 'complexmanager' ), 1);
				echo '</label><br>';
				echo '<input type="text" step="1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'custom_2';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				echo sprintf(__( 'Custom %d', 'complexmanager' ), 2);
				echo '</label><br>';
				echo '<input type="text" step="1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'custom_3';
		        $value = get_post_meta( $post->ID, '_'.$key, true );
		        echo '<p><label for="'.$key.'">';
				echo sprintf(__( 'Custom %d', 'complexmanager' ), 3);
				echo '</label><br>';
				echo '<input type="text" step="1" min="0" id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		    echo "</div>";
		   	echo '<div class="complexmanager-meta-col">';


		 	echo "</div>";
		    echo '<div style="clear:both"></div>';
        echo "</div>";

        /* echo "<hr>";


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
		';*/


		echo '<div style="clear:both"></div>';








		//complexmanager-unit-document-upload.js


	}


	public function render_meta_graphic_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		//wp_nonce_field( 'complexmanager_inner_custom_graphic_box', 'complexmanager_inner_custom_graphic_box_nonce' );

       	$value = get_post_meta( $post->ID, '_complexmanager_unit_graphic_poly', true );
        $image_src = false;

        $overlay = get_post_meta( $post->ID, '_complexmanager_unit_custom_overlay', true );
        if ($overlay) {
        	$image_src = $overlay;
        } else {
	        $project_image_id = $this->get_option("project_image");
	        if ($project_image_id) {
	            $image_attributes = wp_get_attachment_image_src( $project_image_id, 'full' ); // returns an array
	            if ($image_attributes) {
	                $set = true;
	                $image_src = $image_attributes[0];
	            }
	        }
        }

        if ($image_src) {
        	echo '<div class="comlexmanager-polyhelper">
        		<textarea id="complexmanager_unit_graphic_poly" name="complexmanager_unit_graphic_poly" data-image-url="'.$image_src.'">
        		'.$value.'
        		</textarea>
        	</div>';
        }

        echo "<hr>";

        echo '<p>';
        $value = get_post_meta( $post->ID, '_complexmanager_unit_custom_overlay', true );
        echo '<div class="uploader">
				<input style="width:100%;" id="complexmanager_unit_custom_overlay" name="complexmanager_unit_custom_overlay" value="'.$value.'" type="text" />
				<input style="width:100%;" id="complexmanager_unit_custom_overlay_button" class="button" name="complexmanager_unit_custom_overlay_button" type="button" value="Spezifisches Overlay auswählen" />
			</div>';
		echo "</p>";

		echo '<div style="clear:both"></div>';


	}
}

add_action( 'load-post.php', array( 'casasoft\complexmanager\unit_metabox', 'init' )  );
add_action( 'load-post-new.php', array( 'casasoft\complexmanager\unit_metabox', 'init' ) );
