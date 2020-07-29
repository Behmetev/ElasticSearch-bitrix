<?require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
    ->build();

    $data = $client->search([
        'index' => 'catalog',
        'type'    => 'item',/*
        'body' => [
            'query' =>
                'match' => [
                    'site' => 'badcode.ru'
                ]
            ]
        ]*/
    ]);
  
    
    echo '<pre>', print_r($data, true), '</pre>';