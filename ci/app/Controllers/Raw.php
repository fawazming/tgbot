// https://api.telegram.org/bot6590399869:AAF6tg-t18MmqV_0It1sFRJXvdTSeiBGbrg/setWebhook?url=https://tgbot.sgm.ng/telegram


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


        // $bot->onCommand('user', function (Nutgram $bot) {
        //     $user = $bot->get('user');
        //     $bot->sendMessage("Hi user {$user['fname']}!");
        // });

        // $bot->onCommand('opt', function(Nutgram $bot){
        //     $bot->sendMessage(
        //         text: 'Welcome!',
        //         reply_markup: InlineKeyboardMarkup::make()
        //             ->addRow(
        //                 InlineKeyboardButton::make('Google', url:'https://google.com'),
        //                 InlineKeyboardButton::make('VoteBot', url:'tg://resolve?domain=vote'),
        //             )
        //     );
        // });

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
