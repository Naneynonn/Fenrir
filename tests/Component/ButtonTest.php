<?php

use Exan\Dhp\Component\Button\DangerButton;
use Exan\Dhp\Component\Button\LinkButton;
use Exan\Dhp\Component\Button\PrimaryButton;
use Exan\Dhp\Component\Button\SecondaryButton;
use Exan\Dhp\Component\Button\SuccessButton;
use Exan\Dhp\Enums\Component\ButtonStyle;
use Exan\Dhp\Parts\Emoji;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    private function getEmoji(): Emoji
    {
        $emoji = new Emoji();
        $emoji->id = '::emoji id::';
        $emoji->name = '::emoji name::';
        $emoji->animated = true;

        return $emoji;
    }

    /**
     * @dataProvider convertionExpectationProvider
     */
    public function testCorrectlyConverted(array $args, array $expected)
    {
        $buttonTypes = [
            DangerButton::class => ButtonStyle::Danger,
            PrimaryButton::class => ButtonStyle::Primary,
            SecondaryButton::class => ButtonStyle::Secondary,
            SuccessButton::class => ButtonStyle::Success,
        ];

        foreach ($buttonTypes as $buttonClass => $buttonStyle) {
            $expected['style'] = $buttonStyle;

            $button = new $buttonClass(...$args);

            $this->assertEquals($expected, $button->get());
        }
    }

    public function convertionExpectationProvider(): array
    {
        return [
            'Completely filled out' => [
                'args' => [
                    '::custom id::',
                    '::label::',
                    $this->getEmoji(),
                    true,
                ],
                'expected' => [
                    'type' => 2,
                    'custom_id' => '::custom id::',
                    'label' => '::label::',
                    'emoji' => $this->getEmoji()->getPartial(),
                    'disabled' => true
                ],
            ],
            'Missing label' => [
                'args' => [
                    '::custom id::',
                    null,
                    $this->getEmoji(),
                    true,
                ],
                'expected' => [
                    'type' => 2,
                    'custom_id' => '::custom id::',
                    'emoji' => $this->getEmoji()->getPartial(),
                    'disabled' => true
                ],
            ],
            'Missing emoji' => [
                'args' => [
                    '::custom id::',
                    '::label::',
                    null,
                    true,
                ],
                'expected' => [
                    'type' => 2,
                    'custom_id' => '::custom id::',
                    'label' => '::label::',
                    'disabled' => true
                ],
            ],
            'Missing disabled' => [
                'args' => [
                    '::custom id::',
                    '::label::',
                    $this->getEmoji(),
                ],
                'expected' => [
                    'type' => 2,
                    'custom_id' => '::custom id::',
                    'label' => '::label::',
                    'emoji' => $this->getEmoji()->getPartial(),
                    'disabled' => false
                ],
            ],
        ];
    }

    /**
     * @dataProvider convertionExpectationProviderLinkButton
     */
    public function testCorrectlyConvertedLinkButton(array $args, array $expected)
    {
        $button = new LinkButton(...$args);

        $this->assertEquals($expected, $button->get());
    }

    public function convertionExpectationProviderLinkButton(): array
    {
        return [
            'Completely filled out' => [
                'args' => [
                    '::url::',
                    '::label::',
                    $this->getEmoji(),
                    true,
                ],
                'expected' => [
                    'type' => 2,
                    'style' => ButtonStyle::Link,
                    'url' => '::url::',
                    'label' => '::label::',
                    'emoji' => $this->getEmoji()->getPartial(),
                    'disabled' => true
                ],
            ],
            'Missing label' => [
                'args' => [
                    '::url::',
                    null,
                    $this->getEmoji(),
                    true,
                ],
                'expected' => [
                    'type' => 2,
                    'style' => ButtonStyle::Link,
                    'url' => '::url::',
                    'emoji' => $this->getEmoji()->getPartial(),
                    'disabled' => true
                ],
            ],
            'Missing emoji' => [
                'args' => [
                    '::url::',
                    '::label::',
                    null,
                    true,
                ],
                'expected' => [
                    'type' => 2,
                    'style' => ButtonStyle::Link,
                    'url' => '::url::',
                    'label' => '::label::',
                    'disabled' => true
                ],
            ],
            'Missing disabled' => [
                'args' => [
                    '::url::',
                    '::label::',
                    $this->getEmoji(),
                ],
                'expected' => [
                    'type' => 2,
                    'style' => ButtonStyle::Link,
                    'url' => '::url::',
                    'label' => '::label::',
                    'emoji' => $this->getEmoji()->getPartial(),
                    'disabled' => false
                ],
            ],
        ];
    }
}