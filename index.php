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

include('functional.php');
// set false for production
$pass_signature = true;

require ("db.php");

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

$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $conn)
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
            if (substr($event['message']['text'], 0, 19) != "lihat jadwal dokter"
                || substr($event['message']['text'], 0, 13) != "jadwal dokter")
            {
              if (mb_strtolower(substr($event['message']['text'], 0, 6)) == 'apakah'
                  && substr($event['message']['text'], -1) == '?') {
                  //mengacak jawaban kerang kampang
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
              } else if (mb_strtolower($event['message']['text']) == "oyi"
                      || mb_strtolower($event['message']['text']) == "oyii"
                      || mb_strtolower($event['message']['text']) == "oyi lur") {

                $result = $bot->replyText($event['replyToken'], 'oyiiiii sam '.emoticonBuilder("100078"));

              } else if (mb_strtolower($event['message']['text']) == "halo"
                          || mb_strtolower($event['message']['text']) == "halo bot") {
                  // code...
                  $multiMessageBuilder = new MultiMessageBuilder();
                    //carousel message
                  $carouselColumn = array();
                  $counter = 0;

                  $q = "select * from rumah_sakit";

                  $result = executeQuery($conn, $q);
                  if ($result) {
                    foreach ($result as $row) {
                      $carouselColumn[$counter] = new CarouselColumnTemplateBuilder('Rumah Sakit '.$row['id'], $row['nama'], $row['link_gambar'], [
                          new MessageTemplateActionBuilder('Cari Lokasi', '/detaillokasi'.$row['id'])
                        ]);
                        $counter++;
                    }
                      $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumn);
                      $templateMessage = new TemplateMessageBuilder('Daftar Rumah Sakit', $carouselTemplateBuilder);
                  } else {
                    $templateMessage = new TextMessageBuilder("Tidak ditemukan rumah sakitnya :(");
                  }
                    $multiMessageBuilder->add(new TextMessageBuilder("Halo juga !!\nBerikut adalah daftar rumah sakit di kota Malang "))
                    ->add($templateMessage);
                    $res = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

//      CASE
//      1a

                } else if (substr($event['message']['text'], 0, 13) == "/detaillokasi") {
                // code
                $id = substr($event['message']['text'], 13, 1);
                //buat template message untuk menampung banyak message type (multiple)
                  $multiMessageBuilder = new MultiMessageBuilder();

                  $result = executeQuery($conn, "select * from rumah_sakit where id=".$id);
                  //button message
                  foreach ($result as $row) {
                    $buttonTemplateBuilder = new ButtonTemplateBuilder(
                      'Rumah Sakit '.$row['id'],
                      $row['nama'],
                      $row['link_gambar'],
                      [
                        new UriTemplateActionBuilder('Lokasi', $row['link_gmaps'])
                      ]
                    );
                  }
                  $templateMessage = new TemplateMessageBuilder('Lokasi Rumah Sakit', $buttonTemplateBuilder);

                  $multiMessageBuilder->add($templateMessage)
                  ->add(new TextMessageBuilder("Apakah anda ingin melihat jadwal prakteknya juga?".
                    "\nKetik saja\n\n'lihat jadwal praktek'"));
                  $res = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

//     CASE
//     1b
              } else if (mb_strtolower($event['message']['text']) == "lihat jadwal praktek"
                          || mb_strtolower($event['message']['text']) == "lihat jadwal") {

                $text = "Mau lihat jadwal praktek poli apa?\n";
                $array = executeQuery($conn, "select * from public.poli");

                foreach ($array as $row) {
                  // code...
                  $text = $text.$row['id'].". ".$row['nama_poli']."\n";
                }
                $text = $text."Ketikkan nomornya saja.";
                //mulai session
                /*session_start();
                $_SESSION['CONV'] = "TRUE";*/
                $bot->replyText($event['replyToken'], $text);

//      CASE
//      1c


              } else if ($event['message']['text'] >= 1 && $event['message']['text'] <= 19) {
                  // code...
                $multiMessageBuilder = new MultiMessageBuilder();
                  //carousel message
                $imageUrl = 'https://meepwnd-health-assistant.herokuapp.com/static/open.png';
                $carouselColumn = array();
                $counter = 0;

              $q = "select * from public.jadwal where id_poli=".$event['message']['text']." and hari like ".generateDay(date('N'));

              $result = executeQuery($conn, $q);
                if ($result) {
                  foreach ($result as $row) {
                    $carouselColumn[$counter] = new CarouselColumnTemplateBuilder($row['jam'], $row['nama_dokter'], $imageUrl, [
                        new MessageTemplateActionBuilder('Cek Detail', '/detailjadwal'.$row['id'])
                      ]);
                  $counter++;
                  }
                    $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumn);
                  $templateMessage = new TemplateMessageBuilder('Jadwal Praktek', $carouselTemplateBuilder);
                } else {
                  $templateMessage = new TextMessageBuilder("Maaf, hari ini (".mb_strtolower(generateDay(date('N'))).") tidak ada dokter yang membuka jadwal praktek.".emoticonBuilder("100010"));
                }
                  $q1 = "select nama_poli from poli where id=".$event['message']['text'];
                  $result1 = executeQuery($conn, $q1);
                  $selectedPoli = "";
                  foreach ($result1 as $row) {
                  $selectedPoli = $row['nama_poli'];
                }
                  $multiMessageBuilder->add(new TextMessageBuilder("Berikut adalah jadwal dari poli ".$selectedPoli." untuk hari ini."))
                ->add($templateMessage);
                  $res = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
              } else if (substr($event['message']['text'], 0, 13) == "/detailjadwal") {
                $query = "select b.nama_poli, a.nama_dokter, a.hari, a.jam from jadwal a join poli b on a.id_poli = b.id where a.id="
                    .substr($event['message']['text'], 13, 3);
                $result = executeQuery($conn, $query);
                foreach($result as $row){
                  $bot->replyText($event['replyToken'], "-------Detail Jadwal Praktek-------\n"
                    .emoticonBuilder("10004E")." Nama Poli: ".$row['nama_poli']."\n"
                    .emoticonBuilder("100041")." Nama Dokter: ".$row['nama_dokter']."\n"
                    .emoticonBuilder("1000A9")." Hari: ".$row['hari']."\n"
                    .emoticonBuilder("100071")." Jam: ".$row['jam']);
                }
              }
            } else if (substr($event['message']['text'], 0, 19) == "lihat jadwal dokter"
                || substr($event['message']['text'], 0, 13) == "jadwal dokter")
            {

            }
          }
      }
    }
});

$app->run();
?>
