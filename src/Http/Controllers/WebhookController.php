<?php

namespace Inensus\ViberMessaging\Http\Controllers;

use App\Models\Meter\Meter;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inensus\ViberMessaging\Services\ViberContactService;
use Inensus\ViberMessaging\Services\ViberCredentialService;
use Inensus\ViberMessaging\Services\WebhookService;
use Viber\Bot;
use Viber\Api\Sender;

class WebhookController extends Controller
{

    private $bot;
    private $botSender;

    public function __construct(
        private ViberCredentialService $credentialService,
        private ViberContactService $viberContactService
    ) {
        $credential = $this->credentialService->getCredentials();
        $apiKey = $credential->api_token;
        $this->botSender = new Sender([
            'name' => 'MicroPowerManager',
            'avatar' => 'https://micropowermanager.com/assets/images/Icon_2_5Icon_2_2.png',
        ]);
        $this->bot = new Bot(['token' => $apiKey]);
    }

    public function index()
    {
        Log::info("Webhook called");
        $bot = $this->bot;
        $botSender = $this->botSender;
        $this->bot
            ->onConversation(function ($event) use ($bot, $botSender) {
                return (new \Viber\Api\Message\Text())->setSender($this->botSender)->setText("Can I help you?");
            })
            ->onText('|register+.*|si', function ($event) use ($bot, $botSender) {

                $message = $event->getMessage()->getText();
                try {
                    $message = explode('+', $message);
                    $meterSerialNumber = $message[1];
                } catch (\Exception $e) {
                    $this->answerToCustomer($bot, $botSender, $event, $this->setWrongFormatMessage());
                }
                // TODO: For cloud version use $databaseProxyManagerService->runForCompany to get the correct database
                $meter = Meter::query()->where('serial_number', $meterSerialNumber)->first();

                if (!$meter) {
                    $this->answerToCustomer($bot, $botSender, $event, $this->setMeterNotFoundMessage());
                }

                $person = $meter->meterParameter->owner;

                if ($person) {
                   $this->viberContactService->createContact($person->id, $event->getSender()->getId());
                    $this->answerToCustomer($bot, $botSender, $event, $this->setSuccessMessage());
                }else{
                    Log::info("Someone who is not a customer tried to register with viber");
                }

            })
            ->run();

        Log::info("Webhook is working incoming data :", request()->all());
        return response()->json(['success' => 'success'], 200);
    }

    private function setWrongFormatMessage(): string
    {
        return "Please enter your meter serial number after register+";
    }

    private function setMeterNotFoundMessage(): string
    {
        return "We couldn't find your meter. Please check your meter serial number and try again.";
    }

    private function setSuccessMessage(): string
    {
        return "You have successfully registered with MicroPowerManager.";
    }

    private function answerToCustomer($bot, $botSender, $event, $message)
    {
        $bot->getClient()->sendMessage(
            (new \Viber\Api\Message\Text())
                ->setSender($botSender)
                ->setReceiver($event->getSender()->getId())
                ->setText("Hello, {$event->getSender()->getName()}! {$message}")
        );
    }
}