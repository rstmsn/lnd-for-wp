(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 */

$( window ).load(function() {

	/*
	 * Copy payment request invoice to clipboard
	 */

	$("#lnd-copy-invoice-clip").click(function(){
		copyToClipboard("#lnd-pay-req");
		$(this).html('&#10003;');
	});

	/*
	 * Decode QR code from image
	 */

	$("#lnd-read-qr").click(function(){ $("#lnd-qr-image").click(); });

	$("#lnd-qr-image").change(function(){

		var file = document.querySelector('#lnd-qr-image').files[0];
		var reader = new FileReader();
		reader.readAsDataURL(file);

		reader.onload = function () {

			$("#lightning-invoice").val('Decoding QR code...');

			// dispatch ajax request to save menu state
		    var data = {
	   	        'action': 'lnd_decode_qr_ajax',
		        'qr_payload': reader.result
		    };

			$.post(ajax_object.ajax_url, data, function(response) {

				if(response != 0){
					$("#lightning-invoice").val(response);
				}else{
					alert('Unable to decode that QR code');
					$("#lightning-invoice").val('');
				}
			});
		};

		reader.onerror = function (error) {
			alert('Error: ', error);
		};

	});

	/*
     * When a bitcoin balance is clicked, this function toggles
     * all balance amounts on screen between satoshi and BTC
	 */

	$('.lnd-balance').click(function(){

		var currency = $(this).find(".lnd-balance-currency").text().trim();

		if(currency == 'SAT'){

			$('.lnd-balance').each(function(index){
				var amount = parseInt( $(this).find(".lnd-balance-amount").text().replace(/\,/g,'') );
				amount = (amount/100000000).toFixed(8);
				$(this).find(".lnd-balance-amount").text(amount);
				$(this).find(".lnd-balance-currency").text("BTC");
			});

		}else if(currency == 'BTC'){

			$('.lnd-balance').each(function(index){
				var amount = $(this).find(".lnd-balance-amount").text();
				amount = Math.round((amount * 100000000)).toString();
				amount = amount.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
				$(this).find(".lnd-balance-amount").text(amount);
				$(this).find(".lnd-balance-currency").text("SAT");
			});

		}

	});

	/*
	 * Handle the 'Show/Hide Connected Peers' button functionality
	 *
	 */
	 $(".lnd-wp-expand-peers").click(function(){

	 	if($(".lnd-wp-peers").is(':hidden')){
			$(".lnd-wp-peers").slideDown();
			$(".lnd-wp-expand-peer-hide").show();
			$(".lnd-wp-expand-peer-show").hide();
	 	}else{
			$(".lnd-wp-peers").slideUp();
			$(".lnd-wp-expand-peer-hide").hide();
			$(".lnd-wp-expand-peer-show").show();
	 	}

	 });

	/*
	 * Handle the 'Show/Hide Channels' button functionality
	 *
	 */
	 $(".lnd-wp-expand-channels").click(function(){

	 	if($(".lnd-wp-channels").is(':hidden')){
			$(".lnd-wp-channels").slideDown();
			$(".lnd-wp-expand-channel-hide").show();
			$(".lnd-wp-expand-channel-show").hide();
	 	}else{
			$(".lnd-wp-channels").slideUp();
			$(".lnd-wp-expand-channel-hide").hide();
			$(".lnd-wp-expand-channel-show").show();
	 	}

	 });

	/*
	 * Handle the 'more options' button functionality
	 *
	 */

	 $(".lnd-wp-links-expand").click(function(){

	 	if($(".lnd-wp-links-more").is(':hidden')){
			$(".lnd-wp-links-more").slideDown();
			$(".lnd-wp-links-expand a").html("&uarr;<br />Hide Options");
	 	}else{
			$(".lnd-wp-links-more").slideUp();
			$(".lnd-wp-links-expand a").html("More Options<br />&darr;");
	 	}

	 });

	/* configure settings upload macaroon & tls buttons */
	$(".lnd-upload-macaroon").click(function(){
		$(".lnd-input-macaroon").click();
	});
	$(".lnd-upload-tls").click(function(){
		$(".lnd-input-tls-cert").click();
	});

	$(".lnd-inputfile").change(function(){
		var fileInput = $.trim($(this).val());
		if (fileInput.length > 0) {

			if($(this).hasClass('lnd-input-macaroon')){
				$(".lnd-upload-macaroon").html("&#10003;");
			}else if($(this).hasClass('lnd-input-tls-cert')){
				$(".lnd-upload-tls").html("&#10003;");
			}

		}
	});

	/* automatically scroll down to the main wallet view on load */
	$('.lnd-wp-scroll-marker')[0].scrollIntoView();

	/*
	 * Auto hide the configuration panel on page load
	 * Gets visibility parameter from markup, put there by php
	 */

	var hide_configuration_on_load = $('.lnd-hide-config').val();
	if(hide_configuration_on_load){ collapse_settings_menu(false); }

	/*
     * Function to handle the collapse of admin node settings dialog
     */

	$('.lnd-wp-node-settings a.collapse').click(function(){
		collapse_settings_menu(true);
	});

	$('.lnd-wp-node-settings-expand a.expand').click(function(){
		expand_settings_menu(true);
	});

	function collapse_settings_menu(animate){
		var slideTime = 400;
		var animateTime = 300;

		if (!animate){
			slideTime = 0;
			animateTime = 0;
		}

		$('.lnd-wp-node-settings').slideUp(slideTime, function(){
			$('.lnd-wp-console').animate({width: "100%"},animateTime);
			$('.lnd-wp-node-settings-expand').slideDown(slideTime);
		});

		// dispatch ajax request to save settings menu state
	    var data = {
   	        'action': 'lnd_menu_update_default_ajax',
	        'hide_menu': 'true'
	    };

	    $.post(ajax_object.ajax_url, data, function(response) {	});
	}

	function expand_settings_menu(animate){
		var slideTime = 400;
		var animateTime = 300;

		if (!animate){
			slideUpTime = 0;
			animateTime = 0;
		}

		$('.lnd-wp-node-settings-expand').slideUp(slideTime);

		$('.lnd-wp-console').animate({width: "67%"},animateTime, function (){
			$('.lnd-wp-node-settings').slideDown(slideTime);
		});

		// dispatch ajax request to save menu state
	    var data = {
   	        'action': 'lnd_menu_update_default_ajax',
	        'hide_menu': 'false'
	    };

	    $.post(ajax_object.ajax_url, data, function(response) { });
	}

	function copyToClipboard(element) {
	    var $temp = $("<input>");
	    $("body").append($temp);
	    $temp.val($(element).text()).select();
	    document.execCommand("copy");
	    $temp.remove();
	}

});


})( jQuery );