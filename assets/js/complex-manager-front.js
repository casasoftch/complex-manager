jQuery( function () {
	"use strict";

	(function($){

		function endsWith(string, search){
		    return string.substring( string.length - search.length, string.length ) === search;
		}

		function activateProjectUnit($headerRow){
			$('.complex-project-graphic-interaction a').each(function(index, el) {
				$(el).attr("class", "");
			});

			if ($headerRow.hasClass('active')) {
				$headerRow.next().find('.detail-row-wrapper').slideUp('slow');
				$headerRow.removeClass('active');
				$headerRow.next().removeClass('active');
			} else {
				$('.complex-unit-header-row.active').each(function(index, el) {
					
					$(el).next().find('.detail-row-wrapper').slideUp('slow');
					$(el).removeClass('active');
					$(el).next().removeClass('active');
				});

				$headerRow.next().find('.detail-row-wrapper').slideDown('slow');	
				$headerRow.addClass('active');
				$headerRow.next().addClass('active');

				$('html, body').animate({
			        scrollTop: $headerRow.next().find('.detail-row-wrapper').offset().top - 100
			    }, 500);
				
				var id = "#"+$headerRow.prop('id');
				$('.complex-project-graphic-interaction a').each(function(iindex, iel) {

					if (endsWith($(iel).attr("xlink:href"), id)) {
						$(iel).attr("class", "active");
					}
				});

			}
		}

		function ajaxifyContactForm($form){
			$form.on('submit', function(event) {
				event.preventDefault();
				if (!$('#complexContactFormLoader').length) {
					$form.append('<div id="complexContactFormLoader"><i class="fa fa-circle-o-notch fa-spin">&#9883;</i></div>');
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
		$('#complexContactForm').hide();

		//fixes safari 6?
		$(".complex-project-graphic img").load(function(){
			$('.complex-project-graphic-interaction').height($('.complex-project-graphic img').height());
		});

		//hide row-details
		$('.complex-unit-detail-row .detail-row-wrapper').slideUp(0);

		//make form ajaxified
		ajaxifyContactForm($('#complexContactFormAnchor'));
		
		//row click
		$('.complex-unit-header-row').click(function() {
			activateProjectUnit($(this));
		});

		//hash click
		var curHash = $(location).attr('href').replace(/^.*?(#|$)/,'');
		if (curHash && $('#'+curHash).length) {
			$('#'+curHash).click();
		}

		//graphic svg click
		$('.complex-project-graphic-interaction a').click(function(event) {
			event.preventDefault();
			var url =$(this).attr("xlink:href"), idx = url.indexOf("#");
			var hash = idx !== -1 ? url.substring(idx+1) : "";
			if ($('#'+hash).length) {
				$('#'+hash).click();
			}	
		});

		//open contact click
		$('.complex-call-contact-form').click(function(event) {
			event.preventDefault();
			var unit_id = $(this).data('unit-id');
			$('#complexContactForm form [name="complex-unit-inquiry[unit_id]"]').val(unit_id);
			$('#complexContactForm').appendTo($(this).parent());
			$('#complexContactForm').slideUp(0);
			$('#complexContactForm').slideDown('slow');
			$('.complex-sendback-contact-form').show();
			$(this).hide();
		});

		//close contact click
		$('.complex-sendback-contact-form').click(function(event) {
			event.preventDefault();
			$('#complexContactForm').slideUp('slow');
			$('.complex-call-contact-form').show();
		});

	}(jQuery));

} );