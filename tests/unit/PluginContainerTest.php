<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Plugin_Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WP_Error;

#[CoversClass(Plugin_Container::class)]
final class PluginContainerTest extends TestCase
{
    public function testHasReturnsFalseForUnknownService(): void
    {
        $container = new Plugin_Container();
        $this->assertFalse($container->has('unknown'));
    }

    public function testHasReturnsTrueAfterSet(): void
    {
        $container = new Plugin_Container();
        $container->set('service', static fn () => new \stdClass());
        $this->assertTrue($container->has('service'));
    }

    public function testGetReturnsWpErrorForUnknownService(): void
    {
        $container = new Plugin_Container();
        $result = $container->get('unknown');
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertSame('service_not_found', $result->get_error_code());
        $this->assertSame('Service not found: unknown', $result->get_error_message());
    }

    public function testGetReturnsServiceInstance(): void
    {
        $container = new Plugin_Container();
        $instance = new \stdClass();
        $container->set('service', static fn () => $instance);
        $this->assertSame($instance, $container->get('service'));
    }

    public function testGetPassesContainerToFactory(): void
    {
        $container = new Plugin_Container();
        $received = null;
        $container->set('service', static function ($passed) use (&$received) {
            $received = $passed;
            return new \stdClass();
        });
        $container->get('service');
        $this->assertSame($container, $received);
    }

    public function testGetReturnsSameInstanceOnRepeatedCalls(): void
    {
        $container = new Plugin_Container();
        $calls = 0;
        $container->set('service', static function () use (&$calls) {
            $calls++;
            return new \stdClass();
        });
        $first = $container->get('service');
        $second = $container->get('service');
        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
    }

    public function testSetOverwritesResolvedInstance(): void
    {
        $container = new Plugin_Container();
        $first = new \stdClass();
        $second = new \stdClass();
        $container->set('service', static fn () => $first);
        $this->assertSame($first, $container->get('service'));
        $container->set('service', static fn () => $second);
        $this->assertSame($second, $container->get('service'));
    }
}
