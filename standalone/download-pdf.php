<?php

// Month Regex: \/en\/Pages\/FridaySermonDetail.aspx\?did=[0-9][0-9][0-9][0-9]
// Title Regex: document.title = '(.*)'
// pdf Regex: https:\/\/m.awqaf.ae\/uploads\/Friday_speach\/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]-[a-z][a-z].pdf

    $file = '../months/2023-july-october.txt';
    $baseUrl = 'https://www.awqaf.gov.ae';
    $outputDir = "../downloads/pdf/";
    $urls = explode("\n", file_get_contents($file));

    foreach ($urls as $url) {
        if ($url != '') {
            $pageUrl = $baseUrl . $url;
            echo "Downloading page html for $pageUrl to extract title and pdf URLs...\n";
            $page = file_get_contents($pageUrl);
            echo "Getting title...\n";
            preg_match('$document.title = \'(.*)\'$', $page, $titleMatches);
            $title = $titleMatches[1];
            echo "Title is $title. Extracting pdf URLs...\n";
            preg_match_all('/https:\/\/m.awqaf.ae\/uploads\/Friday_speach\/[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]-[a-z][a-z].pdf/', $page, $matches);
            $pdfCount = count($matches[0]);
            echo "$pdfCount pdf files found. Beginning downloads...\n";
            foreach ($matches[0] as $pdf) {
                $parts = explode('/', $pdf);
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
                echo "Downloading $pdf...\n";
                $a = file_get_contents($pdf);
                echo "Writing file $pdf to disk...\n";
                file_put_contents($outputDir . $newDate . '-' . str_replace(['-', ' '], ['_', '_'], $title) . '.pdf', $a);
                echo "Done!\n";
            }
        }
    }


