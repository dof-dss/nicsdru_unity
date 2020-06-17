#!/usr/bin/env bash

while read DB; do
 echo "Exporting database: ${DB}"
 mysqldump --opt --user=${USER} --host=${HOST} --port=${PORT} --databases $DB > `date +%Y-%m-%d`.$DB.sql
done < /app/lando.databases
