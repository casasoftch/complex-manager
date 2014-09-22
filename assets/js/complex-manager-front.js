jQuery( function () {
	"use strict";

	var $ = jQuery;
	
	function activateProjectUnit($headerRow){
		$('.complex-project-graphic-interaction a').each(function(index, el) {
			$(this).attr("class", "");
		});

		if ($headerRow.hasClass('active')) {
			$headerRow.next().find('td').slideUp(0);
			$headerRow.removeClass('active');
			$headerRow.next().removeClass('active');
		} else {
			$('.complex-unit-header-row').each(function(index, el) {
				$(el).next().find('td').slideUp(0);
				$(el).removeClass('active');
				$(el).next().removeClass('active');
			});

			$headerRow.next().find('td').slideDown(0);
			$('html, body').animate({
		        scrollTop: $headerRow.offset().top - 50
		    }, 200);
			$headerRow.addClass('active');
			$headerRow.next().addClass('active');
			var $graphic_anchor = $('.complex-project-graphic-interaction a[data-target = "#'+$headerRow.prop('id')+'" ]');
			if ($graphic_anchor.length) {
				$graphic_anchor.attr("class", "active");
			}

		}
	}

	//$('#complexContactForm').hide();

	$('.complex-unit-detail-row td').slideUp(0);
	$('.complex-unit-header-row').click(function(event) {
		activateProjectUnit($(this));
	});

	$('.complex-project-graphic-interaction a').click(function(event) {
		event.preventDefault();
		var url =$(this).attr("xlink:href"), idx = url.indexOf("#")
		var hash = idx != -1 ? url.substring(idx+1) : "";
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
		$('#complexContactForm').slideDown('fast', function(){
			$('html, body').animate({
		        scrollTop: $("#complexContactForm").offset().top - 50
		    }, 200);
		});

		$('.complex-sendback-contact-form').show();

		$(this).hide();
	});

	$('.complex-sendback-contact-form').click(function(event) {
		event.preventDefault();
		//$('#complexContactForm').appendTo($(this).parent());
		//$('#complexContactForm').slideDown('fast');
		$('#complexContactForm').slideUp('fast');
		$('.complex-call-contact-form').show();
		//$(this).hide();
	});

} );