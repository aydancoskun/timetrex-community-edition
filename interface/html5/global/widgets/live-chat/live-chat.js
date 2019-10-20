var current_company = LocalCacheData.getLocalCache( 'current_company', 'JSON' ); //Can't use LocalCacheData.getCurrentCompany(); as that uses getRequiredLocalCache() and if the user logs in, then logs out before this can load, it will cause a JS exception.
var current_user = LocalCacheData.getLoginUser();

if ( current_company && current_user ) {

	var LHCChatOptions = {};
	LHCChatOptions.attr = new Array();
	LHCChatOptions.attr.push( {
		'name': 'email',
		'value': ( current_user.work_email != '' ? current_user.work_email : current_user.home_email),
		'type': 'hidden',
		'size': 0
	} );
	LHCChatOptions.attr.push( {
		'name': 'phone',
		'value': ( current_user.work_phone != '' ? current_user.work_phone : current_user.home_phone),
		'type': 'hidden',
		'size': 0
	} );
	LHCChatOptions.attr.push( {
		'name': 'registration_key',
		'value': LocalCacheData.getLoginData().registration_key,
		'type': 'hidden',
		'size': 0
	} );

	LHCChatOptions.attr_online = new Array(); //Online Chat List info.
	LHCChatOptions.attr_online.push( {
		'name': 'username',
		'value': current_user.first_name + ' ' + current_user.last_name,
		'hidden': true
	} );
	LHCChatOptions.attr_online.push( {
		'name': 'email',
		'value': ( current_user.work_email != '' ? current_user.work_email : current_user.home_email),
		'hidden': true
	} );
	LHCChatOptions.attr_online.push( { 'name': 'company', 'value': current_company.name, 'hidden': true } );

	LHCChatOptions.attr_prefill = new Array(); //Chat Form info.
	LHCChatOptions.attr_prefill.push( {
		'name': 'username',
		'value': current_user.first_name + ' ' + current_user.last_name,
		'hidden': true
	} );
	LHCChatOptions.attr_prefill.push( {
		'name': 'email',
		'value': ( current_user.work_email != '' ? current_user.work_email : current_user.home_email),
		'hidden': true
	} );
	LHCChatOptions.attr_prefill.push( {
		'name': 'phone',
		'value': ( current_user.work_phone != '' ? current_user.work_phone : current_user.home_phone),
		'hidden': true
	} );
	LHCChatOptions.attr_prefill.push( { 'name': 'company', 'value': current_company.name, 'hidden': true } );

	LHCChatOptions.attr_prefill_admin = new Array();
	LHCChatOptions.attr_prefill_admin.push( { 'index': '0', 'value': current_company.name, 'hidden': true } );

	LHCChatOptions.opt = {
		widget_height: 340,
		widget_width: 300,
		popup_height: 520,
		popup_width: 500,
		domain: 'timetrex.com'
	};

	//Only make a call to load the chat service JS when the chat button is actually clicked. This should help reduce the chance of JS errors occurring for users who never use the chat.
	function openSupportChat() {
		(function() {
			var po = document.createElement( 'script' );
			po.type = 'text/javascript';
			po.async = true;
			var refferer = (document.referrer) ? encodeURIComponent( document.referrer.substr( document.referrer.indexOf( '://' ) + 1 ) ) : '';
			var location = (document.location) ? encodeURIComponent( window.location.href.substring( window.location.protocol.length ) ) : '';
			po.src = 'https://chat.timetrex.com/index.php/chat/getstatus/(click)/internal/(position)/api/(ma)/br/(dot)/true/(units)/pixels/(leaveamessage)/true/(department)/2/(disable_pro_active)/true?r=' + refferer + '&l=' + location + '&ttr=' + new Date().getTime();
			po.crossOrigin = 'anonymous';
			po.onload = function() { return lh_inst.lh_openchatWindow(); }; //Wait for script to load before popping up chat box.
			var s = document.getElementsByTagName( 'script' )[0];
			s.parentNode.insertBefore( po, s );
		})();
	}
}