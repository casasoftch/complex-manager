<div class="complex-list-wrapper">
	<?php 
		$hidden_on_phone = array(
			'status', 
			'r_extra_costs', 
			'extra_costs', 
			'terrace_space',
			'r_terrace_space',
			'balcony_space',
			'r_balcony_space',
		);

		foreach ( $buildings as $building ) { ?>
			<div class="table-responsive">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th colspan="<?= count($cols)+1 ?>"><?= $building['term']->name  ?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="col-labels">
							<?php foreach ($cols as $col): ?>
								<th class="<?= (in_array($col['field'], $hidden_on_phone) ? 'hidden-sm hidden-xs' : '') ?>"><?=nl2br($col['label']) ?></th>
							<?php endforeach ?>
							<th></th>
						</tr>
						<?php 
						foreach ($building['units'] as $unit) {
							$status = get_cxm($unit, 'status');
							$state = 'default';
							switch ($status) {
								case 'available': $state = 'default'; break;
								case 'reserved': $state = 'danger'; break;
							}
							echo '<tr class="complex-unit-header-row '.$state.'" id="unit_'.$unit->ID.'">';
							$i = 0; foreach ($cols as $col) { $i++;
								switch ($col['field']) {
									case 'status':
										$value = '';
										switch ($status) {
											case 'available': $value = '<span class="text-success">'.__('Available', 'complexmanager').'</span>'; break;
											case 'reserved': $value = '<span class="text-'.$state.'">'.__('Reserved', 'complexmanager').'</span>'; break;
											default: $value = $status;
										}
										echo '<td class="hidden-sm hidden-xs"><span class="text-'.$state.'">' . $value . '</span></td>';
										break;
									default:
										$value = get_cxm($unit, $col['field']);
										echo '<td class="'.(in_array($col['field'], $hidden_on_phone) ? 'hidden-sm hidden-xs' : '') . '"><span class="text-'.$state.'">' . ($i == 1 ? '<strong>' : '') . $value . ($i == 1 ? '</strong>' : '') . '</span></td>';
										break;
								}
							}
							echo '<td class="complex-unit-caret-cell text-'.$state.'"><span class="complex-unit-caret"></span></td>';
							echo "</tr>";
							?>
								<tr class="complex-unit-detail-row">
									<td colspan="<?= count($cols)+1 ?>">
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
											<a class="btn btn-primary pull-right complex-call-contact-form" data-unit-id="<?= $unit->ID?>" href="#complexContactForm">Kontakt</a>
											<div class="clearfix"></div>
										</div>
									</td>
								</tr>
							<?php
						}
					echo "</tbody>";
				echo "</table>";
			echo "</div>";
		}
	?>
</div>
<div class="complex-contact-form-wrapper" id="complexContactForm">
	<a style="display:none" class="pull-right complex-sendback-contact-form" href="#complexContactForm"><i class="glyphicon glyphicon-remove"></i><span class="sr-only">Cancel</span></a>
	<?= $form ?>
</div>



