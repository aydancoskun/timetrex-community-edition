class QuickPunchBaseViewController extends TTBackboneView {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	getRequiredFiles() {
		//override in child class
		return [];
	}

	/**
	 * When changing this function, you need to look for all occurences of this function because it was needed in several bases
	 * BaseViewController, HomeViewController, BaseWizardController, QuickPunchBaseViewControler
	 *
	 * @returns {Array}
	 */
	filterRequiredFiles() {
		var retval = [];
		var required_files;

		if ( typeof this._required_files == 'object' ) {
			required_files = this._required_files;
		} else {
			required_files = this.getRequiredFiles();
		}

		if ( required_files && required_files[0] ) {
			retval = required_files;
		} else {
			for ( var edition_id in required_files ) {
				if ( Global.getProductEdition() >= edition_id ) {
					retval = retval.concat( required_files[edition_id] );
				}
			}
		}

		Debug.Arr( retval, 'RETVAL', 'BaseViewController.js', 'BaseViewController', 'filterRequiredFiles', 10 );
		return retval;
	}
}
