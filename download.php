<?php

/**
 * Configuration
 */
/**
 * @var string $url The URL of the UAE Awqaf the current sermon page
 */
// Calculate the date for the current and next Friday.
$friday = new DateTime('next friday');
// Or manually specify a friday date.
$friday = new DateTime('2024-01-05');
$date = $friday->format('d-m-Y');

echo "Checking for " . $friday->format('r') . "...\n";

// Check all file formats.
$baseUrls = ["https://m.awqaf.ae/Uploads/AudioFridaySermons", "https://m.awqaf.ae/uploads/Friday_speach"];
$strings_to_check = ['n-ar', '-n-ar' , '-ar', '-ur', '-en'];
$extensions = ['mp3', 'pdf', 'doc'];
$outputDir = "downloads/";
$downloadable  = [];


// Get sermon title
$title = "Allah_charges_no_soul_save_to_its_capacity";

foreach ($baseUrls as $baseUrl) {
    foreach ($strings_to_check as $string) {
        foreach ($extensions as $extension) {
            $url = "$baseUrl/$date$string.$extension";
            echo "Checking $url\n";
            $headers = get_headers($url);
            if (strpos($headers[0], '200') !== false) {
                $downloadable[$extension][] = $url;
                echo "Found $url\n";
            }
        }
    }
}

echo "\n" . @implode("\n", $downloadable['mp3']);
echo "\n" . @implode("\n", $downloadable['doc']);
echo "\n" . @implode("\n", $downloadable['pdf']);
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
        $a = file_get_contents($url);
        echo "Writing file $url to disk at $downloadName...\n";
        file_put_contents($downloadName, $a);
        echo "Done!\n";
    }
}


