<?php

namespace App\Controllers;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\RunningMode\Webhook;


class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function telegram()
    {
        // https://api.telegram.org/bot6590399869:AAF6tg-t18MmqV_0It1sFRJXvdTSeiBGbrg/setWebhook?url=https://tgbot.sgm.ng/telegram
        
        $config = new Configuration(
            clientTimeout: 10, // default in seconds, when contacting the Telegram API
        );

        $bot = new Nutgram('6590399869:AAF6tg-t18MmqV_0It1sFRJXvdTSeiBGbrg', $config);
        $bot->setRunningMode(Webhook::class);

        // Called when a message contains the command "/start someParameter"
        $bot->onCommand('start {parameter}', function (Nutgram $bot, $parameter) {
            $bot->sendMessage("The parameter is {$parameter}");
        });

        // Called on command "/help"
        $bot->onCommand('help', function (Nutgram $bot) {
            $bot->sendMessage('Help me!');
        });

        // ex. called when a message contains "My name is Mario"
        $bot->onText('My name is {name}', function (Nutgram $bot, $name) {
            $bot->sendMessage("Hi {$name}");
        });

        // ex. called when a message contains "I want 6 pizzas"
        $bot->onText('I want ([0-9]+) pizzas', function (Nutgram $bot, $n) {
            $bot->sendMessage("You will get {$n} pizzas!");
        });

        $bot->onText('I want ([0-9]+) portions of (pizza|cake)', function (Nutgram $bot, $amount, $dish) {
            $bot->sendMessage("You will get {$amount} portions of {$dish}!");
        });

        $bot->run();
    }
}
