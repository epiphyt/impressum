<?php

declare(strict_types=1);

namespace Tests\Unit\blocks;

use epiphyt\Impressum\blocks\Block_Registry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Block_Registry::class)]
final class BlockRegistryTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'add_shortcode' => '__return_null',
            'register_activation_hook' => '__return_null',
            'register_deactivation_hook' => '__return_null',
        ]);
    }

    public function testInit(): void
    {
        $block_registry = new Block_Registry();
        $block_registry->init();
        $this->assertSame(
            10,
            \has_action('init', '\epiphyt\Impressum\blocks\Block_Registry->register()')
        );
        $this->assertSame(
            10,
            \has_action('enqueue_block_editor_assets', '\epiphyt\Impressum\blocks\Block_Imprint->enqueue_assets()')
        );
        $this->assertSame(
            10,
            \has_filter(
                'register_block_type_args',
                '\epiphyt\Impressum\blocks\Block_Imprint->update_block_type_arguments()'
            )
        );
    }

    public function testRegister(): void
    {
        expect('wp_register_block_types_from_metadata_collection')
            ->once()
            ->with(
                \EPI_IMPRESSUM_BASE . 'build',
                \EPI_IMPRESSUM_BASE . 'build/blocks-manifest.php'
            );
        $block_registry = new Block_Registry();
        $block_registry->register();
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
