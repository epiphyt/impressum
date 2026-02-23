<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\blocks;

/**
 * Imprint block functionality.
 * 
 * @since	3.0.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Block_Imprint {
	/**
	 * Initialize the class.
	 */
	public function init(): void {
		\add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
		\add_filter( 'register_block_type_args', [ $this, 'update_block_type_arguments' ] );
	}
	
	/**
	 * Enqueue block assets.
	 */
	public function enqueue_assets(): void {
		\wp_localize_script( 'impressum-imprint-editor-script', 'impressumImprintBlock', [
			'fields' => \epiphyt\Impressum\get_container()->get( 'settings-registry' )->get_settings(),
			'values' => \epiphyt\Impressum\get_container()->get( 'plugin' )->get_block_fields( 'impressum_imprint_options' ),
		] );
	}
	
	/**
	 * Update block type arguments.
	 * Add custom render callback function.
	 * 
	 * @param	array	$arguments Current arguments
	 * @return	array Updated arguments
	 */
	public function update_block_type_arguments( array $arguments ): array {
		if ( $arguments['name'] !== 'impressum/imprint' ) {
			return $arguments;
		}
		
		$arguments['render_callback'] = [ \epiphyt\Impressum\get_container()->get( 'frontend' ), 'render_block' ];
		
		return $arguments;
	}
}
