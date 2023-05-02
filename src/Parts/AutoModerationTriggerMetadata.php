<?php

declare(strict_types=1);

namespace Ragnarok\Fenrir\Parts;

use Ragnarok\Fenrir\Enums\Parts\AutoModerationKeywordPresetTypes;

class AutoModerationTriggerMetadata
{
    /**
     * @var string[]
     */
    public array $keyword_filter;
    /**
     * @var string[]
     */
    public array $regex_patterns;
    /**
     * @var \Ragnarok\Fenrir\Enums\Parts\AutoModerationKeywordPresetTypes[]
     */
    public array $presets;
    /**
     * @var string[]
     */
    public array $allow_list;
    public int $mention_total_limit;

    public function setPresets(array $value): void
    {
        $this->presets = [];

        foreach ($value as $entry) {
            $this->presets[] = AutoModerationKeywordPresetTypes::from($entry);
        }
    }
}
