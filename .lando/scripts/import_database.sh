#!/usr/bin/env bash

for i in "$@"
do
case $i in
    -d=*|--database=*)
    database="${i#*=}"
    shift # past argument=value
    ;;
    -f=*|--file=*)
    file="${i#*=}"
    shift # past argument=value
    ;;
    --default)
    DEFAULT=YES
    shift # past argument with no value
    ;;
    *)
          # unknown option
    ;;
esac
done

if [ -f "$file" ]; then
    echo "Dropping ${database} database"
    mysql -u root -h $DB_HOST  -D $database -e "DROP DATABASE IF EXISTS ${database};"

    echo "Importing to $database"
    mysql -u root -h $DB_HOST $database < $file
else
    echo "File '$file' does not exist. Aborting import"
fi
