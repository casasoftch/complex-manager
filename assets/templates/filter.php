<div class="complex-filter-wrapper">
	<div class="complex-filter">
		<form method="GET" id="complexFilterForm" action="">
			<?php foreach ($filters as $order => $filtertype): ?>
				<?php if ($filtertype == 'rooms'): ?>
					<?php if ($roomfilters): ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-rooms-checkboxes">
							<label class="filteroption-label" for="">Zimmer</label>
							<?php foreach ($roomfilters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="rooms[]" id="cxmFilterRooms_<?php echo $val ?>" value="<?php echo $val ?>"><label for="cxmFilterRooms_<?php echo $val ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>	
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'status') : ?>
					<div class="filteroption complex-filter-checkboxwrapper filteroption-status-checkboxes">
						<label class="filteroption-label" for="">Status</label>
						<span class="checkboxoption"><input type="checkbox" name="status[]" id="cxmFilterStatus_available" value="available"><label for="cxmFilterStatus_available" class="checkbox-inline">Verfügbar</label></span>
					</div>
				<?php elseif ($filtertype == 'livingspace') : ?>
					<div id="filteroption-flaeche" class="filteroption filteroption-livingspace-slider" data-minlivingspace="<?php echo $minlivingspace ?>" data-maxlivingspace="<?php echo $maxlivingspace ?>">
						<div class="slider-range-values">
							<label class="filteroption-label">Fläche</label>
							<span id="flaeche-slider-lower-value"></span>
							<span class="slider-format-middle">bis</span>
							<span id="flaeche-slider-upper-value"></span>
						</div>
						<div id="range-flaeche">
						</div>

						<input id="livingspace_from" name="livingspace_from" type="text" />
						<input id="livingspace_to" name="livingspace_to" type="text" />
					</div>
				<?php elseif ($filtertype == 'rentnet') : ?>
					<div id="filteroption-miete-netto" class="filteroption filteroption-rentnet-slider" data-minrentnet="<?php echo $minrentnet ?>" data-maxrentnet="<?php echo $maxrentnet ?>">
						<div class="slider-range-values">
							<label class="filteroption-label">Mietzins</label>
							<span id="miete-slider-lower-value"></span>
							<span class="slider-format-middle">bis</span>
							<span id="miete-slider-upper-value"></span>
						</div>
						<div id="range-miete">
						</div>
						<input id="rentnet_from" name="rentnet_from" type="text" />
						<input id="rentnet_to" name="rentnet_to" type="text" />
					</div>
				<?php endif ?>
			<?php endforeach ?>
			
		</form>
	</div>
</div>
