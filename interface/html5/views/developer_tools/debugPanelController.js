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

$(document).on('click', '#qunit_test_button', function(e) {
    e.preventDefault();
    $('#tt_debug_console').css('width','80%');
    $('#tt_debug_console').css('margin','0 auto');
    runUnitTests();
});

/**
 * Put all unit tests in this function
 */

$(document).on('change', '#tt_output_variable_select', function(e) {
    e.preventDefault();
    output_system_data( $(this).val() );
});

$(document).on('click', '#trigger_output_variable_select', function(e) {
    e.preventDefault();
    output_system_data( $('#tt_output_variable_select').val() );
});

$(document).on('click', '#trigger_arbitrary_script', function(e) {
    e.preventDefault();
    var script = $('#arbitrary_script').val()
    script = script.replace(/(\r\n|\n|\r)/gm,""); //strip all line-ends
    console.log(eval( script ));
});

function runUnitTests() {
    if( $('#qunit_script').length == 0 ){
        $("<script id='qunit_script' src='framework/qunit/qunit.js'></script>").appendTo('head');
        $("<link rel='stylesheet' type='text/css' href='framework/qunit/qunit.css'>").appendTo('head');
        QUnit.config.autostart = false;
    }
    if( !window.qunit_initiated ) {
        window.qunit_initiated = true;
        QUnit.start();
    }

    QUnit.test("QUnit test", function (assert) {
        assert.ok(1 == "1", "QUnit is loaded and sane!");
    });

    QUnit.test('Global.MoneyRound', function (assert) {
        assert.ok(Global.MoneyRound(1.005, 2) == '1.01', 'Global.MoneyRound(1.005, 2) == 1.01 -- Passed!');
        assert.ok(Global.MoneyRound(1.77777777, 2) == '1.78', 'Global.MoneyRound(1.77777777, 2) == 1.78 -- Passed!');
        assert.ok(Global.MoneyRound(9.1, 2) == '9.10', 'Global.MoneyRound(9.1, 2) == 9.10 -- Passed!');
        assert.ok(Global.MoneyRound(1.0049999999999999, 2) == '1.01', 'Global.MoneyRound(1.0049999999999999, 2) == 1.01 -- Passed!');
    });
}

function output_system_data(val){
    switch ( val ) {
        case '0':
            if ( LocalCacheData.current_open_primary_controller ) {
                Debug.Arr(LocalCacheData.current_open_primary_controller.current_edit_record, 'Primary current_edit_record:', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            } else {
                Debug.Text('Primary current_edit_record does not exist.', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            }
            break;
        case '1':
            if ( LocalCacheData.current_open_report_controller ) {
                Debug.Arr(LocalCacheData.current_open_report_controller.current_edit_record, 'Report current_edit_record:', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            } else {
                Debug.Text('Report current_edit_record does not exist.', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            }
            break;
        case '2':
            if ( LocalCacheData.current_open_sub_controller ) {
                Debug.Arr(LocalCacheData.current_open_sub_controller.current_edit_record, 'Sub Controller current_edit_record:', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            } else {
                Debug.Text('Sub Controller current_edit_record does not exist.', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            }
            break;
        case '3':
            if ( LocalCacheData.current_open_edit_only_controller ) {
                Debug.Arr(LocalCacheData.current_open_edit_only_controller.current_edit_record, 'Edit Only current_edit_record:', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            } else {
                Debug.Text('Edit Only current_edit_record does not exist.', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            }
            break;
        case '4':
            if ( LocalCacheData.current_open_wizard_controller ) {
                Debug.Arr(LocalCacheData.current_open_wizard_controller.current_edit_record, 'Wizard current_edit_record:', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            } else {
                Debug.Text('Wizard current_edit_record does not exist.', 'debugPanelController.js', '' ,"$(document).on('change', '#tt_output_variable_select')", 10);
            }
            break;
    }
}
