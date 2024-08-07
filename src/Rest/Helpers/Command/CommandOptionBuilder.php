<?php

declare(strict_types=1);

namespace Ragnarok\Fenrir\Rest\Helpers\Command;

use Ragnarok\Fenrir\Enums\ApplicationCommandOptionType;
use Ragnarok\Fenrir\Enums\ChannelType;
use Ragnarok\Fenrir\Rest\Helpers\GetNew;

class CommandOptionBuilder
{
    use GetNew;

    private array $data = [];

    /** @var CommandOptionBuilder[] */
    private array $options;

    /** @var ChannelType[] */
    private array $channelTypes;

    public function setType(ApplicationCommandOptionType $type): self
    {
        $this->data['type'] = $type->value;

        return $this;
    }

    public function getType(): ?ApplicationCommandOptionType
    {
        return isset($this->data['type'])
            ? ApplicationCommandOptionType::from($this->data['type'])
            : null;
    }

    public function setName(string $name): self
    {
        $this->data['name'] = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * @see https://discord.com/developers/docs/reference#locales
     *
     * @param array<string, string> $localizedNames `key => locale`, `value => name`
     */
    public function setNameLocalizations(array $localizedNames): self
    {
        $this->data['name_localizations'] = $localizedNames;

        return $this;
    }

    /**
     * @see https://discord.com/developers/docs/reference#locales
     *
     * @return array<string, string> `key => locale`, `value => name`
     */
    public function getNameLocalizations(): ?array
    {
        return $this->data['name_localizations'] ?? null;
    }

    public function setDescription(string $description): self
    {
        $this->data['description'] = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * @see https://discord.com/developers/docs/reference#locales
     *
     * @param array<string, string> $localizedNames `key => locale`, `value => description`
     */
    public function setDescriptionLocalizations(array $localizedDescriptions): self
    {
        $this->data['description_localizations'] = $localizedDescriptions;

        return $this;
    }

    /**
     * @see https://discord.com/developers/docs/reference#locales
     *
     * @return array<string, string> `key => locale`, `value => description`
     */
    public function getDescriptionLocalizations(): ?array
    {
        return $this->data['description_localizations'] ?? null;
    }

    public function setRequired(bool $required): self
    {
        $this->data['required'] = $required;

        return $this;
    }

    public function getRequired(): ?bool
    {
        return $this->data['required'] ?? null;
    }

    /**
     * @see https://discord.com/developers/docs/reference#locales
     *
     * @param array<string, string> $localizedNames `key => locale`, `value => description`
     */
    public function addChoice(string $name, string|int|float $value, ?array $localizedNames = null): self
    {
        if (!isset($this->data['choices'])) {
            $this->data['choices'] = [];
        }

        $choice = [
            'name' => $name,
            'value' => $value,
        ];

        if ($localizedNames) {
            $choice['name_localizations'] = $localizedNames;
        }

        $this->data['choices'][] = $choice;

        return $this;
    }

    public function getChoices(): ?array
    {
        return $this->data['choices'] ?? null;
    }

    public function addOption(self $commandOptionBuilder): self
    {
        if (!isset($this->options)) {
            $this->options = [];
        }

        $this->options[] = $commandOptionBuilder;

        return $this;
    }

    /** @return ?CommandOptionBuilder[] */
    public function getOptions(): ?array
    {
        return $this->options ?? null;
    }

    public function setChannelTypes(ChannelType ...$channelTypes): self
    {
        $this->channelTypes = $channelTypes;

        return $this;
    }

    /** @return ?ChannelType[] */
    public function getChannelTypes(): ?array
    {
        return $this->channelTypes ?? null;
    }

    public function setMinValue(float|int $minValue): self
    {
        $this->data['min_value'] = $minValue;

        return $this;
    }

    public function getMinValue(): float|int|null
    {
        return $this->data['min_value'] ?? null;
    }

    public function setMaxValue(float|int $minValue): self
    {
        $this->data['max_value'] = $minValue;

        return $this;
    }

    public function getMaxValue(): float|int|null
    {
        return $this->data['max_value'] ?? null;
    }

    public function setMinLength(int $minLength): self
    {
        $this->data['min_length'] = $minLength;

        return $this;
    }

    public function getMinLength(): ?int
    {
        return $this->data['min_length'] ?? null;
    }

    public function setMaxLength(int $minLength): self
    {
        $this->data['max_length'] = $minLength;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->data['max_length'] ?? null;
    }

    public function setAutoComplete(bool $autoComplete): self
    {
        $this->data['autocomplete'] = $autoComplete;

        return $this;
    }

    public function getAutoComplete(): ?bool
    {
        return $this->data['autocomplete'] ?? null;
    }

    public function get(): array
    {
        $data = $this->data;

        if (isset($this->options)) {
            $data['options'] = array_map(
                static fn (self $option) => $option->get(),
                $this->options
            );
        }

        if (isset($this->channelTypes)) {
            $data['channel_types'] = array_map(
                static fn (ChannelType $type) => $type->value,
                $this->channelTypes
            );
        }

        return $data;
    }
}
