import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Static text of the imprint tab.
 */
export default function ImprintTabContent() {
	return (
		<>
			<h2>{ __( 'Disclaimer', 'impressum' ) }</h2>
			<p>
				{ __(
					'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. This plugin helps you to fill all necessary fields.',
					'impressum'
				) }
			</p>

			<h2>{ __( 'Usage', 'impressum' ) }</h2>
			<p>
				{ __(
					'There are two methods available on how to output the imprint:',
					'impressum'
				) }
			</p>
			<ul className="impressum__regular-list">
				<li>
					{ __(
						'Add the "Imprint" block in your block editor wherever you want to output your imprint. It works everywhere the block editor is supported.',
						'impressum'
					) }
				</li>
				<li>
					{ createInterpolateElement(
						sprintf(
							/* translators: %s: shortcode name */
							__(
								'Add the %s in your editor wherever you want to output your imprint. It works everywhere shortcodes are supported.',
								'impressum'
							),
							'<code>[impressum]</code>'
						),
						{ code: <code /> }
					) }
				</li>
			</ul>
		</>
	);
}
