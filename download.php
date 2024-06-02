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
//$friday = new DateTime('2024-05-31');
$friday = new DateTime('now', new DateTimeZone('Asia/Dubai'));
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
$strings_to_check = ['-ar', '-ur', '-en'];
$extensions = ['mp3', 'pdf', 'doc'];
$outputDir = "downloads/";
$downloadable  = [];
$downloadedFiles = [];

// Get sermon title
$title = "The Blessing of Long Life";
$title = str_replace(" ", "_", $title);

foreach ($baseUrls as $baseUrl) {
    foreach ($strings_to_check as $string) {
        foreach ($extensions as $extension) {
            $url = "$baseUrl/$date$string.$extension";
            echo "Checking $url\n";
            $headers = get_headers($url, false, $context);
            if (strpos($headers[0], '200') !== false) {
                $downloadable[$extension][] = $url;
                echo "Found $url\n";
            }
	    echo "Wait $wait seconds incase there is throttling\n";
	    sleep($wait);

        }
    }
}

//echo "\n" . @implode("\n", $downloadable['mp3']);
//echo "\n" . @implode("\n", $downloadable['doc']);
//echo "\n" . @implode("\n", $downloadable['pdf']);
echo "\n";

foreach ($downloadable as $extension => $urls) {
    foreach ($urls as $url) {
        $parts = explode('/', $url);
        $tParts = count($parts);
        $datemp3 = $parts[$tParts - 1];
        $dx = explode('.', $datemp3);
        $date = $dx[0];
        $dt = explode("-", $date);
        $day = $dt[0];
        $month = $dt[1];
        $year = str_replace("n", "", $dt[2]);
        $language = $dt[3];
        $newDate = $year . '-' . $month . '-' . $day . '-' . $language;
        $downloadName =$outputDir . $extension . '/' . $newDate . '-' . $title . '.' . $extension;
        echo "Downloading $url...\n";
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
