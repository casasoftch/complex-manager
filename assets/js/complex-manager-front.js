jQuery( function () {
	"use strict";

	var $ = jQuery;
	
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

			$headerRow.next().find('.detail-row-wrapper').slideDown('slow', function(){
				
			});	

			$('html, body').animate({
		        scrollTop: $headerRow.next().find('.detail-row-wrapper').offset().top - 100
		    }, 500);
	
			$headerRow.addClass('active');
			$headerRow.next().addClass('active');
			var $graphic_anchor = $('.complex-project-graphic-interaction a[data-target = "#'+$headerRow.prop('id')+'" ]');
			if ($graphic_anchor.length) {
				$graphic_anchor.attr("class", "active");
			}

		}
	}

	//$('#complexContactForm').hide();

	//fixes safari 6?
	$(".complex-project-graphic img").load(function(){
		$('.complex-project-graphic-interaction').height($('.complex-project-graphic img').height());
	});

	$('.complex-unit-detail-row .detail-row-wrapper').slideUp(0);
	$('.complex-unit-header-row').click(function() {
		activateProjectUnit($(this));
	});

	$('.complex-project-graphic-interaction a').click(function(event) {
		event.preventDefault();
		var url =$(this).attr("xlink:href"), idx = url.indexOf("#");
		var hash = idx !== -1 ? url.substring(idx+1) : "";
		if ($('#'+hash).length) {
			$('#'+hash).click();
		}	
	});

	var curHash = $(location).attr('href').replace(/^.*?(#|$)/,'');
	if (curHash && $('#'+curHash).length) {
		$('#'+curHash).click();
	}

	$('.complex-call-contact-form').click(function(event) {
		event.preventDefault();
		var unit_id = $(this).data('unit-id');
		$('#complexContactForm form [name="complex-unit-inquiry[unit_id]"]').val(unit_id);

		$('#complexContactForm').appendTo($(this).parent());
		$('#complexContactForm').slideUp(0);
		$('#complexContactForm').slideDown('slow', function(){
			/*$('html, body').animate({
		        scrollTop: $("#complexContactForm").offset().top - 100
		    }, 500);*/
		});
		/*$('html, body').animate({
	        scrollTop: $("#complexContactForm").offset().top - 100
	    }, 500);*/

		$('.complex-sendback-contact-form').show();

		$(this).hide();
	});

	$('.complex-sendback-contact-form').click(function(event) {
		event.preventDefault();
		//$('#complexContactForm').appendTo($(this).parent());
		//$('#complexContactForm').slideDown('fast');
		$('#complexContactForm').slideUp('slow');
		$('.complex-call-contact-form').show();
		//$(this).hide();
	});

} );