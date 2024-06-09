<?php

/**
 * Configuration
 */
/**
 * @var string $url The URL of the UAE Awqaf the current sermon page
 */
// Calculate the date for the current and next Friday.
//$friday = new DateTime('next friday');
// Or manually specify a friday date.
$friday = new DateTime('2024-06-07');
//$friday = new DateTime('now', new DateTimeZone('Asia/Dubai'));
$date = $friday->format('d-m-Y');
$wait = 1;
$arrContextOptions = [
   "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
    "http" => [
        "method" => "GET",
        "header" => "Host: mobileappapi.awqaf.gov.aeen\r\n" .
        "Referer: https://www.awqaf.gov.ae/\r\n"
    ]

    ]
;

$context = stream_context_create($arrContextOptions);

echo "Checking for " . $friday->format('r') . "...\n";

// Check all file formats.
$baseUrls = ["https://mobileappapi.awqaf.gov.ae/APIS/TempUploads/KhutbaAttachment"];
//$baseUrls = ["https://mobileappapi.awqaf.gov.ae/APIS/TempUploads/KhutbaAttachment", "https://m.awqaf.ae/Uploads/AudioFridaySermons", "https://m.awqaf.ae/uploads/Friday_speach"];
$extensions = ['mp3', 'pdf', 'doc'];
$outputDir = "downloads/";
$downloadable  = [];
$downloadedFiles = [];
// Get sermon title
$titleOrig = "Welcoming the Blessed Ten Days";
$title = str_replace([" ", "-"], ["_", "_"], $titleOrig);
$titleEn = $titleOrig;
$titleAr = "يا%20مرحبا%20بالعشر8";
$titleUr = "عشرۂ%20ذى%20الحِجَّہ%20كى%20فضيلت";
//$strings_to_check = ["$date-ar", "$date-ur", "$date-en", $titleEn.'-en', $titleAr.'-ar', $titleUr.'-ur'];
$strings_to_check = [$titleEn.'-en', $titleAr.'-ar', $titleUr.'-ur'];

foreach ($baseUrls as $baseUrl) {
    foreach ($strings_to_check as $string) {
        foreach ($extensions as $extension) {
	    $lsplit = explode('-', $string);
	    $lang = $lsplit[1];
        $url = "$baseUrl/" . $lsplit[0] .".$extension";
        echo "Checking $url...";
        $headers = get_headers($url, false, $context);
        var_dump($headers);
        if ($headers[0] === "HTTP/1.1 200 OK") {
            $downloadable[$extension][] = ['url' =>  $url, 'lang' => $lang];
            echo "found!\n";
	    } else {
            echo "not found!\n";
        }
	    sleep($wait);

        }
    }
}

foreach ($downloadable as $extension => $urls) {
    foreach ($urls as $url) {
	$lang = $url['lang'];
	$url = $url['url'];
    $parts = explode('/', $url);
    $tParts = count($parts);
    //$datemp3 = $parts[$tParts - 1];
    //$dx = explode('.', $datemp3);
    //$date = $dx[0];
    $dt = explode("-", $date);
    $day = $dt[0];
    $month = $dt[1];
    $year = str_replace("n", "", $dt[2]);
    $language = $lang;
    $newDate = $year . '-' . $month . '-' . $day . '-' . $language;
    $downloadName = $outputDir . $extension . '/' . $newDate . '-' . $title . '.' . $extension;
    //echo "Downloading $url...\n";
    //$a = file_get_contents($url, false, $context);
    echo "Downloading and Writing file $url to disk at $downloadName...";
    //file_put_contents($downloadName, $a);
    $downloadedFiles[$extension][] = $downloadName;
    if ($language === 'en') {
        shell_exec('wget -O "' . $downloadName .'" ' . str_replace(" ", "%20", $url));
    } else {
        shell_exec('wget -O "' . $downloadName . '" ' . $url);
    }
    echo "Done!\n";
	//echo "Wait $wait seconds incase there is throttling\n";
	sleep($wait);

    }
}

exit;
// Upload the files to the 2 storage drives
foreach ($downloadedFiles as $ext => $df) {
    foreach ($df as $f) {
        echo "Uploading $f to Falkenstein Backup Storage...\n";
        echo shell_exec("scp -P 23 $f u389747@nas.falkenstein.mamluk.net:/home/islamic-network-cdn/sermons/uae-awqaf/$ext/") . "\n";
        echo "Push to s3 bucket...\n";
        //echo shell_exec("s3cmd -c ~/.s3cfg_bb put -v $f s3://islamic-network-cdn/sermons/uae-awqaf/$ext/") . "\n";
        echo shell_exec("s3cmd put -v $f s3://islamic-network-cdn/sermons/uae-awqaf/$ext/") . "\n";
    }

}

echo "Done!\n";
