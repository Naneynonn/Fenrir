<?php

declare(strict_types=1);

namespace Tests\Exan\Fenrir;

use Exan\Fenrir\Component\Button\DangerButton;
use Exan\Fenrir\Interaction\CommandInteraction;
use Exan\Fenrir\Constants\Events;
use Exan\Fenrir\DataMapper;
use Fakes\Exan\Fenrir\DataMapperFake;
use Exan\Fenrir\Enums\Parts\InteractionTypes;
use Exan\Fenrir\EventHandler;
use Exan\Fenrir\Interaction\ButtonInteraction;
use Exan\Fenrir\InteractionHandler;
use Exan\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Exan\Fenrir\Websocket\Objects\Payload;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Exan\Fenrir\Parts\ApplicationCommand;
use Exan\Fenrir\Parts\InteractionData;
use Exan\Fenrir\Websocket\Events\InteractionCreate;
use Fakes\Exan\Fenrir\DiscordFake;
use Fakes\Exan\Fenrir\PromiseFake;
use React\Promise\Promise;

class InteractionHandlerTest extends MockeryTestCase
{
    private function emitReady(EventHandler $eventHandler)
    {
        /** @var Payload */
        $payload = DataMapperFake::get()->map([
            'op' => 0,
            't' => Events::READY,
            'd' => [
                'user' => [
                    'id' => '::bot user id::',
                ],
            ],
        ], Payload::class);

        $eventHandler->handle(
            $payload
        );
    }

