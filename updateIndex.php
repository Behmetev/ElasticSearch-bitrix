<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
    ->build();

$startIndex = microtime(true);

$date = date('Y-m-d H:i:s');
$backInTime = '-30 minutes';

//echo date('d.m.Y H:i:s', strtotime($date . $backInTime));

$arFilter = array(
    "IBLOCK_ID" => 18,
    "ACTIVE_DATE" => "Y",
    "ACTIVE" => "Y",
    "PROPERTY_OTOBRAZHAT_OZHIDAEMYY_PRIKHOD_VALUE" => "Нет",
    ">TIMESTAMP_X" => date('d.m.Y H:i:s', strtotime($date . $backInTime)),
);

$arGroup = array(//"nTopCount" => 50
);

$arSelect = array(
    "ID",
    "DETAIL_PICTURE",
    "NAME",
    "IBLOCK_SECTION_ID",
    "DETAIL_TEXT",
    "DETAIL_PAGE_URL",
    "PROPERTY_CML2_ARTICLE",
    "PROPERTY_TOVAR_MARKETPLEYS",
    "PROPERTY_TOVARMIKROSA"
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
    /*
        echo "<pre>";
        print_r($arFields);
        echo "</pre>";
    */
    /*
    echo $arFields["NAME"];
    echo "<br>";
    echo $arFields["ID"];
    echo "<br>";
    echo $arFields["PROPERTY_CML2_ARTICLE_VALUE"];
    echo "<br>";
    echo $arFields["DETAIL_PAGE_URL"];
    echo "<br>";
    echo $arFields["PROPERTY_TOVAR_MARKETPLEYS_VALUE"];
    echo "<br>";
    echo $arFields["PROPERTY_TOVARMIKROSA_VALUE"];
    echo "<br>";
    echo $arFields["PROPERTY_OTOBRAZHAT_OZHIDAEMYY_PRIKHOD_VALUE"];
    echo "<br>";
    echo $arFields["IBLOCK_SECTION_ID"];
    echo "<br>";
    echo CFile::GetPath($arFields["DETAIL_PICTURE"]);
    echo "<br>";
    echo $arFields["DETAIL_TEXT"];
    echo "<br><br>";
    */

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
            'DETAIL_TEXT' => $arFields["DETAIL_TEXT"],
        ]
    ];

    try {
        $response = $client->index($params);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
$date = date("m.d.y");
$time = date("H:i:s");

echo $line = $date . " - " . $time . ': Время выполнения скрипта добавление в индекс: ' . round(microtime(true) - $startIndex, 4) . ' сек.</br>';

$startDel = microtime(true);

$arFilterDel = array(
    "IBLOCK_ID" => 18,
    "ACTIVE_DATE" => "N",
    "ACTIVE" => "N",
    ">TIMESTAMP_X" => date('d.m.Y H:i:s', strtotime($date . $backInTime)),
);

$arSelectDel = array(
    "ID"
);

$resDel = CIBlockElement::GetList(
    array(),
    $arFilterDel,
    false,
    $arGroup,
    $arSelectDel
);

while ($obDel = $resDel->GetNextElement()) {
    $arFields = $obDel->GetFields();

    $params = [
        'index' => 'catalog',
        'type' => 'item',
        'id' => $arFields["ID"]
    ];
    try {
        $response = $client->delete($params);
    } catch (Exception $e) {
        echo $e->getMessage(), "\n";
    }
}
echo $line = $date . " - " . $time . ': Время выполнения скрипта удвление из индекса: ' . round(microtime(true) - $startDel, 4) . ' сек.</br>';