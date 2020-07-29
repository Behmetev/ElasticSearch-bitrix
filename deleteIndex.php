<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//error_reporting(-1);

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
    ->build();

$start = microtime(true);
$arFilter = array(
    "IBLOCK_ID" => 18,
    "ACTIVE_DATE" => "N",
    "ACTIVE" => "N",
);

$arGroup = array(
    //"nTopCount" => 10000
);

$arSelect = array(
    "ID"
);

$res = CIBlockElement::GetList(
    array(),
    $arFilter,
    false,
    $arGroup,
    $arSelect
);

while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();

    $params = [
        'index' => 'catalog',
        'type' => 'item',
        'id' => $arFields["ID"]
    ];
/*
    try {
        $response = $client->delete($params);
        echo $arFields["ID"] . " - удалён из индекса" . "<br>";
    } catch (Exception $e) {
        echo $arFields["ID"] . " - нет id (Удалить из индекса не получлось)" . "<br>";
    }
*/
}

$date = date("m.d.y");
$time = date("H:i:s");

echo $line = $date . " - " . $time . ': Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';