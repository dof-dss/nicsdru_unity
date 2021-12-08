#!/usr/bin/env bash

if [ $# -eq 0 ]
  then
    echo "No drush command supplied, please supply a drush command to run on all sites e.g. './drushmulti.sh cr'"
    exit 0
fi

for site in `ls -l web/sites | grep ^d | awk '!/default/{print $9}'`
do
  echo "** $site **"
  lando drush -l $site $1
done
