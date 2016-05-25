jQuery( function () {
	"use strict";

	(function($){

		//var CXMscrollOffset = 50;
		var CXMscrollOffset = 124;

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
			if (query.status) {
				if(typeof query.status === "string") {
					returnQuery.status = [query.status];
				} else {
					returnQuery.status = query.status;
				}
			} else {
				returnQuery.status = null;
			}
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

				var status_pass = true;
				if (data && data.status && query.statuss) {
					status_pass = false;
					$.each(query.statuss, function(index, value) {
						if(value == data.status){
					    	status_pass = true;
					    }
					});
				}

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

				if (room_pass && status_pass) {
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
					    return classNames + ' active';
					});
				}
			});
			$('.complex-custom-overlays img').not('.active').fadeOut(speed);
			$('.complex-custom-overlays img[data-show-on-active-unit="'+id+'"]').addClass('active').fadeIn(speed);

			$('.complex-tooltip-unit-item').hide();
			if ($('.complex-project-graphic:hover').length !== 0) {
				$('.complex-tooltip').show();
			}
			$('.complex-tooltip-unit-item[data-unit="'+id+'"]').show();
		}

		function unhighlightProjectUnit($headerRow, speed){
			speed = typeof speed !== 'undefined' ? speed : 0;
			if ($headerRow.hasClass('active')) {

			} else {
				var id = "#"+$headerRow.prop('id');
				$('.complex-project-graphic-interaction a').each(function(iindex, iel) {
					if (endsWith($(iel).attr("xlink:href"), id)) {
						//addClass
						$(iel).attr('class', function(index, classNames) {
						    return classNames.replace('active', '');
						});
					}
				});
				$('.complex-custom-overlays img[data-show-on-active-unit="'+id+'"]').fadeOut(speed);
			}

			$('.complex-tooltip-unit-item').hide();
			$('.complex-tooltip').hide();
			
		}

		function activateProjectUnit($headerRow){
			$('.complex-tooltip').hide();
			$('.complex-project-graphic-interaction a').each(function(index, el) {
				// removeClass
				$(el).attr('class', function(index, classNames) {
				    return classNames.replace('active', '');
				});
			});

			if ($headerRow.hasClass('active')) {
				$headerRow.next().find('.detail-row-wrapper').slideUp('slow');
				$headerRow.removeClass('active');
				$headerRow.next().removeClass('active');
			} else {

				scrolltoheaderRow($headerRow, CXMscrollOffset);

				$('.complex-unit-header-row.active').each(function(index, el) {
					
					$(el).next().find('.detail-row-wrapper').slideUp('slow');
					$(el).removeClass('active');
					$(el).next().removeClass('active');
				});

				$headerRow.next().find('.detail-row-wrapper').slideDown('slow');	
				$headerRow.addClass('active');
				$headerRow.next().addClass('active');

				/*$('html, body').animate({
			        scrollTop: $headerRow.next().find('.detail-row-wrapper').offset().top - 100
			    }, 500);*/

				highlightProjectUnit($headerRow);
			}
		}

		function scrolltoheaderRow($headerRow, offset){
			$('.complex-tooltip').hide();
			offset = typeof offset !== 'undefined' ? offset : 0;
			var $tr = $headerRow;
			if ($('.complex-unit-detail-row.active').length && $('.complex-unit-detail-row.active').offset().top < $tr.offset().top) {
				$('html, body').animate({
			        scrollTop: $tr.offset().top - $('.complex-unit-detail-row.active').outerHeight() - offset
			    }, 500);
			} else {
				$('html, body').animate({
			        scrollTop: $tr.offset().top - offset
			    }, 500);
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

					ajaxifyContactForm($('#complexContactFormAnchor'));
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

		//make form ajaxified
		ajaxifyContactForm($('#complexContactFormAnchor'));
		
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
			highlightProjectUnit($(this));
		}, function(){
			unhighlightProjectUnit($(this));
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
				highlightProjectUnit($('#'+hash));
			}
		}, function(){
			var url =$(this).attr("xlink:href"), idx = url.indexOf("#");
			var hash = idx !== -1 ? url.substring(idx+1) : "";
			if ($('#'+hash).length) {
				unhighlightProjectUnit($('#'+hash));
			}
		});

		//open contact click
		$('.complex-call-contact-form').click(function(event) {
			event.preventDefault();
			var unit_id = $(this).data('unit-id');
			$('#complexContactForm form [name="complex-unit-inquiry[unit_id]"]').val(unit_id).prop('disabled','disabled');
			$('#complexContactForm').appendTo($(this).parent());
			$('#complexContactForm').slideUp(0);
			$('#complexContactForm').slideDown('slow');
			//$("#complexContactForm input:text, #complexContactForm textarea").first().focus();
			$('.complex-sendback-contact-form').show();
			$('html, body').animate({
		        scrollTop: $('#complexContactForm').offset().top - CXMscrollOffset
		    }, 500);
			$(this).hide();
		});

		//close contact click
		$('.complex-sendback-contact-form').click(function(event) {
			event.preventDefault();
			$('#complexContactForm').slideUp('slow');
			$('.complex-call-contact-form').show();
		});

		var query = getQueryStringAsObject();
		filterList(query, $('.complex-list-wrapper'));

		$('#complexFilterForm').change(function(event) {
			var querystring = $('#complexFilterForm').serialize();
			querystring = querystring.replace(/%5B/g, '[');
			querystring = querystring.replace(/%5D/g, ']');
			query = getQueryStringAsObject(querystring);
			filterList(query, $('.complex-list-wrapper'));
		});


		$(document).on('mousemove', function(e){
			if ($('.complex-project-graphic:hover').length !== 0) {
			    $('.complex-tooltip').css({
			       left:  e.pageX-15,
			       top:   e.pageY+25
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

	}(jQuery));

} );