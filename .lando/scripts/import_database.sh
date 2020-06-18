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
    *)
          # unknown option
    ;;
esac
done

if [ -f "$file" ]; then

  sqlconn="mysql -u root -h $DB_HOST -D $database"

    tables=$($sqlconn -e 'SHOW TABLES' | awk '{ print $1}' | grep -v '^Tables' || true)

    for t in $tables; do
      echo "Dropping $t table from $database database..."
      $sqlconn -e "DROP TABLE $t"
    done

    echo "Importing to $database"
    $sqlconn < $file
else
    echo "File '$file' does not exist. Aborting import"
fi
