# UAE Awqaf Khutba Downloader

The UAE Awqaf website's Friday Sermon Archive is down quite often, and there is no programmtic way to interact with it to get the Khutba files. This
utility makes it possible to download the files and add them to the Islamic Network CDN.

## How to use It

## Getting Khtubas for a month

Run `year=2025 month=02 docker compose up --build`.

## Getting Khtubas for a day

Run `date=15/11/2024 docker compose up --build`, where the date is DD-MM-YYYY.

In both cases, the files will go in the downloads folder.

## Once downloaded, add the files to the CDN:

### Primary Storage
```
s3cmd put downloads/doc/* s3://cdn.islamic.network/sermons/uae-awqaf/doc/
s3cmd put downloads/mp3/* s3://cdn.islamic.network/sermons/uae-awqaf/mp3/
s3cmd put downloads/pdf/* s3://cdn.islamic.network/sermons/uae-awqaf/pdf/

```

## Find any issues?

Please raise a PR to fix if you can or post on https://community.islamic.network for support.

## Legal and Disclaimer
The license for these scripts is GNU LGPL v3.

**Please note that where ever you download, view and/or store these khutbas, the copyright 
remains with and belongs to the General Authority of Islamic Affairs and Endowments, UAE. The 
authenticity may be verified using their document verifier at https://www.awqaf.gov.ae/en/Pages/verifydocument.aspx.**