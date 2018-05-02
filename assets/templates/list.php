<div class="complex-list-wrapper <?php echo ($collapsible ? 'complex-list-wrapper-collapsible' : '') ?> <?php echo $class ?>">
	<?php foreach ( $the_buildings as $building ) { ?>
		<div class="complex-unit-wrapper" <?= ($building['hidden'] ? ' style="display:none"' : '') ?>>
			<h2 class="unit-title"><?= $building['term']->name; ?></h2>
			<div class="unit-description"><?= wpautop( $building['description'], false ); ?></div>
			<div class="table-responsive complex-building-<?= $building['term']->slug ?>">
				<table class="table table-condensed">
					<thead>
						<tr class="col-labels">
							<?php foreach ($building['the_units'][0]['displayItems'] as $displayItem): ?>
								<th class="col-<?= $displayItem['field'] ?> <?= ($displayItem['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>"><?= $displayItem['label'] ?></th>		
							<?php endforeach ?>
							<?php if ($collapsible) : ?>
								<th></th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
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
													<div class="complex-unit-featuredimage">
														<img class="img-responsive" src="<?php echo $image[0]; ?>" alt="" />
													</div>
												<?php endif; ?>
												<?php 
													$content = $the_unit['post']->post_content;
													$content = apply_filters('the_content', $content);
													$content = str_replace(']]>', ']]&gt;', $content);
													$content = str_replace('src=', 'src="data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idWlsLXJpbmciIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCBjbGFzcz0iYmsiIGZpbGw9Im5vbmUiIGhlaWdodD0iMTAwIiB3aWR0aD0iMTAwIiB4PSIwIiB5PSIwIi8+PGRlZnM+PGZpbHRlciBoZWlnaHQ9IjMwMCUiIGlkPSJ1aWwtcmluZy1zaGFkb3ciIHdpZHRoPSIzMDAlIiB4PSItMTAwJSIgeT0iLTEwMCUiPjxmZU9mZnNldCBkeD0iMCIgZHk9IjAiIGluPSJTb3VyY2VHcmFwaGljIiByZXN1bHQ9Im9mZk91dCIvPjxmZUdhdXNzaWFuQmx1ciBpbj0ib2ZmT3V0IiByZXN1bHQ9ImJsdXJPdXQiIHN0ZERldmlhdGlvbj0iMCIvPjxmZUJsZW5kIGluPSJTb3VyY2VHcmFwaGljIiBpbjI9ImJsdXJPdXQiIG1vZGU9Im5vcm1hbCIvPjwvZmlsdGVyPjwvZGVmcz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCw1MCkiPjxwYXRoIGQ9Ik0xMCw1MGMwLDAsMCwwLjUsMC4xLDEuNGMwLDAuNSwwLjEsMSwwLjIsMS43YzAsMC4zLDAuMSwwLjcsMC4xLDEuMWMwLjEsMC40LDAuMSwwLjgsMC4yLDEuMmMwLjIsMC44LDAuMywxLjgsMC41LDIuOCBjMC4zLDEsMC42LDIuMSwwLjksMy4yYzAuMywxLjEsMC45LDIuMywxLjQsMy41YzAuNSwxLjIsMS4yLDIuNCwxLjgsMy43YzAuMywwLjYsMC44LDEuMiwxLjIsMS45YzAuNCwwLjYsMC44LDEuMywxLjMsMS45IGMxLDEuMiwxLjksMi42LDMuMSwzLjdjMi4yLDIuNSw1LDQuNyw3LjksNi43YzMsMiw2LjUsMy40LDEwLjEsNC42YzMuNiwxLjEsNy41LDEuNSwxMS4yLDEuNmM0LTAuMSw3LjctMC42LDExLjMtMS42IGMzLjYtMS4yLDctMi42LDEwLTQuNmMzLTIsNS44LTQuMiw3LjktNi43YzEuMi0xLjIsMi4xLTIuNSwzLjEtMy43YzAuNS0wLjYsMC45LTEuMywxLjMtMS45YzAuNC0wLjYsMC44LTEuMywxLjItMS45IGMwLjYtMS4zLDEuMy0yLjUsMS44LTMuN2MwLjUtMS4yLDEtMi40LDEuNC0zLjVjMC4zLTEuMSwwLjYtMi4yLDAuOS0zLjJjMC4yLTEsMC40LTEuOSwwLjUtMi44YzAuMS0wLjQsMC4xLTAuOCwwLjItMS4yIGMwLTAuNCwwLjEtMC43LDAuMS0xLjFjMC4xLTAuNywwLjEtMS4yLDAuMi0xLjdDOTAsNTAuNSw5MCw1MCw5MCw1MHMwLDAuNSwwLDEuNGMwLDAuNSwwLDEsMCwxLjdjMCwwLjMsMCwwLjcsMCwxLjEgYzAsMC40LTAuMSwwLjgtMC4xLDEuMmMtMC4xLDAuOS0wLjIsMS44LTAuNCwyLjhjLTAuMiwxLTAuNSwyLjEtMC43LDMuM2MtMC4zLDEuMi0wLjgsMi40LTEuMiwzLjdjLTAuMiwwLjctMC41LDEuMy0wLjgsMS45IGMtMC4zLDAuNy0wLjYsMS4zLTAuOSwyYy0wLjMsMC43LTAuNywxLjMtMS4xLDJjLTAuNCwwLjctMC43LDEuNC0xLjIsMmMtMSwxLjMtMS45LDIuNy0zLjEsNGMtMi4yLDIuNy01LDUtOC4xLDcuMSBjLTAuOCwwLjUtMS42LDEtMi40LDEuNWMtMC44LDAuNS0xLjcsMC45LTIuNiwxLjNMNjYsODcuN2wtMS40LDAuNWMtMC45LDAuMy0xLjgsMC43LTIuOCwxYy0zLjgsMS4xLTcuOSwxLjctMTEuOCwxLjhMNDcsOTAuOCBjLTEsMC0yLTAuMi0zLTAuM2wtMS41LTAuMmwtMC43LTAuMUw0MS4xLDkwYy0xLTAuMy0xLjktMC41LTIuOS0wLjdjLTAuOS0wLjMtMS45LTAuNy0yLjgtMUwzNCw4Ny43bC0xLjMtMC42IGMtMC45LTAuNC0xLjgtMC44LTIuNi0xLjNjLTAuOC0wLjUtMS42LTEtMi40LTEuNWMtMy4xLTIuMS01LjktNC41LTguMS03LjFjLTEuMi0xLjItMi4xLTIuNy0zLjEtNGMtMC41LTAuNi0wLjgtMS40LTEuMi0yIGMtMC40LTAuNy0wLjgtMS4zLTEuMS0yYy0wLjMtMC43LTAuNi0xLjMtMC45LTJjLTAuMy0wLjctMC42LTEuMy0wLjgtMS45Yy0wLjQtMS4zLTAuOS0yLjUtMS4yLTMuN2MtMC4zLTEuMi0wLjUtMi4zLTAuNy0zLjMgYy0wLjItMS0wLjMtMi0wLjQtMi44Yy0wLjEtMC40LTAuMS0wLjgtMC4xLTEuMmMwLTAuNCwwLTAuNywwLTEuMWMwLTAuNywwLTEuMiwwLTEuN0MxMCw1MC41LDEwLDUwLDEwLDUweiIgZmlsbD0iIzk5OTk5OSIgZmlsdGVyPSJ1cmwoI3VpbC1yaW5nLXNoYWRvdykiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgZnJvbT0iMCA1MCA1MCIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHRvPSIzNjAgNTAgNTAiIHR5cGU9InJvdGF0ZSIvPjwvcGF0aD48L2c+PC9zdmc+" data-src=', $content);
													$content = preg_replace('/(<*[^>]*srcset=)"[^>]+"([^>]*>)/', '\1"data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idWlsLXJpbmciIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCBjbGFzcz0iYmsiIGZpbGw9Im5vbmUiIGhlaWdodD0iMTAwIiB3aWR0aD0iMTAwIiB4PSIwIiB5PSIwIi8+PGRlZnM+PGZpbHRlciBoZWlnaHQ9IjMwMCUiIGlkPSJ1aWwtcmluZy1zaGFkb3ciIHdpZHRoPSIzMDAlIiB4PSItMTAwJSIgeT0iLTEwMCUiPjxmZU9mZnNldCBkeD0iMCIgZHk9IjAiIGluPSJTb3VyY2VHcmFwaGljIiByZXN1bHQ9Im9mZk91dCIvPjxmZUdhdXNzaWFuQmx1ciBpbj0ib2ZmT3V0IiByZXN1bHQ9ImJsdXJPdXQiIHN0ZERldmlhdGlvbj0iMCIvPjxmZUJsZW5kIGluPSJTb3VyY2VHcmFwaGljIiBpbjI9ImJsdXJPdXQiIG1vZGU9Im5vcm1hbCIvPjwvZmlsdGVyPjwvZGVmcz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCw1MCkiPjxwYXRoIGQ9Ik0xMCw1MGMwLDAsMCwwLjUsMC4xLDEuNGMwLDAuNSwwLjEsMSwwLjIsMS43YzAsMC4zLDAuMSwwLjcsMC4xLDEuMWMwLjEsMC40LDAuMSwwLjgsMC4yLDEuMmMwLjIsMC44LDAuMywxLjgsMC41LDIuOCBjMC4zLDEsMC42LDIuMSwwLjksMy4yYzAuMywxLjEsMC45LDIuMywxLjQsMy41YzAuNSwxLjIsMS4yLDIuNCwxLjgsMy43YzAuMywwLjYsMC44LDEuMiwxLjIsMS45YzAuNCwwLjYsMC44LDEuMywxLjMsMS45IGMxLDEuMiwxLjksMi42LDMuMSwzLjdjMi4yLDIuNSw1LDQuNyw3LjksNi43YzMsMiw2LjUsMy40LDEwLjEsNC42YzMuNiwxLjEsNy41LDEuNSwxMS4yLDEuNmM0LTAuMSw3LjctMC42LDExLjMtMS42IGMzLjYtMS4yLDctMi42LDEwLTQuNmMzLTIsNS44LTQuMiw3LjktNi43YzEuMi0xLjIsMi4xLTIuNSwzLjEtMy43YzAuNS0wLjYsMC45LTEuMywxLjMtMS45YzAuNC0wLjYsMC44LTEuMywxLjItMS45IGMwLjYtMS4zLDEuMy0yLjUsMS44LTMuN2MwLjUtMS4yLDEtMi40LDEuNC0zLjVjMC4zLTEuMSwwLjYtMi4yLDAuOS0zLjJjMC4yLTEsMC40LTEuOSwwLjUtMi44YzAuMS0wLjQsMC4xLTAuOCwwLjItMS4yIGMwLTAuNCwwLjEtMC43LDAuMS0xLjFjMC4xLTAuNywwLjEtMS4yLDAuMi0xLjdDOTAsNTAuNSw5MCw1MCw5MCw1MHMwLDAuNSwwLDEuNGMwLDAuNSwwLDEsMCwxLjdjMCwwLjMsMCwwLjcsMCwxLjEgYzAsMC40LTAuMSwwLjgtMC4xLDEuMmMtMC4xLDAuOS0wLjIsMS44LTAuNCwyLjhjLTAuMiwxLTAuNSwyLjEtMC43LDMuM2MtMC4zLDEuMi0wLjgsMi40LTEuMiwzLjdjLTAuMiwwLjctMC41LDEuMy0wLjgsMS45IGMtMC4zLDAuNy0wLjYsMS4zLTAuOSwyYy0wLjMsMC43LTAuNywxLjMtMS4xLDJjLTAuNCwwLjctMC43LDEuNC0xLjIsMmMtMSwxLjMtMS45LDIuNy0zLjEsNGMtMi4yLDIuNy01LDUtOC4xLDcuMSBjLTAuOCwwLjUtMS42LDEtMi40LDEuNWMtMC44LDAuNS0xLjcsMC45LTIuNiwxLjNMNjYsODcuN2wtMS40LDAuNWMtMC45LDAuMy0xLjgsMC43LTIuOCwxYy0zLjgsMS4xLTcuOSwxLjctMTEuOCwxLjhMNDcsOTAuOCBjLTEsMC0yLTAuMi0zLTAuM2wtMS41LTAuMmwtMC43LTAuMUw0MS4xLDkwYy0xLTAuMy0xLjktMC41LTIuOS0wLjdjLTAuOS0wLjMtMS45LTAuNy0yLjgtMUwzNCw4Ny43bC0xLjMtMC42IGMtMC45LTAuNC0xLjgtMC44LTIuNi0xLjNjLTAuOC0wLjUtMS42LTEtMi40LTEuNWMtMy4xLTIuMS01LjktNC41LTguMS03LjFjLTEuMi0xLjItMi4xLTIuNy0zLjEtNGMtMC41LTAuNi0wLjgtMS40LTEuMi0yIGMtMC40LTAuNy0wLjgtMS4zLTEuMS0yYy0wLjMtMC43LTAuNi0xLjMtMC45LTJjLTAuMy0wLjctMC42LTEuMy0wLjgtMS45Yy0wLjQtMS4zLTAuOS0yLjUtMS4yLTMuN2MtMC4zLTEuMi0wLjUtMi4zLTAuNy0zLjMgYy0wLjItMS0wLjMtMi0wLjQtMi44Yy0wLjEtMC40LTAuMS0wLjgtMC4xLTEuMmMwLTAuNCwwLTAuNywwLTEuMWMwLTAuNywwLTEuMiwwLTEuN0MxMCw1MC41LDEwLDUwLDEwLDUweiIgZmlsbD0iIzk5OTk5OSIgZmlsdGVyPSJ1cmwoI3VpbC1yaW5nLXNoYWRvdykiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgZnJvbT0iMCA1MCA1MCIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHRvPSIzNjAgNTAgNTAiIHR5cGU9InJvdGF0ZSIvPjwvcGF0aD48L2c+PC9zdmc+"\2', $content);
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
