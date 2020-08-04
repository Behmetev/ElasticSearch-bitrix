<?
require $_SERVER['DOCUMENT_ROOT'] . '/dev/ElasticSearch/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

class ElasticSearchUpdate
{
    // создаем обработчик события "OnAfterIBlockElementUpdate" 
    function updateByElementId(&$arFields)
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
            ->build();
        if ($arFields["RESULT"]) {
            if ($arFields["ACTIVE"] == 'Y') {

                $group = array();
                $oldPrice = null;

                $res = CIBlockElement::GetByID($arFields['ID']);
                $ar_res = $res->GetNext();

                $obStoreProduct = CCatalogStoreProduct::GetList(
                    array("STORE_ID" => "ASC"),
                    array("PRODUCT_ID" => $arFields["ID"]),
                    false,
                    false,
                    array("ID", "STORE_ID", "AMOUNT")
                );

                $db_old_groups = CIBlockElement::GetElementGroups($arFields['ID']);
                $ind = 0;
                while ($ar_group = $db_old_groups->Fetch()) {
                    $ind++;
                    $res2 = CIBlockSection::GetByID($ar_group["ID"]);
                    $ar_res2 = $res2->GetNext();
                    $group[$ind]['NAME'] = $ar_group["NAME"];
                    $group[$ind]['URL'] = $ar_res2['SECTION_PAGE_URL'];
                }

                $res3 = CIBlockElement::GetProperty(18, $arFields['ID'], array("sort" => "asc"), array("CODE" => "CML2_ARTICLE"));
                while ($ob3 = $res3->GetNext()) {
                    $varVal = $ob3['VALUE'];
                }

                $params = [
                    'index' => 'catalog',
                    'type' => 'item',
                    'id' => $arFields["ID"],
                    'body' => [
                        'NAME' => $arFields["NAME"],
                        'ARTICLE' => $varVal,
                        'URL' => $ar_res['DETAIL_PAGE_URL'],
                        'DETAIL_PICTURE' => CFile::GetPath($ar_res['DETAIL_PICTURE']),
                        'PRICE' => [
                            'OLD_PRICE' => $oldPrice
                        ],
                        'SECTIONS' => $group,

                ];
                try {
                    $response = $client->index($params);
                } catch (Exception $e) {
                    AddMessage2Log($e->getMessage());
                }
            } else {
                $params = [
                    'index' => 'catalog',
                    'type' => 'item',
                    'id' => $arFields["ID"]
                ];
                try {
                    $response = $client->delete($params);
                } catch (Exception $e) {
                    AddMessage2Log("[updateByElementId]" . $e->getMessage());
                }
            }
        } else {
            AddMessage2Log("[updateByElementId] - Ошибка изменения записи " . $arFields["ID"] . " (" . $arFields["RESULT_MESSAGE"] . ").");
        }
    }

    function addByElementId(&$arFields)
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200'])
            ->build();
        if ($arFields["RESULT"]) {

            $store = array();
            $group = array();
            $oldPrice = null;

            $res = CIBlockElement::GetByID($arFields['ID']);
            $ar_res = $res->GetNext();

            $obStoreProduct = CCatalogStoreProduct::GetList(
                array("STORE_ID" => "ASC"),
                array("PRODUCT_ID" => $arFields["ID"]),
                false,
                false,
                array("ID", "STORE_ID", "AMOUNT")
            );

            $db_old_groups = CIBlockElement::GetElementGroups($arFields['ID']);
            $ind = 0;
            while ($ar_group = $db_old_groups->Fetch()) {
                $ind++;
                $res2 = CIBlockSection::GetByID($ar_group["ID"]);
                $ar_res2 = $res2->GetNext();
                $group[$ind]['NAME'] = $ar_group["NAME"];
                $group[$ind]['URL'] = $ar_res2['SECTION_PAGE_URL'];
            }

            $res3 = CIBlockElement::GetProperty(18, $arFields['ID'], array("sort" => "asc"), array("CODE" => "CML2_ARTICLE"));
            while ($ob3 = $res3->GetNext()) {
                $varVal = $ob3['VALUE'];
            }

            $params = [
                'index' => 'catalog',
                'type' => 'item',
                'id' => $arFields["ID"],
                'body' => [
                    'NAME' => $arFields["NAME"],
                    'ARTICLE' => $varVal,
                    'URL' => $ar_res['DETAIL_PAGE_URL'],
                    'DETAIL_PICTURE' => CFile::GetPath($ar_res['DETAIL_PICTURE']),
                    'PRICE' => [
                        'OLD_PRICE' => $oldPrice
                    ],
                    'SECTIONS' => $group,
                ]
            ];
            try {
                $response = $client->index($params);
            } catch (Exception $e) {
                AddMessage2Log("[addByElementId] - " . $e->getMessage());
            }
        }
    }

    function deleteByElementId(&$arFields)
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200'])
            ->build();

        $params = [
            'index' => 'catalog',
            'type' => 'item',
            'id' => $arFields["ID"]
        ];
        try {
            $response = $client->delete($params);
        } catch (Exception $e) {
            AddMessage2Log("[deleteByElementId] - " . $e->getMessage());
        }
    }

    function onBeforePriceUpdate($id, $arFields)
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200'])
            ->build();

        $params = [
            'index' => 'catalog',
            'type' => 'item',
            'id' => $arFields['PRODUCT_ID'],
            'body' => [
                'doc' => [
                    'PRICE' => [
                        'BASE' => $arFields['PRICE']
                    ]
                ]
            ]
        ];
        try {
            $response = $client->update($params);
        } catch (Exception $e) {
            AddMessage2Log("[onBeforePriceUpdate] - " . $e->getMessage());
        }
    }

    function onStoreProductUpdate($id, $arFields)
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200'])
            ->build();

        $params = [
            'index' => 'catalog',
            'type' => 'item',
            'id' => $arFields['PRODUCT_ID'],
            'body' => [
                'doc' => [
                    'STORE' => [
                        $arFields['STORE_ID'] => $arFields['AMOUNT']
                    ]
                ]
            ]
        ];
        try {
            $response = $client->update($params);
        } catch (Exception $e) {
            AddMessage2Log("[onStoreProductUpdate] - " . $e->getMessage());
        }
    }
}