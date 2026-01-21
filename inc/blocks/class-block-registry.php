<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\blocks;

/**
 * Block registry functionality.
 * 
 * @since	3.0.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Block_Registry {
	/**
	 * @var		class-string[] List of block classes
	 */
	private array $blocks = [
		Block_Imprint::class,
	];
	
	/**
	 * Initialize the class.
	 */
	public function init(): void {
		\add_action( 'init', [ $this, 'register' ] );
		
		foreach ( $this->blocks as $block ) {
			( new $block() )->init();
		}
	}
	
	/**
	 * Register blocks.
	 */
	public function register(): void {
		\wp_register_block_types_from_metadata_collection(
			\EPI_IMPRESSUM_BASE . 'build',
			\EPI_IMPRESSUM_BASE . 'build/blocks-manifest.php'
		);
	}
}
