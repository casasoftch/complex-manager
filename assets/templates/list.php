
<?php 
	if (isset(get_option('complex_manager')['translate_labels']) && get_option('complex_manager')['translate_labels']) {
		$labels = maybe_unserialize((maybe_unserialize(get_option('complex_manager')['translate_labels'])));
	} else {
		$labels = [];
	}
	$labels_array = [];
	global $sitepress;
	$lang = get_locale();
	if (!empty($labels) && isset($labels['labels']) && $labels['labels']) {
		foreach ($labels as $key => $label) {
			if ($sitepress) {
				if ($label['label'] && $lang == 'de_DE') {
					$labels_array[$key] = $label['label'];
				} elseif($label['label_en'] && $lang == 'en_US') {
					$labels_array[$key] = $label['label_en'];
				} elseif($label['label_fr'] && $lang == 'fr_FR') {
					$labels_array[$key] = $label['label_fr'];
				} else {
					$labels_array[$key] = $label['label'];
				}
			} else {
				if ($label['label']) {
					$labels_array[$key] = $label['label'];
				}
			}
		}
	}
	
?>


<div class="complex-list-wrapper <?php echo ($collapsible ? 'complex-list-wrapper-collapsible' : '') ?> <?php echo $class ?>">
	<?php foreach ( $the_buildings as $building ) { ?>
		
		<div class="complex-unit-wrapper 
			<?php 
				$html = '';
				echo apply_filters('cxm_render_additional_building_classes', $html, $building['term']->term_id);
			?>	" <?= ($building['hidden'] ? ' style="display:none"' : '') ?>>
	 		<?php 
				$html = '<h2 class="unit-title">';
				echo apply_filters('cxm_render_building_title_opening_tag', $html);
			?>	

			<?php echo $building['term']->name; ?>

	 		<?php 
				$html = '</h2>';
				echo apply_filters('cxm_render_building_title_closing_tag', $html);
			?>	
			<?php 
				$individual_direct_recipients = '';
				if (isset($building['term']->term_id) && $building['term']->term_id) {
					$individual_direct_recipients = get_term_meta($building['term']->term_id, 'individual-direct-recipients');
				}
			 ?>
			<div class="unit-description"><?= wpautop( $building['description'], false ); ?></div>
			
			<?php if (!empty(get_option('complex_manager')['flex_list'])): ?>
				<div class="table-responsive table-responsive--flex complex-building-<?= $building['term']->slug ?>" data-recipients="<?php echo implode(',', $individual_direct_recipients) ?>">
					<div class="complex-building-flex">
						<?php foreach($building['the_units'] as $the_unit): ?>
							<?php 
								$type_array = wp_get_post_terms($the_unit['post']->ID, 'unit_type', array( 'field' => 'slugs' ));
								$the_unit_ID = $the_unit['post']->ID;
								$types = '';
								$new_types = array();
								if ($type_array) {
									foreach ($type_array as $type) {
										$new_types[] = $type->slug;
									}
									$types = 'data-types="' . implode(', ', $new_types) . '"';
								}
								$colcount = count($the_unit['displayItems']);	
							?>
							<div class="complex-building-flex__row-outer" 
								<?php echo $types; ?> 
								id="unit_<?php echo $the_unit_ID ?>" 
								data-unit-id="<?php echo $the_unit_ID ?>" 
								<?php
									$data = array_map('htmlspecialchars_decode', $the_unit['data']);
									$json_encoded_data = json_encode($data);
								?>
								data-json="<?php echo htmlspecialchars($json_encoded_data, ENT_QUOTES, 'UTF-8'); ?>">
								<div class="complex-building-flex__row   
									state-<?php echo $the_unit['state'] ?> 
									status-<?php echo $the_unit['status'] ?> 
									<?php echo (
													get_cxm($the_unit['post'], 'tour_url') && 
													(($the_unit['data']['status'] != 'reserved' && 
													$the_unit['data']['r_link'] != 'hidden-reserved') || 
													($the_unit['data']['status'] != 'sold' && $the_unit['data']['r_link'] != 'hidden-reserved') || 
													($the_unit['data']['status'] != 'rented' && $the_unit['data']['r_link'] != 'hidden-reserved')) ? 
													'complex-building-flex__row--with-tour' : ''
												) ?>">
									<?php if (get_cxm($the_unit['post'], 'status')): ?>
										<div class="complex-building-flex__row__status">
											<?php 
												switch (get_cxm($the_unit['post'], 'status')) {
													case 'sold':
														$availability_label = __('Sold', 'complexmanager');
														break;
													case 'rented':
														$availability_label = __('Rented', 'complexmanager');
														break;
													case 'pre-reserved':
														$availability_label = __('Pre-reserver', 'complexmanager');
														break;
													case 'reserved':
														$availability_label = __('Reserved', 'complexmanager');
														break;
													case 'available':
														$availability_label = __('Available', 'complexmanager');
														break;
													default:
														$availability_label = __('Available', 'complexmanager');
														break;
												}	
											?>
											<div class="complex-unit-status">
												<?php echo $availability_label; ?>
											</div>
										</div>										
									<?php endif; ?>
									<div class="complex-building-flex__row__data">
										<?php foreach($the_unit['displayItems'] as $displayItem): ?>
											<?php if(
												($displayItem['field'] == 'r_rent_net' && (get_cxm($the_unit['post'], 'status') == 'sold' || get_cxm($the_unit['post'], 'status') == 'rented')) || 
												($displayItem['field'] == 'r_purchase_price' && (get_cxm($the_unit['post'], 'status') == 'sold' || get_cxm($the_unit['post'], 'status') == 'rented')) || 
												($displayItem['field'] == 'r_rent_gross' && (get_cxm($the_unit['post'], 'status') == 'sold' || get_cxm($the_unit['post'], 'status') == 'rented'))
												): ?>
											<?php elseif ($displayItem['field'] == 'r_rent_net' || $displayItem['field'] == 'r_rent_gross'): ?>
												<?php 

													$show_price_segments = get_term_meta($building['term']->term_id, 'show_price_segments', true);

													$rent_timesegment = get_post_meta($the_unit_ID, '_complexmanager_unit_rent_timesegment', true);
													switch ($rent_timesegment) {
														case 'W':
															$rent_timesegment = __('week', 'complexmanager');
															break;
														case 'M':
															$rent_timesegment = __('month', 'complexmanager');
															break;
														case 'Y':
															$rent_timesegment = __('year', 'complexmanager');
															break;
														default:
															$rent_timesegment = __('month', 'complexmanager');
															break;
													}

													$rent_propertysegment = get_post_meta($the_unit_ID, '_complexmanager_unit_rent_propertysegment', true);
													switch ($rent_propertysegment) {
														case 'full':
															$rent_propertysegment = '';
															break;
														case 'M2':
															$rent_propertysegment = 'm²';
															break;
														default:
															$rent_propertysegment = '';
															break;
													}

													$value = $displayItem['value'];

													if ($show_price_segments) {
														if ($rent_propertysegment == 'm²') {
															$value = $value . ' / ' . $rent_propertysegment . ' / ' . $rent_timesegment;
														} else {
															$value = $value . ' / ' . $rent_timesegment;
														}
													}

												?>
												<div class="complex-building-flex__row__item <?php echo $displayItem['td_classes']; ?>">
													<?php echo $displayItem['label']; ?>: <strong><?php echo $value; ?></strong>
												</div>
											<?php elseif ($displayItem['field'] != 'status'): ?>
												<div class="complex-building-flex__row__item <?php echo $displayItem['td_classes']; ?>">
													<?php echo $displayItem['label']; ?>: <strong><?php echo $displayItem['value']; ?></strong>
												</div>
											<?php endif; ?>											
										<?php endforeach; ?>
									</div>
									<div class="complex-building-flex__row__info">
										<div class="complex-building-flex__row__info__cta">
											<?php if (get_cxm($the_unit['post'], 'tour_url')): ?>
												<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['r_link'] == 'hidden-reserved')): ?>

												<?php else: ?>
													<a 
														<?php if (get_cxm($the_unit['post'], 'tour_target')): ?> 
															target="<?php echo get_cxm($the_unit['post'], 'tour_target') ?>"
														<?php else: ?>
															target="_self"
														<?php endif; ?>
															class="
														<?php 
															$html = 'btn btn-primary pull-left complex-link-btn';
															echo apply_filters('cxm_render_tour_button_classes', $html);
														?>" 
													href="<?= get_cxm($the_unit['post'], 'tour_url') ?>">
														<?php if (!empty($labels_array) && isset($labels_array['virtual_tour']) && $labels_array['virtual_tour']): ?>
															<span>
																<?php echo $labels_array['virtual_tour']; ?>
															</span>
														<?php else: ?>
															<span>
																<?php echo (get_cxm($the_unit['post'], 'tour_label') ? get_cxm($the_unit['post'], 'tour_label') : 'Link') ?>
															</span>
														<?php endif; ?>
														<?php 
															$html = '';
															echo apply_filters('cxm_render_tour_button_additional_content', $html);
														?>	
													</a>										
												<?php endif; ?>
											<?php endif; ?>
											<?php if (get_cxm($the_unit['post'], 'download_file')): ?>
												<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['quick-download'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['quick-download'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['quick-download'] == 'hidden-reserved')): ?>

												<?php else: ?>
													<a target="_blank" class="
														<?php 
															$html = 'btn btn-primary pull-left complex-download-btn';
															echo apply_filters('cxm_render_download_button_classes', $html);
														?>" 
													href="<?= get_cxm($the_unit['post'], 'download_file') ?>">
														<?php if (!empty($labels_array) && isset($labels_array['download_file']) && $labels_array['download_file']): ?>
															<span>
																<?php echo $labels_array['download_file']; ?>
															</span>
														<?php elseif (isset(get_option('complex_manager')['cxm_emonitor_rewrite_download_label']) && get_option('complex_manager')['cxm_emonitor_rewrite_download_label']): ?>
															<span>
																<?php echo get_option('complex_manager')['cxm_emonitor_rewrite_download_label']; ?>
															</span>
														<?php else: ?>
															<span>
																<?php echo (get_cxm($the_unit['post'], 'download_label') ? get_cxm($the_unit['post'], 'download_label') : 'Download') ?>
															</span>
														<?php endif; ?>
														
														<?php 
															$html = '';
															echo apply_filters('cxm_render_download_button_additional_content', $html);
														?>	
													</a>
												<?php endif; ?>
											<?php endif ?>
											<?php if (get_cxm($the_unit['post'], 'link_url')): ?>
												<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['r_link'] == 'hidden-reserved')): ?>

												<?php else: ?>
													<a 
														<?php if (get_cxm($the_unit['post'], 'link_target')): ?> 
															target="<?php echo get_cxm($the_unit['post'], 'link_target') ?>"
														<?php else: ?>
															target="_self"
														<?php endif; ?>
															class="
														<?php 
															$html = 'btn btn-primary pull-left complex-link-btn';
															echo apply_filters('cxm_render_link_button_classes', $html);
														?>" 
													href="<?= get_cxm($the_unit['post'], 'link_url') ?>">
														<?php if (!empty($labels_array) && isset($labels_array['link']) && $labels_array['link']): ?>
															<span>
																<?php echo $labels_array['link']; ?>
															</span>
														<?php elseif (isset(get_option('complex_manager')['cxm_emonitor_rewrite_link_label']) && get_option('complex_manager')['cxm_emonitor_rewrite_link_label']): ?>
															<span>
																<?php echo get_option('complex_manager')['cxm_emonitor_rewrite_link_label']; ?>
															</span>
														<?php else: ?>
															<span>
																<?php echo (get_cxm($the_unit['post'], 'link_label') ? get_cxm($the_unit['post'], 'link_label') : 'Link') ?>
															</span>
														<?php endif; ?>
														<?php 
															$html = '';
															echo apply_filters('cxm_render_link_button_additional_content', $html);
														?>	
													</a>
												<?php endif; ?>
											<?php endif ?>

											<?php if (get_cxm($the_unit['post'], 'link_url_2')): ?>
												<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['r_link'] == 'hidden-reserved')): ?>

												<?php else: ?>
													<a 
														<?php if (get_cxm($the_unit['post'], 'link_target_2')): ?> 
															target="<?php echo get_cxm($the_unit['post'], 'link_target_2') ?>"
														<?php else: ?>
															target="_self"
														<?php endif; ?>
															class="
														<?php 
															$html = 'btn btn-primary pull-left complex-link-btn';
															echo apply_filters('cxm_render_link_button_classes', $html);
														?>" 
													href="<?= get_cxm($the_unit['post'], 'link_url_2') ?>">
														
														<span>
															<?php echo (get_cxm($the_unit['post'], 'link_label_2') ? get_cxm($the_unit['post'], 'link_label_2') : 'Link') ?>
														</span>
														
														<?php 
															$html = '';
															#echo apply_filters('cxm_render_link_button_additional_content', $html);
														?>	
													</a>
												<?php endif; ?>
											<?php endif ?>

											<?php if ($the_unit['status'] != 'sold' && $the_unit['status'] != 'rented' && $integrate_form): ?>
												<a class="
														<?php 
															$html = 'btn btn-primary pull-right complex-call-contact-form';
															echo apply_filters('cxm_render_contact_button_classes', $html);
														?>"
													data-unit-id="<?= $the_unit_ID ?>" href="#complexContactForm">
													<span>
														<?php echo __('Contact', 'complexmanager'); ?>
													</span>
													<?php 
														$html = '';
														echo apply_filters('cxm_render_contact_button_additional_content', $html);
													?>	
												</a>
											<?php endif ?>
										</div>									
									</div>
								</div>
								<div class="complex-unit-detail-row" data-objectref="<?php echo get_cxm($the_unit['post'], 'idx_ref_object') ?>">
									<div class="detail-row-wrapper">
										
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else: ?>
				<div class="table-responsive complex-building-<?= $building['term']->slug ?>" data-recipients="<?php echo implode(',', $individual_direct_recipients) ?>">
					<table class="table table-condensed">
						<thead>
							<tr class="col-labels">
								<?php foreach ($building['the_units'][0]['displayItems'] as $displayItem): ?>
									<th class="col-<?= $displayItem['field'] ?> <?= ($displayItem['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>"><?= $displayItem['label'] ?></th>		
								<?php endforeach ?>
								<?php if ($collapsible) : ?>
									<th></th>
								<?php endif; ?>
							</tr>
						</thead>
						<tbody>
							<?php 
							foreach ($building['the_units'] as $the_unit) {
							
								$type_array = wp_get_post_terms($the_unit['post']->ID, 'unit_type', array( 'field' => 'slugs' ));
								$types = '';
								$new_types = array();
								if ($type_array) {
									foreach ($type_array as $type) {
										$new_types[] = $type->slug;
									}
									$types = 'data-types="' . implode(', ', $new_types) . '"';
								}
							
								$colcount = count($the_unit['displayItems']);
								$data = array_map('htmlspecialchars_decode', $the_unit['data']);
								$json_encoded_data = json_encode($data);

								echo '<tr class="complex-unit-header-row state-' . $the_unit['state'] . ' status-' . $the_unit['status'] . '" id="unit_'.$the_unit['post']->ID.'" data-unit-id="' . $the_unit['post']->ID .'"' . $types .' data-json="' . htmlspecialchars($json_encoded_data, ENT_QUOTES, 'UTF-8') . '">';
								foreach ($the_unit['displayItems'] as $displayItem) {
									echo '<td class="'.$displayItem['td_classes'].'">'.$displayItem['value'].'</td>';
								}
								if ($collapsible) {
									echo '<td class="complex-unit-caret-cell text-'.$the_unit['state'].'"><span class="complex-unit-caret"></span></td>';
									echo "</tr>";
									?>
									
										<tr class="complex-unit-detail-row" data-objectref="<?php echo get_cxm($the_unit['post'], 'idx_ref_object') ?>" data-imgurl="<?php echo (has_post_thumbnail( $the_unit['post']->ID ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $the_unit['post']->ID ), 'large' )[0] : ''); ?>">
											<td colspan="<?= $colcount+1 ?>">
												<div class="detail-row-wrapper">
													<?php if (has_post_thumbnail( $the_unit['post']->ID ) && $show_image ): ?>
														<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $the_unit['post']->ID ), 'hd' ); ?>
														<div class="complex-unit-featuredimage">
															<img class="img-responsive" data-src="<?php echo $image[0]; ?>" alt="" />
														</div>
													<?php endif; ?>
													<?php 
														$content = $the_unit['post']->post_content;
														$content = apply_filters('the_content', $content);
														$content = str_replace(']]>', ']]&gt;', $content);
														$content = str_replace('src=', 'src="data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idWlsLXJpbmciIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCBjbGFzcz0iYmsiIGZpbGw9Im5vbmUiIGhlaWdodD0iMTAwIiB3aWR0aD0iMTAwIiB4PSIwIiB5PSIwIi8+PGRlZnM+PGZpbHRlciBoZWlnaHQ9IjMwMCUiIGlkPSJ1aWwtcmluZy1zaGFkb3ciIHdpZHRoPSIzMDAlIiB4PSItMTAwJSIgeT0iLTEwMCUiPjxmZU9mZnNldCBkeD0iMCIgZHk9IjAiIGluPSJTb3VyY2VHcmFwaGljIiByZXN1bHQ9Im9mZk91dCIvPjxmZUdhdXNzaWFuQmx1ciBpbj0ib2ZmT3V0IiByZXN1bHQ9ImJsdXJPdXQiIHN0ZERldmlhdGlvbj0iMCIvPjxmZUJsZW5kIGluPSJTb3VyY2VHcmFwaGljIiBpbjI9ImJsdXJPdXQiIG1vZGU9Im5vcm1hbCIvPjwvZmlsdGVyPjwvZGVmcz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCw1MCkiPjxwYXRoIGQ9Ik0xMCw1MGMwLDAsMCwwLjUsMC4xLDEuNGMwLDAuNSwwLjEsMSwwLjIsMS43YzAsMC4zLDAuMSwwLjcsMC4xLDEuMWMwLjEsMC40LDAuMSwwLjgsMC4yLDEuMmMwLjIsMC44LDAuMywxLjgsMC41LDIuOCBjMC4zLDEsMC42LDIuMSwwLjksMy4yYzAuMywxLjEsMC45LDIuMywxLjQsMy41YzAuNSwxLjIsMS4yLDIuNCwxLjgsMy43YzAuMywwLjYsMC44LDEuMiwxLjIsMS45YzAuNCwwLjYsMC44LDEuMywxLjMsMS45IGMxLDEuMiwxLjksMi42LDMuMSwzLjdjMi4yLDIuNSw1LDQuNyw3LjksNi43YzMsMiw2LjUsMy40LDEwLjEsNC42YzMuNiwxLjEsNy41LDEuNSwxMS4yLDEuNmM0LTAuMSw3LjctMC42LDExLjMtMS42IGMzLjYtMS4yLDctMi42LDEwLTQuNmMzLTIsNS44LTQuMiw3LjktNi43YzEuMi0xLjIsMi4xLTIuNSwzLjEtMy43YzAuNS0wLjYsMC45LTEuMywxLjMtMS45YzAuNC0wLjYsMC44LTEuMywxLjItMS45IGMwLjYtMS4zLDEuMy0yLjUsMS44LTMuN2MwLjUtMS4yLDEtMi40LDEuNC0zLjVjMC4zLTEuMSwwLjYtMi4yLDAuOS0zLjJjMC4yLTEsMC40LTEuOSwwLjUtMi44YzAuMS0wLjQsMC4xLTAuOCwwLjItMS4yIGMwLTAuNCwwLjEtMC43LDAuMS0xLjFjMC4xLTAuNywwLjEtMS4yLDAuMi0xLjdDOTAsNTAuNSw5MCw1MCw5MCw1MHMwLDAuNSwwLDEuNGMwLDAuNSwwLDEsMCwxLjdjMCwwLjMsMCwwLjcsMCwxLjEgYzAsMC40LTAuMSwwLjgtMC4xLDEuMmMtMC4xLDAuOS0wLjIsMS44LTAuNCwyLjhjLTAuMiwxLTAuNSwyLjEtMC43LDMuM2MtMC4zLDEuMi0wLjgsMi40LTEuMiwzLjdjLTAuMiwwLjctMC41LDEuMy0wLjgsMS45IGMtMC4zLDAuNy0wLjYsMS4zLTAuOSwyYy0wLjMsMC43LTAuNywxLjMtMS4xLDJjLTAuNCwwLjctMC43LDEuNC0xLjIsMmMtMSwxLjMtMS45LDIuNy0zLjEsNGMtMi4yLDIuNy01LDUtOC4xLDcuMSBjLTAuOCwwLjUtMS42LDEtMi40LDEuNWMtMC44LDAuNS0xLjcsMC45LTIuNiwxLjNMNjYsODcuN2wtMS40LDAuNWMtMC45LDAuMy0xLjgsMC43LTIuOCwxYy0zLjgsMS4xLTcuOSwxLjctMTEuOCwxLjhMNDcsOTAuOCBjLTEsMC0yLTAuMi0zLTAuM2wtMS41LTAuMmwtMC43LTAuMUw0MS4xLDkwYy0xLTAuMy0xLjktMC41LTIuOS0wLjdjLTAuOS0wLjMtMS45LTAuNy0yLjgtMUwzNCw4Ny43bC0xLjMtMC42IGMtMC45LTAuNC0xLjgtMC44LTIuNi0xLjNjLTAuOC0wLjUtMS42LTEtMi40LTEuNWMtMy4xLTIuMS01LjktNC41LTguMS03LjFjLTEuMi0xLjItMi4xLTIuNy0zLjEtNGMtMC41LTAuNi0wLjgtMS40LTEuMi0yIGMtMC40LTAuNy0wLjgtMS4zLTEuMS0yYy0wLjMtMC43LTAuNi0xLjMtMC45LTJjLTAuMy0wLjctMC42LTEuMy0wLjgtMS45Yy0wLjQtMS4zLTAuOS0yLjUtMS4yLTMuN2MtMC4zLTEuMi0wLjUtMi4zLTAuNy0zLjMgYy0wLjItMS0wLjMtMi0wLjQtMi44Yy0wLjEtMC40LTAuMS0wLjgtMC4xLTEuMmMwLTAuNCwwLTAuNywwLTEuMWMwLTAuNywwLTEuMiwwLTEuN0MxMCw1MC41LDEwLDUwLDEwLDUweiIgZmlsbD0iIzk5OTk5OSIgZmlsdGVyPSJ1cmwoI3VpbC1yaW5nLXNoYWRvdykiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgZnJvbT0iMCA1MCA1MCIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHRvPSIzNjAgNTAgNTAiIHR5cGU9InJvdGF0ZSIvPjwvcGF0aD48L2c+PC9zdmc+" data-src=', $content);
														$content = preg_replace('/(<*[^>]*srcset=)"[^>]+"([^>]*>)/', '\1"data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idWlsLXJpbmciIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCBjbGFzcz0iYmsiIGZpbGw9Im5vbmUiIGhlaWdodD0iMTAwIiB3aWR0aD0iMTAwIiB4PSIwIiB5PSIwIi8+PGRlZnM+PGZpbHRlciBoZWlnaHQ9IjMwMCUiIGlkPSJ1aWwtcmluZy1zaGFkb3ciIHdpZHRoPSIzMDAlIiB4PSItMTAwJSIgeT0iLTEwMCUiPjxmZU9mZnNldCBkeD0iMCIgZHk9IjAiIGluPSJTb3VyY2VHcmFwaGljIiByZXN1bHQ9Im9mZk91dCIvPjxmZUdhdXNzaWFuQmx1ciBpbj0ib2ZmT3V0IiByZXN1bHQ9ImJsdXJPdXQiIHN0ZERldmlhdGlvbj0iMCIvPjxmZUJsZW5kIGluPSJTb3VyY2VHcmFwaGljIiBpbjI9ImJsdXJPdXQiIG1vZGU9Im5vcm1hbCIvPjwvZmlsdGVyPjwvZGVmcz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCw1MCkiPjxwYXRoIGQ9Ik0xMCw1MGMwLDAsMCwwLjUsMC4xLDEuNGMwLDAuNSwwLjEsMSwwLjIsMS43YzAsMC4zLDAuMSwwLjcsMC4xLDEuMWMwLjEsMC40LDAuMSwwLjgsMC4yLDEuMmMwLjIsMC44LDAuMywxLjgsMC41LDIuOCBjMC4zLDEsMC42LDIuMSwwLjksMy4yYzAuMywxLjEsMC45LDIuMywxLjQsMy41YzAuNSwxLjIsMS4yLDIuNCwxLjgsMy43YzAuMywwLjYsMC44LDEuMiwxLjIsMS45YzAuNCwwLjYsMC44LDEuMywxLjMsMS45IGMxLDEuMiwxLjksMi42LDMuMSwzLjdjMi4yLDIuNSw1LDQuNyw3LjksNi43YzMsMiw2LjUsMy40LDEwLjEsNC42YzMuNiwxLjEsNy41LDEuNSwxMS4yLDEuNmM0LTAuMSw3LjctMC42LDExLjMtMS42IGMzLjYtMS4yLDctMi42LDEwLTQuNmMzLTIsNS44LTQuMiw3LjktNi43YzEuMi0xLjIsMi4xLTIuNSwzLjEtMy43YzAuNS0wLjYsMC45LTEuMywxLjMtMS45YzAuNC0wLjYsMC44LTEuMywxLjItMS45IGMwLjYtMS4zLDEuMy0yLjUsMS44LTMuN2MwLjUtMS4yLDEtMi40LDEuNC0zLjVjMC4zLTEuMSwwLjYtMi4yLDAuOS0zLjJjMC4yLTEsMC40LTEuOSwwLjUtMi44YzAuMS0wLjQsMC4xLTAuOCwwLjItMS4yIGMwLTAuNCwwLjEtMC43LDAuMS0xLjFjMC4xLTAuNywwLjEtMS4yLDAuMi0xLjdDOTAsNTAuNSw5MCw1MCw5MCw1MHMwLDAuNSwwLDEuNGMwLDAuNSwwLDEsMCwxLjdjMCwwLjMsMCwwLjcsMCwxLjEgYzAsMC40LTAuMSwwLjgtMC4xLDEuMmMtMC4xLDAuOS0wLjIsMS44LTAuNCwyLjhjLTAuMiwxLTAuNSwyLjEtMC43LDMuM2MtMC4zLDEuMi0wLjgsMi40LTEuMiwzLjdjLTAuMiwwLjctMC41LDEuMy0wLjgsMS45IGMtMC4zLDAuNy0wLjYsMS4zLTAuOSwyYy0wLjMsMC43LTAuNywxLjMtMS4xLDJjLTAuNCwwLjctMC43LDEuNC0xLjIsMmMtMSwxLjMtMS45LDIuNy0zLjEsNGMtMi4yLDIuNy01LDUtOC4xLDcuMSBjLTAuOCwwLjUtMS42LDEtMi40LDEuNWMtMC44LDAuNS0xLjcsMC45LTIuNiwxLjNMNjYsODcuN2wtMS40LDAuNWMtMC45LDAuMy0xLjgsMC43LTIuOCwxYy0zLjgsMS4xLTcuOSwxLjctMTEuOCwxLjhMNDcsOTAuOCBjLTEsMC0yLTAuMi0zLTAuM2wtMS41LTAuMmwtMC43LTAuMUw0MS4xLDkwYy0xLTAuMy0xLjktMC41LTIuOS0wLjdjLTAuOS0wLjMtMS45LTAuNy0yLjgtMUwzNCw4Ny43bC0xLjMtMC42IGMtMC45LTAuNC0xLjgtMC44LTIuNi0xLjNjLTAuOC0wLjUtMS42LTEtMi40LTEuNWMtMy4xLTIuMS01LjktNC41LTguMS03LjFjLTEuMi0xLjItMi4xLTIuNy0zLjEtNGMtMC41LTAuNi0wLjgtMS40LTEuMi0yIGMtMC40LTAuNy0wLjgtMS4zLTEuMS0yYy0wLjMtMC43LTAuNi0xLjMtMC45LTJjLTAuMy0wLjctMC42LTEuMy0wLjgtMS45Yy0wLjQtMS4zLTAuOS0yLjUtMS4yLTMuN2MtMC4zLTEuMi0wLjUtMi4zLTAuNy0zLjMgYy0wLjItMS0wLjMtMi0wLjQtMi44Yy0wLjEtMC40LTAuMS0wLjgtMC4xLTEuMmMwLTAuNCwwLTAuNywwLTEuMWMwLTAuNywwLTEuMiwwLTEuN0MxMCw1MC41LDEwLDUwLDEwLDUweiIgZmlsbD0iIzk5OTk5OSIgZmlsdGVyPSJ1cmwoI3VpbC1yaW5nLXNoYWRvdykiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgZnJvbT0iMCA1MCA1MCIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHRvPSIzNjAgNTAgNTAiIHR5cGU9InJvdGF0ZSIvPjwvcGF0aD48L2c+PC9zdmc+"\2', $content);
														echo $content;
													?>
													
													<?php if (get_cxm($the_unit['post'], 'tour_url')): ?>
														<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['r_link'] == 'hidden-reserved')): ?>

														<?php else: ?>
															<a 
																<?php if (get_cxm($the_unit['post'], 'tour_target')): ?> 
																	target="<?php echo get_cxm($the_unit['post'], 'tour_target') ?>"
																<?php else: ?>
																	target="_self"
																<?php endif; ?>
																	class="
																<?php 
																	$html = 'btn btn-primary pull-left complex-link-btn';
																	echo apply_filters('cxm_render_tour_button_classes', $html);
																?>" 
															href="<?= get_cxm($the_unit['post'], 'tour_url') ?>">
																<?php if (!empty($labels_array) && isset($labels_array['virtual_tour']) && $labels_array['virtual_tour']): ?>
																	<span>
																		<?php echo $labels_array['virtual_tour']; ?>
																	</span>
																<?php else: ?>
																	<span>
																		<?php echo (get_cxm($the_unit['post'], 'tour_label') ? get_cxm($the_unit['post'], 'tour_label') : 'Link') ?>
																	</span>
																<?php endif; ?>
																
																<?php 
																	$html = '';
																	echo apply_filters('cxm_render_tour_button_additional_content', $html);
																?>	
															</a>										
														<?php endif; ?>
													<?php endif; ?>
													
													<?php if (get_cxm($the_unit['post'], 'download_file')): ?>
														<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['quick-download'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['quick-download'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['quick-download'] == 'hidden-reserved')): ?>

														<?php else: ?>
															<a target="_blank" class="
																<?php 
																	$html = 'btn btn-primary pull-left complex-download-btn';
																	echo apply_filters('cxm_render_download_button_classes', $html);
																?>" 
															href="<?= get_cxm($the_unit['post'], 'download_file') ?>">
																<?php if (!empty($labels_array) && isset($labels_array['download_file']) && $labels_array['download_file']): ?>
																	<span>
																		<?php echo $labels_array['download_file']; ?>
																	</span>
																<?php elseif (!empty(get_option('complex_manager')['cxm_emonitor_rewrite_download_label'])): ?>
																	<span>
																		<?php echo get_option('complex_manager')['cxm_emonitor_rewrite_download_label']; ?>
																	</span>
																<?php else: ?>
																	<span>
																		<?php echo (get_cxm($the_unit['post'], 'download_label') ? get_cxm($the_unit['post'], 'download_label') : 'Download') ?>
																	</span>
																<?php endif; ?>
																<?php 
																	$html = '';
																	echo apply_filters('cxm_render_download_button_additional_content', $html);
																?>	
															</a>
														<?php endif; ?>
													<?php endif ?>
													
													<?php if (get_cxm($the_unit['post'], 'link_url')): ?>
														<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['r_link'] == 'hidden-reserved')): ?>

														<?php else: ?>
															<a 
																<?php if (get_cxm($the_unit['post'], 'link_target')): ?> 
																	target="<?php echo get_cxm($the_unit['post'], 'link_target') ?>"
																<?php else: ?>
																	target="_self"
																<?php endif; ?>
																	class="
																<?php 
																	$html = 'btn btn-primary pull-left complex-link-btn';
																	echo apply_filters('cxm_render_link_button_classes', $html);
																?>" 
															href="<?= get_cxm($the_unit['post'], 'link_url') ?>">
																<?php if (!empty($labels_array) && isset($labels_array['link']) && $labels_array['link']): ?>
																	<span>
																		<?php echo $labels_array['link']; ?>
																	</span>
																<?php elseif (get_option('complex_manager')['cxm_emonitor_rewrite_link_label']): ?>
																	<span>
																		<?php echo get_option('complex_manager')['cxm_emonitor_rewrite_link_label']; ?>
																	</span>
																<?php else: ?>
																	<span>
																		<?php echo (get_cxm($the_unit['post'], 'link_label') ? get_cxm($the_unit['post'], 'link_label') : 'Link') ?>
																	</span>
																<?php endif; ?>
																<?php 
																	$html = '';
																	echo apply_filters('cxm_render_link_button_additional_content', $html);
																?>	
															</a>
														<?php endif; ?>
													<?php endif ?>

													<?php if (get_cxm($the_unit['post'], 'link_url_2')): ?>
														<?php if (($the_unit['data']['status'] == 'reserved' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'sold' && $the_unit['data']['r_link'] == 'hidden-reserved') || ($the_unit['data']['status'] == 'rented' && $the_unit['data']['r_link'] == 'hidden-reserved')): ?>

														<?php else: ?>
															<a 
																<?php if (get_cxm($the_unit['post'], 'link_target_2')): ?> 
																	target="<?php echo get_cxm($the_unit['post'], 'link_target_2') ?>"
																<?php else: ?>
																	target="_self"
																<?php endif; ?>
																	class="
																<?php 
																	$html = 'btn btn-primary pull-left complex-link-btn';
																	echo apply_filters('cxm_render_link_button_classes', $html);
																?>" 
															href="<?= get_cxm($the_unit['post'], 'link_url_2') ?>">
																
																
																	<span>
																		<?php echo (get_cxm($the_unit['post'], 'link_label_2') ? get_cxm($the_unit['post'], 'link_label_2') : 'Link') ?>
																	</span>
																
																<?php 
																	$html = '';
																	#echo apply_filters('cxm_render_link_button_additional_content', $html);
																?>	
															</a>
														<?php endif; ?>
													<?php endif ?>
													
													<?php if ($the_unit['status'] != 'sold' && $the_unit['status'] != 'rented' && $integrate_form): ?>
														<a class="
																<?php 
																	$html = 'btn btn-primary pull-right complex-call-contact-form';
																	echo apply_filters('cxm_render_contact_button_classes', $html);
																?>"
															data-unit-id="<?= $the_unit['post']->ID ?>" href="#complexContactForm">
															<span>
																<?php echo __('Contact', 'complexmanager'); ?>
															</span>
															<?php 
																$html = '';
																echo apply_filters('cxm_render_contact_button_additional_content', $html);
															?>	
														</a>
													<?php endif ?>
													
													<div class="clearfix"></div>
												</div>
											</td>
										</tr>
									<?php
								}
							} ?>
						</tbody>
						<?php if(isset($building['totals'])) : ?>
							<tfoot>
								<tr class="complex-list-footer-row">
									<?php foreach ($building['the_units'][0]['displayItems'] as $displayItem): ?>
										<th class="col-<?= $displayItem['field'] ?> <?= ($displayItem['hidden-xs'] ? 'hidden-sm hidden-xs' : '') ?>">
											<?php if (isset($building['totals'][$displayItem['field']]) && $building['totals'][$displayItem['field']]): ?>
												<?= $building['totals'][$displayItem['field']] ?>	
											<?php endif; ?>
										</th>		
									<?php endforeach ?>
									<?php if ($collapsible) : ?>
										<th></th>
									<?php endif; ?>
								</tr>
							</tfoot>
						<?php endif; ?>

					</table>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}
	?>
	<?php if ($form): ?>
		<div class="complex-contact-form-wrapper" id="complexContactForm">
			<?php 
				$html = '<a style="display:none" class="pull-right complex-sendback-contact-form" href="#complexContactForm"><i class="glyphicon glyphicon-remove"></i><span class="sr-only">' . __('Cancel', 'complexmanager') . '</span></a>';
				echo apply_filters('cxm_render_sendback_button', $html);
			?>
			<?= $form ?>
		</div>	
	<?php endif ?>
	
</div>
