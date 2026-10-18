import { applyFilters } from '@wordpress/hooks';

import ImprintTabContent from './imprint';

/**
 * The components rendering the static text of a tab, keyed by tab slug.
 */
const TAB_CONTENTS = {
	imprint: ImprintTabContent,
};

/**
 * The components rendering the introduction of a tab, keyed by tab slug.
 */
const TAB_INTROS = {};

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
	 * The text is displayed below the fields. Every tab can display text this
	 * way, including tabs registered through another plugin and tabs rendered
	 * as markup.
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
 * Get the component rendering the introduction of a tab.
 *
 * @param {string} slug Tab slug
 * @return {?Function} Introduction component, or null if the tab has none
 */
export function getTabIntroComponent( slug ) {
	/**
	 * Filter the components rendering the introduction of the tabs.
	 *
	 * The introduction is displayed above the fields. Every tab can display an
	 * introduction this way, including tabs registered through another plugin
	 * and tabs rendered as markup.
	 *
	 * @param {Object} components Introduction components, keyed by tab slug
	 */
	const components = applyFilters(
		'impressum.settings.tabIntros',
		TAB_INTROS
	);

	return components[ slug ] ?? null;
}

/**
 * Render a text component of a tab in its section.
 *
 * @param {Object}    props           Component properties
 * @param {?Function} props.component Component to render
 * @param {Object}    props.tab       Tab to render the text of
 */
function TabText( { component: Component, tab } ) {
	if ( ! Component ) {
		return null;
	}

	return (
		<div className="impressum-settings__section impressum-content-wrapper">
			<Component tab={ tab } />
		</div>
	);
}

/**
 * Render the introduction of a tab.
 *
 * @param {Object} props     Component properties
 * @param {Object} props.tab Tab to render the introduction of
 */
export function TabIntro( { tab } ) {
	return (
		<TabText component={ getTabIntroComponent( tab.slug ) } tab={ tab } />
	);
}

/**
 * Render the static text of a tab.
 *
 * @param {Object} props     Component properties
 * @param {Object} props.tab Tab to render the text of
 */
export default function TabContent( { tab } ) {
	return (
		<TabText component={ getTabContentComponent( tab.slug ) } tab={ tab } />
	);
}
