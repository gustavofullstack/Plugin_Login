/**
 * Google Sign-In JavaScript handler.
 *
 * @package UDI_Custom_Login
 */

/* global udiGoogleLogin */

(function($) {
	'use strict';

	// Global callback function for Google Identity Services
	window.udiHandleGoogleCredential = function(response) {
		if (!response.credential) {
			console.error('Google Sign-In: No credential received');
			return;
		}

		const nonce = $('#udi-google-nonce').val();
		const redirectTo = $('input[name="redirect_to"]').val() || '';

		// Show loading state
		const $googleButton = $('.g_id_signin');
		$googleButton.css('opacity', '0.6').css('pointer-events', 'none');

		// Send credential to server
		$.ajax({
			url: udiGoogleLogin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'udi_google_login',
				credential: response.credential,
				nonce: nonce,
				redirect_to: redirectTo
			},
			success: function(result) {
				if (result.success) {
					// Redirect on success
					window.location.href = result.data.redirect;
				} else {
					// Show error
					const errorMessage = result.data.message || udiGoogleLogin.errorMessage;
					alert(errorMessage);
					$googleButton.css('opacity', '1').css('pointer-events', 'auto');
				}
			},
			error: function() {
				alert(udiGoogleLogin.errorMessage);
				$googleButton.css('opacity', '1').css('pointer-events', 'auto');
			}
		});
	};

})(jQuery);
