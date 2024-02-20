<?php

namespace App\Controllers;
use SergiX44\Nutgram\Nutgram;
// use SergiX44\Nutgram\Handlers\Listeners;
// use SergiX44\Nutgram\Telegram\Types\Message\Message;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\RunningMode\Webhook;


class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function telegramg()
    {
        echo 'welcom tg g1';
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

        $bot->middleware(function (Nutgram $bot, $next) {
            $user = $bot->userId();
            $bot->set('user', $user);
            $next($bot);
        });

        $bot->onCommand('user', function (Nutgram $bot) {
            $user = $bot->get('user');
            $bot->sendMessage("Hi user {$user->name}!");
        });

        $bot->onCommand('choice', function(Nutgram $bot){
            $bot->sendMessage(
                text: 'Welcome!',
                reply_markup: ReplyKeyboardMarkup::make()->addRow(
                    KeyboardButton::make('Give me food!'),
                    KeyboardButton::make('Give me animal!'),
                )
            );
        });

        $bot->onText('Give me food!', function (Nutgram $bot) {
            $bot->sendMessage('Apple!');
        });

        $bot->onText('Give me animal!', function (Nutgram $bot) {
            $bot->sendMessage('Dog!');
        });

        // Called on command "/help"
        $bot->onCommand('help', function (Nutgram $bot) {
            $bot->sendMessage('Helpu ke!');
        });

        // ex. called when a message contains "My name is Mario"
        $bot->onText('My name is {name}', function (Nutgram $bot, $name) {
             $user = $bot->get('user');
            $bot->sendMessage("Hi {$name} and id is {$user->name}");
        });

        // ex. called when a message contains "I want 6 pizzas"
        $bot->onText('I want ([0-9]+) pizzas', function (Nutgram $bot, $n) {
            $bot->sendMessage("You will get {$n} pizzas!");
        });

        $bot->onText('I want ([0-9]+) portions of (pizza|cake)', function (Nutgram $bot, $amount, $dish) {
            $bot->sendMessage("You will get {$amount} portions of {$dish}!");
        });

        $bot->onText('Data (MTN|ART) (1GB|500MB)', function (Nutgram $bot, $network, $amount) {
            $bot->sendMessage("I will recharge {$network} {$amount}!");
        });

        $bot->onText('Airtime (MTN|ART|GLO|9MB) ([0-9]+) to {phone}', function (Nutgram $bot, $network, $amount, $phone) {
            $bot->sendMessage("I will recharge {$network} {$amount} airtime to {$phone}!");
        });

        $bot->run();
    }
}
