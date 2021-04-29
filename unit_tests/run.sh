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

#These can't use ../vendor/bin/ versions of the binaries, as those are symlinks and aren't deployed by PHPStorm.
paratest_bin=../vendor/brianium/paratest/bin/paratest
phpunit_bin=../vendor/phpunit/phpunit/phpunit

if [ "$#" -eq 0 ] ; then
	echo "Running tests in parallel..."

	$paratest_bin --configuration config.xml -f -p8 --max-batch-size=1 --stop-on-failure

#	# Retrieve and parse all groups, strip off the first 5 lines though due to PHPUnit  banner
#	groups=$($php_bin $phpunit_bin -d max_execution_time=86400 --configuration config.xml --list-groups | tail -n +5)
#
#
#	parsed=$(echo $groups | sed "s/-/\t/g")
#	#Pipe through "shuf" with a consistent random-source to randomize order in which tests are run by keep it consistent from one run to another. This can help avoid many of the same tests from running at the same time and avoid deadlocks
#	#results=$(echo $parsed | awk '{for(i=9;i<=NF;i++) {print $i}}' | shuf --random-source config.xml)
#	results=$(echo $parsed | awk '{for(i=1;i<=NF;i++) {print $i}}')
#
#	# Loop on each group name and run parallel. Run 2 more jobs than CPU cores, but don't go above a load of 8.
#	echo "Start: `date`"
#	for i in $results; do
#	   echo $i
#	done | parallel --no-notice -P 200% --load 100% --halt-on-error 2 $0 -v --group {}
#	if [ $? != 0 ] ; then
#	        echo "UNIT TESTS FAILED...";
#			echo "End: `date`"
#	        exit 1;
#	fi
#	echo "End: `date`"
elif [ "$1" == "-v" ] ; then
	#Being called from itself, use quiet mode.
	echo -n "Running: $@ :: ";

	#Capture output to a variable so we show it all if a unit test fails.
	#Always stop on failure in this mode so gitlab pipelines are handled properly.
	PHPUNIT_OUTPUT=$($php_bin $phpunit_bin --configuration config.xml --stop-on-failure $@)
	#Capture the exit status of PHPUNIT and make sure we return that.
	exit_code=${PIPESTATUS[0]};

	if [ $exit_code != 0 ] ; then
		#Unit test failed, show all output
		echo -e "$PHPUNIT_OUTPUT";
	else
		#Unit test succeeded, show summary output
		echo -e "$PHPUNIT_OUTPUT" | tail -n 3 | tr -s "\n" | tr "\n" " "
	fi

	echo ""
	exit $exit_code;
else
  # Don't stop on failure when running a single test.
	$php_bin $phpunit_bin --configuration config.xml $@
fi
