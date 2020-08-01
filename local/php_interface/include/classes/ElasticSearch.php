<?
require '/home/mikros/public_html/dev/ElasticSearch/vendor/autoload.php';

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
            if ($arFields["ACTIVE"] == 'Y') { // Если товар стал активным - заносим в индекс

                $group = array();
                $oldPrice = null;

                $res = CIBlockElement::GetByID($arFields['ID']);
                $ar_res = $res->GetNext();

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
                    //AddMessage2Log($varVal);
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
                    AddMessage2Log("[updateByElementId] - Запись с кодом " . $arFields["ID"] . " изменена.");
                } catch (Exception $e) {
                    AddMessage2Log($e->getMessage());
                }
            } else { // Иначе - удаляем из индекса
                $params = [
                    'index' => 'catalog',
                    'type' => 'item',
                    'id' => $arFields["ID"]
                ];
                
                try {
                    $response = $client->delete($params);
                } catch (Exception $e) {
                    AddMessage2Log($e->getMessage());
                }
                AddMessage2Log("[updateByElementId] - Запись с кодом " . $arFields["ID"] . " Деактивирована");
            }
        } else {
            AddMessage2Log("[updateByElementId] - Ошибка изменения записи " . $arFields["ID"] . " (" . $arFields["RESULT_MESSAGE"] . ").");
        }
    }
    // создаем обработчик события "OnAfterIBlockElementAdd"
    function addByElementId(&$arFields)
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
            ->build();
        if ($arFields["RESULT"]) {

            $group = array();
            $oldPrice = null;

            $res = CIBlockElement::GetByID($arFields['ID']);
            $ar_res = $res->GetNext();

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
                //AddMessage2Log($varVal);
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
                AddMessage2Log("[addByElementId] - Запись с кодом " . $arFields["ID"] . " изменена.");
            } catch (Exception $e) {
                AddMessage2Log($e->getMessage());
            }
        }
    }
    // создаем обработчик события "OnAfterIBlockElementDelete"
    function deleteByElementId(&$arFields)
    {
        //AddMessage2Log($arFields);
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
            ->build();

        $params = [
            'index' => 'catalog',
            'type' => 'item',
            'id' => $arFields["ID"]
        ];

        try {
            $response = $client->delete($params);
            AddMessage2Log("[deleteByElementId] - Запись с кодом " . $arFields["ID"] . " Удалена");
        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }
    }
    // создаем обработчик события "OnBeforePriceUpdate"
    function onBeforePriceUpdate($id, $arFields)
    {
        //AddMessage2Log($arFields);
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
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

        $response = $client->update($params);
    }
    // создаем обработчик события "OnStoreProductUpdate"
    function onStoreProductUpdate($id, $arFields)
    {
        //AddMessage2Log($arFields);
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
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

        $response = $client->update($params);
    }
}
