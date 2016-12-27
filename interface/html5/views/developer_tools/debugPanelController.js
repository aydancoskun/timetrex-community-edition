$(document).on('click', '.tt_debug_close_btn', function(e) {
    e.preventDefault();
    $('#tt_debug_console').remove();
});

$(document).on('change', '#tt_debug_enable_checkbox', function(e) {
    e.preventDefault();
    Debug.setEnable($(this).is(':checked'));
});

$(document).on('click', '#trigger_js_exception_button', function(e) {
    e.preventDefault();
    var exception_type = $('#tt_debug_exception_type_select').val();

    switch ( exception_type ) {
        case 'js_error':
            non_existant_variable.non_existant_function();
            break;
        case 'js_load_script_parser_error':
            var script_path =  Global.getViewPathByViewId( 'DeveloperTools' ) + 'triggerParserError.js'
            //remove from cache to ensure that we're sending a totally new request
            delete LocalCacheData.loadedScriptNames[script_path];
            //change the js version number to trigger forced reload
            APIGlobal.pre_login_data.application_build += '_FORCE'
            return Global.loadScript( script_path, function(result){
                Debug.Arr(result, 'no error happened.')
            })
            break;
        case 'js_load_script_404_error':
            Global.loadScript( 'nonexistantscript.js', function(result){
                Debug.Arr(result, 'no error happened.')
            })
            break;
    }
});

$(document).on('click', '#trigger_js_timeout_button', function(e) {
    e.preventDefault();
    Global.idle_time = 100;
    Global.doPingIfNecessary();
});

$(document).on('change', '#tt_debug_exception_verbosity', function(e) {
    e.preventDefault();
    Debug.setVerbosity($(this).val());
});