<?php

namespace Facebook\WebDriver;

use Exception;

function downloadDoc(mixed $data)
{
    $path = realpath(__DIR__ . '/../');
    $dir = $path . '/doc/';
    echo 'Downloading .docx files...' . "\n";
    foreach ($data as $month) {
        foreach ($month as $day) {
            foreach ($day['data'] as $langFiles) {
                try {
                    if (!empty($langFiles['doc'])) {
                        $newFileName = getFileName($day['title'], $day['date'], $langFiles['lang']) . '.docx';
                        downloadFile($langFiles['doc'], $dir, $newFileName);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }
    echo 'Successfully downloaded all docx files...' . "\n";
}