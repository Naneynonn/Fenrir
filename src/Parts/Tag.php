<?php

namespace Exan\Dhp\Parts;


class Tag
{
    public string $id;
    public string $name;
    public bool $moderated;
    public ?string $emoji_id;
    public ?string $emoji_name;
}