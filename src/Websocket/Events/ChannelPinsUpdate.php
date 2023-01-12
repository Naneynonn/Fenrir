<?php

namespace Exan\Dhp\Websocket\Events;

use Carbon\Carbon;

/**
 * @see https://discord.com/developers/docs/topics/gateway-events#channel-pins-update
 */
class ChannelPinsUpdate
{
    public ?string $guild_id;
    public string $channel_id;
    public ?Carbon $last_pin_timestamp;
}