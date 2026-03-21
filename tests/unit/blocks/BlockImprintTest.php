<?php

declare(strict_types=1);

namespace Tests\Unit\blocks {

    use epiphyt\Impressum\blocks\Block_Imprint;
    use epiphyt\Impressum\Frontend;
    use Mockery\Adapter\Phpunit\MockeryTestCase;
    use PHPUnit\Framework\Attributes\CoversClass;

    use function Brain\Monkey\Functions\stubEscapeFunctions;
    use function Brain\Monkey\Functions\stubs;
    use function Brain\Monkey\Functions\stubTranslationFunctions;
    use function Brain\Monkey\setUp;
    use function Brain\Monkey\tearDown;

    #[CoversClass(Block_Imprint::class)]
    final class BlockImprintTest extends MockeryTestCase
    {
        private static $containerMock;

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
            self::$containerMock = \Mockery::mock('Container');
        }

        public static function getMockContainer()
        {
            return self::$containerMock;
        }

        public function testInit(): void
        {
            $imprint_block_class = new Block_Imprint();
            $imprint_block_class->init();
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

        public function testUpdateBlockTypeArguments(): void
        {
            $frontend = \Mockery::mock('alias:' . Frontend::class);
            self::$containerMock->shouldReceive('get')
                ->with('frontend')
                ->andReturn($frontend);

            $imprint_block_class = new Block_Imprint();
            $different_block = [
            'name' => 'core/button',
            ];
            $imprint_block = [
            'name' => 'impressum/imprint',
            ];
            $this->assertEqualsCanonicalizing(
                $different_block,
                $imprint_block_class->update_block_type_arguments($different_block)
            );
            $this->assertEqualsCanonicalizing(
                $imprint_block = [
                'name' => 'impressum/imprint',
                'render_callback' => [ $frontend, 'render_block' ]
                ],
                $imprint_block_class->update_block_type_arguments($imprint_block)
            );
        }

        protected function tearDown(): void
        {
            tearDown();
            parent::tearDown();
        }
    }
}

namespace epiphyt\Impressum {

    function get_container()
    {
        return \Tests\Unit\blocks\BlockImprintTest::getMockContainer();
    }
}
