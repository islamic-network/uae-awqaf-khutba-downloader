#!/usr/bin/env sh

php download.php
# sh upload.sh
php api.php
cp -r api/uae-awqaf/* ../sermons.islamic.network/api/uae-awqaf/
cp -r yaml/uae-awqaf/* ../sermons.islamic.network/_data/uae-awqaf/
cd ../sermons.islamic.network
git add api/
git add _data/
git commit -m "Update API with latest sermons"
git pull origin master --rebase && git push origin master
