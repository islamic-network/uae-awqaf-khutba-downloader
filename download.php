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
$arrContextOptions = array(
      "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
      )
);

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
$title = str_replace(" ", "_", $titleOrig);
$titleEn = $titleOrig;
$titleAr = "%D9%8A%D8%A7%20%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%20%D8%A8%D8%A7%D9%84%D8%B9%D8%B4%D8%B18";
$titleUr = "%D8%B9%D8%B4%D8%B1%DB%82%20%D8%B0%D9%89%20%D8%A7%D9%84%D8%AD%D9%90%D8%AC%D9%91%D9%8E%DB%81%20%D9%83%D9%89%20%D9%81%D8%B6%D9%8A%D9%84%D8%AA"; 
$strings_to_check = ['-ar', '-ur', '-en', $titleEn.'-en', $titleAr.'-ar', $titleUr.'-ur'];

foreach ($baseUrls as $baseUrl) {
    foreach ($strings_to_check as $string) {
        foreach ($extensions as $extension) {
	    $lsplit = explode('-', $string);
	    $lang = $lsplit[1];
            $url = "$baseUrl/$date$string.$extension";
            echo "Checking $url\n";
            $headers = get_headers($url, false, $context);
	    //var_dump($url, $headers);
            if (strpos($headers[0], '200') !== false) {
                $downloadable[$extension][] = ['url' =>  $url, 'lang' => $lang];
                echo "Found $url\n";
	    }
	    $string2 = urldecode($lsplit[0]);	
            $url2 = "$baseUrl/$string2.$extension";
            echo "Checking $url2\n";
            $headers = get_headers($url2, false, $context);
            if (strpos($headers[0], '200') !== false) {
                $downloadable[$extension][] = ['url' =>  $url2, 'lang' => $lang];
                echo "Found $url2\n";
            }
	    echo "Wait $wait seconds incase there is throttling\n";
	    sleep($wait);

        }
    }
}

//echo "\n" . @implode("\n", $downloadable['mp3']);
//echo "\n" . @implode("\n", $downloadable['doc']);
//echo "\n" . @implode("\n", $downloadable['pdf']);
//echo "\n";

var_dump($downloadable);
exit;
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
        $downloadName =$outputDir . $extension . '/' . $newDate . '-' . $title . '.' . $extension;
        echo "Downloading $url...\n";
	var_dump($url);
	break;
        $a = file_get_contents($url, false, $context);
        echo "Writing file $url to disk at $downloadName...\n";
        file_put_contents($downloadName, $a);
        $downloadedFiles[$extension][] = $downloadName;
        echo "Done!\n";
	echo "Wait $wait seconds incase there is throttling\n";
	sleep($wait);

    }
}
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
