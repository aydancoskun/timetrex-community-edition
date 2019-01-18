<?php
/**
 * $License$
 */

/**
 * @package API\Import
 */
class APIImportRemittanceDestinationAccount extends APIImport {
	protected $main_class = 'ImportRemittanceDestinationAccount';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}
}
?>
