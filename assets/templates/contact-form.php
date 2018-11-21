<?php if (!$state || ($state &&  $state != "success")): ?>
	<?php $for_bstring = 'complexField'; ?>
	<?php $for_randid = rand(0,100); ?>
	<form id="complexContactFormAnchor" class="complex-contact-form" action="#complexContactFormAnchor" method="POST" enctype="multipart/form-data">
		<?php wp_nonce_field( 'send-inquiry' ); ?>
		<input type="hidden" name="complex-unit-inquiry[post]" value="1">
		<?= do_action('cxm_render_before_form_parts', $data, $buildings); ?>
		<div class="complex-form-parts">
			<?php
				$unitcount = 0;
				$first_unit = false;
				foreach ($buildings as $building) {
					foreach ($building['units'] as $unit) {
						if (!$first_unit) {
							$first_unit = $unit;
						}
						$unitcount++;
					}
				}
			?>
			<?php if ($first_unit && $unitcount == 1): ?>
				<input type="hidden" name="complex-unit-inquiry[unit_id]" value="<?= $first_unit->ID ?>">
			<?php else: ?>
			<div class="complex-form-part">
				<?php _e('I am interested in', 'complexmanager') ?>
				<select name="complex-unit-inquiry[unit_id]">
					<option> – </option>
					<?php
						foreach ($buildings as $building) {
							echo '<optgroup label="'.$building['term']->name.'">';
							foreach ($building['units'] as $unit) {
								$story = get_cxm($unit, 'story');
								$status = get_cxm($unit, 'status');
								switch ($status) {
									case 'rented': $state = 'danger';
									case 'sold': $state = 'danger';
										echo '<option disabled="disabled" '.($unit->ID == $data['unit_id'] ? 'selected="selected"' : '').'>'.$unit->post_title . ($story ? ' (' . $story . ')' : '') . '</option>';
										break;
									default:
										echo '<option value="'.$unit->ID.'" '.($unit->ID == $data['unit_id'] ? 'selected="selected"' : '').'>'.$unit->post_title . ($story ? ' (' . $story . ')' : '') . '</option>';
										break;
								}
							}
							echo "</optgroup>";
						}
					?>
				</select>
			</div>
			<?php endif ?>
			<div class="complex-form-part">
				<dl>
					<dt class="editable">
						<label><?php _e('Salutation', 'complexmanager') ?></label>
					</dt>
					<dd class="editable">
						<div class="row">
							<div class="col-xs-6 complex-form-gender-fm">
								<div class="radio">
									<label>
										<input type="radio" name="complex-unit-inquiry[gender]" value="female" <?= ($data['gender'] == 'female' ? 'checked="checked"' : '') ?>> <?php _e('Mrs.', 'complexmanager') ?>
									</label>
								</div>
							</div>
							<div class="col-xs-6 complex-form-gender-m">
								<div class="radio">
									<label>
										<input type="radio" name="complex-unit-inquiry[gender]" value="male" <?= ($data['gender'] == 'male' ? 'checked="checked"' : '') ?>> <?php _e('Mr.', 'complexmanager') ?>
									</label>
								</div>
							</div>
						</div>
					</dd>
					<dt class="editable">
						<label for="<?= $for_bstring ?>firstName<?= $for_randid ?>"><?php _e('Name', 'complexmanager') ?>&nbsp;<small><span class="text-danger">*</span></small></label>
					</dt>
					<dd class="editable">
						<div class="row">
							<div class="col-xs-6 complex-form-first_name">
								<div class="<?= (isset($messages['first_name'])  ? 'has-error' : '') ?>">
									<input id="<?= $for_bstring ?>firstName<?= $for_randid ?>" required type="text" name="complex-unit-inquiry[first_name]" placeholder="<?php echo get_cxm_label(false, 'first_name', 'complex_inquiry') ?>" class="form-control" value="<?= $data['first_name'] ?>">
								</div>
							</div>
							<div class="col-xs-6 complex-form-last_name">
								<div class="<?= (isset($messages['last_name'])  ? 'has-error' : '') ?>">
									<input required type="text" name="complex-unit-inquiry[last_name]" placeholder="<?php echo get_cxm_label(false, 'last_name', 'complex_inquiry') ?>" class="form-control" value="<?= $data['last_name'] ?>">
								</div>
							</div>
						</div>
					</dd>
					<dt class="editable">
						<label for="<?= $for_bstring ?>legalName<?= $for_randid ?>"><?php echo get_cxm_label(false, 'legal_name', 'complex_inquiry') ?></label>
					</dt>
					<dd class="editable">
						<div class="<?= (isset($messages['legal_name'])  ? 'has-error' : '') ?>">
							<input type="text" id="<?= $for_bstring ?>legalName<?= $for_randid ?>" name="complex-unit-inquiry[legal_name]"  class="form-control" value="<?= $data['legal_name'] ?>">
						</div>
					</dd>
					<dt class="editable">
						<label for="<?= $for_bstring ?>email<?= $for_randid ?>"><?php echo get_cxm_label(false, 'email', 'complex_inquiry') ?>&nbsp;<small><span class="text-danger">*</span></small></label>
					</dt>
					<dd class="editable">
						<div class="<?= (isset($messages['email'])  ? 'has-error' : '') ?>">
							<input id="<?= $for_bstring ?>email<?= $for_randid ?>" required type="email" name="complex-unit-inquiry[email]"  class="form-control" value="<?= $data['email'] ?>">
						</div>
					</dd>

					<dt class="editable">
						<label for="<?= $for_bstring ?>name<?= $for_randid ?>"><?php _e('Phone', 'complexmanager') ?>&nbsp;<small><span class="text-danger">*</span></small></label>
					</dt>
					<dd class="editable">
						<div class="row">
							<div class="col-xs-6 complex-form-phone">
								<div class="<?= (isset($messages['phone'])  ? 'has-error' : '') ?>">
									<input id="<?= $for_bstring ?>name<?= $for_randid ?>" type="text" name="complex-unit-inquiry[phone]" placeholder="<?php echo get_cxm_label(false, 'phone', 'complex_inquiry') ?>" class="form-control" value="<?= $data['phone'] ?>">											
								</div>
							</div>
							<div class="col-xs-6 complex-form-mobile">
								<div class="<?= (isset($messages['mobile'])  ? 'has-error' : '') ?>">
									<input type="text" name="complex-unit-inquiry[mobile]" placeholder="<?php echo get_cxm_label(false, 'mobile', 'complex_inquiry') ?>" class="form-control" value="<?= $data['mobile'] ?>">
								</div>
							</div>
						</div>
					</dd>

					<dt class="editable">
						<label for="<?= $for_bstring ?>street<?= $for_randid ?>"><?php _e('Address', 'complexmanager') ?>&nbsp;<small><span class="text-danger">*</span></small></label>
					</dt>
					<dd class="editable">
						<div class="address-picker-group">
							<div class="address-picker-realinputs">
								<div class="<?= (isset($messages['street'])  ? 'has-error' : '') ?>">
									<input id="<?= $for_bstring ?>street<?= $for_randid ?>" required type="text" name="complex-unit-inquiry[street]" placeholder="<?php echo get_cxm_label(false, 'street', 'complex_inquiry') ?>" class="form-control" value="<?= $data['street'] ?>">
								</div>
								<div class="row">
									<div class="col-xs-4 complex-form-postal_code">
										<div class="<?= (isset($messages['postal_code'])  ? 'has-error' : '') ?>">
											<input required type="text" name="complex-unit-inquiry[postal_code]" placeholder="<?php echo get_cxm_label(false, 'postal_code', 'complex_inquiry') ?>" pattern="\d*" class="form-control" value="<?= $data['postal_code'] ?>">
										</div>
									</div>
									<div class="col-xs-8 complex-form-locality">
										<div class="<?= (isset($messages['locality'])  ? 'has-error' : '') ?>">
											<input required type="text" name="complex-unit-inquiry[locality]" placeholder="<?php echo get_cxm_label(false, 'locality', 'complex_inquiry') ?>" class="form-control" value="<?= $data['locality'] ?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</dd>
				</dl>
			</div>
			<?= do_action('cxm_render_before_form_footer', $data, $buildings); ?>
			<div class="complex-form-part complex-form-part-footer">
				<?php if ($reasons): ?>
					<div>
						<label><?php _e('I\'ve heard of this project through...', 'complexmanager') ?></label>
						<select class="form-control-select" name="complex-unit-inquiry[reason]">
							<option value=""> – </option>
							<?php foreach ($reasons as $reason): ?>
								<option value="<?= $reason->term_id ?>" <?= ($data['reason'] == $reason->term_id ? 'selected="selected"' : '') ?>><?= $reason->name ?></option>
							<?php endforeach ?>
						</select>
					</div>
				<?php endif ?>
				<div class="<?= (isset($messages['message'])  ? 'has-error' : '') ?>">
					<label for="<?= $for_bstring ?>message<?= $for_randid ?>"><?php _e('Message', 'complexmanager') ?></label>
					<textarea id="<?= $for_bstring ?>message<?= $for_randid ?>" name="complex-unit-inquiry[message]" rows="7" class="form-control" placeholder="<?php _e('Describe your inquiry', 'complexmanager') ?>"><?= $data['message'] ?></textarea>
				</div>
				<?= do_action('cxm_render_before_form_submission', $data, $buildings); ?>
				<small><span class="text-danger">*</span> <?php _e('Please fill in all the required fields', 'complexmanager') ?></small>
				<br><button type="submit" class="btn btn-primary pull-right"><?php _e('Send', 'complexmanager') ?></button>
			</div>
		</div>
		<?= do_action('cxm_render_after_form_parts', $data, $buildings); ?>
	</form>
<?php endif ?>
<?php if ($message): ?>
	<div class="alert alert-<?= $state ?>">
		<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<?= $message ?>
	</div>
<?php endif ?>
