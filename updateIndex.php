<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//error_reporting(-1);

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
    ->build();

$start = microtime(true);
/*
$backInTime = date('Y-m-d H:i:s');
$backInTime = date_modify($backInTime, '+15 minutes');
echo date('Y-m-d H:i:s');
echo "<br>";
echo $backInTime;
*/
$date = date('Y-m-d H:i:s');
$backInTime = '-20 minutes';

echo date('d.m.Y H:i:s', strtotime($date . $backInTime));

$arFilter = array(
    "IBLOCK_ID" => 18,
    "ACTIVE_DATE" => "Y",
    "ACTIVE" => "Y",
    "PROPERTY_OTOBRAZHAT_OZHIDAEMYY_PRIKHOD_VALUE" => "Нет",
    ">TIMESTAMP_X" => date('d.m.Y H:i:s', strtotime($date . $backInTime)),
);

echo "<pre>";
print_r($arFilter);
echo "</pre>";

$arGroup = array(
    //"nTopCount" => 50
);

$arSelect = array(
  /*  "ID",
    "DETAIL_PICTURE",
    "NAME",
    "IBLOCK_SECTION_ID",
    "DETAIL_TEXT",
    "DETAIL_PAGE_URL",
    "PROPERTY_CML2_ARTICLE",
    "PROPERTY_TOVAR_MARKETPLEYS",
    "PROPERTY_TOVARMIKROSA",*/
);

$res = CIBlockElement::GetList(
    array(),
    $arFilter,
    false,
    $arGroup,
    $arSelect
);
$num = 0;
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    echo "<br>" . ++$num;
    echo "<pre>";
    print_r($arFields);
    echo "</pre>";


    /*
        $arSelect2 = array("ID", "NAME", "CODE");
        $res2 = CIBlockElement::GetElementGroups($arFields['ID'], true, $arSelect2);
        while ($ob = $res2->Fetch()) {
            print_r($ob);
        }*/
}

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
/*
$params = [
    'index' => 'catalog',
    'type'    => 'item',
    'id'    => $arFields["ID"],
    'body'  => [
        'NAME' => $arFields["NAME"],
        'ARTICLE' => $arFields["PROPERTY_CML2_ARTICLE_VALUE"],
        'URL' => $arFields["DETAIL_PAGE_URL"],
        'DETAIL_PICTURE' => CFile::GetPath($arFields["DETAIL_PICTURE"]),
        'TOVARMIKROSA' => $arFields["PROPERTY_TOVARMIKROSA_VALUE"],
        'MARKETPLEYS' => $arFields["PROPERTY_TOVAR_MARKETPLEYS_VALUE"],
        'OZHIDAEMYY_PRIKHOD' => $arFields["PROPERTY_OTOBRAZHAT_OZHIDAEMYY_PRIKHOD_VALUE"],
        'DETAIL_TEXT' => $arFields["DETAIL_TEXT"],

        ]
];*/
//print_r($params);

//$response = $client->index($params);
//UPDATE
/*
$paramsUp = [
    'index' => 'catalog',
    'type'    => 'item',
    'id'    => $arFields["ID"],
    'body'  => [
        'doc' => [
            'MATERIAL' => $arFields["PROPERTY_MATERIAL_VALUE"]

        ]
    ]
];
*/
// Update doc at /my_index/_doc/my_id
//$response = $client->update($paramsUp);


//}

$date = date("m.d.y");
$time = date("H:i:s");

echo $line = $date . " - " . $time . ': Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';