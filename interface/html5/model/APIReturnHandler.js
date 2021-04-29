/*

 To be the common  data model for data return from api

 */
import { Base } from './Base';

export class APIReturnHandler extends Base {

	constructor( options = {} ) {
		_.defaults( options, {
			result_data: null,
			delegate: null
		} );

		super( options );
	}

	isValid() {
		if ( Global.isSet( this.get( 'result_data' ).api_retval ) && this.get( 'result_data' ).api_retval === false ) {
			return false;
		}

		return true;
	}

	getDetails() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.details ) ) {
			return this.get( 'result_data' ).api_details.details;
		}

		return true;
	}

	getPagerData() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.pager ) ) {
			return this.get( 'result_data' ).api_details.pager;
		}

		return false;
	}

	getResult() {

		var result;
		if ( Global.isSet( this.get( 'result_data' ).api_retval ) ) {
			result = this.get( 'result_data' ).api_retval;
		} else {
			result = this.get( 'result_data' );
		}

		if ( typeof result === 'undefined' ) {
			result = null;
		} else if ( $.type( result ) === 'array' && result.length === 0 ) {
			result = {};
		}

		return result;

	}

	getCode() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.code ) ) {
			return this.get( 'result_data' ).api_details.code;
		}

		return false;
	}

	getDescription() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.description ) ) {
			return this.get( 'result_data' ).api_details.description;
		}

		return false;
	}

	getRecordDetails() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) ) {
			return this.get( 'result_data' ).api_details.record_details;
		}

		return false;
	}

	getTotalRecords() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) &&
			Global.isSet( this.get( 'result_data' ).api_details.record_details.total_records ) ) {
			return this.get( 'result_data' ).api_details.record_details.total_records;
		}

		return false;
	}

	getValidRecords() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) &&
			Global.isSet( this.get( 'result_data' ).api_details.record_details.valid_records ) ) {
			return this.get( 'result_data' ).api_details.record_details.valid_records;
		}

		return false;
	}

	getInValidRecords() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) &&
			Global.isSet( this.get( 'result_data' ).api_details.record_details.invalid_records ) ) {
			return this.get( 'result_data' ).api_details.record_details.invalid_records;
		}

		return false;
	}

	getAttributeInAPIDetails( attrName ) {
		return this.get( 'result_data' ).api_details[attrName];
	}

	getDetailsAsString() {
		var errorInfo = '';

		$.each( this.getDetails(), function( index, errorItem ) {

			for ( var i in errorItem ) {
				errorInfo += errorItem[i][0] + '\r';
			}
		} );

		return errorInfo;
	}

}
