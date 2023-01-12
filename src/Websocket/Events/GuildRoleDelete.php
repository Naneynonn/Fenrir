<?php

namespace Exan\Dhp\Websocket\Events;

/**
 * @see https://discord.com/developers/docs/topics/gateway-events#guild-role-delete
 */
class GuildRoleDelete
{
    public string $guild_id;
    public string $role_id;
}