#!/bin/bash

#
# Requires composer modules, install with: "composer install" in this directory
#

#Use: export XDEBUG_REMOTE_HOST=10.7.5.9
# or: unset XDEBUG_REMOTE_HOST
if [[ -z "${XDEBUG_REMOTE_HOST}" ]]; then
    php_bin="/usr/bin/php"
else
    #PHP v7.x
    #php_bin="/usr/bin/php -d xdebug.remote_host=${XDEBUG_REMOTE_HOST} -d xdebug.remote_enable=on -d xdebug.remote_autostart=on -d xdebug.remote_connect_back"

    #PHP v8.0
    php_bin="/usr/bin/php -d xdebug.client_host=${XDEBUG_REMOTE_HOST} -d xdebug.mode=debug -d xdebug.start_with_request=yes -d xdebug.discover_client_host"
fi

phpunit_bin=../vendor/phpunit/phpunit/phpunit

$php_bin $phpunit_bin -d max_execution_time=86400 --configuration config_selenium.xml $@
