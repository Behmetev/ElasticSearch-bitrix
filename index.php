<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//error_reporting(-1);

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
    ->build();

$start = microtime(true);
// фильтруем каталог по свойствам, используем только активные на данный момент товары
$arFilter = array(
    "IBLOCK_ID" => 18,
    "ACTIVE_DATE" => "Y",
    "ACTIVE" => "Y",
);
// группировку не используем
$arGroup = array(//"nTopCount" => 50
);
// добавляем нужные свойства в индекс
$arSelect = array(
    "ID",
    "DETAIL_PICTURE",
    "NAME",
    "IBLOCK_SECTION_ID",
    "DETAIL_TEXT",
    "DETAIL_PAGE_URL",
    "PROPERTY_CML2_ARTICLE",
    "PROPERTY_TOVAR_MARKETPLEYS",
    "PROPERTY_TOVARMIKROSA",
    "PROPERTY_OTOBRAZHAT_OZHIDAEMYY_PRIKHOD",
    "PROPERTY_MATERIAL"
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
        'id' => $arFields["ID"],
        'body' => [
            'NAME' => $arFields["NAME"],
            'ARTICLE' => $arFields["PROPERTY_CML2_ARTICLE_VALUE"],
            'URL' => $arFields["DETAIL_PAGE_URL"],
            'DETAIL_PICTURE' => CFile::GetPath($arFields["DETAIL_PICTURE"]),
            'TOVARMIKROSA' => $arFields["PROPERTY_TOVARMIKROSA_VALUE"],
            'MARKETPLEYS' => $arFields["PROPERTY_TOVAR_MARKETPLEYS_VALUE"],
            'OZHIDAEMYY_PRIKHOD' => $arFields["PROPERTY_OTOBRAZHAT_OZHIDAEMYY_PRIKHOD_VALUE"],
            //'DETAIL_TEXT' => $arFields["DETAIL_TEXT"],
        ]
    ];

    $response = $client->index($params);
}

// считаем время выплнения скрипта
$date = date("m.d.y");
$time = date("H:i:s");
echo $line = $date . " - " . $time . ': Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';