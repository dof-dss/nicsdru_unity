#!/usr/bin/env bash

for dir in $(find /app/web/sites/ -mindepth 1 -maxdepth 1 -type d) ; do
    db=${dir##*/} ;

    if [ $db != 'default' ]; then
        echo "Exporting database: ${db}"
        mysqldump --opt --user=${USER} --host=${HOST} --port=${PORT} --databases $db > `date +%Y-%m-%d`.$db.sql
    fi

done
