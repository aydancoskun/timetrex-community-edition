/*
 * $License$
 */

var APIPayStubEntryAccountLink = ServiceCaller.extend( {

	key_name: 'PayStubEntryAccountLink',
	className: 'APIPayStubEntryAccountLink',

	getPayStubEntryAccountLink: function() {

		return this.argumentsHandler( this.className, 'getPayStubEntryAccountLink', arguments );

	},

} );