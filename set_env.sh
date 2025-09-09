#!/usr/bin/env bash
# set_env.sh - export DB environment variables for local development
export DB_HOST=127.0.0.1
export DB_USER=root
export DB_PASS=
export DB_NAME=picturethis_dev

echo "Exported DB_HOST=$DB_HOST DB_USER=$DB_USER DB_NAME=$DB_NAME"
