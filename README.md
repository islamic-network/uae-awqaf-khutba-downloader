# UAE Awqaf Khutba Downloader

The UAE Awqaf website's Friday Sermon Archive is down quite often, and there is no programmtic way to interact with it to get the Khutba files. This
utility makes it possible to download the files and add them to the Islamic Network CDN (potentially, right now they just sit on OneDrive).

## How to use It

This utility requires PHP 8.0+ (at least that's what it has been tested with) and composer.

You can only download khutbas for one month at a time, technically, and the below process documents how to aggregate that. Unforunately a crawler is
not easy to write for everything as the month and year use some very unpleasant encoding and HTTP posts. It's possible to automate this, but probably
not woth the investment of time. So, for now:

Run ```composer install``` after cloning this repository. Then:

1. Visit https://www.awqaf.gov.ae/en/Pages/FridaySermonArchive.aspx.
2. Select the desired month and date from the dropdown. Press Search.
3. After the page loads, right click, view source and copy the source code.
4. In other tab, open https://regex101.com/.
5. In the Test String text area, paste the entire page source code you copied in step 3.
6. Paste ```\/en\/Pages\/FridaySermonDetail.aspx\?did=[0-9][0-9][0-9][0-9]``` in the Regular Expression input field.
7. You will see some results appear in Match Information on the right sight. Hover over this, and an export button will appear. Click on it, in the
popup select plain text where URLs appear on each line and copy these URLs using the copy button that appears as you hover over these URLs.
8. In the months folder of this repo, create a file with the month name, example: jan-2022.txt. Paste all the URLs into this file and save it.
9. Open ```downloads.php``. On line 8, change the name of the file to match the txt file you saved in step 8.
10. In line 10, please change the path where you want the files to be saved on your PC (or mac or server or whatever you are using).
11. Save the file.
12. Run ```download.php``` and watch the commentary.

## Find any issues?

Please raise a PR to fix if you can or post on https://community.islamic.network for support.

