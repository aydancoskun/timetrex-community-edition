var APIFactory = ( function() {

	var api_dic = {};

	//Use require's path map from main.js instead of building one here.
	var api_path_map = requirejs.s.contexts._.config.paths;

	var getAPIClass = function( apiName ) {
		var api_class = api_dic[apiName];
		var path;
		if ( !api_class ) {
			path = api_path_map[apiName] + '.js';
		}

		Global.loadScript( path );

		try {
			/* jshint ignore:start */
			api_class = eval( apiName + ';' );
			/* jshint ignore:end */
		} catch ( e ) {
			api_class = APIAuthentication;
		}

		return api_class;
	};

	return {
		getAPIClass: getAPIClass

	};

} )();

