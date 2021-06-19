#!/bin/sh
cd /var/www/html/csv-file-loader-app

if [ ! -d "./vendor" ]; then
  composer install --no-interaction
fi

exec "$@"
