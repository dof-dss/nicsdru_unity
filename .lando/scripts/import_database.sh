#!/usr/bin/env bash

if [ -f "$2" ]; then
    echo "Dropping $1 database"
    mysql -u root -h $DB_HOST  -D $1 -e "DROP DATABASE IF EXISTS ${1};"

    echo "Importing to $1"
    mysql -u root -h $DB_HOST $1 < $2
else
    echo "File '$2' does not exist. Aborting import"
fi
