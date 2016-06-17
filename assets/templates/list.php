<div class="complex-list-wrapper <?php echo ($collapsible ? 'complex-list-wrapper-collapsible' : '') ?>">
	<?php foreach ( $the_buildings as $building ) { ?>
		<h2 class="unit-title"><?= $building['term']->name; ?></h2>
		<?= $building['description']; ?>
		<div class="table-responsive complex-building-<?= $building['term']->slug ?>">
			<table class="table table-condensed">
				<tbody>
					<tr class="col-labels">
						<?php foreach ($building['the_units'][0]['displayItems'] as $displayItem): ?>
							<th class="col-<?= $displayItem['field'] ?> <?= ($displayItem['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>"><?= $displayItem['label'] ?></th>		
						<?php endforeach ?>
						<?php if ($collapsible) : ?>
							<th></th>
						<?php endif; ?>
					</tr>
					<?php 
					foreach ($building['the_units'] as $the_unit) {
						$colcount = count($the_unit['displayItems']);
						echo '<tr class="complex-unit-header-row state-' . $the_unit['state'] . ' status-' . $the_unit['status'] . '" id="unit_'.$the_unit['post']->ID.'" data-unit-id="' . $the_unit['post']->ID .'" data-json="' . htmlspecialchars(json_encode($the_unit['data']), ENT_QUOTES, 'UTF-8') . '">';
						foreach ($the_unit['displayItems'] as $displayItem) {
							echo '<td class="'.$displayItem['td_classes'].'">'.$displayItem['value'].'</td>';
						}
						if ($collapsible) {
							echo '<td class="complex-unit-caret-cell text-'.$the_unit['state'].'"><span class="complex-unit-caret"></span></td>';
							echo "</tr>";
							?>
								<tr class="complex-unit-detail-row">
									<td colspan="<?= $colcount+1 ?>">
										<div class="detail-row-wrapper">
											<?php if (has_post_thumbnail( $the_unit['post']->ID ) ): ?>
												<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $the_unit['post']->ID ), 'large' ); ?>
												<a href="#" class="complex-unit-featuredimage">
													<img class="img-responsive" src="<?php echo $image[0]; ?>" alt="" />
												</a>
											<?php endif; ?>
											<?php 
												$content = $the_unit['post']->post_content;
												$content = apply_filters('the_content', $content);
												$content = str_replace(']]>', ']]&gt;', $content);
												echo $content;
											 ?>
											 <?php if (get_cxm($the_unit['post'], 'download_file')): ?>
											 	<a target="_blank" class="btn btn-primary pull-left complex-download-btn" href="<?= get_cxm($the_unit['post'], 'download_file') ?>"><?= (get_cxm($the_unit['post'], 'download_label') ? get_cxm($the_unit['post'], 'download_label') : 'Download') ?></a>
											 <?php endif ?>
												<a class="btn btn-primary pull-right complex-call-contact-form" data-unit-id="<?= $the_unit['post']->ID ?>" href="#complexContactForm"><?= __('Contact', 'complexmanager') ?></a>
											<div class="clearfix"></div>
										</div>
									</td>
								</tr>
							<?php
						}
					}
				echo "</tbody>";
				?>
				<?php if(isset($building['totals'])) : ?>
					<tfoot>
						<tr class="complex-list-footer-row">
							<?php foreach ($building['the_units'][0]['displayItems'] as $displayItem): ?>
								<th class="col-<?= $displayItem['field'] ?> <?= ($displayItem['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>">
									<?php if (isset($building['totals'][$displayItem['field']]) && $building['totals'][$displayItem['field']]): ?>
										<?= $building['totals'][$displayItem['field']] ?>	
									<?php endif; ?>
								</th>		
							<?php endforeach ?>
							<?php if ($collapsible) : ?>
								<th></th>
							<?php endif; ?>
						</tr>
					</tfoot>
				<?php endif; ?>

				<?php

			echo "</table>";
		echo "</div>";
	}
	?>
	<?php if ($form): ?>
		<div class="complex-contact-form-wrapper" id="complexContactForm">
			<a style="display:none" class="pull-right complex-sendback-contact-form" href="#complexContactForm"><i class="glyphicon glyphicon-remove"></i><span class="sr-only"><?= __('Cancel', 'complexmanager') ?></span></a>
			<?= $form ?>
		</div>	
	<?php endif ?>
	
</div>
