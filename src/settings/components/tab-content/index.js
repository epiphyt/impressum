import { applyFilters } from '@wordpress/hooks';

import ImprintTabContent from './imprint';

/**
 * The components rendering the static text of a tab, keyed by tab slug.
 */
const TAB_CONTENTS = {
	imprint: ImprintTabContent,
};

/**
 * Get the component rendering the static text of a tab.
 *
 * @param {string} slug Tab slug
 * @return {?Function} Content component, or null if the tab has no text
 */
export function getTabContentComponent( slug ) {
	/**
	 * Filter the components rendering the static text of the tabs.
	 *
	 * Every tab can display text this way, including tabs registered through
	 * another plugin and tabs rendered as markup.
	 *
	 * @param {Object} components Content components, keyed by tab slug
	 */
	const components = applyFilters(
		'impressum.settings.tabContents',
		TAB_CONTENTS
	);

	return components[ slug ] ?? null;
}

/**
 * Render the static text of a tab.
 *
 * @param {Object} props     Component properties
 * @param {Object} props.tab Tab to render the text of
 */
export default function TabContent( { tab } ) {
	const Component = getTabContentComponent( tab.slug );

	if ( ! Component ) {
		return null;
	}

	return (
		<div className="impressum-settings__section impressum-content-wrapper">
			<Component tab={ tab } />
		</div>
	);
}
