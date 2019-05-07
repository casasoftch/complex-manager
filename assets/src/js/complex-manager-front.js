
function verifyCaptcha() {
	console.log('what');
	jQuery('#verifyTheCaptcha').attr('Value', 'Verified');
}

var CXMscrollOffset = 124;
if (typeof getCXMscrollOffset === "undefined") {
	function getCXMscrollOffset(){
		return 124;
	}
}
if (typeof cxmCallContactFormClickHandler === "undefined") {
	function cxmCallContactFormClickHandler(){
		jQuery('.complex-call-contact-form').click(function(event) {
			event.preventDefault();
			var unit_id = jQuery(this).data('unit-id');
			jQuery('#complexContactForm form [name="complex-unit-inquiry[unit_id]"]').val(unit_id).prop('disabled','disabled');
			jQuery('#complexContactForm').appendTo(jQuery(this).parent());
			jQuery('#complexContactForm').slideUp(0);
			jQuery('#complexContactForm').slideDown('slow');
			//jQuery("#complexContactForm input:text, #complexContactForm textarea").first().focus();
			jQuery('.complex-sendback-contact-form').show();
			jQuery('html, body').animate({
		        scrollTop: jQuery('#complexContactForm').offset().top - getCXMscrollOffset()
		    }, 500);
			jQuery(this).hide();
		});
	}
}
if (typeof getCXMFadeSpeed === "undefined") {
	function getCXMFadeSpeed(){
		return 0;
	}
}

if (typeof scrolltoheaderRow === "undefined") {
	function scrolltoheaderRow($headerRow, offset){
		jQuery('.complex-tooltip').hide();
		offset = typeof offset !== 'undefined' ? offset : 0;
		var $tr = $headerRow;
		if (jQuery('.complex-unit-detail-row.active').length && jQuery('.complex-unit-detail-row.active').offset().top < $tr.offset().top) {
			jQuery('html, body').animate({
		        scrollTop: $tr.offset().top - jQuery('.complex-unit-detail-row.active').outerHeight() - offset
		    }, 500);
		} else {
			jQuery('html, body').animate({
		        scrollTop: $tr.offset().top - offset
		    }, 500);
		}
	}
}


