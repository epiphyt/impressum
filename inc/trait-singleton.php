<?php
namespace epiphyt\Impressum;

/**
 * Singleton functionality.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
trait Singleton {
	/**
	 * @var		static Current instance
	 */
	protected static $instance;
	
	/**
	 * Class constructor.
	 */
	final private function __construct() {
		$this->init();
	}
	
	/**
	 * Initialize functionality.
	 */
	final protected function init(): void {
		// traits have no functionality
	}
	
	/**
	 * Class wakeup functionality.
	 */
	final public function __wakeup(): void {
		// singletons can't be woken up
	}
	
	/**
	 * Class clone functionality.
	 */
	final public function __clone() {
		// singletons can't be cloned
	}
	
	/**
	 * Get a single instance.
	 * 
	 * @return	static Current instance
	 */
	final public static function get_instance(): static {
		return static::$instance ?? static::$instance = new static();
	}
}
