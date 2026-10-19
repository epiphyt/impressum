import { useEffect, useRef } from '@wordpress/element';
import { doAction } from '@wordpress/hooks';

import TabContent, { TabIntro } from './tab-content';

/**
 * A tab whose content is rendered on the server.
 *
 * The panel is mounted whenever its tab is selected, so scripts of extensions
 * that enhance the markup are notified on every mount rather than only once
 * while the page loads.
 *
 * @param {Object} props     Component properties
 * @param {Object} props.tab Tab to render
 */
export default function LegacyTab( { tab } ) {
	const ref = useRef( null );

	useEffect( () => {
		if ( ! ref.current ) {
			return;
		}

		doAction( 'impressum.settings.legacyRendered', ref.current, tab.slug );
		document.dispatchEvent(
			new CustomEvent( 'impressum-legacy-rendered', {
				detail: { container: ref.current, slug: tab.slug },
			} )
		);
	}, [ tab.slug ] );

	return (
		<>
			<TabIntro tab={ tab } />

			<div
				className="impressum-settings__legacy-tab impressum-content-wrapper"
				dangerouslySetInnerHTML={ { __html: tab.html ?? '' } }
				ref={ ref }
			/>

			<TabContent tab={ tab } />
		</>
	);
}
