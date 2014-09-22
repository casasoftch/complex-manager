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
						<th><small><?= $col['label'] ?></small></th>
					<?php endforeach ?>
					<th></th>
				</tr>
				<?php 
				foreach ($building['units'] as $unit) {

					echo '<tr class="complex-unit-header-row" id="unit_'.$unit->ID.'">';
					foreach ($cols as $col) {
						switch ($col['field']) {
							case 'name':
								echo '<td>' . $unit->post_title . '</td>';
								break;
							case 'rent_net':
							case 'purchase_price':
								$value = (int) get_post_meta( $unit->ID, '_complexmanager_unit_'.$col['field'], true );
								$currency = get_post_meta( $unit->ID, '_complexmanager_unit_currency', true );
								$before = true;
								$space = '&nbsp;';
								switch ($currency) {
									case 'EUR': $currency = '€'; $before = false; $space = ''; break;
									case 'USD': $currency = '$'; break;
									case 'GBP': $currency = '£'; break;
									//case 'CHF': $currency = '.–'; $before = false; $space = ''; break;
								}
								echo '<td>' . ($before ? $currency . $space : '') . number_format($value, 0 ,".", "'")  . (!$before ? $space . $currency : '') .'</td>';
								break;
							case 'number_of_rooms':
								$value = get_post_meta( $unit->ID, '_complexmanager_unit_'.$col['field'], true );
								echo '<td>' . $value . '</td>';
								break;
							case 'story':
								$value = get_post_meta( $unit->ID, '_complexmanager_unit_'.$col['field'], true );
								echo '<td>' . $value . '</td>';
								break;
							default:
								$value = get_post_meta( $unit->ID, '_complexmanager_unit_'.$col['field'], true );
								echo '<td>' . $value . '</td>';
								break;
						}
					}
					echo '<td class="complex-unit-caret-cell"><span class="complex-unit-caret"></span></td>';
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

<div class="complex-contact-form-wrapper" id="complexContactForm">
	<a style="display:none" class="btn btn-default pull-right complex-sendback-contact-form" href="#complexContactForm">Abbrechen</a>
	<?= $form ?>
</div>



