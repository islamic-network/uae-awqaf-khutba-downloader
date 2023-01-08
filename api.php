<?php

// Calculate Fridays
$given_year = strtotime("1 January 2018");
$for_start = strtotime('Friday', strtotime("1 January 2018"));
$for_end = strtotime('+1 year', strtotime("1 January 2022"));
$fridays = [];
for ($i = $for_start; $i <= $for_end; $i = strtotime('+1 week', $i)) {
    $fridays[] = date('Y-m-d', $i);
}

$iteratorMp3 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('downloads/mp3'));
$mp3s = array_keys(array_filter(iterator_to_array($iteratorMp3), function($file) {
    return $file->isFile();
}));

$iteratorPdf = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('downloads/pdf'));
$pdfs = array_keys(array_filter(iterator_to_array($iteratorPdf), function($file) {
    return $file->isFile();
}));

$iteratorDoc = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('downloads/doc'));
$docs = array_keys(array_filter(iterator_to_array($iteratorDoc), function($file) {
    return $file->isFile();
}));

$sermons = [];
prep($mp3s, 'mp3', $fridays, $sermons);
prep($docs, 'doc', $fridays, $sermons);
prep($pdfs, 'pdf', $fridays, $sermons);

$providers = [
    ['name' => 'General Authority of Islamic Affairs and Endowments, UAE', 'handle' => 'uae-awqaf', 'years' => [2015, 2016, 2018, 2019, 2020, 2021, 2022]]
];
$languages  = [
    ['code' => 'ur', 'name' => 'Urdu'],
    ['code' => 'ar', 'name' => 'Arabic'],
    ['code' => 'en', 'name' => 'English'],
    ['code' => 'es', 'name' => 'Spanish'],
];

foreach($sermons as $year => $type) {
    foreach ($type as $t => $month) {
        foreach ($month as $m => $s) {
            $sermons[$year][$t][$m] = array_values( $sermons[$year][$t][$m]);
        }
    }
}

// Normalise
$years = [2015, 2016, 2018, 2019, 2020, 2021, 2022];
$months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

// Now let's write the files
// By year.
foreach ($years as $year) {
    mkdir("api/$year");
    foreach ($months as $month) {
        mkdir("api/$year/$month");
    }
    file_put_contents("api/$year.json", json_encode($sermons[$year]));
    if (isset($sermons[$year]['friday'])) {
        file_put_contents("api/$year/friday.json", json_encode($sermons[$year]['friday']));
        foreach($sermons[$year]['friday'] as $m => $x) {
            file_put_contents("api/$year/$m/friday.json", json_encode(array_values($sermons[$year]['friday'][$m])));
        }
    }
    if (isset($sermons[$year]['other'])) {
        file_put_contents("api/$year/other.json", json_encode($sermons[$year]['other']));
        foreach($sermons[$year]['other'] as $m => $x) {
            file_put_contents("api/$year/$m/other.json", json_encode(array_values($sermons[$year]['other'][$m])));
        }
    }
}

file_put_contents("api/providers.json", json_encode($providers));
file_put_contents("api/languages.json", json_encode($languages));

function prep($fx, $format, $fridays, &$sermons) {
    $provider = ['uae-awqaf'];

    foreach($fx as $file) {
        if (strpos($file, '.mp3') !== false || strpos($file, '.doc') !== false || strpos($file, '.pdf') !== false) {
            $parts = explode("/", $file);
            //$df = $parts[2];
            $dx = explode("-", $parts[2]);
            $datex = $dx[0] . '-' . $dx[1] . '-' . $dx[2];
            $lang = $dx[3];
            $title = trim(str_replace("_", " ", explode('.', $dx[4])[0]));
            $url = 'https://cdn.islamic.network/sermons/uae-awqaf/' . $parts[2];
            $date = new DateTime(str_replace("-", "/", $datex), new DateTimeZone('Asia/Dubai'));
            $dateObj = [
                'iso8601' => $date->format('c'),
                'month' => ['name' => $date->format('F'), 'shortname' => $date->format('M'), 'number' => $date->format('m')],
                'day' => ['name' => $date->format('l'), 'shortname' => $date->format('D'), 'number' => $date->format('d')],
                'year' => $date->format('Y')
            ];
            if (in_array($datex, $fridays)) {
                // Friday sermon
                if (isset($sermons[$date->format('Y')]['friday'][$date->format('m')][md5($title)])) {
                    $sermons[$date->format('Y')]['friday'][$date->format('m')][md5($title)]['editions'][] = [
                        'language' => $lang,
                        'format' => $format,
                        'url' => $url
                    ];

                } else {
                    $sermons[$date->format('Y')]['friday'][$date->format('m')][md5($title)]['title'] = $title;
                    $sermons[$date->format('Y')]['friday'][$date->format('m')][md5($title)]['date'] = $dateObj;
                    $sermons[$date->format('Y')]['friday'][$date->format('m')][md5($title)]['provider'] = $provider;
                    $sermons[$date->format('Y')]['friday'][$date->format('m')][md5($title)]['editions'][] = [
                        'language' => $lang,
                        'format' => $format,
                        'url' => $url
                    ];
                }
            } else {
                // Other / Eid sermon
                if (isset($sermons[$date->format('Y')]['other'][$date->format('m')][md5($title)])) {
                    $sermons[$date->format('Y')]['other'][$date->format('m')][md5($title)]['editions'][] = [
                        'language' => $lang,
                        'format' => $format,
                        'url' => $url
                    ];
                } else {
                    $sermons[$date->format('Y')]['other'][$date->format('m')][md5($title)]['title'] = $title;
                    $sermons[$date->format('Y')]['other'][$date->format('m')][md5($title)]['date'] = $dateObj;
                    $sermons[$date->format('Y')]['other'][$date->format('m')][md5($title)]['provider'] = $provider;
                    $sermons[$date->format('Y')]['other'][$date->format('m')][md5($title)]['editions'][] = [
                        'language' => $lang,
                        'format' => $format,
                        'url' => $url
                    ];
                }
            }
        }
    }
}