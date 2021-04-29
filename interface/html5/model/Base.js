export class Base extends Backbone.Model {

	constructor( options = {} ) {
		_.defaults( options, {
		} );

		super( options );
	}
}
