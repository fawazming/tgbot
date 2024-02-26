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
        // $log->insert(['name'=>'middlewareCheckUser','data'=>"in checkUser ".json_encode($user)]);

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

        $log->insert(['name'=>'webhook','data'=>$incoming]);
        return $this->response->setStatusCode(200);
    }


    public function datawebhook() {
        $hash = $_ENV['hash'];
        $incoming = $this->request->getPostGet();
        $log = new \App\Models\Logs();

        $inc = json_decode($incoming);

        $log->insert(['name'=>'datawebhookIncoming','data'=>"I am coming"]);
        $log->insert(['name'=>'datawebhook','data'=>$incoming['status']]);
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

    public function network($net)
    {
        switch ($net) {
            case 'MTN':
                return 1;
                break;
            case 'ART':
                return 3;
                break;
            case 'GLO':
                return 2;
                break;
            case 'NMB':
                return 6;
                break;
        }
    }

    public function compareBalance($user, $bundle, $type)
    {
        $Pricing = new \App\Models\Pricing();
        $network = strtoupper( explode('-', $bundle)[0]);
        $amt = strtoupper( explode('-', $bundle)[1] );

        // $network = $this->network($network);
        if($type == 'data'){
            $sPrice = $Pricing->where(['name'=>"{$network} {$amt}"])->findAll()[0]['s_price'];
        }else{
            $sPrice = $amt;
        }

        if($user['balance'] > $sPrice){
            return true;
        }else{
            return false;
        }
    }

    public function updateBalance($uid, $amt, $negative=false)
    {
        $Users = new \App\Models\Users();

        $user = $Users->where(['tg_id'=>$uid])->find();
        $data = [];
        if($negative){
            $data = [
                'balance' => $user[0]['balance'] - $amt
            ];
        }else{
            $data = [
                'balance' => $user[0]['balance'] + $amt
            ];
        }
        $Users->set($data);
        $res = $Users->where(['tg_id'=>$uid])->update();
    }

    public function rechargeData($user, $net, $amt, $phn)
    {
        $Pricing = new \App\Models\Pricing();
        $amt = strtoupper($amt);
        $netw = $this->network(strtoupper($net));  
        $plist = $Pricing->where(['name'=>"{$net} {$amt}"])->findAll()[0];
        $sPrice = $plist['s_price'];  
        $code = $plist['code'];
        $code = explode('-', $code)[1];

        $log = new \App\Models\Logs();
        $client = \Config\Services::curlrequest();
        $log->insert(['name'=>'Data_'.$user['tg_id'],'data'=>'{"amt":"'.$net.$amt.$code.'", "tg_id": "'.$user['tg_id'].'", "phoneRecharged":"'.$phn.'" }'] );
        $this->updateBalance($user['tg_id'], $sPrice, true);
        $response = $client->request('POST', 'https://www.gladtidingsdata.com/api/data/', [
            'headers' => [
                'Authorization' => 'Token '.$_ENV['glad'],
            ],
            'json' => [
                "network"=>$netw, 
                "mobile_number"=>$phn,
                "plan"=> $code, 
                "Ported_number" => true
            ]
        ] );

        $body = $response->getBody();
        if (strpos($response->header('content-type'), 'application/json') !== false) {
            $body = json_decode($body);
        }
        $log->insert(['name'=>'DataReturned', 'data'=>json_encode($body)]);
        // $link = $body->data->link;

        return true;
    }


    public function rechargeAirtime($user, $net, $amt, $phn)
    {
        $Pricing = new \App\Models\Pricing();
        $netw = $this->network(strtoupper($net));

        $log = new \App\Models\Logs();
        $client = \Config\Services::curlrequest();
        // $log->insert(['name'=>'Airtime_'.$user['tg_id'],'data'=>'{"amt":"'.$net.$amt.'", "tg_id": "'.$user['tg_id'].'", "phoneRecharged":"'.$phn.'" }'] );
        // $this->updateBalance($user['tg_id'], $amt, true);
        // $response = $client->request('POST', 'https://www.gladtidingsdata.com/api/topup/', [
        //     'headers' => [
        //         'Authorization' => 'Token '.$_ENV['glad'],
        //     ],
        //     'json' => [
        //         "network"=>$netw,
        //         "amount"=>$amt, 
        //         "mobile_number"=>$phn, 
        //         "Ported_number" => true,
        //         "airtime_type"=>"VTU"
        //     ]
        // ] );

        // $body = $response->getBody();
        // if (strpos($response->header('content-type'), 'application/json') !== false) {
        //     $body = json_decode($body);
        // }
        // $log->insert(['name'=>'AirtimeReturned', 'data'=>json_encode($body)]);

        return true;
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

        $log = new \App\Models\Logs();
        $Users = new \App\Models\Users();
        
        $config = new Configuration(
            clientTimeout: 10, // default in seconds, when contacting the Telegram API
        );

        $bot = new Nutgram($_ENV['tgToken'], $config);
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
<b>I am your airtime/data subscription bot</b>. 


You can recharge your data subscription right here on Telegram. Just send me the keyword Data then data network, data size, and your phone number in the format 'Data Network DataSize PhoneNumber' <i>(e.g.Data mtn 1gb 1234567890)</i>. Follow the format on the price list for network/size


Also recharge airtime on your phone right here on Telegram. Just send me the keyword Airtime then network, amount, and your phone number in the format 'Airtime Network Amount PhoneNumber' <i>(e.g.Airtime mtn 100 1234567890)</i>. Follow the format on the price list for network code name


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

Buy data using the format <i>(e.g.Data mtn 1gb 1234567890)</i>

Buy airtime using the format <i>(e.g.Airtime mtn 100 1234567890)</i>

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

        $bot->onText('(data|Data) {net} {amt} ([0-9]+)', function (Nutgram $bot, $c, $net, $amt, $phn) {
            $user = $bot->get('user');
            $enoughBalance = $this->compareBalance($user, "{$net}-{$amt}", 'data');
            if($enoughBalance){
                $bot->sendMessage(
                text: "Are you certain that you want to recharge {$net} {$amt} for {$phn}",
                reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true, input_field_placeholder: 'Do Not Type anything, Choose from options', selective: true,)->addRow(
                    KeyboardButton::make("✔️ ".strtoupper($net)." ".strtoupper($amt)." {$phn}"),
                    KeyboardButton::make("❌ ".strtoupper($net)." ".strtoupper($amt)." {$phn}"),
                ));
            }else{
                $bot->sendMessage("Sorry you can't buy {$net} {$amt} as your balance is {$user['balance']} & it's not enough. /fund your /wallet");
            }
           
        });


        $bot->onText('(airtime|Airtime) {net} {amt} ([0-9]+)', function (Nutgram $bot, $c, $net, $amt, $phn) {
            $user = $bot->get('user');
            $enoughBalance = $this->compareBalance($user, "{$net}-{$amt}", 'airtime');
            if($enoughBalance){
                $bot->sendMessage(
                text: "Are you certain that you want to recharge ₦{$amt} {$net} for {$phn}",
                reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true, input_field_placeholder: 'Do Not Type anything, Choose from options', selective: true,)->addRow(
                    KeyboardButton::make("₦ ".strtoupper($amt)." ".strtoupper($net)." {$phn}✔️"),
                    KeyboardButton::make("₦ ".strtoupper($amt)." ".strtoupper($net)." {$phn}❌"),
                ));
            }else{
                $bot->sendMessage("Sorry you can't buy {$net} ₦{$amt} as your balance is ₦{$user['balance']} & it's not enough. /fund your /wallet");
            }
           
        });

        $bot->onText('✔️ {net} {amt} ([0-9]+)', function (Nutgram $bot, $net, $amt, $phn) {
            $user = $bot->get('user');
            // $this->rechargeData($user, $net, $amt, $phn);
           $bot->sendMessage("🏎️Your data is on its way 🏎️");
        });

        $bot->onText('❌ {net} {amt} ([0-9]+)', function (Nutgram $bot, $net, $amt, $phn) {
           $bot->sendMessage("You just cancelled the recharge of {$net} {$amt} for {$phn}");
        });


        $bot->onText('₦ {amt} {net} ([0-9]+)✔️', function (Nutgram $bot, $amt, $net, $phn) {
            $user = $bot->get('user');
            // $this->rechargeData($user, $net, $amt, $phn);
            $this->rechargeAirtime($user, $net, $amt, $phn);
           $bot->sendMessage("🏎️Your airtime is will get to you soon!! 🏎️");
        });

        $bot->onText('₦ {amt} {net} ([0-9]+)❌', function (Nutgram $bot, $amt, $net, $phn) {
           $bot->sendMessage("You just cancelled the recharge of {$net} ₦{$amt} for {$phn}");
        });

        $bot->run();
    }
}
