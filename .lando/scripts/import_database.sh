#!/usr/bin/env bash

# Parse command options & flags.
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

# Check the dump file is present on the filesystem.
if [ -f "$file" ]; then

  # Reusable database connection.
  sqlconn="mysql -u root -h $DB_HOST -D $database"

    # Fetch all the tables for this database.
    tables=$($sqlconn -e 'SHOW TABLES' | awk '{ print $1}' | grep -v '^Tables' || true)

    # We drop each table to cleanup the database before import.
    for t in $tables; do
      echo "Dropping $t table from $database database..."
      $sqlconn -e "DROP TABLE $t"
    done

    echo "Importing to $database"
    $sqlconn < $file
else
    echo "File '$file' does not exist. Aborting import"
fi
