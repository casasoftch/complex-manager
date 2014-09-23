<?php
namespace casasoft\complexmanager;


class inquiry_metabox extends Feature {

	public $prefix = 'complexmanager_inquiry_';

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
        $post_types = array('complex_inquiry'); //limit meta box to certain post types
        if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				'complexmanager_inquiry_box'
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
		if ('complex_inquiry' == $_POST['post_type']) {
			$texts = array(
				$this->prefix.'unit_id',
				$this->prefix.'gender',
				$this->prefix.'first_name',
				$this->prefix.'last_name',
				$this->prefix.'email',
				$this->prefix.'phone',
				$this->prefix.'street',
				$this->prefix.'postal_code',
				$this->prefix.'locality',
				$this->prefix.'subject',
				$this->prefix.'message',
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
		    if( $typenow == 'complex_inquiry' ) {
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

		echo '<div class="complexmanager-meta-row">';
			echo '<div class="complexmanager-meta-col">';

				$key = $this->prefix.'unit_id';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Unit id', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

				$key = $this->prefix.'gender';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Salutation', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

				$key = $this->prefix.'first_name';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'First name', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'last_name';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Last name', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';


				$key = $this->prefix.'email';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Email', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'phone';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Phone', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';





		    echo "</div>";
		    echo '<div class="complexmanager-meta-col">';

		        $key = $this->prefix.'street';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Street', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'postal_code';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'ZIP', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'locality';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'City', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'subject';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Subject', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';

		        $key = $this->prefix.'message';
				$value = get_post_meta( $post->ID, '_'.$key, true );
				echo '<p><label for="'.$key.'">';
				_e( 'Message', 'complexmanager' );
				echo '</label><br>';
				echo '<input type="text"  id="'.$key.'" name="'.$key.'"';
		                echo ' value="' . esc_attr( $value ) . '" size="25" />';
		        echo '</p>';
		    echo "</div>";
		    echo '<div style="clear:both">';
        echo "</div>"; 	


	}
}

add_action( 'load-post.php', array( 'casasoft\complexmanager\inquiry_metabox', 'init' )  );
add_action( 'load-post-new.php', array( 'casasoft\complexmanager\inquiry_metabox', 'init' ) );

