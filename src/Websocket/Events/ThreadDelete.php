<?php

namespace Exan\Dhp\Websocket\Events;

/**
 * @see https://discord.com/developers/docs/topics/gateway-events#thread-delete
 */
class ThreadDelete
{
    public string $id;
    public ?string $guild_id;
    public ?string $parent_id;
    public int $type;
}