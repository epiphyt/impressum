import { Button, Snackbar, TabPanel } from '@wordpress/components';
import {
	useCallback,
	useEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

import { getFieldComponent } from './components/fields';
import LegacyTab from './components/legacy-tab';
import SettingsTab from './components/tab-panel';
import { addSubmittedOptions, getLegacyValue } from './lib/form';
import { getErrorMessage, getFieldErrors } from './lib/error';
import { getEffectiveValues, getErrors } from './lib/validate';
import { getSubmittableValues, useSettingsState } from './store';
import { isVisible } from './lib/conditions';
import { saveSettings } from './lib/api';

/**
 * Get the first control of a field.
 *
 * @param {string} fieldKey Group and name of the field, separated by a dot
 * @return {?HTMLElement} First control, or null if the field is not rendered
 */
function getFieldControl( fieldKey ) {
	const row = Array.from(
		document.querySelectorAll( '[data-impressum-field]' )
	).find( ( element ) => element.dataset.impressumField === fieldKey );

	return (
		row?.querySelector(
			'td input:not([type="hidden"]), td select, td textarea'
		) ?? null
	);
}

/**
 * The settings app.
 *
 * @param {Object} props           Component properties
 * @param {Object} props.bootstrap Settings payload
 */
export default function App( { bootstrap } ) {
	const [ state, dispatch ] = useSettingsState( bootstrap );
	const {
		fieldData,
		isDirty,
		isSaving,
		isSubmitted,
		payload,
		serverErrors,
		touched,
		values,
	} = state;
	const [ notice, setNotice ] = useState( null );
	const tabs = useMemo( () => payload.tabs ?? [], [ payload.tabs ] );
	const [ currentTab, setCurrentTab ] = useState(
		() =>
			new URL( window.location.href ).searchParams.get( 'imprint_tab' ) ||
			payload.defaultTab ||
			tabs[ 0 ]?.slug
	);
	// the tab panel only selects a tab on its own, so it is mounted again to
	// select one programmatically
	const [ tabPanelKey, setTabPanelKey ] = useState( 0 );
	const [ focusTarget, setFocusTarget ] = useState( null );
	const submittedOptionsRef = useRef( null );

	const activeTab = useMemo(
		() => tabs.find( ( tab ) => tab.slug === currentTab ) ?? tabs[ 0 ],
		[ currentTab, tabs ]
	);
	const tabItems = useMemo(
		() =>
			tabs.map( ( tab ) => ( {
				className: `impressum-settings__tab impressum-settings__tab--${ tab.slug }`,
				name: tab.slug,
				title: tab.title,
			} ) ),
		[ tabs ]
	);

	// the classic form has to return to the tab it has been submitted from,
	// which is not necessarily the one the page has been loaded with
	const referer = useMemo( () => {
		if ( ! payload.referer || ! currentTab ) {
			return payload.referer;
		}

		const url = new URL( payload.referer, window.location.href );

		url.searchParams.set( 'imprint_tab', currentTab );

		return url.pathname + url.search;
	}, [ currentTab, payload.referer ] );

	const onSelectTab = useCallback( ( slug ) => {
		setCurrentTab( slug );

		const url = new URL( window.location.href );

		url.searchParams.set( 'imprint_tab', slug );
		window.history.replaceState( {}, '', url );
	}, [] );

	// fields of every group a tab edits, grouped by that group
	const fieldsByGroup = useMemo( () => {
		const grouped = {};

		for ( const field of Object.values( payload.fields ?? {} ) ) {
			grouped[ field.group ] ??= [];
			grouped[ field.group ].push( field );
		}

		return grouped;
	}, [ payload.fields ] );

	const effectiveValues = useMemo( () => {
		const effective = {};

		for ( const group of Object.keys( fieldsByGroup ) ) {
			effective[ group ] = getEffectiveValues(
				values[ group ],
				payload.defaults?.[ group ]
			);
		}

		return effective;
	}, [ fieldsByGroup, payload.defaults, values ] );

	const visibleByGroup = useMemo( () => {
		const visible = {};

		for ( const [ group, fields ] of Object.entries( fieldsByGroup ) ) {
			visible[ group ] = fields.filter( ( field ) =>
				isVisible( field, effectiveValues[ group ] )
			);
		}

		return visible;
	}, [ effectiveValues, fieldsByGroup ] );

	const errors = useMemo( () => {
		const next = {};

		for ( const [ group, fields ] of Object.entries( visibleByGroup ) ) {
			const groupErrors = getErrors( fields, effectiveValues[ group ] );

			for ( const [ name, message ] of Object.entries( groupErrors ) ) {
				next[ `${ group }.${ name }` ] = message;
			}
		}

		return next;
	}, [ effectiveValues, visibleByGroup ] );

	useEffect( () => {
		if ( ! focusTarget ) {
			return;
		}

		const control = getFieldControl( focusTarget );

		control?.focus();
		control?.scrollIntoView( { block: 'center' } );
		setFocusTarget( null );
	}, [ focusTarget ] );

	useEffect( () => {
		if ( ! isDirty ) {
			return undefined;
		}

		const warn = ( event ) => {
			event.preventDefault();
			event.returnValue = '';
		};

		window.addEventListener( 'beforeunload', warn );

		return () => window.removeEventListener( 'beforeunload', warn );
	}, [ isDirty ] );

	const onSave = useCallback(
		( event ) => {
			const form = event.currentTarget;

			// a tab rendered as markup keeps submitting through the classic form
			if ( activeTab?.type === 'legacy' ) {
				addSubmittedOptions( form, submittedOptionsRef.current );

				return;
			}

			event.preventDefault();
			dispatch( { type: 'SAVE_START' } );
			setNotice( null );

			const submittable = {};

			for ( const [ group, fields ] of Object.entries( fieldsByGroup ) ) {
				const groupValues = { ...( values[ group ] ?? {} ) };

				// markup rendered on the server is not controlled by the app
				for ( const field of fields ) {
					if ( getFieldComponent( field ) ) {
						continue;
					}

					const legacyValue = getLegacyValue( form, field.inputName );

					if ( legacyValue !== undefined ) {
						groupValues[ field.name ] = legacyValue;
					}
				}

				submittable[ group ] = getSubmittableValues(
					fields,
					groupValues
				);
			}

			saveSettings( {
				fieldData,
				isNetwork: payload.isNetwork,
				values: submittable,
			} )
				.then( ( next ) => {
					dispatch( { payload: next, type: 'SAVE_SUCCESS' } );
					setNotice( {
						status: 'success',
						text: __( 'Settings saved.', 'impressum' ),
					} );
				} )
				.catch( ( error ) => {
					const invalid = getFieldErrors( error );

					if ( ! invalid.length ) {
						dispatch( { type: 'SAVE_ERROR' } );
						setNotice( {
							status: 'error',
							text: getErrorMessage( error ),
						} );

						return;
					}

					const byField = {};

					for ( const entry of invalid ) {
						byField[ `${ entry.group }.${ entry.name }` ] =
							entry.message;
					}

					dispatch( {
						serverErrors: byField,
						type: 'SAVE_ERROR',
					} );
					setNotice( {
						status: 'error',
						text: sprintf(
							/* translators: %d: number of invalid settings */
							_n(
								'%d setting is invalid.',
								'%d settings are invalid.',
								invalid.length,
								'impressum'
							),
							invalid.length
						),
					} );

					// the first one is where the server stopped
					const [ first ] = invalid;
					const tab = tabs.find(
						( one ) => one.group === first.group
					);

					if ( tab && tab.slug !== activeTab?.slug ) {
						onSelectTab( tab.slug );
						setTabPanelKey( ( key ) => key + 1 );
					}

					setFocusTarget( `${ first.group }.${ first.name }` );
				} );
		},
		[
			activeTab,
			dispatch,
			fieldData,
			fieldsByGroup,
			onSelectTab,
			payload.isNetwork,
			tabs,
			values,
		]
	);

	const onChange = useCallback(
		( field, value ) =>
			dispatch( {
				group: field.group,
				name: field.name,
				type: 'SET_VALUE',
				value,
			} ),
		[ dispatch ]
	);
	const onCustomTitleChange = useCallback(
		( field, value ) =>
			dispatch( { name: field.name, type: 'SET_CUSTOM_TITLE', value } ),
		[ dispatch ]
	);
	const onTouch = useCallback(
		( field ) =>
			dispatch( {
				key: `${ field.group }.${ field.name }`,
				type: 'TOUCH',
			} ),
		[ dispatch ]
	);
	const onLegacyInput = useCallback(
		() => dispatch( { type: 'MARK_DIRTY' } ),
		[ dispatch ]
	);

	return (
		<>
			{ payload.legacy?.form?.before && (
				<div
					dangerouslySetInnerHTML={ {
						__html: payload.legacy.form.before,
					} }
				/>
			) }

			<form
				action={ payload.formAction }
				method="post"
				onSubmit={ onSave }
			>
				<input
					name="option_page"
					type="hidden"
					value={ payload.optionPage }
				/>
				<input name="action" type="hidden" value="update" />
				<input name="_wpnonce" type="hidden" value={ payload.nonce } />
				<input
					name="_wp_http_referer"
					type="hidden"
					value={ referer }
				/>
				{ /* filled while submitting a tab rendered as markup */ }
				<div hidden ref={ submittedOptionsRef } />

				<TabPanel
					className="impressum-settings__tabs"
					initialTabName={ currentTab }
					key={ tabPanelKey }
					onSelect={ onSelectTab }
					tabs={ tabItems }
				>
					{ ( selected ) => {
						const tab = tabs.find(
							( one ) => one.slug === selected.name
						);

						if ( ! tab ) {
							return null;
						}

						return tab.type === 'legacy' ? (
							<LegacyTab tab={ tab } />
						) : (
							<SettingsTab
								effectiveValues={
									effectiveValues[ tab.group ] ?? {}
								}
								errors={ errors }
								fieldData={ fieldData }
								fields={ visibleByGroup[ tab.group ] ?? [] }
								isSubmitted={ isSubmitted }
								onChange={ onChange }
								onCustomTitleChange={ onCustomTitleChange }
								onLegacyInput={ onLegacyInput }
								onTouch={ onTouch }
								payload={ payload }
								serverErrors={ serverErrors }
								tab={ tab }
								touched={ touched }
								values={ values[ tab.group ] ?? {} }
							/>
						);
					} }
				</TabPanel>

				<div className="impressum-settings__section impressum-content-wrapper">
					<p className="submit">
						<Button
							__next40pxDefaultSize
							disabled={ isSaving }
							isBusy={ isSaving }
							type="submit"
							variant="primary"
						>
							{ payload.i18n?.saveSettings ??
								__( 'Save Settings', 'impressum' ) }
						</Button>
					</p>
				</div>
			</form>

			{ payload.legacy?.form?.after && (
				<div
					dangerouslySetInnerHTML={ {
						__html: payload.legacy.form.after,
					} }
				/>
			) }

			{ notice && (
				<div className="impressum-settings__snackbars">
					<Snackbar
						// an error stays until it is dismissed, a confirmation
						// disappears on its own
						explicitDismiss={ notice.status === 'error' }
						onRemove={ () => setNotice( null ) }
						politeness={
							notice.status === 'error' ? 'assertive' : 'polite'
						}
					>
						{ notice.text }
					</Snackbar>
				</div>
			) }
		</>
	);
}
