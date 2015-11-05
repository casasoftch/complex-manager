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
							echo '<img style="display:none" data-show-on-active-unit="#unit_' . $unit->ID . '" src="'.$overlay.'" alt="" />';
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
						if ($poly) {
							echo '<a class="status-'.$status.'" xlink:href="' . $current_url . '#unit_'.$unit->ID.'"><polygon points="'.$poly.'" /></a>';
						}
					}
				}
			?>
		</svg>
	</div>
</div>
