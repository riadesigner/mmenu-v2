#!/bin/bash

# export $(grep -v '^#' .env | xargs)
. .env

NOW=$(date +"%m%d%Y-%H%M%S")
RND=$((1 + $RANDOM % 100))
SAVETO="$PWD/app/$LOG_PATH"

docker exec docker.mariadb mariadb-dump -d "$LOCAL_DBNAME" -uroot -p"$LOCAL_DBPASS" > "$SAVETO"db-dump-$NOW-$RND.sql


