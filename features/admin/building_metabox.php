<?php
namespace casasoft\complexmanager;


class building_metabox extends Feature {

	public $prefix = 'complexmanager_building_';

	public function __construct() {
		//register_meta( 'term', 'color', array($this, 'sanitize_hex' ));
		add_action( 'building_add_form_fields', array($this, 'add_group_field' ), 10, 2 );
	}
	
	public function sanitize_hex( $color ) {
	    $color = ltrim( $color, '#' );
	    return preg_match( '/([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : '';
	}



	public function add_group_field($taxonomy) {
	    $feature_groups = array(
		    'bedroom' => __('Bedroom', 'my_plugin'),
		    'living' => __('Living room', 'my_plugin'),
		    'kitchen' => __('Kitchen', 'my_plugin')
		);
	    ?><div class="form-field term-group">
	        <label for="featuret-group"><?php _e('Feature Group', 'my_plugin'); ?></label>
	        <select class="postform" id="equipment-group" name="feature-group">
	            <option value="-1"><?php _e('none', 'my_plugin'); ?></option><?php foreach ($feature_groups as $_group_key => $_group) : ?>
	                <option value="<?php echo $_group_key; ?>" class=""><?php echo $_group; ?></option>
	            <?php endforeach; ?>
	        </select>
	    </div><?php
	}


}

add_action( 'init', array( 'casasoft\complexmanager\building_metabox', 'init' )  );
