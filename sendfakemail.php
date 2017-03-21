<?php
define('API_KEY','توکن');
//----######------
function makereq($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}
//##############=--API_REQ
function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }
  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }
  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = "https://api.telegram.org/bot".API_KEY."/".$method.'?'.http_build_query($parameters);
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  return exec_curl_request($handle);
}
//----######------
//---------
$update = json_decode(file_get_contents('php://input'));
var_dump($update);
//=========
$chat_id = $update->message->chat->id;
$message_id = $update->message->message_id;
$from_id = $update->message->from->id;
$name = $update->message->from->first_name;
$username = $update->message->from->username;
$textmessage = isset($update->message->text)?$update->message->text:'';
$txtmsg = $update->message->text;
$reply = $update->message->reply_to_message->forward_from->id;
$stickerid = $update->message->reply_to_message->sticker->file_id;
$admin = ایدی عددی ادمین;
$step = file_get_contents("data/".$from_id."/step.txt");
$mtn = file_get_contents("data/".$from_id."/mtn.txt");
$mail = file_get_contents("data/".$from_id."/mail.txt");
$subject = file_get_contents("data/".$from_id."/subject.txt");
$from = file_get_contents("data/".$from_id."/from.txt");
$ban = file_get_contents("data/banlist.txt");
//-------
function SendMessage($ChatId, $TextMsg)
{
 makereq('sendMessage',[
'chat_id'=>$ChatId,
'text'=>$TextMsg,
'parse_mode'=>"MarkDown"
]);
}
function SendSticker($ChatId, $sticker_ID)
{
 makereq('sendSticker',[
'chat_id'=>$ChatId,
'sticker'=>$sticker_ID
]);
}
function Forward($KojaShe,$AzKoja,$KodomMSG)
{
makereq('ForwardMessage',[
'chat_id'=>$KojaShe,
'from_chat_id'=>$AzKoja,
'message_id'=>$KodomMSG
]);
}
function save($filename,$TXTdata)
	{
	$myfile = fopen($filename, "w") or die("Unable to open file!");
	fwrite($myfile, "$TXTdata");
	fclose($myfile);
	}
