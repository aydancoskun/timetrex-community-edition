import { ServiceCaller } from '@/services/ServiceCaller';

class TimeTrexClientAPI extends ServiceCaller {
	constructor( class_name, key_name ) {
		super();

		this.className = class_name;

		if ( !key_name ) {
			key_name = class_name.replace( 'API', '' );
		}
		this.key_name = key_name;

		return this.enableNoSuchMethod( this );
	}

	enableNoSuchMethod( obj ) {
		return new Proxy( obj, {
			get( target, property_key ) {
				if ( property_key in target ) {
					return target[property_key];
				} else if ( typeof target.__noSuchMethod__ == 'function' ) {
					return function( ...args ) {
						return target.__noSuchMethod__.call( target, property_key, args );
					};
				}
			}
		} );
	}
}

TimeTrexClientAPI.prototype.__noSuchMethod__ = function( method_name, args ) {
	//Debug.Text('Magic Method: '+ method_name + ' Class: '+ this.service_caller.className +' Args: '+ args, 'TimeTrexClientAPI.js', 'TimeTrexClientAPI', '__noSuchMethod__', 11);
	return this.argumentsHandler( this.className, method_name, args );
};

const tt_api_target = {};
const tt_api_class_handler = {
	get( target, class_name ) {
		//Debug.Text('Proxy Handler: Class: ' + class_name, 'TimeTrexClientAPI.js', 'TimeTrexClientAPI', 'get', 11);
		return new TimeTrexClientAPI( class_name );
	},
};

export const TTAPI = new Proxy( tt_api_target, tt_api_class_handler );

