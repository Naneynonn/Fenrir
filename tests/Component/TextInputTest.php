<?php

declare(strict_types=1);

namespace Tests\Exan\Dhp\Component;

use Exan\Dhp\Component\TextInput;
use Exan\Dhp\Enums\Component\TextInputStyle;
use PHPUnit\Framework\TestCase;

class TextInputTest extends TestCase
{
    /**
     * @dataProvider convertionExpectationProvider
     */
    public function testCorrectlyConverted(array $args, array $expected)
    {
        $textInput = new TextInput(...$args);

        $this->assertEquals($expected, $textInput->get());
    }

    public function convertionExpectationProvider(): array
    {
        return [
            'Completely filled out' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Short,
                    '::label::',
                    1,
                    10,
                    true,
                    '::value::',
                    '::placeholder::'
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Short,
                    'label' => '::label::',
                    'min_length' => 1,
                    'max_length' => 10,
                    'required' => true,
                    'value' => '::value::',
                    'placeholder' => '::placeholder::',
                ],
            ],
            'Completely filled out with Paragraph input style' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Paragraph,
                    '::label::',
                    1,
                    10,
                    true,
                    '::value::',
                    '::placeholder::'
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Paragraph,
                    'label' => '::label::',
                    'min_length' => 1,
                    'max_length' => 10,
                    'required' => true,
                    'value' => '::value::',
                    'placeholder' => '::placeholder::',
                ],
            ],
            'Missing min length' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Short,
                    '::label::',
                    null,
                    10,
                    true,
                    '::value::',
                    '::placeholder::'
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Short,
                    'label' => '::label::',
                    'max_length' => 10,
                    'required' => true,
                    'value' => '::value::',
                    'placeholder' => '::placeholder::',
                ],
            ],
            'Missing max length' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Short,
                    '::label::',
                    1,
                    null,
                    true,
                    '::value::',
                    '::placeholder::'
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Short,
                    'label' => '::label::',
                    'min_length' => 1,
                    'required' => true,
                    'value' => '::value::',
                    'placeholder' => '::placeholder::',
                ],
            ],
            'Missing required' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Short,
                    '::label::',
                    1,
                    10,
                    null,
                    '::value::',
                    '::placeholder::'
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Short,
                    'label' => '::label::',
                    'min_length' => 1,
                    'max_length' => 10,
                    'value' => '::value::',
                    'placeholder' => '::placeholder::',
                ],
            ],
            'Missing value' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Short,
                    '::label::',
                    1,
                    10,
                    true,
                    null,
                    '::placeholder::'
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Short,
                    'label' => '::label::',
                    'min_length' => 1,
                    'max_length' => 10,
                    'required' => true,
                    'placeholder' => '::placeholder::',
                ],
            ],
            'Missing placeholder' => [
                'args' => [
                    '::custom id::',
                    TextInputStyle::Short,
                    '::label::',
                    1,
                    10,
                    true,
                    '::value::',
                    null
                ],
                'expected' => [
                    'type' => 4,
                    'custom_id' => '::custom id::',
                    'style' => TextInputStyle::Short,
                    'label' => '::label::',
                    'min_length' => 1,
                    'max_length' => 10,
                    'required' => true,
                    'value' => '::value::',
                ],
            ],
        ];
    }
}
