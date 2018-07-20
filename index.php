<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \Dotenv\Dotenv;

// set false for production
$pass_signature = true;

//env

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

// set LINE channel_access_token and channel_secret
$channel_access_token = $_ENV['CHANNEL_ACCESS_TOKEN'];
$channel_secret = $_ENV['CHANNEL_SECRET'];

// inisialisasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);


$configs =  [
    'settings' => ['displayErrorDetails' => true],
];

$app = new Slim\App($configs);

//home route
$app->get('/', function($request, $response) use($channel_access_token, $channel_secret)
{
  echo "OK\n";
});

$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
  // get request body and line signature header

  $body        = file_get_contents('php://input');
  $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

  // log body and signature
  file_put_contents('php://stderr', 'Body: '.$body);

  if($pass_signature === false)
  {
      // is LINE_SIGNATURE exists in request header?
      if(empty($signature)){
          return $response->withStatus(400, 'Signature not set');
      }

      // is this request comes from LINE?
      if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
          return $response->withStatus(400, 'Invalid signature');
      }
  }

      $data = json_decode($body, true);
      if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
              if (mb_strtolower(substr($event['message']['text'], 0, 6)) == 'apakah'
                  && substr($event['message']['text'], -1) == '?') {
                  $temp = rand(0, 2);
                  if ($temp == 0) {
                    # code...
                    $result = $bot->replyText($event['replyToken'], 'Tidak');

                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                  } else if ($temp == 1) {
                    # code...
                    $result = $bot->replyText($event['replyToken'], 'Ya');

                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                  } else if ($temp == 2) {
                    # code...
                    $result = $bot->replyText($event['replyToken'], 'Bisa Jadi');

                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                  }
              } else if ($event['message']['text'] == "halo") {
                // code...
                $result = $bot->replyText($event['replyToken'], 'Halo juga');
              //case 1a "lihat lokasi rs"
              } else if (mb_strtolower($event['message']['text']) == "lihat lokasi rs"
                          || mb_strtolower($event['message']['text']) == "lokasi rs"
                          || mb_strtolower($event['message']['text']) == "lihat rs") {
                // code
                //buat template message untuk menampung banyak message type
                  //$multiMessageBuilder = new MultiMessageBuilder();
                  //pesan pertama
                  $imageUrl = 'https://meepwnd-health-assistant.herokuapp.com/static/rs-logo-1.png';
                  $buttonTemplateBuilder = new ButtonTemplateBuilder(
                    'RSUB',
                    'Jl. Soekarno - Hatta',
                    $imageUrl,
                    [
                      new UriTemplateActionBuilder('Lokasi', 'https://www.google.com/maps/place/Rumah+Sakit+Universitas+Brawijaya/')
                    ]
                  );
                  $templateMessage = new TemplateMessageBuilder('Lokasi Rumah Sakit', $buttonTemplateBuilder);
                  $res = $bot->replyMessage($event['replyToken'], $templateMessage);
              }
            }
        }
      }
});

$app->run();
?>
