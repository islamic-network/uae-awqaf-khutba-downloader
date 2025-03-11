<?php
namespace Facebook\WebDriver;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require __DIR__ . '/khutba-download-pdf.php';
require __DIR__ . '/khutba-download-mp3.php';
require __DIR__ . '/khutba-download-doc.php';

require_once('vendor/autoload.php');

function mainScript(string $year = null, string $month = null, string $date = null): void
{
    $driver = getDriverObject();
    $data = [];

    if (!empty($year) && !empty($month)) {
        $monthName = date_create_from_format('n', $month)->format('F');
        $driver->navigate()->to('https://www.awqaf.gov.ae/khutba-archive?year=' . $year . '&month=' . $month . '&lang=en');
    } elseif (!empty($date)) {
        $splitDate = explode('/', $date);
        $m = $splitDate[1];
        $y = $splitDate[2];
        $formatDate = date_create_from_format("d/n/Y", $date)->format('d M Y');
        $monthName = date_create_from_format('n', $m)->format('F');

        $driver->navigate()->to('https://www.awqaf.gov.ae/khutba-archive?year=' . $y . '&month=' . $m . '&lang=en');
    }
    $driver->wait(60)->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('Friday-Khutba-In'))
    );
    $driver->wait(60)->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//*[@id="pills-tabContent"]'))
    );

    if (!empty($year) && !empty($month)) {
        $cards = $driver->findElement(WebDriverBy::className('pt-5'))
            ->findElement(WebDriverBy::className('Friday-Khutba-In'))
            ->findElement(WebDriverBy::xpath('//*[@id="pills-tabContent"]'))
            ->findElements(WebDriverBy::tagName('a'));
    } elseif (!empty($date)) {
        $cards[] = $driver->findElement(WebDriverBy::xpath("//h3[text()='". $formatDate ."']/ancestor-or-self::a"));
    }

    echo 'Total number of cards to scrape: ' . count($cards) . "\n";
    foreach (range(1, count($cards)) as $i) {
        $driver->wait(60)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('Friday-Khutba-In'))
        );

        $driver->wait(60)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//*[@id="pills-tabContent"]'))
        );
        $driver->executeScript('window.scrollTo(0, 750);');
        sleep(2);
        if (!empty($year) && !empty($month)) {
            $card = $driver->findElement(WebDriverBy::className('pt-5'))
                ->findElement(WebDriverBy::className('Friday-Khutba-In'))
                ->findElement(WebDriverBy::xpath('//*[@id="pills-tabContent"]'))
                ->findElement(WebDriverBy::xpath('//*[@id="pills-2022"]/div/div/div[' . $i . ']'));
        } elseif (!empty($date)) {
            $card = $driver->findElement(WebDriverBy::xpath("//h3[text()='" . $formatDate . "']/ancestor-or-self::a/.."));
        }
        $card->click();
        $driver->wait(60)->until(
            WebDriverExpectedCondition::textToBePresentInElement(WebDriverBy::className('title-text-small'), 'Friday Sermon Date')
        );

        $tempData = [];
        $langs = ['en','ur','ar'];
        foreach ($langs as $lang) {
            $driver->findElement(WebDriverBy::xpath("//button[@data-rr-ui-event-key='" . $lang . "']"))->click();
            $mp3 = '';
            $pdf = '';
            $doc = '';

            $driver->wait(60)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath("//div[@class='arabic-event']"))
            );

            if ($lang === 'en') {
                $date = '';
                $title = '';
                $date = $driver->findElement(WebDriverBy::className('title-text-small'))
                    ->findElement(WebDriverBy::className('color-gold'))
                    ->getText();

                $title = $driver->findElement(WebDriverBy::xpath("//div[@class='banner-content']/h1"))->getText();
            }
            $mp3 = checkElement($driver, 'mp3');
            $pdf = checkElement($driver, 'pdf');
            $doc = checkElement($driver, 'doc');

            $tempData[] = [
                'lang' => $lang,
                'mp3' => $mp3,
                'pdf' => $pdf,
                'doc' => $doc
            ];
            sleep(2);
        }
        $data[$monthName][] = [
            'date' => $date,
            'title' => $title,
            'data' => $tempData
        ];
        sleep(2);
        $driver->navigate()->back();
    }
    $driver->quit();
    sleep(3);
    echo 'Successfully scraped all the data.' . "\n";
    downloadMp3($data);
    downloadPdf($data);
    downloadDoc($data);
}

function getDriverObject(array $prefs = null): RemoteWebDriver
{
    $host = 'http://127.0.0.1:4444/wd/hub';
    $args = [];
    $args[] = '--start-maximized=true';
    $args[] = '--disable-popup-blocking=true';
    $args[] = '--headless=true';
    $chromeOptions = new ChromeOptions();
    if (!empty($prefs)) {
        $chromeOptions->setExperimentalOption('prefs', $prefs);
    }
    $chromeOptions->addArguments($args);

    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
    $driver = RemoteWebDriver::create($host, $capabilities);

    return $driver;
}

function checkElement(RemoteWebDriver $driver, string $elem): string
{
    try {
        $element = '';
        if ($elem === 'mp3') {
            $element = $driver->findElement(WebDriverBy::xpath("//button[text()='Download']/ancestor-or-self::a"))
                ->getAttribute('href');
        } elseif ($elem === 'pdf') {
            $element = $driver->findElement(WebDriverBy::xpath("//button[text()='PDF']/ancestor-or-self::a"))
                ->getAttribute('href');
        } elseif ($elem === 'doc') {
            $element = $driver->findElement(WebDriverBy::xpath("//button[text()='Documents']/ancestor-or-self::a"))
                ->getAttribute('href');
        }

        return $element;
    } catch (NoSuchElementException $e) {

        return '';
    }
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

//mainScript(null, null, '08/11/2024');
mainScript('2024', '11');
