<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require __DIR__ . '/vendor/autoload.php';
$file = __DIR__ . '/logs/indexLog.txt';
$file2 = __DIR__ . '/logs/indexDel.txt';

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

$arGroup = array(
    "nTopCount" => 50
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
    "PROPERTY_TOVARMIKROSA",
    "PROPERTY_STARAYATSENA"
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
    $ar_res = CPrice::GetBasePrice($arFields['ID']);
    //echo CurrencyFormat($ar_res["PRICE"], $ar_res["CURRENCY"]);

    $store = array();
    $oldPrice = "";
    if ($arFields['PROPERTY_STARAYATSENA_VALUE'] > 0) {
        $oldPrice = $arFields['PROPERTY_STARAYATSENA_VALUE'];
    }
    $obStoreProduct = CCatalogStoreProduct::GetList(
        array("STORE_ID" => "ASC"),
        array("PRODUCT_ID" => $arFields["ID"]),
        false,
        false,
        array("ID", "STORE_ID", "AMOUNT")
    );

    while ($arStoreProduct = $obStoreProduct->Fetch()) {
        $store[] = $arStoreProduct;
    }
    /*
    echo "<pre>";
    print_r($store);
    echo "</pre>";
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
            'PRICE' => [
                'BASE' => $ar_res["PRICE"],
                'OLD_PRICE' => $oldPrice
            ],
            'STORE' => $store,
        ]
    ];

    try {
        $response = $client->index($params);
        $log = $arFields["ID"] . " - " . $arFields["PROPERTY_CML2_ARTICLE_VALUE"] . " - проиндексирован\r\n";
        file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
        file_put_contents($file, $arFields["ID"] . " - " . $arFields["PROPERTY_CML2_ARTICLE_VALUE"] . $e->getMessage() . '\r\n', FILE_APPEND | LOCK_EX);
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
    "ID",
    "PROPERTY_CML2_ARTICLE_VALUE"
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
        $log = $arFields["ID"] . " - " . $arFields["PROPERTY_CML2_ARTICLE_VALUE"] . " - удалён из индекса" . '\r\n';
        file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
        file_put_contents($file2, $arFields["ID"] . " - " . $arFields["PROPERTY_CML2_ARTICLE_VALUE"] . $e->getMessage() . '\r\n', FILE_APPEND | LOCK_EX);
    }
}
echo $line = $date . " - " . $time . ': Время выполнения скрипта удвление из индекса: ' . round(microtime(true) - $startDel, 4) . ' сек.</br>';
