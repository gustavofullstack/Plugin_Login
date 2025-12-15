( function( $ ) {
	'use strict';

	var LoginUX = {
		currentView: ( window.udiLogin && window.udiLogin.currentView ) || 'login',

		init: function() {
			this.$forms = $( '.udi-login-forms' );
			this.bindSwitchLinks();
			this.bindPasswordToggle();
			this.renderRecaptcha( this.$forms );
		},

		bindSwitchLinks: function() {
			var self = this;

			$( document ).on( 'click', '.udi-switch', function( event ) {
				var $link = $( this );
				var targetView = $link.data( 'udiView' ) || $link.data( 'view' );

				if ( ! targetView || ! window.udiLogin ) {
					return;
				}

				event.preventDefault();
				self.switchView( targetView, $link.attr( 'href' ) );
			} );
		},

		bindPasswordToggle: function() {
			$( document ).on( 'click', '.udi-toggle-password', function() {
				var $btn = $( this );
				var $input = $btn.closest( '.udi-password-wrapper' ).find( 'input' );
				if ( ! $input.length ) {
					return;
				}
				var showText = window.udiLogin && window.udiLogin.labelShowPassword ? window.udiLogin.labelShowPassword : 'Ocultar senha';
				var hideText = window.udiLogin && window.udiLogin.labelHidePassword ? window.udiLogin.labelHidePassword : 'Mostrar senha';

				if ( 'password' === $input.attr( 'type' ) ) {
					$input.attr( 'type', 'text' );
					$btn.attr( 'aria-label', showText );
				} else {
					$input.attr( 'type', 'password' );
					$btn.attr( 'aria-label', hideText );
				}
			} );
		},

		switchView: function( view, fallbackUrl ) {
			if ( ! view || view === this.currentView ) {
				return;
			}

			if ( ! window.udiLogin || ! window.udiLogin.ajaxUrl ) {
				this.fallbackRedirect( fallbackUrl );
				return;
			}

			var self = this;
			var requestData = {
				action: 'udi_login_switch_view',
				nonce: window.udiLogin.ajaxNonce,
				view: view,
			};

			this.toggleLoading( true );

			$.post( window.udiLogin.ajaxUrl, requestData )
				.done( function( response ) {
					if ( response && response.success && response.data && response.data.html ) {
						self.currentView = response.data.view;
						self.$forms.html( response.data.html ).attr( 'data-current-view', response.data.view );
						self.renderRecaptcha( self.$forms );
					} else {
						self.fallbackRedirect( fallbackUrl );
					}
				} )
				.fail( function() {
					self.fallbackRedirect( fallbackUrl );
				} )
				.always( function() {
					self.toggleLoading( false );
				} );
		},

		fallbackRedirect: function( url ) {
			if ( url ) {
				window.location.href = url;
			}
		},

		toggleLoading: function( state ) {
			this.$forms.toggleClass( 'is-loading', !! state );
		},

		renderRecaptcha: function( $context ) {
			if ( 'undefined' === typeof grecaptcha || ! grecaptcha.render ) {
				return;
			}

			$context.find( '.g-recaptcha' ).each( function( index, element ) {
				var $el = $( element );
				if ( $el.data( 'rendered' ) ) {
					return;
				}

				var siteKey = $el.data( 'sitekey' );
				if ( ! siteKey ) {
					return;
				}

				var widgetId = grecaptcha.render( element, {
					sitekey: siteKey,
				} );

				$el.data( 'rendered', widgetId );
			} );
		},
	};

	$( document ).ready( function() {
		LoginUX.init();
	} );
}( jQuery ) );
