<?php
require_once( '../../includes/global.inc.php' );

$authenticate = false;
require_once( Environment::getBasePath() . 'includes/Interface.inc.php' );

//Debug::setVerbosity(11);
$install_obj = new Install();
if ( $install_obj->isInstallMode() == false ) {
	Redirect::Page( URLBuilder::getURL( null, '../install/install.php' ) );
	exit;
} else {
	phpinfo();
}
?>