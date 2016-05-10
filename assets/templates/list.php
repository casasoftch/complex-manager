<?php $lang = substr(get_bloginfo('language'), 0, 2) ?>

<div class="complex-list-wrapper <?php echo ($collapsible ? 'complex-list-wrapper-collapsible' : '') ?>">
	<?php foreach ( $buildings as $building ) { ?>
		<h2 class="unit-title"><?= $building['term']->name; ?></h2>
		<?php if ($building['term']->description): ?>
			<p class="unit-description"><?= $building['term']->description ?></p>
		<?php endif ?>
		<div class="table-responsive complex-building-<?= $building['term']->slug ?>">
			<?php 
				$colcount = 0;
				foreach ($cols as $field => $col){
					if ($col['active']) {
						$colcount++;
					}
				}
			 ?>
			<table class="table table-condensed">
				<tbody>
					<tr class="col-labels">
						<?php foreach ($cols as $field => $col): ?>
							<?php if ($col['active']): ?>
								<?php // check for lingustic alternatives ?>
								<?php if (isset($col['label_'.$lang])): ?>
									<th class="col-<?= $field ?> <?= ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>"><?=nl2br(str_replace('\n', "\n", ($col['label_'.$lang] ? $col['label_'.$lang] : get_cxm_label(false, $field, 'complex_unit') ) ) ) ?></th>	
								<?php else: ?>
									<th class="col-<?= $field ?> <?= ($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>"><?=nl2br(str_replace('\n', "\n", ($col['label'] ? $col['label'] : get_cxm_label(false, $field, 'complex_unit') ) ) ) ?></th>	
								<?php endif ?>
								
							<?php endif ?>
						<?php endforeach ?>
						<?php if ($collapsible) : ?>
							<th></th>
						<?php endif; ?>
					</tr>
					<?php 
					foreach ($building['units'] as $unit) {
						$status = get_cxm($unit, 'status');
						$state = 'default';
						switch ($status) {
							case 'available': $state = 'default'; break;
							case 'reserved': $state = 'danger'; break;
							case 'rented': $state = 'danger'; break;
							case 'sold': $state = 'danger'; break;
						}

						$data = array();
						foreach ($cols as $field => $col) {
							$value = get_cxm($unit, $field);
							$data[$field] = htmlentities($value);
						}
						echo '<tr class="complex-unit-header-row '.$state.'" id="unit_'.$unit->ID.'" data-unit-id="' . $unit->ID .'" data-json="' . htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8') . '">';
						$i = 0; foreach ($cols as $field => $col) { 

							
							if ($col['active']):
								
								$i++;
								switch ($field) {
									case 'status':
										$value = '';
										switch ($status) {
											case 'available': $value = '<span class="text-success">'.strtolower(__('Available', 'complexmanager')).'</span>'; break;
											case 'reserved': $value = '<span class="text-'.$state.'">'.strtolower(__('Reserved', 'complexmanager')).'</span>'; break;
											case 'rented': $value = '<span class="text-'.$state.'">'.strtolower(__('Rented', 'complexmanager')).'</span>'; break;
											case 'sold': $value = '<span class="text-'.$state.'">'.strtolower(__('Sold', 'complexmanager')).'</span>'; break;
											default: $value = $status;
										}
										echo '<td class="hidden-sm hidden-xs col-status"><span class="text-'.$state.'">' . $value . '</span></td>';
										break;
									case 'r_purchase_price':
									case 'r_rent_net':
									case 'r_rent_gross':
										if (
											$col['hidden-reserved'] == 0
											||
											!in_array($status, array('reserved', 'sold', 'rented'))
										) {
											$value = get_cxm($unit, $field);	
											$currency = false;
											if (get_cxm($unit, 'unit_currency')) {
												$currency = get_cxm($unit, 'unit_currency');
											}
										} else {
											$value = '';
										}
										echo '<td class="'.($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field . '">' . ($currency ? $currency . ' ' : '') . $value . '</td>';
										break;
									case 'quick-download':
										if (
											$col['hidden-reserved'] == 0
											||
											!in_array($status, array('reserved', 'sold', 'rented'))
										) {
											if (get_cxm($unit, 'download_file')) {
												$value = '<a target="_blank" class="btn btn-xs btn-default" href="' . get_cxm($unit, 'download_file') . '">' . (get_cxm($unit, 'download_label') ? get_cxm($unit, 'download_label') : 'Download') . '</a>';
											} else {
												$value = '';
											}
											
										} elseif(
											$col['hidden-reserved'] == 1 
											&& in_array($status, array('reserved', 'sold', 'rented'))
										) {
											$value = '';

											//show availability instead if deactivated?
											$statustext = '';
											switch ($status) {
												case 'reserved': $statustext = '<span class="text-'.$state.'">'.strtolower(__('Reserved', 'complexmanager')).'</span>'; break;
												case 'rented': $statustext = '<span class="text-'.$state.'">'.strtolower(__('Rented', 'complexmanager')).'</span>'; break;
												case 'sold': $statustext = '<span class="text-'.$state.'">'.strtolower(__('Sold', 'complexmanager')).'</span>'; break;
											}
											if ($statustext) {
												$value = $statustext;
											}
										} else {
											$value = '';
										}

										echo '<td class="'.($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field . '">';
										echo $value;
										echo '</td>';
										break;
									default:
										if (
											$col['hidden-reserved'] == 0
											||
											!in_array($status, array('reserved', 'sold', 'rented'))
										) {
											$value = get_cxm($unit, $field);	
										} else {
											$value = '';
										}
										
										echo '<td class="'.($col['hidden-xs'] ? 'hidden-sm hidden-xs' : '') . ' col-' . $field . '"><span class="text-'.$state.'">' . ($i == 1 ? '<strong>' : '') . $value . ($i == 1 ? '</strong>' : '') . '</span></td>';
										break;
								}
							endif;
						}
						if ($collapsible) {
							echo '<td class="complex-unit-caret-cell text-'.$state.'"><span class="complex-unit-caret"></span></td>';
							echo "</tr>";
							?>
								<tr class="complex-unit-detail-row">
									<td colspan="<?= $colcount+1 ?>">
										<div class="detail-row-wrapper">
											<?php if (has_post_thumbnail( $unit->ID ) ): ?>
												<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $unit->ID ), 'large' ); ?>
												<a href="#" class="complex-unit-featuredimage">
													<img class="img-responsive" src="<?php echo $image[0]; ?>" alt="" />
												</a>
											<?php endif; ?>
											<?php 
												$content = $unit->post_content;
												$content = apply_filters('the_content', $content);
												$content = str_replace(']]>', ']]&gt;', $content);
												echo $content;
											 ?>
											 <?php if (get_cxm($unit, 'download_file')): ?>
											 	<a target="_blank" class="btn btn-primary pull-left complex-download-btn" href="<?= get_cxm($unit, 'download_file') ?>"><?= (get_cxm($unit, 'download_label') ? get_cxm($unit, 'download_label') : 'Download') ?></a>
											 <?php endif ?>
												<a class="btn btn-primary pull-right complex-call-contact-form" data-unit-id="<?= $unit->ID?>" href="#complexContactForm">Kontakt</a>
											<div class="clearfix"></div>
										</div>
									</td>
								</tr>
							<?php
						}
					}
				echo "</tbody>";
			echo "</table>";
		echo "</div>";
	}
	?>
	<?php if ($form): ?>
		<div class="complex-contact-form-wrapper" id="complexContactForm">
			<a style="display:none" class="pull-right complex-sendback-contact-form" href="#complexContactForm"><i class="glyphicon glyphicon-remove"></i><span class="sr-only">Cancel</span></a>
			<?= $form ?>
		</div>	
	<?php endif ?>
	
</div>




