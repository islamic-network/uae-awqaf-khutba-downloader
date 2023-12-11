#!/usr/bin/env sh

s3cmd -c ~/.s3cfg_in_as sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/doc/* s3://cdn.islamic.network/sermons/uae-awqaf/doc/
s3cmd -c ~/.s3cfg_in_as sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/mp3/* s3://cdn.islamic.network/sermons/uae-awqaf/mp3/
s3cmd -c ~/.s3cfg_in_as sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/pdf/* s3://cdn.islamic.network/sermons/uae-awqaf/pdf/
s3cmd -c ~/.s3cfg_in_eu sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/doc/* s3://cdn.islamic.network/sermons/uae-awqaf/doc/
s3cmd -c ~/.s3cfg_in_eu sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/mp3/* s3://cdn.islamic.network/sermons/uae-awqaf/mp3/
s3cmd -c ~/.s3cfg_in_eu sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/pdf/* s3://cdn.islamic.network/sermons/uae-awqaf/pdf/
s3cmd -c ~/.s3cfg_in_us sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/doc/* s3://cdn.islamic.network/sermons/uae-awqaf/doc/
s3cmd -c ~/.s3cfg_in_us sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/mp3/* s3://cdn.islamic.network/sermons/uae-awqaf/mp3/
s3cmd -c ~/.s3cfg_in_us sync --acl-public --add-header="cache-control: public, max-age=2628000" --add-header="expires: access plus 30 days" downloads/pdf/* s3://cdn.islamic.network/sermons/uae-awqaf/pdf/