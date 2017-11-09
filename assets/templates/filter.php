<div class="complex-filter-wrapper">
	<div class="complex-filter">
		<form method="GET" id="complexFilterForm" action="">
			<?php foreach ($filters as $order => $filtertype): ?>
				<?php if ($filtertype == 'rooms'): ?>
					<?php if ($roomfilters): ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-rooms-checkboxes">
							<label class="filteroption-label" for=""><?= __('Number of rooms', 'complexmanager') ?></label>
							<?php foreach ($roomfilters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="rooms[]" id="cxmFilterRooms_<?php echo $val ?>" value="<?php echo $val ?>"><label for="cxmFilterRooms_<?php echo $val ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'status') : ?>
					<div class="filteroption complex-filter-checkboxwrapper filteroption-status-checkboxes">
						<label class="filteroption-label" for=""><?= __('Status', 'complexmanager') ?></label>
						<span class="checkboxoption"><input type="checkbox" name="status[]" id="cxmFilterStatus_available" value="available"><label for="cxmFilterStatus_available" class="checkbox-inline">Verfügbar</label></span>
					</div>
				<?php elseif ($filtertype == 'livingspace') : ?>
					<div id="filteroption-flaeche" class="filteroption filteroption-livingspace-slider" data-minlivingspace="<?php echo $minlivingspace ?>" data-maxlivingspace="<?php echo $maxlivingspace ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?= __('Surface', 'complexmanager') ?></label>
							<span id="flaeche-slider-lower-value"></span>
							<span class="slider-format-middle"><?= __('to', 'complexmanager') ?></span>
							<span id="flaeche-slider-upper-value"></span>
						</div>
						<div id="range-flaeche">
						</div>

						<input id="livingspace_from" name="livingspace_from" type="text" value="<?php echo $minlivingspace ?>" />
						<input id="livingspace_to" name="livingspace_to" type="text" value="<?php echo $maxlivingspace ?>" />
					</div>
				<?php elseif ($filtertype == 'rentnet') : ?>
					<div id="filteroption-miete-netto" class="filteroption filteroption-rentnet-slider" data-minrentnet="<?php echo $minrentnet ?>" data-maxrentnet="<?php echo $maxrentnet ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?= __('Rent price', 'complexmanager') ?></label>
							<span id="miete-slider-lower-value"></span>
							<span class="slider-format-middle"><?= __('to', 'complexmanager') ?></span>
							<span id="miete-slider-upper-value"></span>
						</div>
						<div id="range-miete">
						</div>
						<input id="rentnet_from" name="rentnet_from" type="text" value="<?php echo $minrentnet ?>" />
						<input id="rentnet_to" name="rentnet_to" type="text" value="<?php echo $maxrentnet ?>" />
					</div>
				<?php elseif ($filtertype == 'rentgross') : ?>
					<div id="filteroption-miete-grossto" class="filteroption filteroption-rentgross-slider" data-minrentgross="<?php echo $minrentgross ?>" data-maxrentgross="<?php echo $maxrentgross ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?= __('Rent price', 'complexmanager') ?></label>
							<span id="miete-slider-lower-value"></span>
							<span class="slider-format-middle"><?= __('to', 'complexmanager') ?></span>
							<span id="miete-slider-upper-value"></span>
						</div>
						<div id="range-miete">
						</div>
						<input id="rentgross_from" name="rentgross_from" type="text" value="<?php echo $minrentgross ?>" />
						<input id="rentgross_to" name="rentgross_to" type="text" value="<?php echo $maxrentgross ?>" />
					</div>
				<?php elseif ($filtertype == 'story') : ?>
					<?php if ($story_filters): ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-story-checkboxes">
							<?php  ?>
							<label class="filteroption-label">Etage</label>
							<?php foreach ($story_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="stories[]" id="cxmFilterStory_<?php echo $val ?>" value="<?php echo $val ?>"><label for="cxmFilterStory_<?php echo $val ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'custom_3') : ?>
					<?php if ($custom_3_filters): ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-custom_3-checkboxes">
							<?php  ?>
							<label class="filteroption-label">Custom 3</label>
							<?php foreach ($custom_3_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="custom_3s[]" id="cxmFilterCustom3_<?php echo $val ?>" value="<?php echo $val ?>"><label for="cxmFilterCustom3_<?php echo $val ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'income') : ?>
					<div id="filteroption-income" class="filteroption filteroption-income-field" data-min="1" data-max="10">
						<label class="filteroption-label" for=""><?= __('Your yearly income', 'complexmanager') ?></label>
						<input id="income" value="0" name="income" type="range" min="0" max="100000" step="1000" value="" onchange="document.getElementById('filteroption-income-preview').innerHTML = parseFloat(this.value).toLocaleString(['de-CH', 'fr-CH']);" />
						<div id="filteroption-income-preview">0</div>
					</div>
				<?php elseif ($filtertype == 'persons') : ?>
					<div id="filteroption-persons" class="filteroption filteroption-persons-field" data-min="1" data-max="10">
						<label class="filteroption-label" for=""><?= __('Number of people', 'complexmanager') ?></label>
						<input id="persons" value="0" name="persons" type="range" min="0" max="10" value="" onchange="document.getElementById('filteroption-persons-preview').innerHTML = parseFloat(this.value).toLocaleString(['de-CH', 'fr-CH']);" />
						<div id="filteroption-persons-preview">0</div>
					</div>
				<?php endif ?>
			<?php endforeach ?>

		</form>
	</div>
</div>
