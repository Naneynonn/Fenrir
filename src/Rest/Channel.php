<?php

declare(strict_types=1);

namespace Ragnarok\Fenrir\Rest;

use Discord\Http\Endpoint;
use Discord\Http\Multipart\MultipartBody;
use Ragnarok\Fenrir\Parts\Channel as PartsChannel;
use Ragnarok\Fenrir\Parts\Invite;
use Ragnarok\Fenrir\Parts\Message;
use Ragnarok\Fenrir\Parts\ThreadMember;
use Ragnarok\Fenrir\Parts\User;
use Ragnarok\Fenrir\Rest\Helpers\Channel\Channel\ChannelBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\EditMessageBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\EditPermissionsBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\GetMessagesBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\GetReactionsBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\InviteBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\MessageBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\StartThreadFromMessageBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Channel\StartThreadWithoutMessageBuilder;
use Ragnarok\Fenrir\Rest\Helpers\Emoji\EmojiBuilder;
use React\Promise\ExtendedPromiseInterface;

/**
 * @see https://discord.com/developers/docs/resources/channel
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @todo seperate calls, `$this->reaction->create(...)` instead of `$this->createReaction(...)` etc
 */
class Channel extends HttpResource
{
    /**
     * @see https://discord.com/developers/docs/resources/channel#get-channel
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel>
     */
    public function get(string $channelId): ExtendedPromiseInterface
    {
        return $this->mapPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL,
                    $channelId
                )
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#modify-channel
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel>
     */
    public function modify(string $channelId, ChannelBuilder $channel, ?string $reason = null): ExtendedPromiseInterface
    {
        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(
                    Endpoint::CHANNEL,
                    $channelId
                ),
                $channel->get(),
                $this->getAuditLogReasonHeader($reason)
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#deleteclose-channel
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel>
     */
    public function delete(string $channelId, ?string $reason = null): ExtendedPromiseInterface
    {
        return $this->mapPromise(
            $this->http->delete(
                Endpoint::bind(
                    Endpoint::CHANNEL,
                    $channelId
                ),
                null,
                $this->getAuditLogReasonHeader($reason)
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#get-channel-messages
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message[]>
     */
    public function getMessages(
        string $channelId,
        GetMessagesBuilder $getMessagesBuilder = new GetMessagesBuilder()
    ): ExtendedPromiseInterface {
        $endpoint = Endpoint::bind(
            Endpoint::CHANNEL_MESSAGES,
            $channelId
        );

        $queryParams = $getMessagesBuilder->get();
        foreach ($queryParams as $key => $value) {
            $endpoint->addQuery($key, $value);
        }

        return $this->mapArrayPromise(
            $this->http->get($endpoint),
            Message::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#get-channel-message
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message>
     */
    public function getMessage(string $channelId, string $messageId): ExtendedPromiseInterface
    {
        return $this->mapPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_MESSAGE,
                    $channelId,
                    $messageId
                )
            ),
            Message::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#create-message
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message>
     */
    public function createMessage(
        string $channelId,
        MessageBuilder $message
    ): ExtendedPromiseInterface {
        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(
                    Endpoint::CHANNEL_MESSAGES,
                    $channelId
                ),
                $message->get()
            ),
            Message::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#crosspost-message
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message>
     */
    public function crosspostMessage(string $channelId, string $messageId): ExtendedPromiseInterface
    {
        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(
                    Endpoint::CHANNEL_CROSSPOST_MESSAGE,
                    $channelId,
                    $messageId
                )
            ),
            Message::class,
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#create-reaction
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function createReaction(
        string $channelId,
        string $messageId,
        EmojiBuilder $emoji
    ): ExtendedPromiseInterface {
        return $this->http->put(
            Endpoint::bind(
                Endpoint::OWN_MESSAGE_REACTION,
                $channelId,
                $messageId,
                (string) $emoji
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#delete-own-reaction
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function deleteOwnReaction(
        string $channelId,
        string $messageId,
        EmojiBuilder $emoji
    ): ExtendedPromiseInterface {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::OWN_MESSAGE_REACTION,
                $channelId,
                $messageId,
                (string) $emoji
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#delete-user-reaction
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function deleteUserReaction(
        string $channelId,
        string $messageId,
        EmojiBuilder $emoji,
        string $userId
    ): ExtendedPromiseInterface {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::USER_MESSAGE_REACTION,
                $channelId,
                $messageId,
                (string) $emoji,
                $userId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#get-reactions
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message>
     */
    public function getReactions(
        string $channelId,
        string $messageId,
        EmojiBuilder $emoji,
        GetReactionsBuilder $getReactionsBuilder = new GetReactionsBuilder()
    ): ExtendedPromiseInterface {
        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_MESSAGES,
                    $channelId,
                    $messageId,
                    (string) $emoji
                ),
                $getReactionsBuilder->get()
            ),
            User::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#delete-all-reactions
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function deleteAllReactions(string $channelId, string $messageId): ExtendedPromiseInterface
    {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::MESSAGE_REACTION_ALL,
                $channelId,
                $messageId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#delete-all-reactions-for-emoji
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function deleteAllReactionsForEmoji(
        string $channelId,
        string $messageId,
        EmojiBuilder $emoji
    ): ExtendedPromiseInterface {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::MESSAGE_REACTION_EMOJI,
                $channelId,
                $messageId,
                (string) $emoji
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#edit-message
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message>
     */
    public function editMessage(
        string $channelId,
        string $messageId,
        EditMessageBuilder $message
    ): ExtendedPromiseInterface {
        return $this->mapPromise(
            $this->http->patch(
                Endpoint::bind(
                    Endpoint::CHANNEL_MESSAGE,
                    $channelId,
                    $messageId
                ),
                $message->get()
            ),
            Message::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#delete-message
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function deleteMessage(
        string $channelId,
        string $messageId,
        ?string $reason = null,
    ): ExtendedPromiseInterface {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::CHANNEL_MESSAGE,
                $channelId,
                $messageId
            ),
            headers: $this->getAuditLogReasonHeader($reason),
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#bulk-delete-messages
     *
     * @var string[] $messageIds
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function bulkDeleteMessages(
        string $channelId,
        array $messageIds,
        ?string $reason = null
    ): ExtendedPromiseInterface {
        return $this->http->post(
            Endpoint::bind(
                Endpoint::CHANNEL_MESSAGES_BULK_DELETE,
                $channelId
            ),
            ['messages' => $messageIds],
            $this->getAuditLogReasonHeader($reason)
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#edit-channel-permissions
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function editChannelPermissions(
        string $channelId,
        EditPermissionsBuilder $editPermissionsBuilder,
        ?string $reason = null
    ): ExtendedPromiseInterface {
        return $this->http->put(
            Endpoint::bind(
                Endpoint::CHANNEL_PERMISSIONS,
                $channelId,
                $editPermissionsBuilder->getOverwriteId()
            ),
            $editPermissionsBuilder->get(),
            $this->getAuditLogReasonHeader($reason)
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/invite#invite-object
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Invite>
     */
    public function getChannelInvites(string $channelId): ExtendedPromiseInterface
    {
        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_INVITES,
                    $channelId
                )
            ),
            Invite::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#get-channel-invites
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Invite>
     */
    public function createChannelInvite(
        string $channelId,
        InviteBuilder $inviteBuilder = new InviteBuilder(),
        ?string $reason = null
    ): ExtendedPromiseInterface {
        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(
                    Endpoint::CHANNEL_INVITES,
                    $channelId
                ),
                $inviteBuilder->get(),
                $this->getAuditLogReasonHeader($reason)
            ),
            Invite::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#delete-channel-permission
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function deleteChannelPermissions(
        string $channelId,
        string $overwriteId,
        ?string $reason = null
    ): ExtendedPromiseInterface {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::CHANNEL_PERMISSIONS,
                $channelId,
                $overwriteId
            ),
            null,
            $this->getAuditLogReasonHeader($reason)
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#follow-announcement-channel
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function followAnnouncementChannel(string $channelId, string $webhookChannelId): ExtendedPromiseInterface
    {
        return $this->http->post(
            Endpoint::bind(
                Endpoint::CHANNEL_FOLLOW,
                $channelId
            ),
            ['webhook_channel_id' => $webhookChannelId]
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#trigger-typing-indicator
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function triggerTypingIndicator(string $channelId): ExtendedPromiseInterface
    {
        return $this->http->post(
            Endpoint::bind(
                Endpoint::CHANNEL_TYPING,
                $channelId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#get-pinned-messages
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Message[]>
     */
    public function getPinnedMessages(string $channelId): ExtendedPromiseInterface
    {
        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_PINS,
                    $channelId
                )
            ),
            Message::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#pin-message
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function pinMessage(string $channelId, string $messageId): ExtendedPromiseInterface
    {
        return $this->http->put(
            Endpoint::bind(
                Endpoint::CHANNEL_PIN,
                $channelId,
                $messageId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#unpin-message
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function unpinMessage(string $channelId, string $messageId): ExtendedPromiseInterface
    {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::CHANNEL_PIN,
                $channelId,
                $messageId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#start-thread-from-message
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel>
     */
    public function startThreadFromMessage(
        string $channelId,
        string $messageId,
        StartThreadFromMessageBuilder $startThreadFromMessageBuilder
    ): ExtendedPromiseInterface {
        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(
                    Endpoint::CHANNEL_MESSAGE_THREADS,
                    $channelId,
                    $messageId,
                ),
                $startThreadFromMessageBuilder->get()
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#start-thread-without-message
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel>
     */
    public function startThreadWithoutMessage(
        string $channelId,
        StartThreadWithoutMessageBuilder $startThreadWithoutMessageBuilder
    ): ExtendedPromiseInterface {
        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(
                    Endpoint::CHANNEL_THREADS,
                    $channelId
                ),
                $startThreadWithoutMessageBuilder->get()
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#start-thread-in-forum-or-media-channel
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel> includes $message property
     */
    public function startThreadInForumChannel(
        string $channelId,
        MultipartBody|array $params,
        ?string $reason = null
    ): ExtendedPromiseInterface {
        $forumChannelWithMessage = new class () extends Channel {
            public Message $message;
        };

        return $this->mapPromise(
            $this->http->post(
                Endpoint::bind(Endpoint::CHANNEL_THREADS, $channelId),
                $params,
                $this->getAuditLogReasonHeader($reason),
            ),
            $forumChannelWithMessage::class,
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#join-thread
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function joinThread(string $channelId): ExtendedPromiseInterface
    {
        return $this->http->put(
            Endpoint::bind(
                Endpoint::THREAD_MEMBER_ME,
                $channelId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#add-thread-member
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function addThreadMember(string $channelId, string $userId): ExtendedPromiseInterface
    {
        return $this->http->put(
            Endpoint::bind(
                Endpoint::THREAD_MEMBER,
                $channelId,
                $userId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#leave-thread
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function leaveThread(string $channelId): ExtendedPromiseInterface
    {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::THREAD_MEMBER_ME,
                $channelId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#remove-thread-member
     *
     * @return ExtendedPromiseInterface<void>
     */
    public function removeThreadMember(string $channelId, string $userId): ExtendedPromiseInterface
    {
        return $this->http->delete(
            Endpoint::bind(
                Endpoint::THREAD_MEMBER,
                $channelId,
                $userId
            )
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#get-thread-member
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\ThreadMember>
     */
    public function getThreadMember(string $channelId, string $userId): ExtendedPromiseInterface
    {
        return $this->mapPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::THREAD_MEMBER,
                    $channelId,
                    $userId
                )
            ),
            ThreadMember::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#list-thread-members
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\ThreadMember[]>
     */
    public function listThreadMembers(
        string $channelId,
        ?bool $withMember = null,
        ?string $after = null,
        ?int $limit = null,
    ): ExtendedPromiseInterface {
        $options = array_filter([
            'with_member' => $withMember,
            'after' => $after,
            'limit' => $limit,
        ], static fn ($value) => !is_null($value));

        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::THREAD_MEMBERS,
                    $channelId
                ),
                $options
            ),
            ThreadMember::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#list-public-archived-threads
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel[]>
     */
    public function listPublicArchivedThreads(string $channelId): ExtendedPromiseInterface
    {
        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_THREADS_ARCHIVED_PUBLIC,
                    $channelId
                )
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#list-private-archived-threads
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel[]>
     */
    public function listPrivateArchivedThreads(string $channelId): ExtendedPromiseInterface
    {
        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_THREADS_ARCHIVED_PRIVATE,
                    $channelId
                )
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }

    /**
     * @see https://discord.com/developers/docs/resources/channel#list-joined-private-archived-threads
     *
     * @return ExtendedPromiseInterface<\Ragnarok\Fenrir\Parts\Channel[]>
     */
    public function listJoinedPrivateArchivedThreads(string $channelId): ExtendedPromiseInterface
    {
        return $this->mapArrayPromise(
            $this->http->get(
                Endpoint::bind(
                    Endpoint::CHANNEL_THREADS_ARCHIVED_PRIVATE_ME,
                    $channelId
                )
            ),
            PartsChannel::class
        )->otherwise($this->logThrowable(...));
    }
}
