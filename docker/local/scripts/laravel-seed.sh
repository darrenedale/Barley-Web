#! /usr/bin/env bash
docker exec barley-web-php81-1 php /usr/share/nginx/html/artisan db:seed