if (strpos($ban , "$from_id") !== false  ) {
SendMessage($chat_id,"😊 شما از ربات مسدود شده اید !
📢 لطفا دیگه پیامی ارسال نکنید . . .");
	}
	
elseif(isset($update->callback_query)){
    $callbackMessage = '';
    var_dump(makereq('answerCallbackQuery',[
        'callback_query_id'=>$update->callback_query->id,
        'text'=>$callbackMessage
    ]));
    $chat_id = $update->callback_query->message->chat->id;
    
    $message_id = $update->callback_query->message->message_id;
    $data = $update->callback_query->data;
}
//
elseif ($textmessage == '🔙 برگشت') {
save("data/$from_id/step.txt","none");
var_dump(makereq('sendMessage',[
         'chat_id'=>$update->message->chat->id,
         'text'=>"🔃 به منوی اصلی خوش آمدید",
  'parse_mode'=>'MarkDown',
         'reply_markup'=>json_encode([
             'keyboard'=>[
                [
                   ['text'=>"📮 ارسال ایمیل"]
                ],
           [
                ['text'=>"🗣 پشتیبانی"],['text'=>"📩 ارسال نظر"]
            ]
                
             ],
             'resize_keyboard'=>false
         ])
      ]));
}

elseif ($textmessage == '📋 آمار ربات' && $from_id == $admin) {
 $usercount = -1;
 $fp = fopen( "data/users.txt", 'r');
 while( !feof( $fp)) {
      fgets( $fp);
      $usercount ++;
 }
 fclose( $fp);
 SendMessage($chat_id,"📋 تعداد اعضای ربات : ".$usercount."
");
 }
 elseif ($textmessage == '🗣 پیام همگانی')
if ($from_id == $admin)
{
save("data/$from_id/step.txt","sendtoall");
var_dump(makereq('sendMessage',[
'chat_id'=>$update->message->chat->id,
'text'=>"🔸 لطفا پیام خود را ارسال کنید :",
'parse_mode'=>'MarkDown',

                               ]
        )
    );
}
else
{
SendMessage($chat_id,"😐📛شما ادمین نیستید.");
}
elseif ($step == 'sendtoall')
{
SendMessage($chat_id,"`📢 در حال ارسال . . .`");
save("data/$from_id/step.txt","none");
$fp = fopen( "data/users.txt", 'r');
while( !feof( $fp)) {
$ckar = fgets( $fp);
SendMessage($ckar,$textmessage);
}
SendMessage($chat_id,"✅ پیام شما به همه ی کاربران ربات ارسال گردید.");
}
elseif ($textmessage == '📢 فروارد همگانی')
if ($from_id == $admin)
{
save("data/$from_id/step.txt","fortoall");
var_dump(makereq('sendMessage',[
'chat_id'=>$update->message->chat->id,
'text'=>"🔹 لطفا پیام خود را فوروارد کنید :",
'parse_mode'=>'MarkDown',

                               ]
        )
    );
}
else
{
SendMessage($chat_id,"😐📛شما ادمین نیستید.");
}
elseif ($step == 'fortoall')
{
SendMessage($chat_id,"`📢 در حال فروارد پیام . . .`");
save("data/$from_id/step.txt","none");
$forp = fopen( "data/users.txt", 'r');
while( !feof( $forp)) {
$fakar = fgets( $forp);
Forward($fakar, $chat_id,$message_id);
  }
   makereq('sendMessage',[
   'chat_id'=>$chat_id,
   'text'=>"✅ پیام شما به همه ی کاربران ربات فروارد شد.",
   ]);
}
elseif($textmessage == '/start' )
{
if (!file_exists("data/$from_id/step.txt")) {
mkdir("data/$from_id");
save("data/$from_id/step.txt","none");
save("data/$from_id/mail.txt","ایمیل");
save("data/$from_id/from.txt","از طرف");
save("data/$from_id/mtn.txt","متن");
save("data/$from_id/subject.txt","موضوع");
$myfile2 = fopen("data/users.txt", "a") or die("Unable to open file!"); 
fwrite($myfile2, "$from_id\n");
fclose($myfile2);
}

var_dump(makereq('sendMessage',[
         'chat_id'=>$update->message->chat->id,
         'text'=>"✴️ سلام $name ! به ربات ارسال ایمیل فیک خوش آمدید.",
  'parse_mode'=>'HTML',
         'reply_markup'=>json_encode([
             'keyboard'=>[
                [
                   ['text'=>"📮 ارسال ایمیل"]
                ],
           [
                ['text'=>"🗣 پشتیبانی"],['text'=>"📩 ارسال نظر"]
            ]
                
             ],
             'resize_keyboard'=>false
         ])
      ]));
Forward($chat_id,"@idch:/",0);  
}

elseif($textmessage == '🗣 پشتیبانی' ) {
var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"📢 برای ارتباط برقرار کردن با پشتیبانی یکی از دکمه های زیر را انتخاب کنید.",
  'parse_mode'=>'MarkDown',
         'reply_markup'=>json_encode([
             'inline_keyboard'=>[
                [
                   ["text"=>"👤 پشتیبانی",'url'=>"https://telegram.me/SudoAmin"]
                ],
                [
				   ["text"=>"🤖 ربات پشتیبانی",'url'=>"https://telegram.me/AminRezaAslani_bot"]
            ]
                
             ],
             'resize_keyboard'=>true
         ])
      ]));
}
elseif($textmessage == '/bi') {
Forward($chat_id,"@idch:/",0);
}
//
elseif ($step == 'from') {
	$txtmsg = $textmessage ;
        save("data/$from_id/step.txt","set mtn");
		save("data/$from_id/mail.txt","$txtmsg");
		var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"⚜ ایمیل از طرف چه ایمیلی ارسال شود ؟
`مثال :`
FakeMail@Bot.Com",
			'parse_mode'=>'MarkDown',
        	'reply_markup'=>json_encode([
            	'keyboard'=>[
				
                 [
                   ['text'=>'🔙 برگشت']
                ]
            	],
            	'resize_keybord'=>false
       		])
    		]));
	}
