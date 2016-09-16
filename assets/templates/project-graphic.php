<div class="complex-project-graphic-wrapper">
	<div class="complex-project-graphic">
		<img src="<?= $image ?>" class="complex-project-graphic-bg" width="1152" height="680" alt="">
		<div class="complex-custom-overlays">
			<?php 
				$current_url = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				foreach ($buildings as $building) {
					foreach ($building['units'] as $unit) {
						$overlay = get_post_meta( $unit->ID, '_complexmanager_unit_custom_overlay', true );
						if ($overlay) {
							echo '<img style="display:none" data-show-on-active-unit="#unit_' . $unit->ID . '" src="data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idWlsLXJpbmciIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCBjbGFzcz0iYmsiIGZpbGw9Im5vbmUiIGhlaWdodD0iMTAwIiB3aWR0aD0iMTAwIiB4PSIwIiB5PSIwIi8+PGRlZnM+PGZpbHRlciBoZWlnaHQ9IjMwMCUiIGlkPSJ1aWwtcmluZy1zaGFkb3ciIHdpZHRoPSIzMDAlIiB4PSItMTAwJSIgeT0iLTEwMCUiPjxmZU9mZnNldCBkeD0iMCIgZHk9IjAiIGluPSJTb3VyY2VHcmFwaGljIiByZXN1bHQ9Im9mZk91dCIvPjxmZUdhdXNzaWFuQmx1ciBpbj0ib2ZmT3V0IiByZXN1bHQ9ImJsdXJPdXQiIHN0ZERldmlhdGlvbj0iMCIvPjxmZUJsZW5kIGluPSJTb3VyY2VHcmFwaGljIiBpbjI9ImJsdXJPdXQiIG1vZGU9Im5vcm1hbCIvPjwvZmlsdGVyPjwvZGVmcz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCw1MCkiPjxwYXRoIGQ9Ik0xMCw1MGMwLDAsMCwwLjUsMC4xLDEuNGMwLDAuNSwwLjEsMSwwLjIsMS43YzAsMC4zLDAuMSwwLjcsMC4xLDEuMWMwLjEsMC40LDAuMSwwLjgsMC4yLDEuMmMwLjIsMC44LDAuMywxLjgsMC41LDIuOCBjMC4zLDEsMC42LDIuMSwwLjksMy4yYzAuMywxLjEsMC45LDIuMywxLjQsMy41YzAuNSwxLjIsMS4yLDIuNCwxLjgsMy43YzAuMywwLjYsMC44LDEuMiwxLjIsMS45YzAuNCwwLjYsMC44LDEuMywxLjMsMS45IGMxLDEuMiwxLjksMi42LDMuMSwzLjdjMi4yLDIuNSw1LDQuNyw3LjksNi43YzMsMiw2LjUsMy40LDEwLjEsNC42YzMuNiwxLjEsNy41LDEuNSwxMS4yLDEuNmM0LTAuMSw3LjctMC42LDExLjMtMS42IGMzLjYtMS4yLDctMi42LDEwLTQuNmMzLTIsNS44LTQuMiw3LjktNi43YzEuMi0xLjIsMi4xLTIuNSwzLjEtMy43YzAuNS0wLjYsMC45LTEuMywxLjMtMS45YzAuNC0wLjYsMC44LTEuMywxLjItMS45IGMwLjYtMS4zLDEuMy0yLjUsMS44LTMuN2MwLjUtMS4yLDEtMi40LDEuNC0zLjVjMC4zLTEuMSwwLjYtMi4yLDAuOS0zLjJjMC4yLTEsMC40LTEuOSwwLjUtMi44YzAuMS0wLjQsMC4xLTAuOCwwLjItMS4yIGMwLTAuNCwwLjEtMC43LDAuMS0xLjFjMC4xLTAuNywwLjEtMS4yLDAuMi0xLjdDOTAsNTAuNSw5MCw1MCw5MCw1MHMwLDAuNSwwLDEuNGMwLDAuNSwwLDEsMCwxLjdjMCwwLjMsMCwwLjcsMCwxLjEgYzAsMC40LTAuMSwwLjgtMC4xLDEuMmMtMC4xLDAuOS0wLjIsMS44LTAuNCwyLjhjLTAuMiwxLTAuNSwyLjEtMC43LDMuM2MtMC4zLDEuMi0wLjgsMi40LTEuMiwzLjdjLTAuMiwwLjctMC41LDEuMy0wLjgsMS45IGMtMC4zLDAuNy0wLjYsMS4zLTAuOSwyYy0wLjMsMC43LTAuNywxLjMtMS4xLDJjLTAuNCwwLjctMC43LDEuNC0xLjIsMmMtMSwxLjMtMS45LDIuNy0zLjEsNGMtMi4yLDIuNy01LDUtOC4xLDcuMSBjLTAuOCwwLjUtMS42LDEtMi40LDEuNWMtMC44LDAuNS0xLjcsMC45LTIuNiwxLjNMNjYsODcuN2wtMS40LDAuNWMtMC45LDAuMy0xLjgsMC43LTIuOCwxYy0zLjgsMS4xLTcuOSwxLjctMTEuOCwxLjhMNDcsOTAuOCBjLTEsMC0yLTAuMi0zLTAuM2wtMS41LTAuMmwtMC43LTAuMUw0MS4xLDkwYy0xLTAuMy0xLjktMC41LTIuOS0wLjdjLTAuOS0wLjMtMS45LTAuNy0yLjgtMUwzNCw4Ny43bC0xLjMtMC42IGMtMC45LTAuNC0xLjgtMC44LTIuNi0xLjNjLTAuOC0wLjUtMS42LTEtMi40LTEuNWMtMy4xLTIuMS01LjktNC41LTguMS03LjFjLTEuMi0xLjItMi4xLTIuNy0zLjEtNGMtMC41LTAuNi0wLjgtMS40LTEuMi0yIGMtMC40LTAuNy0wLjgtMS4zLTEuMS0yYy0wLjMtMC43LTAuNi0xLjMtMC45LTJjLTAuMy0wLjctMC42LTEuMy0wLjgtMS45Yy0wLjQtMS4zLTAuOS0yLjUtMS4yLTMuN2MtMC4zLTEuMi0wLjUtMi4zLTAuNy0zLjMgYy0wLjItMS0wLjMtMi0wLjQtMi44Yy0wLjEtMC40LTAuMS0wLjgtMC4xLTEuMmMwLTAuNCwwLTAuNywwLTEuMWMwLTAuNywwLTEuMiwwLTEuN0MxMCw1MC41LDEwLDUwLDEwLDUweiIgZmlsbD0iIzk5OTk5OSIgZmlsdGVyPSJ1cmwoI3VpbC1yaW5nLXNoYWRvdykiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgZnJvbT0iMCA1MCA1MCIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHRvPSIzNjAgNTAgNTAiIHR5cGU9InJvdGF0ZSIvPjwvcGF0aD48L2c+PC9zdmc+" data-src="'.$overlay.'" alt="" />';
						}
					}
				}
			?>
		</div>
		<svg class="complex-project-graphic-interaction" version="1.1" viewBox="0 0 <?= $width ?> <?= $height ?>" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			<?php 
				$current_url = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				foreach ($buildings as $building) {
					foreach ($building['units'] as $unit) {
						$status = get_cxm($unit, 'status');
						//$color = get_post_meta( $unit->ID, '_complexmanager_unit_graphic_hover_color', true );
						$poly = get_post_meta( $unit->ID, '_complexmanager_unit_graphic_poly', true );
						if ($poly && $poly != 'NaN') {
							echo '<a class="status-'.$status.'" xlink:href="' . $current_url . '#unit_'.$unit->ID.'"><polygon points="'.$poly.'" /></a>';
						}
					}
				}
			?>
		</svg>
	</div>
	<div class="complex-tooltip" style="display:none" >
		<?php 
			foreach ($the_buildings as $building) {
				foreach ($building['the_units'] as $the_unit) {
					echo '<div style="display:none" class="complex-tooltip-unit-item" data-unit="#unit_'.$the_unit['post']->ID.'">';
						echo '<table class="table table-condensed">';
						echo "<tbody>";
						foreach ($the_unit['displayItems'] as $displayItem){
							if ($displayItem['field'] != 'quick-download') {
								if ($displayItem['value']) {
									echo '<tr><th>'. $displayItem['label'] . '</th><td class="text-right">' . $displayItem['value'] . '</td></tr>';
								}
							}
						}
						echo "</tbody>";
						echo "</table>";
					echo '</div>';
				}
			}
		?>
	</div>
</div>
