<form id="complexContactFormAnchor" class="complex-contact-form" action="#complexContactFormAnchor" method="POST">
	<input type="hidden" name="complex-unit-inquiry[post]" value="1">
	<div class="row">
		<div class="col-md-2">
			Ich interessiere mich für die Wohnung
			<select name="complex-unit-inquiry[unit_id]" id="">
				<option> – </option>
				<?php 
					foreach ($buildings as $building) {
						echo '<optgroup label="'.$building['term']->name.'">';
						foreach ($building['units'] as $unit) {
							echo '<option value="'.$unit->ID.'" '.($unit->ID == $data['unit_id'] ? 'selected="selected"' : '').'>'.$unit->post_title.'</option>';
						}
						echo "</optgroup>";
					}
				 ?>
			</select>
		</div>
		<div class="col-md-5">
			<dl>
				<dt class="editable">
					Anrede
				</dt>
				<dd class="editable">
					<div class="row">
						<div class="col-xs-6">
							<div class="radio">
								<label>
									<input type="radio" name="complex-unit-inquiry[gender]" value="male" <?= ($data['gender'] == 'male' ? 'checked="checked"' : '') ?>> Herr										
								</label>
							</div>
						</div>
						<div class="col-xs-6">
							<div class="radio">
								<label>
									<input type="radio" name="complex-unit-inquiry[gender]" value="female" <?= ($data['gender'] == 'female' ? 'checked="checked"' : '') ?>> Frau
								</label>
							</div>
						</div>
					</div>
				</dd>
				<dt class="editable">
					Name
				</dt>
				<dd class="editable">
					<div class="row">
						<div class="col-xs-6">
							<input type="text" name="complex-unit-inquiry[first_name]" placeholder="First name" class="form-control" value="<?= $data['first_name'] ?>">											
						</div>
						<div class="col-xs-6">
							<input type="text" name="complex-unit-inquiry[last_name]" placeholder="Last name" class="form-control" value="<?= $data['last_name'] ?>">											
						</div>
					</div>
				</dd>
				<dt class="editable"><label for="complex-unit-inquiry[email]">Email</label></dt>
				<dd class="editable">
					<input type="email" name="complex-unit-inquiry[email]"  class="form-control" value="<?= $data['email'] ?>">									
				</dd>
				<dt class="editable">
					<label for="complex-unit-inquiry[phone]">Phone</label>									
				</dt>
				<dd class="editable">
					<input type="tel" name="complex-unit-inquiry[phone]"  pattern="[0-9]*" class="form-control" value="<?= $data['phone'] ?>">									
				</dd>
				<dt class="editable">
					Address
				</dt>
				<dd class="editable">
					<input type="text" name="complex-unit-inquiry[street]" placeholder="Street" class="form-control" value="<?= $data['street'] ?>">										
					<div class="row">
						<div class="col-xs-4">
							<input type="text" name="complex-unit-inquiry[postal_code]" placeholder="ZIP" pattern="\d*" class="form-control" value="<?= $data['postal_code'] ?>">											
						</div>
						<div class="col-xs-8">
							<input type="text" name="complex-unit-inquiry[locality]" placeholder="City" class="form-control" value="<?= $data['locality'] ?>">											
						</div>
					</div>
				</dd>
			</dl>

		</div>
		<div class="col-md-5">
			<dl>
				<dt class="editable">
					Betreff
				</dt>
				<dd class="editable">
					<input type="text" name="complex-unit-inquiry[subject]" rows="7" class="form-control" placeholder="Um was handelt es sich?" value="<?= $data['subject'] ?>" /><br>		
				</dd>
				<dt class="editable">
					Ihre Nachricht
				</dt>
				<dd class="editable">
					<textarea name="complex-unit-inquiry[message]" rows="7" class="form-control" placeholder="Genauere Beschreibung"><?= $data['message'] ?></textarea><br>		
				</dd>
			</dl>
			
			<button type="submit" class="btn btn-primary pull-right">Senden</button>
		</div>
	</div>
</form> 