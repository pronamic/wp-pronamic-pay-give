/**
 * Pronamic Pay gateway registration for the GiveWP visual donation form builder.
 *
 * Each Pronamic Pay gateway is an offsite gateway: the donor is redirected to
 * the payment provider to complete the donation, so the only field rendered is
 * a short help text.
 */
import { createElement } from '@wordpress/element';

( () => {
	const data = window.pronamicPayGive || { ids: [] };

	const ids = [ ...new Set( data.ids ) ];

	ids.forEach( ( id ) => {
		let settings = {};

		window.givewp.gateways.register( {
			id,
			initialize() {
				settings = this.settings || {};
			},
			Fields() {
				return createElement(
					'div',
					{ className: 'pronamic-pay-give-help-text' },
					createElement(
						'p',
						{ style: { marginBottom: 0 } },
						settings.message || ''
					)
				);
			},
		} );
	} );
} )();
