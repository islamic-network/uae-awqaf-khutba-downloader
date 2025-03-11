<?php

namespace Facebook\WebDriver;

use Exception;

function downloadMp3(mixed $data): void
{
    $path = realpath(__DIR__ . '/../');
    $dir = $path . '/mp3/';
    echo 'Downloading .mp3 files...' . "\n";
    foreach ($data as $month) {
        foreach ($month as $day) {
            foreach ($day['data'] as $langFiles) {
                try {
                    if (!empty($langFiles['mp3'])) {
                        $newFileName = getFileName($day['title'], $day['date'], $langFiles['lang']);

                        $e = explode(".", $langFiles['mp3']);
                        if (end($e) === 'mpeg') {
                            downloadFile($langFiles['mp3'], $dir, $newFileName . '.mpeg');
                        } elseif (end($e) === 'mp3') {
                            downloadFile($langFiles['mp3'], $dir, $newFileName . '.mp3');
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }
    echo 'Successfully downloaded all mp3 files...' . "\n";
}