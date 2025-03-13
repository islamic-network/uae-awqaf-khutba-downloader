<?php
namespace Facebook\WebDriver;

use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;

function downloadDoc(mixed $data): void
{
    $path = realpath(__DIR__ . '/../downloads/');
    $dir = $path . '/doc/';
    echo 'Downloading .docx files...' . "\n";
    foreach ($data as $month) {
        foreach ($month as $day) {
            foreach ($day['data'] as $langFiles) {
                try {
                    if (!empty($langFiles['doc'])) {
                        $newFileName = getFileName($day['title'], $day['date'], $langFiles['lang']) . '.' . pathinfo($langFiles['doc'])['extension'];
                        echo "Attempting to download file " . $langFiles['doc'] . " as $newFileName... \n";
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
    $path = realpath(__DIR__ . '/../downloads/');
    $dir = $path . '/mp3/';
    echo 'Downloading .mp3 files...' . "\n";
    foreach ($data as $month) {
        foreach ($month as $day) {
            foreach ($day['data'] as $langFiles) {
                try {
                    if (!empty($langFiles['mp3'])) {
                        $newFileName = getFileName($day['title'], $day['date'], $langFiles['lang']) . '.mp3';
                        echo "Attempting to download file " . $langFiles['mp3'] . " as $newFileName... \n";
                        downloadFile($langFiles['mp3'], $dir, $newFileName);
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
    $path = realpath(__DIR__ . '/../downloads/');
    $dir = $path . '/pdf/';
    echo 'Downloading .pdf files...' . "\n";
    foreach($data as $month) {
        foreach ($month as $day) {
            foreach ($day['data'] as $langFiles) {
                try {
                    if (!empty($langFiles['pdf'])) {
                        $newFileName = getFileName($day['title'], $day['date'], $langFiles['lang']) . '.' . pathinfo($langFiles['pdf'])['extension'];
                        echo "Attempting to download file " . $langFiles['doc'] . " as $newFileName... \n";
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

function downloadFile(string $url, string $dir, string $newFileName): void
{
    $baseUrl = str_replace(basename($url), '', $url);
    $encodedUrl = $baseUrl . rawurlencode(basename($url));
    $saveFilePath = $dir . $newFileName;
    $ch = curl_init($encodedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_RESPONSE_CODE) === 200) {
        echo "Received a 200 for the file, saving...\n";
        file_put_contents($saveFilePath, $data);
    } else {
        $rCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        echo "Received a $rCode for the file, skipping...\n";
    }
    curl_close($ch);
}

function checkElement(RemoteWebDriver $driver, string $elem, string $lang): string|bool
{
    try {
        $element = '';
        if ($elem === 'mp3') {
            echo "Extracting URL of $lang mp3...";
            $element = $driver->findElement(WebDriverBy::xpath("//button[text()='Download']/ancestor-or-self::a"))
                ->getAttribute('href');
        } elseif ($elem === 'pdf') {
            echo "Extracting URL of $lang PDF...";
            $element = $driver->findElement(WebDriverBy::xpath("//button[text()='PDF']/ancestor-or-self::a"))
                ->getAttribute('href');
        } elseif ($elem === 'doc') {
            echo "Extracting URL of $lang Document...";
            $element = $driver->findElement(WebDriverBy::xpath("//button[text()='Documents']/ancestor-or-self::a"))
                ->getAttribute('href');
        }
        echo "Found $element\n";
        return $element;
    } catch (NoSuchElementException $e) {
        echo 'ERROR ::: ' . $e->getMessage() . "\n";

        return false;
    }
}