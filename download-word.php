<?php

// Month Regex: \/en\/Pages\/FridaySermonDetail.aspx\?did=[0-9][0-9][0-9][0-9]
// Title Regex: document.title = '(.*)'
// word Regex: https:\/\/m.awqaf.ae\/uploads\/Friday_speach\/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]-[a-z][a-z].doc


$file = 'months/2023-until-july.txt';
$baseUrl = 'https://www.awqaf.gov.ae';
$outputDir = "downloads/doc/";
$urls = explode("\n", file_get_contents($file));

foreach ($urls as $url) {
  if ($url != '') {
    $pageUrl = $baseUrl . $url;
    echo "Downloading page html for $pageUrl to extract title and word URLs...\n";
    $page = file_get_contents($pageUrl);
    echo "Getting title...\n";
    preg_match('$document.title = \'(.*)\'$', $page, $titleMatches);
    $title = $titleMatches[1];
    echo "Title is $title. Extracting word URLs...\n";
    preg_match_all('/https:\/\/m.awqaf.ae\/uploads\/Friday_speach\/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]-[a-z][a-z].doc/', $page, $matches);
    $wordCount = count($matches[0]);
    echo "$wordCount word files found. Beginning downloads...\n";
    foreach ($matches[0] as $word) {
      $parts = explode('/', $word);
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
      echo "Downloading $word...\n"; 
      $a = file_get_contents($word);
      echo "Writing file $word to disk...\n"; 
      file_put_contents($outputDir . $newDate . '-' . str_replace(['-', ' '], ['_', '_'], $title) . '.doc', $a);
      echo "Done!\n";
    }
  }
}

