<?php

declare(strict_types=1);

namespace GCSS\Tests\Integration;

use GCSS\Converter;
use PHPUnit\Framework\TestCase;

class ConvertGCSSTest extends TestCase
{
    /** @test */
    public function test_conversion_of_gcss_to_tres()
    {
        $this->markTestIncomplete('Implementation is far from complete.');

        $input = file_get_contents(__DIR__ . '/../fixtures/theme.gcss');
        $convertor = new Converter();
        $this->assertStringEqualsFile(__DIR__ . '/../fixtures/theme.gcss', $convertor->execute($input));
    }
}
