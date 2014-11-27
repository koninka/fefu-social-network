<?php

$lang = 0; // russian
$headerOptions = [
    'http' => [
        'method' => "GET",
        'header' => "Accept-language: en\r\n" .
                    "Cookie: remixlang=$lang\r\n"
    ]
];
$counts = [
    'getCountries'   => 1000,
    'getRegions'     => 1000,
    'getCities'      => 1000,
    'getUniversities'=> 10000,
    'getSchools'     => 10000,
    'getFaculties'   => 10000,
    'getChairs'      => 10000,
];

function getResponse($url)
{
    global $headerOptions;
    $streamContext = stream_context_create($headerOptions);
    $json = file_get_contents($url, false, $streamContext);
    $arr = json_decode($json, true);
    if (is_null($arr)) {
        echo "WTF: $url \n";
        file_put_contents('errors', "$url\n", FILE_APPEND);

        return [];
    }
    while (array_key_exists('error', $arr) && $arr['error'] == 10) {
        print_r($arr);
        echo "$url\n";
        $json = file_get_contents($url, false, $streamContext);
        $arr = json_decode($json, true);
    }

    return $arr;
}

function writeJson($file, $arr, $last, $p)
{
    foreach ($arr as $a) {
        foreach ($p as $key => $value) {
            $a[$key] = $value;
        }
        $data = json_encode($a);
        fwrite($file, $data . "\n");
    }
}

function setDefaultKey(&$p, $k, $v)
{
    if (!array_key_exists($k, $p)) {
        $p[$k] = $v;
    }
}

function get($method, $params)
{
    setDefaultKey($params, 'v', '5.5');
    setDefaultKey($params, 'need_all', '1');
    setDefaultKey($params, 'count', '0');
    setDefaultKey($params, 'offset', '0');
    $methodUrl = "http://api.vk.com/method/database.$method?" . http_build_query($params);
    $resp = getResponse($methodUrl);

    return $resp;
}

function singleRun($method, $params, $file, $nameId)
{
    global $counts;
    $params['count']  = 0;
    $params['offset'] = 0;
    $offset           = 0;
    $count            = $counts[$method];
    $arr              = get($method, $params);
    if (!array_key_exists('response', $arr)) {
        print_r($arr);

        return [];
    }
    $totalCount = $arr['response']['count'];
    if ($totalCount == 0) {
        echo "no items:\t method $method\t id $params[$nameId]\n";
    }
    while ($offset < $totalCount) {
        $params['count'] = $count;
        $params['offset'] = $offset;
        $arr = get($method, $params);
        if (empty($arr['response']['items'])) {
            continue;
        }

        $res = $arr['response']['items'];
        $offset += $count;
        //$c = count($res);
        $p = [];
        if ($nameId != '') {
            $p = [$nameId => $params[$nameId]];
        }
        writeJson($file, $res, $offset < $totalCount, $p);
        echo "$method : $offset < $totalCount\n";
    }
}

function getInCycle($method, $containers, $nameId, $fileName)
{
    $file = fopen($fileName, 'w');
    if ($containers == '') {
        singleRun($method, [], $file, $nameId);
    } else {
        $containersFile = fopen($containers, 'r');
        while (!feof($containersFile)) {
            $line = fgets($containersFile);
            if (trim($line) == '') {
                continue;
            }
            $container = json_decode($line);
            $params = [$nameId => $container->id];
            singleRun($method, $params, $file, $nameId);
        }
        fclose($containersFile);
    }
    fclose($file);
}

function getCountries()
{
    getInCycle('getCountries', '', '', 'countries');
}

function getRegions($countries)
{
    getInCycle('getRegions', $countries, 'country_id', 'regions');
}

function getCities($countries)
{
    getInCycle('getCities', $countries, 'country_id', 'cities');
}

function getUniversities($cities)
{
    getInCycle('getUniversities', $cities, 'city_id', 'universities');
}

function getSchools($cities)
{
    getInCycle('getSchools', $cities, 'city_id', 'schools');
}

function getFaculties($universities)
{
    getInCycle('getFaculties', $universities, 'university_id', 'faculties');
}

function getChairs($faculties)
{
    getInCycle('getChairs', $faculties, 'faculty_id', 'chairs');
}

getCountries();
getRegions('countries');
getCities('countries');
getUniversities('cities');
getFaculties('universities');
getChairs('faculties');
getSchools('cities');
