<?php

declare(strict_types=1);

namespace Ragnarok\Fenrir\Parts;

class Widget
{
    public string $id;
    public string $name;
    public ?string $instant_invite;

    /**
     * @var \Ragnarok\Fenrir\Parts\Channel[]
     */
    public array $channels;

    /**
     * @var \Ragnarok\Fenrir\Parts\User[]
     */
    public array $users;
    public int $presence_count;
}
