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
$friday = new DateTime('2024-07-05');
//$friday = new DateTime('now', new DateTimeZone('Asia/Dubai'));
$date = $friday->format('d-m-Y');
$wait = 1;
$arrContextOptions = [
   "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
    //"http" => [
    //    "method" => "GET",
    //    "header" => "Host: mobileappapi.awqaf.gov.aeen\r\n" .
    //    "Referer: https://www.awqaf.gov.ae/\r\n"
    //]

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
$titleOrig = "The Prophet's Migration";
$title = str_replace([" ", "-","(", ")"], ["_", "_", "_", "_"], $titleOrig);
$titleEn = $title;
$titleAr = "هجرة%20النبي%20صلى%20الله%20عليه%20وسلم%2012";
$titleUr = "نبى%20كريم%20%20%20کی%20%20ہِجْرَتِ%20مدينہ%20%20كے%20%20دينى%20%20و%20اخلاقى%20فوائد%20%20وبركات";
//$strings_to_check = ["$date-ar", "$date-ur", "$date-en", $titleEn.'-en', $titleAr.'-ar', $titleUr.'-ur'];
$strings_to_check = ["$titleOrig::en", "$titleEn::en", "$titleAr::ar", "$titleUr::ur", "$date-en::en", "$date-ar::ar", "$date-ur::ur"];

foreach ($baseUrls as $baseUrl) {
    foreach ($strings_to_check as $string) {
        foreach ($extensions as $extension) {
	    $lsplit = explode('::', $string);
	    $lang = $lsplit[1];
        $url = "$baseUrl/" . $lsplit[0] .".$extension";
        echo "Checking $url...";
        $headers = get_headers($url, false, $context);
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
	shell_exec("wget -O " . addslashes($downloadName) . " " . str_replace([" "], ["%20"], $url));
    /*if ($language === 'en') {
	shell_exec('wget -O "' . $downloadName .'" ' . str_replace(" ", "%20", $url));
    } else {
	shell_exec('wget -O "' . $downloadName . '" ' . $url);
    }*/
	if (filesize($downloadName) < 2) {
		unlink($downloadName);
	}
	echo "Done!\n";
	//echo "Wait $wait seconds incase there is throttling\n";
	sleep($wait);

    }
}

// Upload the files to the 2 storage drives
foreach ($downloadedFiles as $ext => $df) {
    foreach ($df as $f) {
        //echo "Uploading $f to Falkenstein Backup Storage...\n";
        //echo shell_exec("scp -P 23 $f u389747@nas.falkenstein.mamluk.net:/home/islamic-network-cdn/sermons/uae-awqaf/$ext/") . "\n";
        echo "Push to s3 bucket...\n";
        if (file_exists($f)) {
        //echo shell_exec("s3cmd -c ~/.s3cfg_bb put -v $f s3://islamic-network-cdn/sermons/uae-awqaf/$ext/") . "\n";
            echo shell_exec("s3cmd put -v " . addslashes($f) . " s3://islamic-network-cdn/sermons/uae-awqaf/$ext/") . "\n";
            
        }
    }
}

echo "Done!\n";
