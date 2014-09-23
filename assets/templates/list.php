<div class="complex-list-wrapper">
	<?php 
		foreach ( $buildings as $building ) { ?>
			<table class="table table-condensed">
				<thead>
					<tr>
						<th colspan="<?= count($cols)+1 ?>"><?= $building['term']->name ?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="col-labels">
						<?php foreach ($cols as $col): ?>
							<th class="<?= ($col['field'] == 'status' ? 'hidden-xs' : '') ?>"><small><?= $col['label'] ?></small></th>
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
						foreach ($cols as $col) {
							switch ($col['field']) {
								case 'status':
									$value = '';
									switch ($status) {
										case 'available': $value = '<span class="text-success">'.__('Available', 'complexmanager').'</span>'; break;
										case 'reserved': $value = '<span class="text-'.$state.'">'.__('Reserved', 'complexmanager').'</span>'; break;
										default: $value = $status;
									}
									echo '<td class="hidden-xs"><span class="text-'.$state.'">' . $value . '</span></td>';
									break;
								default:
									$value = get_cxm($unit, $col['field']);
									echo '<td><span class="text-'.$state.'">' . $value . '</span></td>';
									break;
							}
						}
						echo '<td class="complex-unit-caret-cell text-'.$state.'"><span class="complex-unit-caret"></span></td>';
						echo "</tr>";
						?>
							<tr class="complex-unit-detail-row">
								<td colspan="<?= count($cols)+1 ?>">
									<?php if (has_post_thumbnail( $unit->ID ) ): ?>
										<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $unit->ID ), 'large' ); ?>
										<a href="#" class="complex-unit-featuredimage">
											<img class="img-responsive" src="<?php echo $image[0]; ?>" alt="" />
										</a>
									<?php endif; ?>
									<a class="btn btn-default pull-left" href="#">Grundriss</a>
									<a class="btn btn-primary pull-right complex-call-contact-form" data-unit-id="<?= $unit->ID?>" href="#complexContactForm">Kontakt</a>
									<div class="clearfix"></div>
								</td>
							</tr>
						<?php
					}
				echo "</tbody>";
			echo "</table>";
		}
	?>
</div>
<div class="complex-contact-form-wrapper" id="complexContactForm">
	<a style="display:none" class="btn btn-default pull-right complex-sendback-contact-form" href="#complexContactForm">Abbrechen</a>
	<?= $form ?>
</div>



