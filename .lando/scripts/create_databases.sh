#!/usr/bin/env bash

for dir in $(find /app/web/sites/ -mindepth 1 -maxdepth 1 -type d) ; do
    db=${dir##*/} ;

    if [ $db != 'default' ]; then
          mysql -uroot -e "CREATE DATABASE IF NOT EXISTS $db; \
                 GRANT ALL PRIVILEGES ON $db.* TO 'drupal8'@'%' IDENTIFIED by 'drupal8'; \
                 CREATE DATABASE IF NOT EXISTS ${db}_legacy; \
                 GRANT ALL PRIVILEGES ON ${db}_legacy.* TO 'drupal8'@'%' IDENTIFIED by 'drupal8';" | cut -f1 -d":";
    fi
done
