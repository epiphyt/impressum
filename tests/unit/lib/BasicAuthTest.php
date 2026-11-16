<?php

declare(strict_types=1);

namespace Tests\Unit\lib;

use epiphyt\Basic_Auth\Basic_Auth;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Basic_Auth::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class BasicAuthTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubTranslationFunctions();
        stubs([
            'add_filter' => true,
            'get_http_origin' => '',
            'home_url' => 'https://example.com',
            'is_allowed_http_origin' => '',
            'remove_filter' => true,
            'sanitize_key' => static fn (string $key): string => \strtolower($key),
            'site_url' => 'https://example.com',
            'wp_is_serving_rest_request' => true,
            'wp_parse_url' => static fn (string $url): array|false => \parse_url($url),
            'wp_unslash' => static fn ($value) => $value,
        ]);
        when('wp_get_current_user')->justReturn(null);
        when('is_wp_error')->alias(static fn ($value): bool => $value instanceof \WP_Error);

        if (!\class_exists('WP_User')) {
            eval('class WP_User { public int $ID = 0; public function __construct(int $id = 0) { $this->ID = $id; } }'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
        }

        unset(
            $_SERVER['HTTP_AUTHORIZATION'],
            $_SERVER['HTTP_SEC_FETCH_SITE'],
            $_SERVER['PHP_AUTH_PW'],
            $_SERVER['PHP_AUTH_USER'],
            $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        );
    }

    private function authorize(string $username, string $password): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . \base64_encode($username . ':' . $password);
    }

    public function testRequestsOtherThanRestAreNotAuthenticated(): void
    {
        when('wp_is_serving_rest_request')->justReturn(false);
        expect('wp_authenticate')->never();
        $this->authorize('admin', 'secret');

        $this->assertSame(0, (new Basic_Auth())->json_basic_auth_handler(0));
    }

    public function testAuthenticatedUserIsKept(): void
    {
        expect('wp_authenticate')->never();
        $this->authorize('admin', 'secret');

        $this->assertSame(5, (new Basic_Auth())->json_basic_auth_handler(5));
    }

    public function testCredentialsArePassedOnUnaltered(): void
    {
        // characters sanitizing would strip, and a colon inside the password
        $password = "p<a>ss %41b:c\td  e";
        expect('wp_authenticate')
            ->once()
            ->with('admin', $password)
            ->andReturn(new \WP_User(7));
        $this->authorize('admin', $password);

        $basic_auth = new Basic_Auth();

        $this->assertSame(7, $basic_auth->json_basic_auth_handler(0));
        $this->assertNull($basic_auth->json_basic_auth_error(null));
    }

    public function testCredentialsOfThePhpEnvironmentAreUsed(): void
    {
        $_SERVER['PHP_AUTH_USER'] = 'admin';
        $_SERVER['PHP_AUTH_PW'] = 'se<cr>et';
        expect('wp_authenticate')
            ->once()
            ->with('admin', 'se<cr>et')
            ->andReturn(new \WP_User(7));

        $this->assertSame(7, (new Basic_Auth())->json_basic_auth_handler(0));
    }

    public function testOtherAuthorizationSchemesAreIgnored(): void
    {
        expect('wp_authenticate')->never();
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . \base64_encode('admin:secret');

        $basic_auth = new Basic_Auth();

        $this->assertSame(0, $basic_auth->json_basic_auth_handler(0));
        $this->assertNull($basic_auth->json_basic_auth_error(null));
    }

    public function testApplicationPasswordIsTriedAfterThePassword(): void
    {
        expect('wp_authenticate')->once()->andReturn(new \WP_Error('incorrect_password', 'Wrong.'));
        expect('wp_authenticate_application_password')
            ->once()
            ->with(null, 'admin', 'abcd efgh')
            ->andReturn(new \WP_User(9));
        $this->authorize('admin', 'abcd efgh');

        $this->assertSame(9, (new Basic_Auth())->json_basic_auth_handler(0));
    }

    public function testInvalidCredentialsAreReported(): void
    {
        expect('wp_authenticate')->once()->andReturn(new \WP_Error('incorrect_password', 'Wrong.'));
        expect('wp_authenticate_application_password')->once()->andReturn(null);
        $this->authorize('admin', 'wrong');

        $basic_auth = new Basic_Auth();

        $this->assertSame(0, $basic_auth->json_basic_auth_handler(0));

        $error = $basic_auth->json_basic_auth_error(null);

        $this->assertInstanceOf(\WP_Error::class, $error);
        $this->assertSame('impressum_rest_basic_auth_invalid', $error->get_error_code());
        $this->assertSame(['status' => 401], $error->get_error_data());
    }

    public function testCrossSiteRequestsAreRejected(): void
    {
        when('get_http_origin')->justReturn('https://attacker.example');
        expect('wp_authenticate')->never();
        $this->authorize('admin', 'secret');

        $basic_auth = new Basic_Auth();

        $this->assertSame(0, $basic_auth->json_basic_auth_handler(0));

        $error = $basic_auth->json_basic_auth_error(null);

        $this->assertInstanceOf(\WP_Error::class, $error);
        $this->assertSame('impressum_rest_basic_auth_cross_site', $error->get_error_code());
        $this->assertSame(['status' => 403], $error->get_error_data());
    }

    public function testCrossSiteRequestsWithoutOriginAreRejected(): void
    {
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'cross-site';
        expect('wp_authenticate')->never();
        $this->authorize('admin', 'secret');

        $this->assertSame(0, (new Basic_Auth())->json_basic_auth_handler(0));
    }

    public function testSameOriginRequestsAreAuthenticated(): void
    {
        when('home_url')->justReturn('http://localhost:8080');
        when('site_url')->justReturn('http://localhost:8080/wp');
        when('get_http_origin')->justReturn('http://localhost:8080');
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'same-origin';
        expect('wp_authenticate')->once()->andReturn(new \WP_User(7));
        $this->authorize('admin', 'secret');

        $this->assertSame(7, (new Basic_Auth())->json_basic_auth_handler(0));
    }

    public function testAllowedOriginsAreAuthenticated(): void
    {
        when('get_http_origin')->justReturn('https://app.example');
        when('is_allowed_http_origin')->justReturn('https://app.example');
        expect('wp_authenticate')->once()->andReturn(new \WP_User(7));
        $this->authorize('admin', 'secret');

        $this->assertSame(7, (new Basic_Auth())->json_basic_auth_handler(0));
    }

    public function testOtherErrorsArePassedThrough(): void
    {
        $error = new \WP_Error('other', 'Other.');

        $this->assertSame($error, (new Basic_Auth())->json_basic_auth_error($error));
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
