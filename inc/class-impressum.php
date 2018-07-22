<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * The main Impressum class.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum {
	/**
	 * @var		array Countries with their country codes in 3-digit ISO form
	 */
	protected static $countries = [];
	
	/**
	 * @var		array All legal entities we support
	 */
	protected static $legal_entities = [];
	
	/**
	 * @var		string The full path to the main plugin file
	 */
	public $plugin_file = '';
	
	/**
	 * Impressum constructor.
	 * 
	 * @param	string		$plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		// return on Ajax or autosave
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		
		// assign variables
		$this->plugin_file = $plugin_file;
		
		add_action( 'init', [ $this, 'load_textdomain' ] );
	}
	
	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'impressum', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
		
		// set the value of countries
		self::$countries = [
			'dza' => __( 'Algeria', 'impressum' ),
			'arg' => __( 'Argentinia', 'impressum' ),
			'aus' => __( 'Australia', 'impressum' ),
			'aut' => __( 'Austria', 'impressum' ),
			'bel' => __( 'Belgium', 'impressum' ),
			'bra' => __( 'Brasil', 'impressum' ),
			'bgr' => __( 'Bulgaria', 'impressum' ),
			'can' => __( 'Canada', 'impressum' ),
			'chl' => __( 'Chile', 'impressum' ),
			'chn' => __( 'China', 'impressum' ),
			'col' => __( 'Columbia', 'impressum' ),
			'hrv' => __( 'Croatia', 'impressum' ),
			'cze' => __( 'Czech Republic', 'impressum' ),
			'dnk' => __( 'Denmark', 'impressum' ),
			'est' => __( 'Estonia', 'impressum' ),
			'fin' => __( 'Finland', 'impressum' ),
			'fra' => __( 'France', 'impressum' ),
			'deu' => __( 'Germany', 'impressum' ),
			'grc' => __( 'Greece', 'impressum' ),
			'hkg' => __( 'Hong Kong', 'impressum' ),
			'hun' => __( 'Hungary', 'impressum' ),
			'idn' => __( 'Indonesia', 'impressum' ),
			'irl' => __( 'Ireland', 'impressum' ),
			'isr' => __( 'Israel', 'impressum' ),
			'ita' => __( 'Italy', 'impressum' ),
			'jpn' => __( 'Japan', 'impressum' ),
			'ltu' => __( 'Lithuania', 'impressum' ),
			'nld' => __( 'Netherlands', 'impressum' ),
			'nzl' => __( 'New Zealand', 'impressum' ),
			'nor' => __( 'Norway', 'impressum' ),
			'pol' => __( 'Poland', 'impressum' ),
			'prt' => __( 'Portugal', 'impressum' ),
			'rou' => __( 'Romania', 'impressum' ),
			'rus' => __( 'Russia', 'impressum' ),
			'srb' => __( 'Serbia', 'impressum' ),
			'svn' => __( 'Slowenia', 'impressum' ),
			'zaf' => __( 'South Africa', 'impressum' ),
			'kor' => __( 'South Korea', 'impressum' ),
			'esp' => __( 'Spain', 'impressum' ),
			'swe' => __( 'Sweden', 'impressum' ),
			'che' => __( 'Switzerland', 'impressum' ),
			'twn' => __( 'Taiwan', 'impressum' ),
			'tha' => __( 'Thailand', 'impressum' ),
			'tur' => __( 'Turky', 'impressum' ),
			'gbr' => __( 'United Kingdom', 'impressum' ),
			'usa' => __( 'United States', 'impressum' ),
			'ven' => __( 'Venezuela', 'impressum' ),
			'vnm' => __( 'Vietnam', 'impressum' ),
			'other' => __( 'other', 'impressum' ),
		];
		
		self::$legal_entities = [
			'ag' => __( 'AG', 'impressum' ),
			'ev' => __( 'e.V.', 'impressum' ),
			'ek' => __( 'e.K.', 'impressum' ),
			'einzelkaufmann' => __( 'Einzelkaufmann', 'impressum' ),
			'freelancer' => __( 'Freelancer', 'impressum' ),
			'ggmbh' => __( 'gGmbH', 'impressum' ),
			'gmbh' => __( 'GmbH', 'impressum' ),
			'gbr' => __( 'GbR', 'impressum' ),
			'gmbh_co_kg' => __( 'GmbH & Co. KG', 'impressum' ),
			'kg' => __( 'KG', 'impressum' ),
			'kgag' => __( 'KGaA', 'impressum' ),
			'ohg' => __( 'OHG', 'impressum' ),
			'individual' => __( 'Individual', 'impressum' ),
			'self' => __( 'Self-employed', 'impressum' ),
			'ug' => __( 'UG (haftungsbeschränkt)', 'impressum' ),
			'ug_co_kg' => __( 'UG (haftungsbeschränkt) & Co. KG', 'impressum' ),
		];
		
		// make sure the array is always sorted depending on localization
		asort( self::$countries );
		natcasesort( self::$legal_entities );
	}
	
	/**
	 * Get an option or a site option with the same name.
	 * The site option is received if there is no option
	 * with the same name.
	 * 
	 * @param	string		$option The option you want to get
	 * @return	mixed|void
	 */
	protected static function impressum_get_option( $option ) {
		if ( ! is_string( $option ) ) return;
		
		if ( ! is_network_admin() ) {
			// try receive option
			$options = get_option( $option );
			
			if ( ! $options ) {
				$options = get_site_option( $option );
			}
		}
		else {
			$options = get_site_option( $option );
		}
		
		return $options;
	}
}