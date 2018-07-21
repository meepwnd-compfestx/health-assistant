<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \Dotenv\Dotenv;

include('functional.php');
// set false for production
$pass_signature = true;

//load env
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

require 'db.php';

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
$app->get('/', function($request, $response) use($channel_access_token, $channel_secret, $pdo)
{
  echo "OK\n";
  $statement = executeQuery($pdo, "SELECT * FROM POLI");
  $rslt = $statement->fetchAll();
  $rslt = array();
  foreach ($rslt as $row) {
    // code...
    $tet = $row->ID.". ".$row->NAMA_POLI."\n";
  }
  echo $tet;
});

$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $pdo)
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
              } else if (mb_strtolower($event['message']['text']) == "halo"
                          || mb_strtolower($event['message']['text']) == "halo bot") {
                // code...
                $result = $bot->replyText($event['replyToken'], 'Hai ! Aku health-assistant siap membantu anda ! '.emoticonBuilder("10008D").
              "\nMaaf, saat ini saya hanya bisa menampilkan jadwal praktek dari Rumah Sakit Universitas Brawijaya Malang :(".
              "\nCukup ketik saja 'Lihat lokasi RS' jika ingin melihat lokasinya.");
              //case 1a "lihat lokasi rs"
              } else if (mb_strtolower($event['message']['text']) == "lihat lokasi rs"
                          || mb_strtolower($event['message']['text']) == "lokasi rs"
                          || mb_strtolower($event['message']['text']) == "lihat rs") {
                // code
                //buat template message untuk menampung banyak message type (multiple)
                  $multiMessageBuilder = new MultiMessageBuilder();

                  //button message
                  $imageUrl = 'https://meepwnd-health-assistant.herokuapp.com/static/rs-logo-1.png';
                  $buttonTemplateBuilder = new ButtonTemplateBuilder(
                    'RSUB',
                    'Jl. Soekarno - Hatta',
                    $imageUrl,
                    [
                      new UriTemplateActionBuilder('Lokasi', 'https://www.google.com/maps/place/Rumah+Sakit+Universitas+Brawijaya/@-7.9408252,112.6210425,18z/data=!4m5!3m4!1s0x2dd629e076b6db3f:0xe31a591bdc49e6fe!8m2!3d-7.9409421!4d112.6217452')
                    ]
                  );
                  $templateMessage = new TemplateMessageBuilder('Lokasi Rumah Sakit', $buttonTemplateBuilder);

                  $multiMessageBuilder->add($templateMessage)
                  ->add(new TextMessageBuilder("Apakah anda ingin melihat jadwal prakteknya juga?".
                    "\nKetik saja 'lihat jadwal praktek'"));
                  $res = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
              } else if (mb_strtolower($event['message']['text']) == "lihat jadwal praktek"
                          || mb_strtolower($event['message']['text']) == "lihat jadwal") {

                $text = "Mau lihat jadwal praktek poli apa?";
                $statement = executeQuery($pdo, "select * from poli");
                $rslt = $statement->fetchAll();
                $rslt = array();
                foreach ($rslt as $row) {
                  // code...
                  $text = $text.$row->id.". ".$row->nama_poli."\n";
                }
                $text = $text."Ketikkan nomornya saja.";
                //mulai session
                //session_start();
                //$_SESSION['CONV'] = "TRUE";
                $bot->replyText($event['replyToken'], $text);
              } /*else if ($_SESSION['CONV'] == 'TRUE' &&
                  ($event['message']['text'] <= 1 && $event['message']['text'] >= 10)) {
                // code...

              }*/
            }
        }
      }
});

$app->run();
?>
