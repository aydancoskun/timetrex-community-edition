// Removing this now, as we no longer support IE11, and we already use ES6 classes which also need modern support.
/*
 * Polyfill file to allow use of modern JS functions in browsers which do not support them, such as IE11.
 * For each Polyfill, try to note which browser/ES version it is targetted at.
 * polyfill added in main.js in 'shim deps' for several key components to ensure polyfills get loaded as early as possible. E.g. underscore, jquery, leaflet-timetrex
 */

/* Object.entries
 * ECMAScript 2017 - Not supported by IE11
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/entries#Polyfill
 * Initially added for use in leaflet-timetrex.js
 */
if ( !Object.entries ) {
	Object.entries = function ( obj ) {
		var ownProps = Object.keys( obj ),
			i = ownProps.length,
			resArray = new Array( i ); // preallocate the Array
		while ( i-- )
			resArray[i] = [ownProps[i], obj[ownProps[i]]];

		return resArray;
	};
}

if ( typeof module !== 'undefined' && module.exports ) {
	module.exports = Object.entries;
}

/* Object.values
 * ECMAScript 2017 - Not supported by IE11
 * https://github.com/KhaledElAnsari/Object.values/blob/master/index.js
 * Initially added for use in leaflet-timetrex.js
 */
if ( !Object.values ) {
	Object.values = function ( obj ) {
		var allowedTypes = ["[object String]", "[object Object]", "[object Array]", "[object Function]"];
		var objType = Object.prototype.toString.call( obj );

		if ( obj === null || typeof obj === "undefined" ) {
			throw new TypeError( "Cannot convert undefined or null to object" );
		} else if ( !~allowedTypes.indexOf( objType ) ) {
			return [];
		} else {
			// if ES6 is supported
			if ( Object.keys ) {
				return Object.keys( obj ).map( function ( key ) {
					return obj[key];
				} );
			}

			var result = [];
			for ( var prop in obj ) {
				if ( obj.hasOwnProperty( prop ) ) {
					result.push( obj[prop] );
				}
			}

			return result;
		}
	};
}

if ( typeof module !== 'undefined' && module.exports ) {
	module.exports = Object.values;
}
