<?php

namespace App\Controllers;
// use CodeIgniter\Psr\Cache\SimpleCache;

use SergiX44\Nutgram\Nutgram;
// use SergiX44\Nutgram\Handlers\Listeners;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ForceReply;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
// use SergiX44\Nutgram\Telegram\Types\Message\Message;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\RunningMode\Webhook;


class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function test()
    {
        // return view('err');
        $Pricing = new \App\Models\Pricing();
 
        $prices = $Pricing->findAll();
            $plist = "";
            foreach ($prices as $price) {
$plist = $plist."
{$price['name']}   <b>₦{$price['s_price']}</b>";
            }
            echo $plist;
    }

    public function telegramg()
    {
        $log = new \App\Models\Logs();
        $Users = new \App\Models\Users();

            // $incoming = $this->request->getPost();
            $res = $log->findAll();

            dd($res);
        // echo 'welcom tg g';

        // return $this->generatePaylink('215', ['tg_id'=>'987656','fname'=>'Fawaz']);

    }

    public function checkUser($user)
    {
        $Users = new \App\Models\Users();
        $log = new \App\Models\Logs();
        $log->insert(['name'=>'middlewareCheckUser','data'=>"in checkUser ".json_encode($user)]);

        if(($User = $Users->where('tg_id', $user->id)->findAll()) != []) {
            return $User[0];
        }else{
            return $this->registerUser($user);
        }
    }

    public function registerUser($user)
    {
        $Users = new \App\Models\Users();
        $data = [
            'fname'=> $user->first_name,
            'tg_id'=> $user->id,
            'phone'=> '',
            'email'=> '',
            'balance' => '50',
            'clearance' => '1',
            'pin' => '0000'
        ];
        $Users->insert($data);

        return $data;
    }

    public function webhook() {
        $hash = $_ENV['hash'];
        $incoming = $this->request->getPost();
        $log = new \App\Models\Logs();

        $log->insert(['name'=>'webhook','data'=>json_encode($incoming)]);
        return $this->response->setStatusCode(200);
    }

    public function verifyPay()
    {
        $log = new \App\Models\Logs();
        $Users = new \App\Models\Users();
        $incoming = $this->request->getGet();
        if($incoming['status'] == 'completed'){
            $res = $log->where(['name'=>'pay_'.$incoming['tx_ref']])->findAll();
            if($res){
                $uid = json_decode($res[0]['data'])->tg_id;
                $amt = (json_decode($res[0]['data'])->amt) - 15;
                $user = $Users->where(['tg_id'=>$uid])->find();
                $data = [
                    'balance' => $user[0]['balance'] + $amt
                ];
                $Users->set($data);
                $res = $Users->where(['tg_id'=>$uid])->update();
                if($res){
                    $data = ['name'=>'ProcessedPay_'.$incoming['tx_ref']];
                    $log->set($data);
                    $done = $log->where(['name'=>'pay_'.$incoming['tx_ref']])->update();

                    if($done){
                        return redirect()->to('https://t.me/Rayyan234Bot');
                    }
                }
            }
           return redirect()->to('https://t.me/Rayyan234Bot');
        }
    }

    public function generatePaylink($amt, $user)
    {
        $log = new \App\Models\Logs();
        $client = \Config\Services::curlrequest();
        $tx = "sgmData-tx-{$user['tg_id']}t".rand()*24;
        $log->insert(['name'=>'pay_'.$tx,'data'=>'{"amt":"'.$amt.'", "tg_id": "'.$user['tg_id'].'", "fname":"'.$user['fname'].'" }'] );

        if($amt < 1016){
            $response = $client->request('POST', 'https://api.flutterwave.com/v3/payments', [
                'headers' => [
                    'Authorization' => 'Bearer '.$_ENV['flw'],
                ],
                'json' => [
                    "tx_ref"=>$tx,
                    "amount"=> $amt,
                    "currency"=> "NGN",
                    "redirect_url"=> "https://tgbot.sgm.ng/verifypay",
                    "customer"=> [
                        "email"=> $user['tg_id']."@data.sgm.ng",
                        "name"=> $user['fname'],
                    ],
                    "customizations"=> [
                        "title"=> "SGM Data",
                        "logo"=> "https://rayyantech.sgm.ng/assets/images/logo-dark.png",
                    ],
                    "payment_options"=> "banktransfer",
                    "bank_transfer_options"=> [
                        "expires"=> "3600"
                    ],
                ]
            ] );

            $body = $response->getBody();
            if (strpos($response->header('content-type'), 'application/json') !== false) {
                $body = json_decode($body);
            }

            $link = $body->data->link;

            return $link;
        }else{
            return "http://notAvailableYet.co";
        }
    }

    public function getPriceList()
    {
        $Pricing = new \App\Models\Pricing();
        $prices = $Pricing->findAll();
            $plist = "";
            foreach ($prices as $price) {
$plist = $plist."
{$price['name']}   <b>₦{$price['s_price']}</b>";
            }
            return $plist;
    }

    public function telegram()
    {

        //LOGGER
        $log = new \App\Models\Logs();
        $Users = new \App\Models\Users();
        // $incoming = $this->request->getPostGet();
        // $res = $log->insert(['name'=>'tgIncoming','data'=>"incoming ".json_decode($incoming)]);
        // $psr16Cache = new SimpleCache();
        // https://api.telegram.org/bot6590399869:AAF6tg-t18MmqV_0It1sFRJXvdTSeiBGbrg/setWebhook?url=https://tgbot.sgm.ng/telegram
        
        $config = new Configuration(
                // cache: $psr16Cache,
            clientTimeout: 10, // default in seconds, when contacting the Telegram API
        );

        $bot = new Nutgram('6590399869:AAF6tg-t18MmqV_0It1sFRJXvdTSeiBGbrg', $config);
        $bot->setRunningMode(Webhook::class);

        $bot->middleware(function (Nutgram $bot, $next) {
            $user = $bot->user();
            $User = $this->checkUser($user);
            $bot->set('user', $User);
            $next($bot);
        });

        // Called when a message contains the command "/start someParameter"
        $bot->onCommand('start', function (Nutgram $bot) {
            $user = $bot->get('user');
            $bot->sendMessage(text: 
"Welcome {$user['fname']}!
<b>I am your data subscription bot</b>. 
You can recharge your data subscription right here on Telegram. Just send me the data network, data size, and your phone number in the format 'Data Network DataSize PhoneNumber' <i>(e.g.Data mtn 1gb 1234567890)</i>

You can <b>fund your wallet</b> by using the command /fund 
<b>Check your balance</b> by using command /wallet
<b>Check available data and price list</b> by using command /price
", parse_mode: ParseMode::HTML
            );
        });

        $bot->onCommand('fund', function (Nutgram $bot) {
            $user = $bot->get('user');
            $bot->sendMessage(text: 
"Please note that there's a <b>₦15</b> charge on amount less than <b>₦1000</b>
and <b>₦35</b> charge on any amount greater than ₦1000

Proceed with the funding by send the amount in the format 'fund amount' <i> (e.g fund 200)</i>


<b>NB:</b> <u>Payment more than ₦1000 is not available yet</u>", 
                parse_mode: ParseMode::HTML,
            );
        });


        $bot->onCommand('wallet', function (Nutgram $bot) {
            $user = $bot->get('user');
            $bot->sendMessage(text: 
"Your balance is <b>₦{$user['balance']}</b> 
You are subscriber number <b>{$user['id']}</b>
You can add funds to your wallet by send the amount in the format 'fund amount' <i> (e.g fund 200)</i>


<b>NB:</b> <u>Payment more than ₦1000 is not available yet</u>", 
                parse_mode: ParseMode::HTML,
            );
        });

        $bot->onCommand('price', function (Nutgram $bot) {
            $user = $bot->get('user');
            $plist = $this->getPriceList();
            $bot->sendMessage(text: 
"Our Price list is as follows: 
{$plist}

You can add funds to your wallet by send the amount in the format 'fund amount' <i> (e.g fund 200)</i>
<b>NB:</b> <u>Payment more than ₦1000 is not available yet</u>", 
                parse_mode: ParseMode::HTML,
            );
        });

        $bot->onText('(fund|Fund) {amt}', function (Nutgram $bot, $c, $amt) {
            $user = $bot->get('user');

            //Generate link from flutterwave or payvessel
            if($amt < 1001){
                $amt = $amt+15;
            }else{
                $amt = $amt+35;
            }
            $PayLink = $this->generatePaylink($amt, $user);
            // $PayLink = '';
            // $log->insert(['name'=>'generatePaylink','data'=>"in Link {$amt}"]);

            
           $bot->sendMessage(text: "Follow the link below to make the payment of ₦{$amt}",
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make("Pay ₦{$amt}", url: $PayLink ),
                    ));
        } );


        $bot->onCommand('user', function (Nutgram $bot) {
            $user = $bot->get('user');
            $bot->sendMessage("Hi user {$user['fname']}!");
        });

        $bot->onCommand('opt', function(Nutgram $bot){
            $bot->sendMessage(
                text: 'Welcome!',
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make('Google', url:'https://google.com'),
                        InlineKeyboardButton::make('VoteBot', url:'tg://resolve?domain=vote'),
                    )
            );
        });

        // $bot->onCommand('choice', function(Nutgram $bot){
        //     $bot->sendMessage(
        //         text: 'Welcome!',
        //         reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true, input_field_placeholder: 'Type something', selective: true,)->addRow(
        //             KeyboardButton::make('Give me food!'),
        //             KeyboardButton::make('Give me animal!'),
        //         )
        //     );
        // });

        // $bot->onCommand('cancel', function (Nutgram $bot) {
        //     $bot->sendMessage(
        //         text: 'Removing keyboard...',
        //         reply_markup: ReplyKeyboardRemove::make(true),
        //     )?->delete();
        // });

        // $bot->onCommand('freply', function(Nutgram $bot){
        //     $bot->sendMessage(
        //         text: 'Welcome!',
        //         reply_markup: ForceReply::make(
        //             force_reply: true,
        //             input_field_placeholder: 'Type something',
        //             selective: true,
        //         ),
        //     );
        // });

        // $bot->onText('08108097322', function (Nutgram $bot) {
        //     $bot->setUserData('phn', '08108097322');
        //     $amt = $bot->getUserData('amt');

        //    $bot->sendMessage("Successfully recharged {$amt} for 08108097322");
        // });

        $bot->onText('(data|Data) (mtn|MTN|Mtn) {amt} ([0-9]+)', function (Nutgram $bot, $c, $net, $amt, $phn) {
           $bot->sendMessage(
                text: "Are you certain that you want to recharge {$net} {$amt} for {$phn}",
                reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true, input_field_placeholder: 'Do Not Type anything, Choose from options', selective: true,)->addRow(
                    KeyboardButton::make("✔️ MTN ".strtoupper($amt)." {$phn}"),
                    KeyboardButton::make("❌ MTN ".strtoupper($amt)." {$phn}"),
                ));
        });

        $bot->onText('✔️ MTN {amt} ([0-9]+)', function (Nutgram $bot, $amt, $phn) {
           $bot->sendMessage("Successfully recharged MTN {$amt} for {$phn}");
        });

        $bot->onText('❌ MTN {amt} ([0-9]+)', function (Nutgram $bot, $amt, $phn) {
           $bot->sendMessage("You just cancelled the recharge of MTN {$amt} for {$phn}");
        });

        // $bot->onText('MTN {amt}', function (Nutgram $bot, $amt) {
        //     $bot->setUserData('amt', $amt);

        //    $bot->sendMessage(
        //         text: "Phone Number to recharge MTN {$amt}",
        //         reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true, input_field_placeholder: 'Type phone Number', selective: true,)->addRow(
        //             KeyboardButton::make('MTN 500MB'),
        //             KeyboardButton::make('MTN 1GB'),
        //             KeyboardButton::make('MTN 2GB'),
        //         ));
        // });

        // $bot->onCommand('data', function (Nutgram $bot) {
        //     $bot->sendMessage(
        //         text: 'Data Size? 1GB or 500MB',
        //         reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true, input_field_placeholder: 'Type something', selective: true,)->addRow(
        //             KeyboardButton::make('MTN 500MB'),
        //             KeyboardButton::make('MTN 1GB'),
        //             KeyboardButton::make('MTN 2GB'),
        //         )
        //     );
        // });

        // $bot->onCommand('start', 'firstStep');


        // function firstStep(Nutgram $bot)
        // {
        //     // do stuff
        //     $bot->stepConversation('secondStep');
        // }

        // function secondStep(Nutgram $bot)
        // {
        //     // do stuff
        //     $bot->endConversation();
        // }

        // $bot->onText('Give me food!', function (Nutgram $bot) {
        //     $bot->sendMessage('Apple!');
        // });

        // $bot->onText('Give me animal!', function (Nutgram $bot) {
        //     $bot->sendMessage('Dog!');
        // });

        // // Called on command "/help"
        // $bot->onCommand('help', function (Nutgram $bot) {
        //     $bot->sendMessage('Helpu ke!');
        // });

        // // ex. called when a message contains "My name is Mario"
        // $bot->onText('My name is {name}', function (Nutgram $bot, $name) {
        //      $user = $bot->get('user');
        //     $bot->sendMessage("Hi {$name} and id is {$user['tg_id']}");
        // });

        // // ex. called when a message contains "I want 6 pizzas"
        // $bot->onText('I want ([0-9]+) pizzas', function (Nutgram $bot, $n) {
        //     $bot->sendMessage("You will get {$n} pizzas!");
        // });

        // $bot->onText('I want ([0-9]+) portions of (pizza|cake)', function (Nutgram $bot, $amount, $dish) {
        //     $bot->sendMessage("You will get {$amount} portions of {$dish}!");
        // });

        // $bot->onText('Data (MTN|ART) (1GB|500MB)', function (Nutgram $bot, $network, $amount) {
        //     $bot->sendMessage("I will recharge {$network} {$amount}!");
        // });

        // $bot->onText('Airtime (MTN|ART|GLO|9MB) ([0-9]+) to {phone}', function (Nutgram $bot, $network, $amount, $phone) {
        //     $bot->sendMessage("I will recharge {$network} {$amount} airtime to {$phone}!");
        // });

        $bot->run();
    }
}
