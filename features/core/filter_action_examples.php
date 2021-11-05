<?php
/* namespace casasoft\complexmanager;

class filter_action_examples extends Feature {

	public function __construct() {
		$this->add_action( 'cxm_render_before_form_parts', 'cxm_render_before_form_parts', 10 );
		$this->add_action( 'cxm_render_after_form_parts', 'cxm_render_after_form_parts', 10 );
	}

	public function cxm_render_before_form_parts($data){
		?>
			<div class="row">
				<div class="col-md-3">
					<div class="complex-form-part">
						<strong>I'm Interested in</strong>

						<input type="hidden" name="extra_data[interest_residential]" value="0" />
						<?php $randy = rand(0,100000);  ?>
						<div class="checkboxoption">
							<input 
								id="interest_residential_<?= $randy ?>" 
								type="checkbox" 
								name="extra_data[interest_residential]" 
								value="1" 
								<?= (isset($data['extra_data']['interest_residential']) && $data['extra_data']['interest_residential'] == 1 ? 'CHECKED' : '') ?> 
							/>
							<label for="interest_residential_<?= $randy ?>">Renting</label>
						</div>
						
						<input type="hidden" name="extra_data[interest_comercial]" value="0" />
						<div class="checkboxoption">
							<input 
								id="interest_comercial" 
								type="checkbox" 
								name="extra_data[interest_comercial]" 
								value="1"
								<?= (isset($data['extra_data']['interest_comercial']) && $data['extra_data']['interest_comercial'] == 1 ? 'CHECKED' : '') ?>
							/>
							<label for="interest_comercial">Office Space</label>
						</div>

						<input type="hidden" name="extra_data[interest_parking]" value="0" />
						<div class="checkboxoption">
							<input 
								id="interest_parking" 
								type="checkbox" 
								name="extra_data[interest_parking]" 
								value="1" 
								<?= (isset($data['extra_data']['interest_parking']) && $data['extra_data']['interest_parking'] == 1 ? 'CHECKED' : '') ?> 
							/>
							<label for="interest_parking">Extra Parking</label>
						</div>
					</div>

				</div> 
				<div class="col-md-9">
		<?
	}

	public function cxm_render_after_form_parts($data){
		?>
					<input type="hidden" name="extra_data[send_me_documentations]" value="0" />
					<div class="checkboxoption">
						<input 
							id="send_me_documentations" 
							type="checkbox" 
							name="extra_data[send_me_documentations]" 
							value="1" 
							<?= (isset($data['extra_data']['interest_parking']) && $data['extra_data']['interest_parking'] == 1 ? 'CHECKED' : '') ?> 
						/>
						<label for="send_me_documentations">Send to my E-Mail the Properties Documentation</label>
					</div>

				</div> 
			</div> 
		<?
	}
	

} */


// Subscribe to the drop-in to the initialization event
//add_action( 'complexmanager_init', array( 'casasoft\complexmanager\filter_action_examples', 'init' ), 10 );