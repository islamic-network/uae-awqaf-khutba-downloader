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

function getFileName(string $title, string $date, string $lang): string
{
    $temp = preg_replace('/[^a-zA-Z0-9 ]/s', '', $title);
    $newTitleFormat = str_replace(' ', '_', $temp);
    $newDateFormat = date_create_from_format('d M Y', $date)->format('Y-m-d');

    return $newDateFormat . '-' . $lang . '-' . $newTitleFormat;
}

function downloadFile(string $url, string $dir, string $newFileName)
{
    $saveFilePath = $dir . $newFileName;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_RESPONSE_CODE) === 200) {
        file_put_contents($saveFilePath, $data);
    }
    curl_close($ch);
}