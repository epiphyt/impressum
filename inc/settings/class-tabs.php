<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

/**
 * Registration of the settings tabs.
 * 
 * A tab is a structure, not markup: it names the settings group it edits, and
 * the settings app renders its fields and static text with its own components.
 * Tabs registered as markup through the legacy filter are appended as they are,
 * so that existing extensions keep working.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Tabs {
	/**
	 * @var		?\epiphyt\Impressum\settings\Legacy_Renderer Legacy renderer
	 */
	private ?Legacy_Renderer $legacy_renderer = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	private ?Registry $registry = null;
	
	/**
	 * Tabs constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry		$registry Settings registry
	 * @param	\epiphyt\Impressum\settings\Legacy_Renderer	$legacy_renderer Legacy renderer
	 */
	public function __construct( Registry $registry, Legacy_Renderer $legacy_renderer ) {
		$this->legacy_renderer = $legacy_renderer;
		$this->registry = $registry;
	}
	
	/**
	 * Get the tabs of this plugin.
	 * 
	 * @return	mixed[] List of tabs
	 */
	private function get_default_tabs(): array {
		return [
			[
				'group' => 'impressum_imprint_options',
				'page' => 'impressum_imprint',
				'priority' => 10,
				'slug' => 'imprint',
				'title' => \__( 'Imprint', 'impressum' ),
			],
		];
	}
	
	/**
	 * Get the tabs registered as markup.
	 * 
	 * @param	array{current_tab?: string, form_action?: string}	$args Tab arguments
	 * @param	mixed[]	$known Tabs that are already registered
	 * @return	mixed[] List of tabs
	 */
	private function get_legacy_tabs( array $args, array $known ): array {
		$slugs = \array_column( $known, 'slug' );
		$tabs = [];
		$legacy_tabs = $this->legacy_renderer->get_tabs(
			(string) ( $args['form_action'] ?? '' ),
			(string) ( $args['current_tab'] ?? '' )
		);
		
		foreach ( $legacy_tabs as $tab ) {
			if ( empty( $tab['slug'] ) || \in_array( $tab['slug'], $slugs, true ) ) {
				continue;
			}
			
			$tabs[] = [
				'html' => (string) ( $tab['content'] ?? '' ),
				'priority' => 50,
				'sections' => [],
				'slug' => (string) $tab['slug'],
				'title' => (string) ( $tab['title'] ?? '' ),
				'type' => 'legacy',
			];
		}
		
		return $tabs;
	}
	
	/**
	 * Get the sections of a settings page.
	 * 
	 * The sections are derived from the registered settings rather than from
	 * the settings API, which is not available outside of an admin request.
	 * 
	 * @param	string	$group Settings group
	 * @param	string	$page Settings page
	 * @param	mixed[]	$titles Section titles, keyed by section ID
	 * @return	mixed[] List of sections
	 */
	private function get_sections( string $group, string $page, array $titles ): array {
		$sections = [];
		
		foreach ( $this->registry->get_settings( $group ) as $key => $setting ) {
			if ( $page !== '' && $setting->get_data( 'setting_page' ) !== $page ) {
				continue;
			}
			
			$id = (string) $setting->get_data( 'setting_section' );
			
			if ( ! isset( $sections[ $id ] ) ) {
				$sections[ $id ] = [
					'fields' => [],
					'id' => $id,
					'title' => (string) ( $titles[ $id ] ?? '' ),
				];
			}
			
			$sections[ $id ]['fields'][] = $key;
		}
		
		return \array_values( $sections );
	}
	
	/**
	 * Get all tabs.
	 * 
	 * @param	array{current_tab?: string, form_action?: string}	$args Tab arguments
	 * @return	mixed[] List of tabs, ordered by priority
	 */
	public function get_tabs( array $args = [] ): array {
		$tabs = $this->get_default_tabs();
		
		/**
		 * Filter the settings tabs.
		 * 
		 * Every tab needs a 'slug' and a 'title'. A tab editing settings also
		 * names its settings group as 'group' and the settings page its fields
		 * are registered to as 'page', and 'priority' decides the order. Static
		 * text is added in the settings app through the JavaScript filter
		 * 'impressum.settings.tabContents'.
		 * 
		 * @since	3.1.0
		 * 
		 * @param	array	$tabs List of tabs
		 */
		$tabs = (array) \apply_filters( 'impressum_settings_tabs', $tabs );
		
		foreach ( $tabs as $index => $tab ) {
			$tabs[ $index ] = $this->normalize_tab( (array) $tab );
		}
		
		$tabs = \array_merge( $tabs, $this->get_legacy_tabs( $args, $tabs ) );
		
		\usort(
			$tabs,
			static fn ( array $a, array $b ): int => $a['priority'] <=> $b['priority']
		);
		
		return $tabs;
	}
	
	/**
	 * Normalize a tab.
	 * 
	 * @param	mixed[]	$tab Tab to normalize
	 * @return	mixed[] Normalized tab
	 */
	private function normalize_tab( array $tab ): array {
		$group = (string) ( $tab['group'] ?? '' );
		$page = (string) ( $tab['page'] ?? '' );
		
		return [
			'group' => $group,
			'html' => (string) ( $tab['html'] ?? '' ),
			'priority' => (int) ( $tab['priority'] ?? 10 ),
			'sections' => $tab['sections'] ?? (
				$group !== '' ? $this->get_sections( $group, $page, (array) ( $tab['section_titles'] ?? [] ) ) : []
			),
			'slug' => (string) ( $tab['slug'] ?? '' ),
			'title' => (string) ( $tab['title'] ?? '' ),
			'type' => (string) ( $tab['type'] ?? 'native' ),
		];
	}
}
