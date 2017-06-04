<?php

$accessToken = getenv('CHANNEL_ACCESS_TOKEN');

// apiから送信されて来たイベントオブジェクトを取得
$jsonString = file_get_contents('php://input');
error_log($jsonString);
$jsonObj = json_decode($jsonString);

// イベントオブジェクトから必要な情報を抽出
$message = $jsonObj->{"events"}[0]->{"message"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};


// ユーザーからのメッセージに対し、おうむ返しをする
$messageData = [
    'replyToken' => $replyToken,
    'messages' => [
      [
        'type' => 'text',
        'text' => $message->{"text"}
      ]
    ]
];

error_log(json_encode($response));


// curlを用いてメッセージを返信する
$ch = curl_init('https://api.line.me/v2/bot/message/reply');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
));
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
curl_setopt($ch, CURLOPT_PROXYPORT, '80');
curl_setopt($ch, CURLOPT_PROXY, getenv('FIXIE_URL'));
$result = curl_exec($ch);
error_log($result);
curl_close($ch);