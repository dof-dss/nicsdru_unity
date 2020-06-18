#!/usr/bin/env bash

for i in "$@"
do
case $i in
    -n=*|--name=*)
    database="${i#*=}"
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

echo "Creating database: ${database}";

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ${database}; \
                 GRANT ALL PRIVILEGES ON ${database}.* TO 'drupal8'@'%' IDENTIFIED by 'drupal8';"
