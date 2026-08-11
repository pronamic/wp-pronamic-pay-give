import { createElement } from '@wordpress/element';

( () => {
	let settings = {};

	function Fields() {
		return createElement(
			'div',
			{ className: 'pronamic-pay-give-gateway' },
			createElement(
				'p',
				{
					className: 'pronamic-pay-give-gateway__message',
					style: { marginBottom: 0 },
				},
				settings.message ||
					'You will be redirected to complete your payment.'
			)
		);
	}

	window.PronamicPayGiveGateway = window.PronamicPayGiveGateway || {};

	window.PronamicPayGiveGateway.register = ( gatewaySettings ) => {
		if ( ! gatewaySettings || ! gatewaySettings.id ) {
			return;
		}

		window.givewp.gateways.register( {
			id: gatewaySettings.id,
			initialize() {
				settings = this.settings || gatewaySettings;
			},
			Fields() {
				return createElement( Fields );
			},
		} );
	};
} )();
