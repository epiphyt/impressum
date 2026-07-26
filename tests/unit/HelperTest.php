<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Helper;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Helper::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class HelperTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    public function testGetOptionReturnsOptionValue(): void
    {
        expect('get_option')
            ->once()
            ->with('impressum_imprint_options')
            ->andReturn(['name' => 'value']);
        $this->assertSame(['name' => 'value'], Helper::get_option('impressum_imprint_options'));
    }

    public function testGetOptionIgnoresSecondArgument(): void
    {
        expect('get_option')
            ->once()
            ->with('impressum_field_data')
            ->andReturn(false);
        // The second parameter is unused in the free version and must not be
        // passed on to get_option().
        $this->assertFalse(Helper::get_option('impressum_field_data', true));
    }

    public function testGetOptionMemoizesValue(): void
    {
        // get_option() must only hit the database once for repeated reads.
        expect('get_option')
            ->once()
            ->with('impressum_field_data')
            ->andReturn(['field' => 'data']);
        $this->assertSame(['field' => 'data'], Helper::get_option('impressum_field_data'));
        $this->assertSame(['field' => 'data'], Helper::get_option('impressum_field_data'));
    }

    public function testClearOptionCacheForcesReload(): void
    {
        // After invalidation, the next read hits the database again.
        expect('get_option')
            ->twice()
            ->with('impressum_field_data')
            ->andReturn(['field' => 'old'], ['field' => 'new']);
        $this->assertSame(['field' => 'old'], Helper::get_option('impressum_field_data'));
        Helper::clear_option_cache('impressum_field_data');
        $this->assertSame(['field' => 'new'], Helper::get_option('impressum_field_data'));
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