elseif ($step == 'set mtn') {
	$txtmsg = $textmessage ;
		save("data/$from_id/step.txt","subject");
		save("data/$from_id/from.txt","$txtmsg");
		var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"😐❤️ لطفا متن مورد نظر خود را وارد کنید :",
			'parse_mode'=>'MarkDown',
        	'reply_markup'=>json_encode([
            	'keyboard'=>[
				
                 [
                   ['text'=>'🔙 برگشت']
                ]
            	],
            	'resize_keybord'=>false
       		])
    		]));
	}
	elseif ($step == 'subject') {
		$txtmsg = $textmessage ;
		save("data/$from_id/mtn.txt","$txtmsg");
			save("data/$from_id/step.txt","send");
		
		var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"😶 موضوع ایمیل را وارد کنید :",
			'parse_mode'=>'MarkDown',
        	'reply_markup'=>json_encode([
            	'keyboard'=>[
				[
                   ['text'=>'🔙 برگشت']
                ]
            	],
            	'resize_keybord'=>false
       		])
    		]));			
	}
	elseif ($step == 'send') {
		$txtmsg = $textmessage ;
		save("data/$from_id/step.txt","none");
		save("data/$from_id/subject.txt","$txtmsg");

		
		var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"🎐 آیا ایمیل مورد نظر ارسال شود؟",
			'parse_mode'=>'HTML',
        	'reply_markup'=>json_encode([
            	'keyboard'=>[
				[
                   ['text'=>"📩 ارسال شود !"]
                ],
           [
                ['text'=>"📭 ارسال نشود !"]
            ]
                
             ],
             'resize_keyboard'=>false
         ])
      ]));	
			
	}
	//
elseif ($textmessage == '📮 ارسال ایمیل' ) {
	save("data/$from_id/step.txt","from");
		var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"😊 لطفا ایمیل دریافت کننده را وارد کنید :",
			'parse_mode'=>'MarkDown',
        	'reply_markup'=>json_encode([
            	'keyboard'=>[
				
                 [
                   ['text'=>'🔙 برگشت']
                ]
            	],
            	'resize_keyboard'=>false
       		])
    		]));
	}
elseif($textmessage == '📩 ارسال شود !') {
save("data/$from_id/step.txt","none");
var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"🎈 ایمیل مورد نظر با اطلاعات زیر ارسال شد !

از طرف : $from
موضوغ ایمیل : $subject
ایمیل دریافت کننده : $mail
متن : $mtn",
  'parse_mode'=>'HTML',
 ]
        )
    );
