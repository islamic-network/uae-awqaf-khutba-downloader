<?php
namespace Facebook\WebDriver;

use Exception;

function downloadPdf(mixed $data): void
{
    $path = realpath(__DIR__ . '/../');
    $dir = $path . '/pdf/';
    echo 'Downloading .pdf files...' . "\n";
    foreach($data as $month) {
        foreach ($month as $day) {
            foreach ($day['data'] as $langFiles) {
                try {
                    if (!empty($langFiles['pdf'])) {
                        $newFileName = getFileName($day['title'], $day['date'], $langFiles['lang']) . '.pdf';

                        downloadFile($langFiles['pdf'], $dir, $newFileName);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }
    echo 'Successfully downloaded all pdf files...' . "\n";
}

