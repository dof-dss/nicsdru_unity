#!/bin/bash

. /helpers/log.sh

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

lando_green "Deleting database: ${database}";

# Drop the database if it exists.
mysql -uroot -e "DROP DATABASE IF EXISTS ${database}";

