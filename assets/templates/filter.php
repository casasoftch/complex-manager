<div class="complex-filter-wrapper">
	<div class="complex-filter">
		<form method="GET" id="complexFilterForm" action="">
			<?php if ($roomfilters): ?>
				<div class="filteroption complex-filter-checkboxwrapper filteroption-rooms-checkboxes">
					<label class="filteroption-label" for="">Zimmer</label>
					<?php foreach ($roomfilters as $val): ?>
						<span class="checkboxoption"><input type="checkbox" name="rooms[]" id="cxmFilterRooms_<?php echo $val ?>" value="<?php echo $val ?>"><label for="cxmFilterRooms_<?php echo $val ?>" class="checkbox-inline">&nbsp;<?php echo $val ?></label></span>	
					<?php endforeach ?>
				</div>
			<?php endif ?>
			<div class="filteroption complex-filter-checkboxwrapper filteroption-status-checkboxes">
				<label class="filteroption-label" for="">Status</label>
				<span class="checkboxoption"><input type="checkbox" name="status[]" id="cxmFilterStatus_available" value="available"><label for="cxmFilterStatus_available" class="checkbox-inline">Verfügbar</label></span>
			</div>
		</form>
	</div>
</div>
