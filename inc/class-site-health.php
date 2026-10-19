<?php
declare(strict_types = 1);

namespace epiphyt\Impressum;

/**
 * Site Health integration.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Site_Health {
	/**
	 * @var		?\epiphyt\Impressum\Admin Admin functions
	 */
	private ?Admin $admin = null;
	
	/**
	 * Site Health constructor.
	 * 
	 * @param	\epiphyt\Impressum\Admin	$admin Admin functions
	 */
	public function __construct( Admin $admin ) {
		$this->admin = $admin;
	}
	
	/**
	 * Initialize functionality.
	 */
	public function init(): void {
		\add_filter( 'site_status_tests', [ $this, 'register_tests' ] );
	}
	
	/**
	 * Register the Site Health tests.
	 * 
	 * @param	array{direct?: array<string, mixed[]>, async?: array<string, mixed[]>}	$tests Registered tests
	 * @return	array{direct?: array<string, mixed[]>, async?: array<string, mixed[]>} Updated tests
	 */
	public function register_tests( array $tests ): array {
		$tests['direct']['impressum_imprint_required_fields'] = [
			'label' => \__( 'Required imprint fields', 'impressum' ),
			'test' => [ $this, 'test_imprint_required_fields' ],
		];
		
		return $tests;
	}
	
	/**
	 * Test whether all required fields of the imprint are filled.
	 * 
	 * @return	array{actions: string, badge: array{color: string, label: string}, description: string, label: string, status: string, test: string} Test result
	 */
	public function test_imprint_required_fields(): array {
		$invalid_fields = $this->admin->get_invalid_fields();
		$result = [
			'actions' => '',
			'badge' => [
				'color' => 'blue',
				'label' => \__( 'Imprint', 'impressum' ),
			],
			'description' => '<p>' . \esc_html__( 'Your imprint has to contain certain information, depending on your legal entity and country.', 'impressum' ) . '</p>',
			'label' => \__( 'Your imprint contains all required information', 'impressum' ),
			'status' => 'good',
			'test' => 'impressum_imprint_required_fields',
		];
		
		if ( empty( $invalid_fields ) ) {
			return $result;
		}
		
		$result['actions'] = \sprintf(
			'<p><a href="%1$s">%2$s</a></p>',
			\esc_url( \add_query_arg(
				[
					'imprint_tab' => 'imprint',
					'page' => 'impressum',
				],
				\admin_url( 'options-general.php' )
			) ),
			\esc_html__( 'Configure your imprint', 'impressum' )
		);
		$result['badge']['color'] = 'red';
		$result['description'] .= '<p>' . \esc_html__( 'The following required fields are empty or invalid:', 'impressum' ) . '</p>';
		$result['description'] .= '<ul><li>' . \implode( '</li><li>', \array_map( 'esc_html', $invalid_fields ) ) . '</li></ul>';
		$result['label'] = \__( 'Your imprint is missing required information', 'impressum' );
		$result['status'] = 'critical';
		
		return $result;
	}
}
