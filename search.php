<? require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['localhost:9200']) // указываем, в виде массива, хост и порт сервера elasticsearch
    ->build();

$params = [
    'size' => 20,
    //'scroll' => '3s',
    'index' => 'catalog',
    'type' => 'item',
    'body' => [
        'query' => [
            'bool' => [
                'should' => [
                    ['match' => ['ARTICLE' => switcher_ru($_GET['q'])]],
                ]
            ]
        ]
    ]
];
//todo: добавить похожие товары
$results = $client->search($params);

if ($results['hits']['total']['value'] == 1) {
    //echo "полное совпадение по артикулу:<br>";
    // echo '<pre>', print_r($results, true), '</pre>';
    echo json_encode($results);
} else {
    //echo "не артикул, - ищем дальше";
    //echo "...<br>";
    $params = [
        'size' => 20,
        //'scroll' => '3s',
        'index' => 'catalog',
        'type' => 'item',
        'body' => [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['ARTICLE' => switcher_ru($_GET['q'])]],
                        ['match' => ['NAME' => switcher_ru($_GET['q'])]],
                    ]
                ]
            ]
        ]
    ];
    $results = $client->search($params);
    if ($results['hits']['total']['value'] < 1) {
        //echo "нет совпадений";
        $params = [
            'index' => 'catalog',
            'size' => 20,
            'type' => 'item',
            'body' => [
                'query' => [
                    'fuzzy' => [
                        'ARTICLE' => [
                            'value' => switcher_ru($_GET['q']),
                            'fuzziness' => 1,
                            'transpositions' => false,
                            "rewrite" => "constant_score",
                            //'max_expansions' => 50,
                            //"boost" => 1.0,
                            //"prefix_length" => 1,
                            //"max_expansions" => 100
                        ]
                    ],
                ],
            ]

        ];
        $results = $client->search($params);

        //echo '<pre>', print_r($results, true), '</pre>';
        echo json_encode($results);


    } else {
        //echo "полное совпадение по артикулу + имя:<br>";
        //echo '<pre>', print_r($results, true), '</pre>';
        echo json_encode($results);
    }
}


/*
$params = [
    'index' => 'catalog',
    'size' => 20,
    'type' => 'item',
    'body' => [
        'query' => [
            'fuzzy' => [
                'NAME' => [
                    'value' => $_GET['q'],
                    'fuzziness' => 2,
                    'transpositions' => false,
                    "rewrite" => "constant_score",
                    //'max_expansions' => 50,
                    //"boost" => 1.0,
                    //"prefix_length" => 1,
                    //"max_expansions" => 100
                ]
            ],
        ],
    ]
    /*
    'body' => [
        'query' => [
            'bool' => [
                'filter' => [

                ],
                'should' => [
                    ['match' => ['ARTICLE' => $_GET['q']]],
                    ['match' => ['NAME' => $_GET['q']]],
                ],
            ],
        ],
    ],
];

try {
    $results = $client->search($params);
} catch (Exception $e) {
    var_dump($e->getMessage());
}
echo '<pre>', print_r($results, true), '</pre>';
*/
/*

if ($results['hits']['total']['value'] <= 1) {
    $params = [
        'size' => 5,
        'scroll' => '3s',
        'index' => 'catalog',
        'type' => 'item',
        'body' => [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['ARTICLE' => switcher_ru($_GET['q'])]],
                        ['match' => ['NAME' => switcher_ru($_GET['q'])]],
                    ]
                ]
            ]
        ]
    ];

    $results = $client->search($params);
}

echo '<pre>', print_r($results, true), '</pre>';

*/
function switcher_ru($value)
{
    $converter = array(
        'f' => 'а', ',' => 'б', 'd' => 'в', 'u' => 'г', 'l' => 'д', 't' => 'е', '`' => 'ё',
        ';' => 'ж', 'p' => 'з', 'b' => 'и', 'q' => 'й', 'r' => 'к', 'k' => 'л', 'v' => 'м',
        'y' => 'н', 'j' => 'о', 'g' => 'п', 'h' => 'р', 'c' => 'с', 'n' => 'т', 'e' => 'у',
        'a' => 'ф', '[' => 'х', 'w' => 'ц', 'x' => 'ч', 'i' => 'ш', 'o' => 'щ', 'm' => 'ь',
        's' => 'ы', ']' => 'ъ', "'" => "э", '.' => 'ю', 'z' => 'я',

        'F' => 'А', '<' => 'Б', 'D' => 'В', 'U' => 'Г', 'L' => 'Д', 'E' => 'Е', '~' => 'Ё',
        ':' => 'Ж', 'P' => 'З', 'B' => 'И', 'Q' => 'Й', 'R' => 'К', 'K' => 'Л', 'V' => 'М',
        'Y' => 'Н', 'J' => 'О', 'G' => 'П', 'H' => 'Р', 'C' => 'С', 'N' => 'Т', 'E' => 'У',
        'A' => 'Ф', '{' => 'Х', 'W' => 'Ц', 'X' => 'Ч', 'I' => 'Ш', 'O' => 'Щ', 'M' => 'Ь',
        'S' => 'Ы', '}' => 'Ъ', '"' => 'Э', '>' => 'Ю', 'Z' => 'Я',

        '@' => '"', '#' => '№', '$' => ';', '^' => ':', '&' => '?', '/' => '.', '?' => ',',
    );

    $value = strtr($value, $converter);
    return $value;
}