#!/usr/bin/env sh

php download.php
sh upload.sh
cp -r api/uae-awqaf/* ../sermons.islamic.network/api/uae-awqaf/
cd ../sermons.islamic.network/api/uae-awqaf/
git add .
git commit -m "Update API with latest sermons"
git pull origin master --rebase && git push origin master