function getCookie( cname ) {
	var name = cname + "=";
	var ca = document.cookie.split( ';' );
	for ( var i = 0; i < ca.length; i++ ) {
		var c = ca[i];
		while ( c.charAt( 0 ) == ' ' ) c = c.substring( 1 );
		if ( c.indexOf( name ) != -1 ) return c.substring( name.length, c.length );
	}
	return "";
}

function setCookie( cname, cvalue, exdays, path, domain ) {
	if ( !path && LocalCacheData && LocalCacheData.cookie_path ) {
		path = LocalCacheData.cookie_path;
	}

	var d = new Date();
	d.setTime( d.getTime() + (exdays * 24 * 60 * 60 * 1000) );
	var expires = "expires=" + d.toGMTString();

	if ( domain ) {
		document.cookie = cname + "=" + cvalue + "; " + expires + "; path=" + path + "; domain=" + domain;
	} else {
		document.cookie = cname + "=" + cvalue + "; " + expires + "; path=" + path;
	}

}

function deleteCookie(name, path) {
	if ( !path && LocalCacheData && LocalCacheData.cookie_path ) {
		path = LocalCacheData.cookie_path;
	}
	document.cookie= name+'; path='+ path +';expires=Thu, 01-Jan-1970 00:00:01 GMT; domain=' + Global.getHost();
}