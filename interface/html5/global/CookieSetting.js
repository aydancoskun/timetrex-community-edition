function getCookie( name ) {
	var name = name + '=';
	var split_cookie = document.cookie.split( ';' );
	for ( var i = 0; i < split_cookie.length; i++ ) {
		var tmp_cookie = split_cookie[i].trim();

		if ( tmp_cookie.indexOf( name ) === 0 ) {
			return tmp_cookie.substring( name.length, tmp_cookie.length );
		}
	}
	return '';
}

//NOTE: Setting cookie value to "null" does not delete it, and can cause checks like: if ( getCookie('SessionID') ) {} to still succeed.
function setCookie( name, value, expire_days, path, domain, secure ) {
	if ( !path && LocalCacheData && LocalCacheData.cookie_path ) {
		path = LocalCacheData.cookie_path;
	}

	if ( !expire_days ) {
		expire_days = 30;
	}

	//If secure flag is not specified, try to default to secure when using SSL.
	if ( typeof secure === 'undefined' ) {
		if ( APIGlobal.pre_login_data && APIGlobal.pre_login_data.is_ssl && APIGlobal.pre_login_data.is_ssl == true ) {
			secure = true;
		}
	}

	var d = new Date();
	d.setTime( d.getTime() + ( expire_days * 24 * 60 * 60 * 1000 ) );
	var expires = 'expires=' + d.toGMTString();

	cookie_str = name + '=' + value + '; ' + expires + '; path=' + path;
	if ( domain ) {
		cookie_str += '; domain=' + domain;
	}
	if ( secure ) {
		cookie_str += '; secure';
	}

	document.cookie = cookie_str;

	return true;
}

function deleteCookie( name, path, domain ) {
	if ( !path && LocalCacheData && LocalCacheData.cookie_path ) {
		path = LocalCacheData.cookie_path;
	}

	//To delete a cookie we must set 'name=' without any value.
	cookie_str = name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; path=' + path;
	if ( domain ) {
		cookie_str += '; domain=' + domain;
	}

	document.cookie = cookie_str;

	return true;
}