#!/usr/bin/env bash


if [ -z "$1" ]; then
  for dir in $(find /app/web/sites/ -mindepth 1 -maxdepth 1 -type d) ; do
    database=${dir##*/} ;

    if [ $database != 'default' ]; then
        echo "Exporting database: ${database}"
        mysqldump --opt --user=${USER} --host=${HOST} --port=${PORT} --databases $database > `date +%Y-%m-%d`.$database.sql
    fi
  done
else
  echo "Exporting database: ${1}"
  mysqldump --opt --user=${USER} --host=${HOST} --port=${PORT} --databases ${1} > `date +%Y-%m-%d`.${1}.sql
fi



