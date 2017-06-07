<?php

$accessToken = getenv('CHANNEL_ACCESS_TOKEN');

// apiから送信されて来たイベントオブジェクトを取得
$jsonString = file_get_contents('php://input');
error_log($jsonString);
$jsonObj = json_decode($jsonString);

// イベントオブジェクトから必要な情報を抽出
$message = $jsonObj->{"events"}[0]->{"message"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$type = $message->{"type"};

// APIからメッセージを取得
$url = 'https://api.line.me/v2/bot/message/reply';

if ($type == "sticker") {
    $messageData = [
        'messages' => [
            [
                'type' => 'sticker',
                'packageId' => '3',
                'stickerId' => '234'
            ]
        ]
    ];
} else {
    $messageData = [
        'messages' => [
            [
                'type' => 'text',
                'text' => chat($message->{"text"})
            ]
        ]
    ];
}

$response = [
    'replyToken' => $replyToken,
    'messages' => [$messageData]
];


error_log(json_encode($response));


// curlを用いてメッセージを返信する
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
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



// リクルートのAPI(A3RT)
function getTalk($text) {
    // A3RT TalkAPI
    $url = "https://api.a3rt.recruit-tech.co.jp/talk/v1/smalltalk";
    
    $data = [
        'apikey' => getenv('API_KEY'),
        'query' => $text
    ];
    
    // セッションを初期化
	$conn = curl_init();
	// オプション
	curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($conn, CURLOPT_URL,  $url);
	curl_setopt($conn, CURLOPT_POST, true);
	curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
	// 実行
	$res = curl_exec($conn);
	// close
	curl_close($conn);
    
    //$res = mb_convert_encoding($res,'UTF-8');
	$obj = json_decode($res, false);
	$reply = $obj->results[0]->reply;
	return $reply;
}

//docomoのAPI
function chat($text) {
    // docomo chatAPI
    $api_key = getenv('DOCOMO_API_KEY');;
    $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
    $req_body = array('utt' => $text);
    
    $headers = array(
        'Content-Type: application/json; charset=UTF-8',
    );
    $options = array(
        'http'=>array(
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => json_encode($req_body),
            )
        );
    $stream = stream_context_create($options);
    $res = json_decode(file_get_contents($api_url, false, $stream));
 
    return $res->utt;
}