#!/usr/bin/env bash

while read DB; do
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS $DB; \
                 GRANT ALL PRIVILEGES ON $DB.* TO 'drupal8'@'%' IDENTIFIED by 'drupal8'; \
                 CREATE DATABASE IF NOT EXISTS ${DB}_legacy; \
                 GRANT ALL PRIVILEGES ON ${DB}_legacy.* TO 'drupal8'@'%' IDENTIFIED by 'drupal8';" | cut -f1 -d":";
done < /app/lando.databases
