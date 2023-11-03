<?php
require_once('download-audio.php');
require_once('download-pdf.php');
require_once('download-word.php');

/**
 * Configuration
 */
/**
 * @var string $url The URL of the UAE Awqaf sermons page
 */
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
    file_put_contents("months/all.txt", implode("\n", $matches[0]), FILE_APPEND);
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

