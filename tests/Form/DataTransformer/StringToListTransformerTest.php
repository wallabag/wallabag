<?php

namespace Tests\Wallabag\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Wallabag\Form\DataTransformer\StringToListTransformer;

class StringToListTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransformWithValidData($inputData, $expectedResult)
    {
        $transformer = new StringToListTransformer();

        $this->assertSame($expectedResult, $transformer->transform($inputData));
    }

    public function transformProvider()
    {
        return [
            [null, ''],
            [[], ''],
            [['single value'], 'single value'],
            [['first value', 'second value'], 'first value,second value'],
        ];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransformWithValidData($inputData, $expectedResult)
    {
        $transformer = new StringToListTransformer();

        $this->assertSame($expectedResult, $transformer->reverseTransform($inputData));
    }

    public function reverseTransformProvider()
    {
        return [
            [null, null],
            ['', []],
            ['single value', ['single value']],
            ['first value,second value', ['first value', 'second value']],
            ['first value,     second value', ['first value', 'second value']],
            ['first value,  ,  second value', ['first value', 'second value']],
        ];
    }
}
