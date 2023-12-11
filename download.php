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
// $friday = new DateTime('2023-12-08');
$date = $friday->format('d-m-Y');

echo "Checking for " . $friday->format('r') . "...\n";

// Check all file formats.
$baseUrls = ["https://m.awqaf.ae/Uploads/AudioFridaySermons", "https://m.awqaf.ae/uploads/Friday_speach"];
$strings_to_check = ['n-ar', '-n-ar' , '-ar', '-ur', '-en'];
$extensions = ['mp3', 'pdf', 'doc'];
$outputDir = "downloads/";
$downloadable  = [];

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
        $year = $dt[2];
        $language = $dt[3];
        $newDate = $year . '-' . $month . '-' . $day . '-' . $language;
        $downloadName = str_replace("n-", "-", $outputDir . $extension . '/' . $newDate . '.' . $extension);
        echo "Downloading $url...\n";
        $a = file_get_contents($url);
        echo "Writing file $url to disk at $downloadName...\n";
        file_put_contents($downloadName, $a);
        echo "Done!\n";
    }
}


/*
$url = 'https://www.awqaf.gov.ae/en/Pages/FridaySermonArchive.aspx';
$tempDir = 'temp/';
// Step 1: Download markup of current awqaf sermons page.
echo "Download markup of current awqaf sermons page.\n";
$page = file_get_contents($url);
// Step 2: Extract all links from the markup.
echo "Extract all links from the markup.\n";
preg_match_all("|/en/Pages/FridaySermonDetail.aspx\?did=[0-9][0-9][0-9][0-9]|", $page, $matches);
if (!empty($matches[0])) {
    // Step 3: Write the links to a file.
    $numberOfLinks = count($matches[0]);
    $file = date("Y-m-d") . ".txt";
    echo "Found $numberOfLinks links. Writing to file $file...\n";
    // Write the file
    file_put_contents("$tempDir/$file", implode("\n", $matches[0]));
    // Also update all.txt with the matches.
    echo "Updating all.txt with the matches.\n";
    file_put_contents("months/all.txt", "\n" . implode("\n", $matches[0]), FILE_APPEND);
    // Step 4: Download all the audio files.
    echo "Download all the audio files.\n";
    downloadAudio("$tempDir/$file");
    // Step 5: Download all the pdf files.
    echo "Download all the pdf files.\n";
    downloadPdf("$tempDir/$file");
    // Step 6: Download all the word files.
    echo "Download all the word files.\n";
    downloadWord("$tempDir/$file");
} else {
    echo "No links found. Exiting...\n";
    exit;
}
*/
