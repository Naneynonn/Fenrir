<?php

declare(strict_types=1);

namespace Ragnarok\Fenrir\Gateway\Events;

use Ragnarok\Fenrir\Attributes\Intent;
use Ragnarok\Fenrir\Parts\ThreadMember;

/**
 * @see https://discord.com/developers/docs/topics/gateway-events#thread-members-update
 */
#[Intent("GUILD_MEMBERS")]
class ThreadMemberUpdate
{
    public string $id;
    public ?string $guild_id;
    public int $member_count;

    /**
     * @var ThreadMember[]
     */
    public ?array $added_members;

    /**
     * @var string[]
     */
    public ?array $removed_member_ids;
}
