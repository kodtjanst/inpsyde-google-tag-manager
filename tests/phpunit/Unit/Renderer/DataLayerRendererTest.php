<?php # -*- coding: utf-8 -*-

namespace Inpsyde\GoogleTagManager\Tests\Unit\Renderer;

use Brain\Monkey\Functions;
use Inpsyde\GoogleTagManager\DataLayer\DataCollectorInterface;
use Inpsyde\GoogleTagManager\DataLayer\DataLayer;
use Inpsyde\GoogleTagManager\Renderer\DataLayerRenderer;
use Inpsyde\GoogleTagManager\Tests\Unit\AbstractTestCase;
use Mockery;

class DataLayerRendererTest extends AbstractTestCase
{

    public function test_basic()
    {

        $testee = new DataLayerRenderer(Mockery::mock(DataLayer::class));
        static::assertInstanceOf(DataLayerRenderer::class, $testee);
    }

    public function test_render()
    {

        $expected_data = ['foo' => 'bar'];
        $expected_name = 'baz';

        $data = Mockery::mock(DataCollectorInterface::class);
        $data->shouldReceive('data')
            ->once()
            ->andReturn($expected_data);

        $dataLayer = Mockery::mock(DataLayer::class);
        $dataLayer->shouldReceive('name')
            ->once()
            ->andReturn($expected_name);
        $dataLayer->shouldReceive('data')
            ->once()
            ->andReturn([$data]);

        Functions\expect('esc_js')
            ->with(Mockery::type('string'))
            ->andReturnFirstArg();

        Functions\expect('wp_json_encode')
            ->with(Mockery::type('array'))
            ->andReturnUsing(
                function ($data) {

                    return json_encode($data);
                }
            );

        $testee = new DataLayerRenderer($dataLayer);

        ob_start();
        static::assertTrue($testee->render());
        $output = ob_get_clean();

        static::assertContains('<script>', $output);
        static::assertContains('var ' . $expected_name, $output);
        static::assertContains($expected_name . '.push(', $output);
        static::assertContains(json_encode($expected_data), $output);
        static::assertContains('</script>', $output);
    }

}
