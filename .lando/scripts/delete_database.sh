#!/usr/bin/env bash

# Parse command options & flags.
for i in "$@"
do
case $i in
    -n=*|--name=*)
    database="${i#*=}"
    shift # past argument=value
    ;;
    *)
      # unknown option
    ;;
esac
done

echo "Deleting database: ${database}";

# Drop the database if it exists.
mysql -uroot -e "DROP DATABASE IF EXISTS ${database}";

