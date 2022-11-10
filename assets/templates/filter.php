<div class="complex-filter-wrapper">
	<div class="complex-filter">
		<form method="GET" id="complexFilterForm" action="">
			<?php foreach ($filters as $filtertype => $value): ?>
				<?php if ($filtertype == 'number_of_rooms'): ?>
					<?php if ($roomfilters): ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-rooms-checkboxes">
							<label class="filteroption-label" for=""><?php echo $value ?></label>
							<?php foreach ($roomfilters as $val): ?>

								<?php $pattern = '.0'; ?>
								<?php $replacement = ''; ?>
								<?php $val = str_replace($pattern, $replacement, $val) ?>

								<span class="checkboxoption"><input type="checkbox" name="rooms[]" id="cxmFilterRooms_<?php echo $val ?>" value="<?php echo $val ?>"><label for="cxmFilterRooms_<?php echo $val ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'status') : ?>
					<div class="filteroption complex-filter-checkboxwrapper filteroption-status-checkboxes">
						<label class="filteroption-label" for=""><?php echo $value ?></label>
						<span class="checkboxoption"><input type="checkbox" name="status[]" id="cxmFilterStatus_available" value="available"><label for="cxmFilterStatus_available" class="checkbox-inline"><?= __('Available', 'complexmanager') ?></label></span>
					</div>
				<?php elseif ($filtertype == 'livingspace') : ?>
					<div id="filteroption-flaeche" class="filteroption filteroption-livingspace-slider" data-minlivingspace="<?php echo $minlivingspace ?>" data-maxlivingspace="<?php echo $maxlivingspace ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?php echo $value ?></label>
							<span id="flaeche-slider-lower-value"></span>
							<span class="slider-format-middle"><?= __('to', 'complexmanager') ?></span>
							<span id="flaeche-slider-upper-value"></span>
						</div>
						<div id="range-flaeche">
						</div>
						<input id="livingspace_from" name="livingspace_from" type="text" value="<?php echo $minlivingspace ?>" />
						<input id="livingspace_to" name="livingspace_to" type="text" value="<?php echo $maxlivingspace ?>" />
					</div>
				<?php elseif ($filtertype == 'usablespace') : ?>
					<div id="filteroption-nutzflaeche" class="filteroption filteroption-usablespace-slider" data-minusablespace="<?php echo $minusablespace ?>" data-maxusablespace="<?php echo $maxusablespace ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?php echo $value ?></label>
							<span id="nutzflaeche-slider-lower-value"></span>
							<span class="slider-format-middle"><?= __('to', 'complexmanager') ?></span>
							<span id="nutzflaeche-slider-upper-value"></span>
						</div>
						<div id="range-nutzflaeche">
						</div>
						<input id="usablespace_from" name="usablespace_from" type="text" value="<?php echo $minusablespace ?>" />
						<input id="usablespace_to" name="usablespace_to" type="text" value="<?php echo $maxusablespace ?>" />
					</div>
				<?php elseif ($filtertype == 'purchaseprice') : ?>
					<div id="filteroption-purchase-price" class="filteroption filteroption-purchaseprice-slider" data-minpurchaseprice="<?php echo $minpurchaseprice ?>" data-maxpurchaseprice="<?php echo $maxpurchaseprice ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?php echo $value ?></label>
							<span id="kauf-slider-lower-value"></span>
							<span class="slider-format-middle"><?= __('to', 'complexmanager') ?></span>
							<span id="kauf-slider-upper-value"></span>
						</div>
						<div id="range-kauf">
						</div>
						<input id="purchaseprice_from" name="purchaseprice_from" type="text" value="<?php echo $minpurchaseprice ?>" />
						<input id="purchaseprice_to" name="purchaseprice_to" type="text" value="<?php echo $maxpurchaseprice ?>" />
					</div>
				<?php elseif ($filtertype == 'rentnet') : ?>
					<div id="filteroption-miete-netto" class="filteroption filteroption-rentnet-slider" data-minrentnet="<?php echo $minrentnet ?>" data-maxrentnet="<?php echo $maxrentnet ?>">
						<div class="slider-range-values">
							<label class="filteroption-label"><?php echo $value ?></label>
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
							<label class="filteroption-label"><?php echo $value ?></label>
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
							<?php asort($story_filters) ?>
							<label class="filteroption-label"><?php echo $value ?></label>
							<?php foreach ($story_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="stories[]" id="cxmFilterStory_<?php echo htmlentities(htmlentities($val)) ?>" value="<?php echo htmlentities(htmlentities($val)) ?>"><label for="cxmFilterStory_<?php echo htmlentities(htmlentities($val)) ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'types') : ?>
					<?php if ($type_filters): ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-type-checkboxes">
							<label class="filteroption-label"><?php echo $value ?></label>
							<?php foreach ($type_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="types[]" id="cxmFilterTypes_<?php echo htmlentities(htmlentities($val->slug)) ?>" value="<?php echo htmlentities(htmlentities($val->slug)) ?>"><label for="cxmFilterTypes_<?php echo htmlentities(htmlentities($val->slug)) ?>" class="checkbox-inline">&nbsp;<?php echo $val->name ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'custom_3') : ?>
					<?php if ($custom_3_filters): ?>
						<?php natsort($custom_3_filters); ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-custom_3-checkboxes">
							<?php  ?>
							<label class="filteroption-label"><?php echo $value ?></label>
							<?php foreach ($custom_3_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="custom_3s[]" id="cxmFilterCustom3_<?php echo htmlentities(htmlentities($val)) ?>" value="<?php echo htmlentities(htmlentities($val)) ?>"><label for="cxmFilterCustom3_<?php echo htmlentities(htmlentities($val)) ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'custom_2') : ?>
					<?php if ($custom_2_filters): ?>
						<?php natsort($custom_2_filters); ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-custom_2-checkboxes">
							<?php  ?>
							<label class="filteroption-label"><?php echo $value ?></label>
							<?php foreach ($custom_2_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="custom_2s[]" id="cxmFilterCustom2_<?php echo htmlentities(htmlentities($val)) ?>" value="<?php echo htmlentities(htmlentities($val)) ?>"><label for="cxmFilterCustom2_<?php echo htmlentities(htmlentities($val)) ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'custom_1') : ?>
					<?php if ($custom_1_filters): ?>
						<?php natsort($custom_1_filters); ?>
						<div class="filteroption complex-filter-checkboxwrapper filteroption-custom_1-checkboxes">
							<?php  ?>
							<label class="filteroption-label"><?php echo $value ?></label>
							<?php foreach ($custom_1_filters as $val): ?>
								<span class="checkboxoption"><input type="checkbox" name="custom_1s[]" id="cxmFilterCustom1_<?php echo htmlentities(htmlentities($val)) ?>" value="<?php echo htmlentities(htmlentities($val)) ?>"><label for="cxmFilterCustom1_<?php echo htmlentities(htmlentities($val)) ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				<?php elseif ($filtertype == 'income') : ?>
					<div id="filteroption-income" class="filteroption filteroption-income-field" data-min="1" data-max="10">
						<label class="filteroption-label" for=""><?php echo $value ?></label>
						<input id="income" value="0" name="income" type="range" min="0" max="<?= $filter_income_max ?>" step="1000" value="" onchange="document.getElementById('filteroption-income-preview').innerHTML = parseFloat(this.value).toLocaleString(['de-CH', 'fr-CH']);" />
						<div id="filteroption-income-preview"><?= __('Please choose', 'complexmanager') ?></div>
					</div>
				<?php elseif ($filtertype == 'persons') : ?>
					<div id="filteroption-persons" class="filteroption filteroption-persons-field" data-min="1" data-max="10">
						<label class="filteroption-label" for=""><?php echo $value ?></label>
						<input id="persons" value="0" name="persons" type="range" min="0" max="10" value="" onchange="document.getElementById('filteroption-persons-preview').innerHTML = parseFloat(this.value).toLocaleString(['de-CH', 'fr-CH']);" />
						<div id="filteroption-persons-preview"><?= __('Please choose', 'complexmanager') ?></div>
					</div>
				<?php endif ?>
			<?php endforeach ?>
			
		</form>
		<div class="result-count" style="display: none;">
			<label class="filteroption-label" for=""><?php echo __('Results', 'complexmanager') ?></label>
			<span id="resultCount"></span> <span id="resultLabel"><?php echo __('objects', 'complexmanager') ?></span>
		</div>
	</div>
</div>
