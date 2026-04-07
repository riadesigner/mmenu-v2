#!/bin/bash
git restore .
git fetch -a
git pull
chmod -R 777 ./app/tmp
