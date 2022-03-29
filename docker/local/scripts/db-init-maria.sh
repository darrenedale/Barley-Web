#! /usr/bin/env bash

# create initial (empty) databases and users for coda
docker exec -it barley-web-db-1 mysql -u root -p -e "
CREATE DATABASE IF NOT EXISTS barley;
GRANT ALL ON barley.* TO 'barley-web'@'%' IDENTIFIED BY 'barley';
FLUSH PRIVILEGES;
"
