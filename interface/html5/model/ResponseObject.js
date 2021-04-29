import { Base } from './Base';

export class ResponseObject extends Base {
	constructor( options = {} ) {
		_.defaults( options, {
			onResult: null,
			onError: null,
			delegate: null,
			async: null
		} );
		super( options );
	}
}