file_get_contents("http://api.metti.ir/sheri/?to=$mail&from=$from&subject=$subject&message=$mtn");
	var_dump(makereq('sendMessage',[
         'chat_id'=>$update->message->chat->id,
         'text'=>"🔃 یکی از دکمه های زیر را انتخاب کنید :",
  'parse_mode'=>'MarkDown',
         'reply_markup'=>json_encode([
             'keyboard'=>[
                [
                   ['text'=>"📮 ارسال ایمیل"]
                ],
           [
                ['text'=>"🗣 پشتیبانی"],['text'=>"📩 ارسال نظر"]
            ]
                
             ],
             'resize_keyboard'=>false
         ])
      ]));
}
elseif($textmessage == '📭 ارسال نشود !') {
save("data/$from_id/step.txt","none");
var_dump(makereq('sendMessage',[
         'chat_id'=>$update->message->chat->id,
         'text'=>"😁 عملیات ارسال لغو شد !
🔃 یکی از دکمه های زیر را انتخاب کنید :",
  'parse_mode'=>'MarkDown',
         'reply_markup'=>json_encode([
             'keyboard'=>[
                [
                   ['text'=>"📮 ارسال ایمیل"]
                ],
           [
                ['text'=>"🗣 پشتیبانی"],['text'=>"📩 ارسال نظر"]
            ]
                
             ],
             'resize_keyboard'=>false
         ])
      ]));

}
//
elseif ($step == 'nazar') {
		$txtmsg = $textmessage ;
		save("data/$from_id/step.txt","none");
var_dump(makereq('sendMessage',[
        	'chat_id'=>$admin,
        	'text'=>"📩 نظر جدیدی ثبت شد :

➖ نام کاربر : $name
➖ یوزرنیم کاربر : @$username
➖ ایدی عددی کاربر : $from_id

نظر کاربر :
$txtmsg",
  'parse_mode'=>'HTML',
 ]
        )
    );
var_dump(makereq('sendMessage',[
         'chat_id'=>$update->message->chat->id,
         'text'=>"😃❤️ نظر شما با موفقیت ارسال شد.",
  'parse_mode'=>'MarkDown',
         'reply_markup'=>json_encode([
             'keyboard'=>[
                [
                   ['text'=>"📮 ارسال ایمیل"]
                ],
           [
                ['text'=>"🗣 پشتیبانی"],['text'=>"📩 ارسال نظر"]
            ]
                
             ],
             'resize_keyboard'=>false
         ])
      ]));			
	}
elseif ($textmessage == '📩 ارسال نظر' ) {
	save("data/$from_id/step.txt","nazar");
		var_dump(makereq('sendMessage',[
        	'chat_id'=>$update->message->chat->id,
        	'text'=>"📩 لطفا نظر خود را در رابطه با ربات بیان کنید :",
			'parse_mode'=>'MarkDown',
        	'reply_markup'=>json_encode([
            	'keyboard'=>[
				
                 [
                   ['text'=>'🔙 برگشت']
                ]
            	],
            	'resize_keyboard'=>false
       		])
    		]));
	}
elseif ($textmessage == '/panel')
 if ($from_id == $admin) {
var_dump(makereq('sendMessage',[
          'chat_id'=>$update->message->chat->id,
          'text'=>"به پنل مدیریت ربات خوش آمدید !",
  'parse_mode'=>'MarkDown',
         'reply_markup'=>json_encode([
             'keyboard'=>[
                [
                   ['text'=>"📋 آمار ربات"],['text'=>"🗣 پیام همگانی"]
				],
                [
				   ['text'=>"📢 فروارد همگانی"]
				],
                [
				   ['text'=>"🔙 برگشت"]
            ]
             ],
             'resize_keyboard'=>false
         ])
      ]));
}
else
{
SendMessage($chat_id,"😐📛شما ادمین نیستید.");
}
elseif (strpos($textmessage , "/ban" ) !== false ) {
if ($from_id == $admin) {
$text = str_replace("/ban","",$textmessage);
$myfile2 = fopen("data/banlist.txt", 'a') or die("Unable to open file!");	
fwrite($myfile2, "$text\n");
fclose($myfile2);
SendMessage($admin,"📢 کاربر$text از ربات بن شد.

برای آن بن کردن کاربر از دستور زیر استفاده کنید :
/unban$text");
}
else {
SendMessage($chat_id,"⛔️ شما ادمین نیستید.");
}
}

elseif (strpos($textmessage , "/unban" ) !== false ) {
if ($from_id == $admin) {
$text = str_replace("/unban","",$textmessage);
			$newlist = str_replace($text,"",$ban);
			save("data/banlist.txt",$newlist);
SendMessage($admin,"📢 کاربر$text از ربات آن بن شد.");
}
else {
SendMessage($chat_id,"⛔️ شما ادمین نیستید.");
}
}

else
{
SendMessage($chat_id,"😁 یافت نشد !");
}
?>