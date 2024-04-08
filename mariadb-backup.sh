#!/bin/bash

export $(grep -v '^#' .env | xargs)

NOW=$(date +"%m%d%Y-%H%M%S")
RND=$((1 + $RANDOM % 100))
SAVETO="/home/webuser/YDisk/backups/"

docker exec docker.mariadb mariadb-dump "$PROD_DBNAME" -uroot -p"$PROD_DBPASS" > "$SAVETO"db-mmenu-$NOW-$RND.sql


