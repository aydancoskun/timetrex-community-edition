#!/bin/bash

#
# Requires composer modules, install with: "composer install" in this directory
#


#Unit testw with HHVM
#hhvm /usr/bin/phpunit -d max_execution_time=86400 --configuration config.xml



php_bin=/usr/bin/php
#php_bin=/usr/bin/hhvm
#phpunit_bin=/usr/bin/phpunit
phpunit_bin=vendor/phpunit/phpunit/phpunit

if [ "$#" -eq 0 ] ; then
	echo "Running tests in parallel..."
	# Retrieve and parse all groups, strip off the first 5 lines though due to PHPUnit  banner
	groups=$($php_bin $phpunit_bin -d max_execution_time=86400 --configuration config.xml --list-groups | tail -n +5)
	
	 
	parsed=$(echo $groups | sed "s/-/\t/g")
	#Pipe through "shuf" with a consistent random-source to randomize order in which tests are run by keep it consistent from one run to another. This can help avoid many of the same tests from running at the same time and avoid deadlocks 
	#results=$(echo $parsed | awk '{for(i=9;i<=NF;i++) {print $i}}' | shuf --random-source config.xml)
	results=$(echo $parsed | awk '{for(i=9;i<=NF;i++) {print $i}}')
 
	# Loop on each group name and run parallel. Run 2 more jobs than CPU cores, but don't go above a load of 8.
	echo "Start: `date`"
	for i in $results; do
	   echo $i
	done | parallel --no-notice -P 200% --load 100% --halt-on-error 2 $0 -v --group {}
	if [ $? != 0 ] ; then
	        echo "UNIT TESTS FAILED...";
			echo "End: `date`"
	        exit 1;
	fi      
	echo "End: `date`"
elif [ "$1" == "-v" ] ; then
	#Being called from itself, use quiet mode.
	echo -n "Running: $@ :: ";
	#$php_bin $phpunit_bin -d max_execution_time=86400 --configuration config.xml $@ | tail -n 3 | tr -s "\n" | tr "\n" " "
	
	#Capture output to a variable so we show it all if a unit test fails.
	PHPUNIT_OUTPUT=$($php_bin $phpunit_bin -d max_execution_time=86400 --configuration config.xml $@)
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
	$php_bin $phpunit_bin -d max_execution_time=86400 --configuration config.xml $@ 
fi
