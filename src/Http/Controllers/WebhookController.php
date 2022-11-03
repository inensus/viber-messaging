<?php

namespace Inensus\ViberMessaging\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inensus\ViberMessaging\Services\ViberCredentialService;
use Inensus\ViberMessaging\Services\WebhookService;
use Viber\Bot;
use Viber\Api\Sender;

class WebhookController extends Controller
{

    public function __construct(
        private ViberCredentialService $credentialService,
    ) {
    }

    public function listen()
    {
        $credential = $this->credentialService->getCredentials();

        $apiKey = $credential->api_token;
        $botSender = new Sender([
            'name' => 'MicroPowerManager',
            'avatar' => 'https://micropowermanager.com/assets/images/Icon_2_5Icon_2_2.png',
        ]);

        try {
            $bot = new Bot(['token' => $apiKey]);
            $bot
                ->onConversation(function ($event) use ($bot, $botSender) {
                    Log::info("Conversation started");
                    Log::info($event);
                    // this event fires if user open chat, you can return "welcome message"
                    // to user, but you can't send more messages!
                    return (new \Viber\Api\Message\Text())
                        ->setSender($botSender)
                        ->setText("Can i help you?");
                })
                ->onText('|test .*|si', function ($event) use ($bot, $botSender) {
                    Log::info("Test message received");
                    Log::info($event);
                    // match by template, for example "test xxx"
                    $bot->getClient()->sendMessage(
                        (new \Viber\Api\Message\Text())
                            ->setSender($botSender)
                            ->setReceiver($event->getSender()->getId())
                            ->setText("Hello, {$event->getSender()->getName()}!")
                    );
                })
                ->onSubscribe(function ($event) use ($bot, $botSender) {
                    Log::info("Subscribed");
                    Log::info($event);
                    // this event fires if user subscribed to your bot
                    $bot->getClient()->sendMessage(
                        (new \Viber\Api\Message\Text())
                            ->setSender($botSender)
                            ->setReceiver($event->getSender()->getId())
                            ->setText("Hello, {$event->getSender()->getName()}!")
                    );
                })
                ->run();
        } catch (\Exception $e) {

            Log::error("An error occurred on Viber while getting customer message.", ['message: ' => $e->getMessage()]);
        }
    }
}