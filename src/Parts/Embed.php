<?php

namespace Exan\Dhp\Parts;

use Exan\Dhp\Enums\Parts\EmbedTypes;
use Carbon\Carbon;

class Embed
{
    public ?string $title;
    public ?EmbedTypes $type;
    public ?string $description;
    public ?string $url;
    public ?Carbon $timestamp;
    public ?int $color;
    public ?EmbedFooter $footer;
    public ?EmbedImage $image;
    public ?EmbedThumbnail $thumbnail;
    public ?EmbedVideo $video;
    public ?EmbedProvider $provider;
    public ?EmbedAuthor $author;
    /** @var ?\Exan\Dhp\Parts\EmbedField[] */
    public ?array $fields;
}
