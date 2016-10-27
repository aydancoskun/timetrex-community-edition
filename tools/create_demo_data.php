<?php
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

Debug::setBufferOutput(FALSE);
Debug::setEnable(FALSE);
Debug::setVerbosity(0);

/*
Debug::setBufferOutput(TRUE);
Debug::setEnable(TRUE);
Debug::setEnableDisplay(TRUE);
//Debug::setVerbosity(11);
Debug::setVerbosity(10);
*/

//Allow CLI scripts to run much longer.
ini_set( 'max_execution_time', 86400 );
ini_set( 'memory_limit' , '1024M' );

if ( $argc < 2 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: create_demo_data.php [OPTIONS]\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -f (Force creating data even if DEMO_MODE is not enabled. *NOT RECOMMENDED*)\n";
	$help_output .= "    -s [Numeric USER NAME suffix, ie: '100' to create user names like: 'demoadmin100']\n";
	$help_output .= "    -n [Number of random users to create above 25]\n";

	echo $help_output;
} else {
	if ( in_array('-s', $argv) ) {
		$data['suffix'] = trim($argv[array_search('-s', $argv)+1]);
	} else {
		$data['suffix'] = '1';
	}

	if ( in_array('-n', $argv) ) {
		$data['random_users'] = (int)trim($argv[array_search('-n', $argv)+1]);
	} else {
		$data['random_users'] = 0;
	}

	if ( in_array('-f', $argv) ) {
		$data['force'] = TRUE;
	} else {
		$data['force'] = FALSE;
	}

	if ( DEMO_MODE == TRUE OR $data['force'] === TRUE ) {
		$obj = new SystemSettingListFactory();
		$obj->setName( 'system_version' );
		$obj->setValue( APPLICATION_VERSION );
		if ( $obj->isValid() ) {
			$obj->Save();
		}

		$obj = new SystemSettingListFactory();
		$obj->setName( 'tax_engine_version' );
		$obj->setValue( '1.1.0' );
		if ( $obj->isValid() ) {
			$obj->Save();
		}

		$obj = new SystemSettingListFactory();
		$obj->setName( 'tax_data_version' );
		$obj->setValue( date('Ymd') );
		if ( $obj->isValid() ) {
			$obj->Save();
		}

		Debug::Text('Generating Data...', __FILE__, __LINE__, __METHOD__,10);
		echo "UserName suffix: ". $data['suffix'] ." Max Random Users: ". $data['random_users'] ."<br>\n";
		sleep(1);

		$dd = new DemoData();
		$dd->setMaxRandomUsers( $data['random_users'] );
		$dd->setUserNamePostFix( $data['suffix'] );
		$dd->generateData();
	} else {
		echo "DEMO MODE IS NOT ENABLED!<br>\n";
		exit(1);
	}
}
//Debug::Display();
?>