jQuery( function () {
	"use strict";

	(function($){

		//var CXMscrollOffset = 50;


		function endsWith(string, search){
		    return string.substring( string.length - search.length, string.length ) === search;
		}


		function getQueryStringKey(key) {
		    return getQueryStringAsObject()[key];
		}


		function getQueryStringAsObject(custom_q) {
		    var b, cv, e, k, ma, sk, v, r = {},
		        d = function (v) { return decodeURIComponent(v).replace(/\+/g, " "); }, //# d(ecode) the v(alue)
		        q = window.location.search.substring(1),
		        s = /([^&;=]+)=?([^&;]*)/g //# original regex that does not allow for ; as a delimiter:   /([^&=]+)=?([^&]*)/g
		    ;
		    if (custom_q) {
		    	q = custom_q;
		    }

		    //# ma(make array) out of the v(alue)
		    ma = function(v) {
		        //# If the passed v(alue) hasn't been setup as an object
		        if (typeof v != "object") {
		            //# Grab the cv(current value) then setup the v(alue) as an object
		            cv = v;
		            v = {};
		            v.length = 0;

		            //# If there was a cv(current value), .push it into the new v(alue)'s array
		            //#     NOTE: This may or may not be 100% logical to do... but it's better than loosing the original value
		            if (cv) { Array.prototype.push.call(v, cv); }
		        }
		        return v;
		    };

		    //# While we still have key-value e(ntries) from the q(uerystring) via the s(earch regex)...
		    while (e = s.exec(q)) { //# while((e = s.exec(q)) !== null) {
		        //# Collect the open b(racket) location (if any) then set the d(ecoded) v(alue) from the above split key-value e(ntry)
		        b = e[1].indexOf("[");
		        v = d(e[2]);

		        //# As long as this is NOT a hash[]-style key-value e(ntry)
		        if (b < 0) { //# b == "-1"
		            //# d(ecode) the simple k(ey)
		            k = d(e[1]);

		            //# If the k(ey) already exists
		            if (r[k]) {
		                //# ma(make array) out of the k(ey) then .push the v(alue) into the k(ey)'s array in the r(eturn value)
		                r[k] = ma(r[k]);
		                Array.prototype.push.call(r[k], v);
		            }
		            //# Else this is a new k(ey), so just add the k(ey)/v(alue) into the r(eturn value)
		            else {
		                r[k] = v;
		            }
		        }
		        //# Else we've got ourselves a hash[]-style key-value e(ntry)
		        else {
		            //# Collect the d(ecoded) k(ey) and the d(ecoded) sk(sub-key) based on the b(racket) locations
		            k = d(e[1].slice(0, b));
		            sk = d(e[1].slice(b + 1, e[1].indexOf("]", b)));

		            //# ma(make array) out of the k(ey)
		            r[k] = ma(r[k]);

		            //# If we have a sk(sub-key), plug the v(alue) into it
		            if (sk) { r[k][sk] = v; }
		            //# Else .push the v(alue) into the k(ey)'s array
		            else { Array.prototype.push.call(r[k], v); }
		        }
		    }

		    //# Return the r(eturn value)
		    return r;
		}


		function fixQuery(query){
			var returnQuery = {};
			if (query.rooms) {
				if(typeof query.rooms === "string") {
					returnQuery.rooms = [query.rooms];
				} else {
					returnQuery.rooms = query.rooms;
				}
			} else {
				returnQuery.rooms = null;
			}

			if (query.custom_1s) {
				if(typeof query.custom_1s === "string") {
					returnQuery.custom_1s = [query.custom_1s];
				} else {
					returnQuery.custom_1s = query.custom_1s;
				}
			} else {
				returnQuery.custom_1s = null;
			}

			if (query.custom_2s) {
				if(typeof query.custom_2s === "string") {
					returnQuery.custom_2s = [query.custom_2s];
				} else {
					returnQuery.custom_2s = query.custom_2s;
				}
			} else {
				returnQuery.custom_2s = null;
			}

			if (query.custom_3s) {
				if(typeof query.custom_3s === "string") {
					returnQuery.custom_3s = [query.custom_3s];
				} else {
					returnQuery.custom_3s = query.custom_3s;
				}
			} else {
				returnQuery.custom_3s = null;
			}

			if (query.stories) {
				if(typeof query.stories === "string") {
					returnQuery.stories = [query.stories];
				} else {
					returnQuery.stories = query.stories;
				}
			} else {
				returnQuery.stories = null;
			}

			if (query.status) {
				if(typeof query.status === "string") {
					returnQuery.status = [query.status];
				} else {
					returnQuery.status = query.status;
				}
			} else {
				returnQuery.status = null;
			}


			returnQuery.livingspace_from = (!query.livingspace_from ? 0 : query.livingspace_from);
			returnQuery.livingspace_to = (!query.livingspace_to ? 99999999999 : query.livingspace_to);

			returnQuery.rentnet_from = (!query.rentnet_from ? 0 : query.rentnet_from);
			returnQuery.rentnet_to = (!query.rentnet_to ? 99999999999 : query.rentnet_to);

			returnQuery.rentgross_from = (!query.rentgross_from ? 0 : query.rentgross_from);
			returnQuery.rentgross_to = (!query.rentgross_to ? 99999999999 : query.rentgross_to);

			returnQuery.income = (parseFloat(query.income) > 0 ? query.income : null);
			returnQuery.persons = (parseFloat(query.persons) > 0 ? query.persons : null);
			return returnQuery;
		}

		function filterList(query, $list){

			query = fixQuery(query);

			$list.find('tr.complex-unit-header-row').each(function(index, tr) {
				var data = $(tr).data('json');

				var room_pass = true;
				if (data && data.number_of_rooms && query.rooms) {
					room_pass = false;
					$.each(query.rooms, function(index, value) {
						if(value == data.number_of_rooms){
					    	room_pass = true;
					    }
					});
				}

				//income
				var income_pass = true;
				if (data && data.min_income && !data.max_income && query.income) {
					income_pass = false;
					if(parseFloat(query.income) >= parseFloat(data.min_income)){
				    	income_pass = true;
				    }
				} else if (data && !data.min_income && data.max_income && query.income) {
					income_pass = false;
					if(parseFloat(query.income) <= parseFloat(data.max_income)){
				    	income_pass = true;
				    }
				} else if (data && data.min_income && data.max_income && query.income) {
					income_pass = false;
					if(parseFloat(query.income) >= parseFloat(data.min_income) && parseFloat(query.income) <= parseFloat(data.max_income)){
				    	income_pass = true;
				    }
				}

				//persons
				var persons_pass = true;
				if (data && data.min_persons && !data.max_persons && query.persons) {
					persons_pass = false;
					if(parseFloat(query.persons) >= parseFloat(data.min_persons)){
				    	persons_pass = true;
				    }
				} else if (data && !data.min_persons && data.max_persons && query.persons) {
					persons_pass = false;
					if(parseFloat(query.persons) <= parseFloat(data.max_persons)){
				    	persons_pass = true;
				    }
				} else if (data && data.min_persons && data.max_persons && query.persons) {
					persons_pass = false;
					if(parseFloat(query.persons) >= parseFloat(data.min_persons) && parseFloat(query.persons) <= parseFloat(data.max_persons)){
				    	persons_pass = true;
				    }
				}

				var custom_1_pass = true;
				if (data && data.custom_1 && query.custom_1s) {
					custom_1_pass = false;
					$.each(query.custom_1s, function(index, value) {
						if(value == data.custom_1){
					    	custom_1_pass = true;
					    }
					});
				}

				var custom_2_pass = true;
				if (data && data.custom_2 && query.custom_2s) {
					custom_2_pass = false;
					$.each(query.custom_2s, function(index, value) {
						if(value == data.custom_2){
					    	custom_2_pass = true;
					    }
					});
				}

				var custom_3_pass = true;
				if (data && data.custom_3 && query.custom_3s) {
					custom_3_pass = false;
					$.each(query.custom_3s, function(index, value) {
						if(value == data.custom_3){
					    	custom_3_pass = true;
					    }
					});
				}

				var story_pass = true;
				if (data && data.story && query.stories) {
					story_pass = false;
					$.each(query.stories, function(index, value) {
						if(value == data.story){
					    	story_pass = true;
					    }
					});
				}

				var status_pass = true;
				if (data && data.status && query.statuss) {
					status_pass = false;
					$.each(query.statuss, function(index, value) {
						if(value == data.status){
					    	status_pass = true;
					    }
					});
				}

				// why again ?
				status_pass = true;
				if (data.status && query.status) {
					var init = $.grep(query.status, function(item) {
					    return (item == data.status);
					});
					if (init.length === 0) {
						status_pass = false;
					} else {
						status_pass = true;
					}
				}


				var livingspace_pass = true;
				if (data.r_living_space && query.livingspace_from) {
					var living_space = parseFloat(data.r_living_space.replace("&amp;nbsp;m&lt;sup&gt;2&lt;/sup&gt;", '').replace(/[^\d\.]/g,''));
					livingspace_pass = false;
					if (query.livingspace_from !== 0) {
						if (living_space >= query.livingspace_from && living_space <= query.livingspace_to) {
							livingspace_pass = true;
						}
					} else {
						if (living_space <= query.livingspace_to) {
							livingspace_pass = true;
						}
					}
				}

				var rentnet_pass = true;
				if (data.r_rent_net && query.rentnet_from) {
					//only care to filter if it differs from the original value
					if (query.rentnet_from != parseInt($('#filteroption-miete-netto').data('minrentnet')) || query.rentnet_to !=  parseInt($('#filteroption-miete-netto').data('maxrentnet'))) {
                        var rent_net = parseFloat(data.r_rent_net.replace(/[^\d\.]/g, ''));
						rentnet_pass = false;
						if (query.rentnet_from !== 0) {
							if (rent_net >= query.rentnet_from && rent_net <= query.rentnet_to) {
								rentnet_pass = true;
							}
						} else {
							if (rent_net <= query.rentnet_to) {
								rentnet_pass = true;
							}
						}
					}
				}

				var rentgross_pass = true;
				if (data.r_rent_gross && query.rentgross_from) {
					//only care to filter if it differs from the original value
					if (query.rentgross_from != parseInt($('#filteroption-miete-grossto').data('minrentgross')) || query.rentgross_to !=  parseInt($('#filteroption-miete-grossto').data('maxrentgross'))) {
                        var rent_gross = parseFloat(data.r_rent_gross.replace(/[^\d\.]/g, ''));
						rentgross_pass = false;
						if (query.rentgross_from !== 0) {
							if (rent_gross >= query.rentgross_from && rent_gross <= query.rentgross_to) {
								rentgross_pass = true;
							}
						} else {
							if (rent_gross <= query.rentgross_to) {
								rentgross_pass = true;
							}
						}
					}
				}

				if (persons_pass && income_pass && room_pass && status_pass && livingspace_pass && rentnet_pass && rentgross_pass && custom_1_pass && custom_2_pass && custom_3_pass && story_pass) {
					$(tr).removeClass('filtered');
					$(tr).next().removeClass('filtered');
				} else {
					$(tr).addClass('filtered');
					$(tr).next().addClass('filtered');
				}
			});
		}

		function highlightProjectUnit($headerRow, speed){
			speed = typeof speed !== 'undefined' ? speed : 0;
			var id = "#"+$headerRow.prop('id');
			$('.complex-project-graphic-interaction a').each(function(iindex, iel) {
				if (endsWith($(iel).attr("xlink:href"), id)) {
					//addClass
					$(iel).attr('class', function(index, classNames) {
						var re = /active/gi;
						classNames = classNames.replace(re, '');
					    return classNames + ' active';
					});
				}
			});
			$('.complex-custom-overlays img').not('.active').fadeOut(speed);
			var $targetOverlay = $('.complex-custom-overlays img[data-show-on-active-unit="'+id+'"]');
			$targetOverlay.addClass('active').fadeIn(speed);

			//lazy load
			if ($targetOverlay.data('src')) {
				$targetOverlay.prop('src', $targetOverlay.data('src'));
				$targetOverlay.data('src', null);
			}

			$('.complex-tooltip-unit-item').hide();
			if ($('.complex-project-graphic:hover').length !== 0) {
				$('.complex-tooltip').show();
			}
			$('.complex-tooltip-unit-item[data-unit="'+id+'"]').show();
		}

		function unhighlightProjectUnit($headerRow, speed){
			// console.log("unhighlight");
			speed = typeof speed !== 'undefined' ? speed : 0;
			if ($headerRow.hasClass('active')) {

			} else {
				var id = "#"+$headerRow.prop('id');
				$('.complex-project-graphic-interaction a').each(function(iindex, iel) {
					if (endsWith($(iel).attr("xlink:href"), id)) {
						//addClass
						$(iel).attr('class', function(index, classNames) {
							var re = /active/gi;
							classNames = classNames.replace(re, '');
						    return classNames;
						});
					}
				});
				var $targetOverlay = $('.complex-custom-overlays img[data-show-on-active-unit="'+id+'"]');
				$targetOverlay.addClass('active').fadeOut(speed);
			}

			$('.complex-tooltip-unit-item').hide();
			$('.complex-tooltip').hide();

		}

		function activateProjectUnit($headerRow){
			$('.complex-tooltip').hide();
			$('.complex-project-graphic-interaction a').each(function(index, el) {
				// removeClass
				$(el).attr('class', function(index, classNames) {
					var re = /active/gi;
					classNames = classNames.replace(re, '');
				    return classNames;
				});
			});



			if ($headerRow.hasClass('active')) {
				$headerRow.next().find('.detail-row-wrapper').slideUp('slow');
				$headerRow.removeClass('active');
				$headerRow.next().removeClass('active');
			} else {
	

				$headerRow.next().find('.detail-row-wrapper img').each(function(index, el) {
					if ($(el).data('src')) {
						$(el).prop('src',$(el).data('src'));
						$(el).prop('srcset',$(el).data('src'));
						$(el).data('src', null);
					}
				});

				scrolltoheaderRow($headerRow, getCXMscrollOffset());

				$('.complex-unit-header-row.active').each(function(index, el) {

					$(el).next().find('.detail-row-wrapper').slideUp('slow');
					$(el).removeClass('active');
					$(el).next().removeClass('active');
				});


				$headerRow.next().find('.detail-row-wrapper').delay(300).slideDown('slow');
				$headerRow.addClass('active');
				$headerRow.next().addClass('active');

				/*$('html, body').animate({
			        scrollTop: $headerRow.next().find('.detail-row-wrapper').offset().top - 100
			    }, 500);*/

				highlightProjectUnit($headerRow);
			}
		}

		
		function ajaxifyContactForm($form){
			$form.on('submit', function(event) {
				event.preventDefault();
				$form.find(':input').prop('disabled', false);
				if (!$('#complexContactFormLoader').length) {
					$form.append('<div id="complexContactFormLoader"><i class="fa fa-circle-o-notch fa-spin"></i></div>');
				}
				$('#complexContactFormLoader').fadeIn('slow');
				var data = $form.serialize();
				$.post($form.prop('action'), data, function(data) {
					var $new_form = $(data).find('.complex-contact-form-wrapper');
					$('.complex-contact-form-wrapper').html($new_form.html());
					if ($('.complex-contact-form-wrapper .alert').length) {
						$('html, body').animate({
				        scrollTop: ($('.complex-contact-form-wrapper .alert').offset().top - 200)
				    }, 500);
					}
					ajaxifyContactForm($('#complexContactFormAnchor'));
					$form.trigger( "cxm-form-ajax-replaced", [ "Custom", "Event" ] );

				});

			});
		}

		//hide form
		$('.complex-list-wrapper #complexContactForm').hide();

		//fixes safari 6?
		$(".complex-project-graphic img").load(function(){
			$('.complex-project-graphic-interaction').height($('.complex-project-graphic img').height());
		});

		//hide row-details
		$('.complex-unit-detail-row .detail-row-wrapper').slideUp(0);

		/*$('.complex-unit-detail-row .detail-row-wrapper img').each(function(index, el) {
			$(el).data('src', $(el).prop('src'));
			$(el).prop('src','data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
			$(el).prop('srcset','data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
		});*/

		//make form ajaxified
		var form_events = $('#complexContactFormAnchor').data('events');
		if (form_events && form_events.submit) {
			console.log('theme has already set a submit event on #complexContactFormAnchor. The plugin yields.');
		} else {
			ajaxifyContactForm($('#complexContactFormAnchor'));
		}


		//row click
		var dragging = false;
		$("body").on("touchmove", function(){
		      dragging = true;
		});
		$("body").on("touchstart", function(){
		    dragging = false;
		});
		$('.complex-unit-header-row').on('click touchend', function(event) {
			var row = this;
			var anchorclick = $(event.target).is('a');
			if (!anchorclick) {
				event.preventDefault();

				if (!dragging) {
					if ($('.complex-list-wrapper').hasClass('complex-list-wrapper-collapsible')) {
						$('.complex-custom-overlays img').removeClass('active').hide();
						activateProjectUnit($(this));
					} else {

						if ($('.complex-contact-form-wrapper').length) {
							$('html, body').animate({
					        scrollTop: $('.complex-contact-form-wrapper').offset().top
					    }, 500);
					    $(".complex-contact-form-wrapper input:text, .complex-contact-form-wrapper textarea").first().focus();
					   	var unit_id = $(row).data('unit-id');
					    $('.complex-contact-form-wrapper form [name="complex-unit-inquiry[unit_id]"]').val(unit_id);
					    //.prop('disabled','disabled')


						} else if($(row).find('.col-quick-download a').length){
							$(row).find('.col-quick-download a').first()[0].click();
						} else if($(row).find('.col-quick-download').length){
							//silence
						} else {
							alert('form not found add it with [contactform-complex] or enable collapsible="1" property on [CXM-list] and make sure there is only one form available.');
						}
					}
				}
			}
		});

		//row hover
		$('.complex-unit-header-row').hover(function() {
			highlightProjectUnit($(this), getCXMFadeSpeed());
		}, function(){
			unhighlightProjectUnit($(this), getCXMFadeSpeed());
		});


		//hash click
		var curHash = $(location).attr('href').replace(/^.*?(#|$)/,'');
		if (curHash && $('#'+curHash).length) {
			$('#'+curHash).click();
		}

		//graphic svg click
		$('.complex-project-graphic-interaction a').on('click touchend', function(event) {
			event.preventDefault();
			var url =$(this).attr("xlink:href"), idx = url.indexOf("#");
			var hash = idx !== -1 ? url.substring(idx+1) : "";
			if ($('#'+hash).length) {
				$('#'+hash).click();
			}
		}).hover(function(){
			var url =$(this).attr("xlink:href"), idx = url.indexOf("#");
			var hash = idx !== -1 ? url.substring(idx+1) : "";
			if ($('#'+hash).length) {
				highlightProjectUnit($('#'+hash), getCXMFadeSpeed());
			}
		}, function(){
			var url =$(this).attr("xlink:href"), idx = url.indexOf("#");
			var hash = idx !== -1 ? url.substring(idx+1) : "";
			if ($('#'+hash).length) {
				unhighlightProjectUnit($('#'+hash), getCXMFadeSpeed());
			}
		});

		//open contact click
		cxmCallContactFormClickHandler();
		// $('.complex-call-contact-form').click(function(event) {
		// 	event.preventDefault();
		// 	var unit_id = $(this).data('unit-id');
		// 	$('#complexContactForm form [name="complex-unit-inquiry[unit_id]"]').val(unit_id).prop('disabled','disabled');
		// 	$('#complexContactForm').appendTo($(this).parent());
		// 	$('#complexContactForm').slideUp(0);
		// 	$('#complexContactForm').slideDown('slow');
		// 	//$("#complexContactForm input:text, #complexContactForm textarea").first().focus();
		// 	$('.complex-sendback-contact-form').show();
		// 	$('html, body').animate({
		//         scrollTop: $('#complexContactForm').offset().top - getCXMscrollOffset()
		//     }, 500);
		// 	$(this).hide();
		// });

		//close contact click
		$('.complex-sendback-contact-form').click(function(event) {
			event.preventDefault();
			$('#complexContactForm').slideUp('slow');
			$('.complex-call-contact-form').show();
		});

		var query = getQueryStringAsObject();
		//filterList(query, $('.complex-list-wrapper'));

		$('#complexFilterForm').change(function(event) {
			var querystring = $('#complexFilterForm').serialize();
			querystring = querystring.replace(/%5B/g, '[');
			querystring = querystring.replace(/%5D/g, ']');
			query = getQueryStringAsObject(querystring);
			filterList(query, $('.complex-list-wrapper'));
		});


		$(document).on('mousemove', function(e){
			//$('.complex-project-graphic-wrapper').css('position', 'relative');
			if ($('.complex-project-graphic:hover').length !== 0) {
				var parentOffset = $('.complex-project-graphic-wrapper').offset();

			    $('.complex-tooltip').css({
			       left:  e.pageX-15 - parentOffset.left,
			       top:   e.pageY+25 - parentOffset.top
			    });
			} else {
				$('.complex-tooltip').hide();
			}
		});

		/*$('tr.complex-unit-header-row').click(function() {
			var $tr = $(this);
			if ($('complex-unit-detail-row.active').length) {
				alert('complex-unit active exists');
				if ($('complex-unit-detail-row.active').offset().top < $tr.offset().top) {
					alert($('complex-unit-detail-row.active').outerHeight());
					$('html, body').animate({
				        scrollTop: $tr.offset().top - $('complex-unit-detail-row.active').outerHeight()
				    }, 500);
				} else {
					alert('offset top of active is smaller than clicked element');
					alert();
					$('html, body').animate({
				        scrollTop: $tr.offset().top
				    }, 500);
				};
			} else {
				alert('complex-unit active does not exist');
				$('html, body').animate({
			        scrollTop: $tr.offset().top
			    }, 500);
			}

		});*/


		//google address picker api
		function prepAddressPickerForm(){
			$('.address-picker-group').each(function(index, el) {
				var $streetInput = $(el).find('input[name="complex-unit-inquiry[street]"]');
				var $postalCodeInput = $(el).find('input[name="complex-unit-inquiry[postal_code]"]');
				var $localityInput = $(el).find('input[name="complex-unit-inquiry[locality]"]');
				if ($streetInput.length && $postalCodeInput.length && $localityInput.length) {
					$(el).find('.address-picker-realinputs').hide();

					$streetInput.attr('type', 'hidden');
					$postalCodeInput.attr('type', 'hidden');
					$localityInput.attr('type', 'hidden');

					$(el).append('<div class="address-picker-input"><input type="text" class="form-control" value="' + $streetInput.val() + ' ' + $postalCodeInput.val() + ' ' + $localityInput.val() + '" /></div>')
				}

			});
		}
		//prepAddressPickerForm();



	}(jQuery));

} );
