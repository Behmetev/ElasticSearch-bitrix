<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/ElasticSearch.php');

AddEventHandler("iblock", "OnAfterIBlockElementAdd", array("ElasticSearchUpdate", "addByElementId"));
AddEventHandler("iblock", "OnAfterIBlockElementDelete", array("ElasticSearchUpdate", "deleteByElementId"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("ElasticSearchUpdate", "updateByElementId"));
AddEventHandler("catalog", "OnBeforePriceUpdate", array("ElasticSearchUpdate", "onBeforePriceUpdate"));
AddEventHandler("catalog", "OnStoreProductUpdate", array("ElasticSearchUpdate", "onStoreProductUpdate"));