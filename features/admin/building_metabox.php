<?php
namespace casasoft\complexmanager;


class building_metabox extends Feature {

	public $prefix = 'complexmanager_building_';

	public function __construct() {

		$this->acf_fields();

		//$this->add_action( 'building_add_form_fields', 'add_group_field');
		$this->add_action( 'building_edit_form_fields', 'add_group_field');

		//$this->add_action( 'created_building', 'save_group_metas', 10, 2 );
		$this->add_action( 'edited_building', 'save_group_metas', 10, 2 );


		add_filter('manage_edit-building_columns', array($this, 'add_group_column') );
		add_filter('manage_building_custom_column', array($this, 'add_group_column_content'), 10, 3 );



	}

	private function acf_fields(){

		acf_add_local_field_group(array (
			'key' => 'group_5756e44015508',
			'title' => 'Weitere Einstellungen',
			'fields' => array (
				array (
					'key' => 'field_5756e4e4bcca4',
					'label' => 'Alternative Ausgangsgrafik',
					'name' => 'alternate-base-image',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'id',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'building',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

	}

	public function add_group_column( $columns ){
	    $columns['hide_building'] = __( 'Visible', 'complexmanager' );
	    $columns['has_building_col_options'] = __( 'Options', 'complexmanager' );
	    return $columns;
	}

	public function add_group_column_content( $content, $column_name, $term_id ){

		if( $column_name === 'hide_building' ){
			$term_id = absint( $term_id );
			$hide_building = get_term_meta( $term_id, 'hide_building', true );

			if( !empty( $hide_building ) && $hide_building ){
				$content = '⨉';
			} else {
				$content = '✓';
			}
		}

		if( $column_name === 'has_building_col_options' ){
			$term_id = absint( $term_id );
			$col_options = get_term_meta( $term_id, 'building_col_options', true );


			if( !empty( $col_options )){
				$items = array();
				foreach ($col_options as $key => $col_option) {
					if ($col_option['hide']) {
						$items[] .= '<strike>' . $key . '</strike>';
					} else if ($col_option['alternate_label']) {
						$items[] .= $col_option['alternate_label'];
					}
				}
			    $content = '<ul><li>'.implode('</li><li>', $items).'</li></ul>';
			}
		}

		return $content;
	}

	private function getOptionCols(){
		$cols = maybe_unserialize((maybe_unserialize($this->get_option("list_cols"))));
		if (!$cols || !is_array($cols)) {
			$cols = array();
		} else {
			//sort
			uasort($cols, function($a, $b){
				return $a["order"] - $b["order"];
			});
		}
		return $cols;
	}

	public function add_group_field($term, $taxonomy) {
		$default_cols = cxm_get_list_col_defaults();
		?>
		<table class="form-table">
			<tbody>
				<?php $hide_building = get_term_meta( $term->term_id, 'hide_building', true ); ?>
				<?php $show_total = get_term_meta( $term->term_id, 'show_total', true ); ?>
				<tr class="form-field form-required term-name-wrap">
					<th scope="row"><label for="name">Anzeige</label></th>
					<td>
						<input type="hidden" name="hide_building" value="0" />
						<label><input type="checkbox" name="hide_building" value="1" <?= ($hide_building ? 'CHECKED' : '')?> /> Auf Liste verbergen</label>
						<br>
						<input type="hidden" name="show_total" value="0" />
						<label><input type="checkbox" name="show_total" value="1" <?= ($show_total ? 'CHECKED' : '')?> /> Spalten-Total auf Liste anzeigen</label>
					</td>
				</tr>



				<?php
					$building_col_options = get_term_meta( $term->term_id, 'building_col_options', true );
				?>
				<tr class="form-field form-required term-name-wrap">
					<th scope="row"><label for="name">Feld/Spalte</label></th>
					<td>
						<table style="width: 100%; background-color: white; border: 1px solid #ddd;">
							<thead>
								<tr>
									<td><strong>Feld</strong></td>
									<td style="text-align:center"><strong>Ausblenden</strong></td>
									<td><strong>Alternativer Anzeigetitel</strong></td>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($this->getOptionCols() as $key => $value) : ?>
									<?php if ($value['active']) : ?>
										<?php
											$label = $value['label'];
											if (!$label && array_key_exists($key, $default_cols)) {
												$label = $default_cols[$key]['o_label'];
											}

											$alternate_label = '';
											$hide = '';
											if ($building_col_options && isset($building_col_options[$key])) {
												$alternate_label = (isset($building_col_options[$key]['alternate_label']) ? $building_col_options[$key]['alternate_label'] : '' );
												$hide = (isset($building_col_options[$key]['hide']) ? $building_col_options[$key]['hide'] : '' );
											}

										?>
										<tr>
											<td><?php
												$fieldlabel = false;
												if (array_key_exists($key, $default_cols)) {
													$fieldlabel = $default_cols[$key]['o_label'];
												}
												if (!$fieldlabel) {
													$fieldlabel = $key;
												}
												echo $fieldlabel;
											?></td>
											<td style="text-align:center">
												<input type="hidden" name="building_col_options[<?= $key ?>][hide]" value="0" />
												<input type="checkbox" name="building_col_options[<?= $key ?>][hide]" value="1" <?= ($hide ? 'CHECKED' : '') ?>/>
											</td>
											<td>
												<input name="building_col_options[<?= $key ?>][alternate_label]" type="text" value="<?= $alternate_label ?>" placeholder="<?= $label ?>" size="40" aria-required="true">
											</td>
										</tr>
										<?php endif; ?>
								<?php endforeach ?>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table><?php
	}

	public function save_group_metas( $term_id, $tt_id ){
		if( isset( $_POST['hide_building'] ) && '' !== $_POST['hide_building'] ){
			$val = ($_POST['hide_building'] ? 1 : 0);
			update_term_meta( $term_id, 'hide_building', $val );
		}

		if( isset( $_POST['show_total'] ) && '' !== $_POST['show_total'] ){
			$val = ($_POST['show_total'] ? 1 : 0);
			update_term_meta( $term_id, 'show_total', $val );
		}

		if( isset( $_POST['building_col_options'] ) && '' !== $_POST['building_col_options'] ){
			$val = $_POST['building_col_options'];
			update_term_meta( $term_id, 'building_col_options', $val );
		}

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
		
	}


}

add_action( 'complexmanager_init', array( 'casasoft\complexmanager\building_metabox', 'init' ), 90  );
