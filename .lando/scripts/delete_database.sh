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

echo "Deleting database: ${database}";

mysql -uroot -e "DROP DATABASE IF EXISTS ${database}";

