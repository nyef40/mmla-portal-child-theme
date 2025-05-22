/**
 * Start cookie consent widget script
 */

( function( $, elementor ) {

	'use strict';

	var widgetCookieConsent = function( $scope, $ ) {

		var $cookieConsent = $scope.find('.bdt-cookie-consent'),
            $settings      = $cookieConsent.data('settings'),
            editMode       = Boolean( elementorFrontend.isEditMode() ),
			gtagSettings   = $cookieConsent.data('gtag');
        
        if ( ! $cookieConsent.length || editMode ) {
            return;
        }

        window.cookieconsent.initialise($settings);

		/**
		 * gtag consent update
		 */
		if( gtagSettings === undefined ) {
			return;
		}

		if (true !== gtagSettings.gtag_enabled) {
			return;
		}

		function consentGrantedAdStorage($args) {
			gtag('consent', 'update', $args);
		}
		
		let gtag_attr_obj = {
			'ad_user_data': gtagSettings.ad_user_data,
			'ad_personalization': gtagSettings.ad_personalization,
			'ad_storage': gtagSettings.ad_storage,
			'analytics_storage': gtagSettings.analytics_storage,
		};

		$('.cc-btn.cc-dismiss').on('click', function() {
			consentGrantedAdStorage(gtag_attr_obj);
		});

	};


	jQuery(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/bdt-cookie-consent.default', widgetCookieConsent );
	});

}( jQuery, window.elementorFrontend ) );

/**
 * End cookie consent widget script
 */

