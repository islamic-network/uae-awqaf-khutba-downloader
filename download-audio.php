<?php

// Month Regex: \/en\/Pages\/FridaySermonDetail.aspx\?did=[0-9][0-9][0-9][0-9]
// Title Regex: document.title = '(.*)'
// Audio Regex: https:\/\/m.awqaf.ae\/Uploads\/AudioFridaySermons\/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]-[a-z][a-z].mp3


$file = 'months/all.txt';
$baseUrl = 'https://www.awqaf.gov.ae';
$outputDir = "downloads/mp3/";
$urls = explode("\n", file_get_contents($file));

foreach ($urls as $url) {
  if ($url != '') {
    $pageUrl = $baseUrl . $url;
    echo "Downloading page html for $pageUrl to extract title and audio URLs...\n";
    $page = file_get_contents($pageUrl);
    echo "Getting title...\n";
    preg_match('$document.title = \'(.*)\'$', $page, $titleMatches);
    $title = $titleMatches[1];
    echo "Title is $title. Extracting audio URLs...\n";
    preg_match_all('/https:\/\/m.awqaf.ae\/Uploads\/AudioFridaySermons\/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]-[a-z][a-z].mp3/', $page, $matches);
    $audioCount = count($matches[0]);
    echo "$audioCount audio files found. Beginning downloads...\n";
    foreach ($matches[0] as $audio) {
      $parts = explode('/', $audio);
      $tParts = count($parts);
      $datemp3 = $parts[$tParts-1];
      $dx = explode('.', $datemp3);
      $date = $dx[0];
      $dt = explode("-", $date);
      $day = $dt[0];
      $month = $dt[1];
      $year = $dt[2];
      $language = $dt[3];
      $newDate = $year . '-' . $month . '-' . $day . '-' . $language;
      echo "Downloading $audio...\n"; 
      $a = file_get_contents($audio);
      echo "Writing file $audio to disk...\n"; 
      file_put_contents($outputDir . $newDate . '-' . str_replace(['-', ' '], ['_', '_'], $title) . '.mp3', $a);
      echo "Done!\n";
    }
  }
}