    public function testRegisterGlobalCommand()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord);

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $discord->rest->globalCommand
            ->shouldReceive('createApplicationCommand')
            ->with('::bot user id::', $commandBuilder)
            ->andReturn(PromiseFake::get())
            ->once();

        $interactionHandler->registerGlobalCommand(
            $commandBuilder,
            fn (CommandInteraction $command) => 1
        );

        $this->emitReady($discord->gateway->events);
    }

    public function testRegisterGuildCommand()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord);

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $discord->rest->guildCommand
            ->shouldReceive('createApplicationCommand')
            ->with('::bot user id::', '::guild id::', $commandBuilder)
            ->andReturn(new Promise(fn ($resolver) => $resolver))
            ->once();

        $interactionHandler->registerGuildCommand(
            $commandBuilder,
            '::guild id::',
            fn (CommandInteraction $command) => 1
        );

        $this->emitReady($discord->gateway->events);
    }

    public function testItOnlySetsASingleListener()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord);

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $interactionHandler->registerGuildCommand(
            $commandBuilder,
            '::guild id::',
            fn (CommandInteraction $command) => 1
        );

        $interactionHandler->registerGuildCommand(
            $commandBuilder,
            '::guild id::',
            fn (CommandInteraction $command) => 1
        );

        $this->assertCount(1, $discord->gateway->events->listeners(Events::INTERACTION_CREATE));
    }

    public function testRegisterCommandIsGlobalWithoutDevGuild()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord);

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $discord->rest->globalCommand
            ->shouldReceive('createApplicationCommand')
            ->with('::bot user id::', $commandBuilder)
            ->andReturn(new Promise(fn ($resolver) => $resolver))
            ->once();

        $interactionHandler->registerCommand(
            $commandBuilder,
            fn (CommandInteraction $command) => 1
        );

        $this->emitReady($discord->gateway->events);
    }

    public function testRegisterCommandIsGuildWithDevGuild()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord, '::guild id::');

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $interactionHandler->registerCommand(
            $commandBuilder,
            fn (CommandInteraction $command) => 1
        );

        $interactionHandler->registerGuildCommand(
            $commandBuilder,
            '::guild id::',
            fn (CommandInteraction $command) => 1
        );

        $this->assertCount(1, $discord->gateway->events->listeners(Events::INTERACTION_CREATE));
    }

    public function testItHandlesAnInteraction()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord);

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $discord->rest->globalCommand
            ->shouldReceive('createApplicationCommand')
            ->with('::bot user id::', $commandBuilder)
            ->andReturn(PromiseFake::get(
                DataMapperFake::get()->map([
                    'id' => '::application command id::',
                ], ApplicationCommand::class)
            ))
            ->once();

        $hasRun = false;

        $interactionHandler->registerGlobalCommand(
            $commandBuilder,
            function ($command) use (&$hasRun) {
                $hasRun = true;

                $this->assertInstanceOf(CommandInteraction::class, $command);
            }
        );

        $this->emitReady($discord->gateway->events);

        /** @var InteractionCreate */
        $interactionCreate = DataMapperFake::get()->map([
            'type' => InteractionTypes::APPLICATION_COMMAND->value,
            'data' => [
                'id' => '::application command id::',
            ],
        ], InteractionCreate::class);

        $discord->gateway->events->emit(Events::INTERACTION_CREATE, [$interactionCreate]);

        $this->assertTrue($hasRun, 'Command handler has not been run');
    }

    public function testItIgnoresCommandIfNoHanlderIsRegistered()
    {
        $discord = DiscordFake::get();

        $interactionHandler = new InteractionHandler($discord);

        $commandBuilder = CommandBuilder::new()
            ->setName('command')
            ->setDescription('::description::');

        $discord->rest->globalCommand
            ->shouldReceive('createApplicationCommand')
            ->with('::bot user id::', $commandBuilder)
            ->andReturn(PromiseFake::get(
                DataMapperFake::get()->map([
                    'id' => '::application command id::',
                ], ApplicationCommand::class)
            ))
            ->once();

        $hasRun = false;

        $interactionHandler->registerGlobalCommand(
            $commandBuilder,
            function ($command) use (&$hasRun) {
                $hasRun = true;
            }
        );

        $this->emitReady($discord->gateway->events);

        /** @var InteractionCreate */
        $interactionCreate = DataMapperFake::get()->map([
            'type' => InteractionTypes::APPLICATION_COMMAND->value,
            'data' => [
                'id' => '::other application command id::',
            ],
        ], InteractionCreate::class);

        $discord->gateway->events->emit(Events::INTERACTION_CREATE, [$interactionCreate]);

        $this->assertFalse($hasRun, 'Command handler should not have been run');
    }

    public function testItCanRegisterButtonInteractionHandlers()
    {
        $discord = DiscordFake::get();
        $interactionHandler = new InteractionHandler($discord);

        $button = new DangerButton('::custom id::');

        $hasRun = false;
        $interactionHandler->onButtonInteraction(
            $button,
            function (ButtonInteraction $buttonInteraction) use (&$hasRun) {
                $hasRun = true;
            }
        );

        $this->assertCount(1, $discord->gateway->events->listeners(Events::INTERACTION_CREATE));

        $interactionCreate = DataMapperFake::get()->map([
                'id' => '::interaction id::',
                'token' => '::token::',
                'type' => InteractionTypes::MESSAGE_COMPONENT->value,
                'application_id' => '::application id::',
                'data' => [
                    'component_type' => 2, // @todo enum
                    'custom_id' => '::custom id::',
                ],
            ], InteractionCreate::class);

        $discord->gateway->events->emit(Events::INTERACTION_CREATE, [$interactionCreate]);

        $this->assertTrue($hasRun, 'Handler did not run');
    }

    public function testItOnlyRegistersASingleListener()
    {
        $discord = DiscordFake::get();
        $interactionHandler = new InteractionHandler($discord);

        $button = new DangerButton('::custom id::');
        $interactionHandler->onButtonInteraction($button, fn (ButtonInteraction $btnInt) => null);

        $otherButton = new DangerButton('::some other custom id::');
        $interactionHandler->onButtonInteraction($otherButton, fn (ButtonInteraction $btnInt) => null);

        $this->assertCount(1, $discord->gateway->events->listeners(Events::INTERACTION_CREATE));
    }

    public function testItRemovesButtonListenerIfHandlerReturnsTrue()
    {
        $discord = DiscordFake::get();
        $interactionHandler = new InteractionHandler($discord);

        $button = new DangerButton('::custom id::');

        $runs = 0;
        $interactionHandler->onButtonInteraction(
            $button,
            function (ButtonInteraction $buttonInteraction) use (&$runs) {
                $runs++;

                return true;
            }
        );

        $this->assertCount(1, $discord->gateway->events->listeners(Events::INTERACTION_CREATE));

        $interactionCreate = DataMapperFake::get()->map([
                'id' => '::interaction id::',
                'token' => '::token::',
                'type' => InteractionTypes::MESSAGE_COMPONENT->value,
                'application_id' => '::application id::',
                'data' => [
                    'component_type' => 2, // @todo enum
                    'custom_id' => '::custom id::',
                ],
            ], InteractionCreate::class);

        $discord->gateway->events->emit(Events::INTERACTION_CREATE, [$interactionCreate]);

        $this->assertEquals(1, $runs, 'Handler did not run');

        $discord->gateway->events->emit(Events::INTERACTION_CREATE, [$interactionCreate]);

        $this->assertEquals(1, $runs, 'Handler ran incorrect number of times');
    }
}