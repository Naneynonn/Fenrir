<?php

declare(strict_types=1);

namespace Tests\Exan\Fenrir\Command;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ragnarok\Fenrir\Command\FiredCommand;
use Ragnarok\Fenrir\Command\Helpers\InteractionCallbackBuilder;
use Ragnarok\Fenrir\Discord;
use Ragnarok\Fenrir\Rest\Rest;
use Ragnarok\Fenrir\Rest\Webhook;
use Ragnarok\Fenrir\Websocket\Events\InteractionCreate;
use React\Promise\Promise;

class FiredCommandTest extends MockeryTestCase
{
    public function testSendFollowUpMessage()
    {
        $interactionCreate = new InteractionCreate();
        $interactionCreate->id = '::interaction id::';
        $interactionCreate->token = '::interaction token::';

        $discord = Mockery::mock(Discord::class);
        $discord->rest = Mockery::mock(Rest::class);
        $discord->rest->webhook = Mockery::mock(Webhook::class);

        $interactionCallbackBuilder = Mockery::mock(InteractionCallbackBuilder::class);

        $discord->rest->webhook
            ->shouldReceive('createFollowUpMessage')
            ->with('::interaction id::', '::interaction token::', $interactionCallbackBuilder)
            ->andReturn(new Promise(fn ($resolver) => $resolver()))
            ->once();

        $firedCommand = new FiredCommand($interactionCreate, $discord);

        $firedCommand->sendFollowUpMessage($interactionCallbackBuilder);
    }
}
