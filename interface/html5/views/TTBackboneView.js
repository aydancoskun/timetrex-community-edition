/**
 * TTBackboneView is the interface between the TimeTrex BaseViewController and any other classes that need
 * to extend from Backbone.View. It allows common logic such as the hierarchical options and required_files
 * to work as expected.
 *
 * When adding functions to this class, make sure they will be compatible with all files that extend from this.
 * Do a search to see these, they include the main UI views, as well as the portal etc.
 *
 * Usage note: When extending this view in a child view instead of from Backbone.View, make sure that your initialize function in the child view
 * has the super.initialize( options ); line at the start (before the required_files or any other non-backbone values from the options are used).
 */

export class TTBackboneView extends Backbone.View {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initialize( options ) {
		//Convert options object to this object properties as early as possible.
		if ( options && typeof options == 'object' ) {
			for ( const property in options ) {
				if ( options.hasOwnProperty( property ) ) { // #2808 See Safari bug https://bugs.webkit.org/show_bug.cgi?id=212449 This hasOwnProperty check is essential to prevent problems due to a bug in Safari with an inherited 'render' on the options Object interfering with the Views render() function.
					this[property] = options[property];
				}
			}
		}
	}
}