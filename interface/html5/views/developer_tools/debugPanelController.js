$(document).on('click', '.tt_debug_close_btn', function(e) {
    e.preventDefault();
    Debug.closePanel();
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


$(document).on('change', '#tt_overlay_disable_checkbox', function(e) {
    e.preventDefault();
    Global.UNIT_TEST_MODE = $(this).is(':checked');
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
        $('#qunit_container').css('width','100vw');
        $('#qunit_container').css('height','100vh');
        $('#qunit_container').css('overflow-y','scroll');
        $('#qunit_container').css('position','fixed');
        $('#qunit_container').css('top','0px');
        $('#qunit_container').css('left','0px');
        $('#qunit_container').css('z-index','100');
        $('#qunit_container').css('background','#fff');
        $('#qunit_container').show();

        $('#tt_debug_console').remove();
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

		assert.ok(Global.MoneyRound(-28.120, 2) == '-28.12', 'Global.MoneyRound(-28.120, 2) == -28.12 -- Passed!');
		assert.ok(Global.MoneyRound(28.120, 2) == '28.12', 'Global.MoneyRound(28.120, 2) == 28.12 -- Passed!');

		assert.ok(Global.MoneyRound(-28.124, 2) == '-28.12', 'Global.MoneyRound(-28.124, 2) == -28.12 -- Passed!');
		assert.ok(Global.MoneyRound(28.124, 2) == '28.12', 'Global.MoneyRound(28.124, 2) == 28.12 -- Passed!');
		assert.ok(Global.MoneyRound(-28.125, 2) == '-28.13', 'Global.MoneyRound(-28.125, 2) == -28.13 -- Passed!');
		assert.ok(Global.MoneyRound(28.125, 2) == '28.13', 'Global.MoneyRound(28.125, 2) == 28.13 -- Passed!');

		assert.ok(Global.MoneyRound(-28.129, 2) == '-28.13', 'Global.MoneyRound(-28.129, 2) == -28.13 -- Passed!');
		assert.ok(Global.MoneyRound(28.129, 2) == '28.13', 'Global.MoneyRound(28.129, 2) == 28.13 -- Passed!');

		assert.ok(Global.MoneyRound(-0.124, 2) == '-0.12', 'Global.MoneyRound(-0.124, 2) == -0.12 -- Passed!');
		assert.ok(Global.MoneyRound(0.124, 2) == '0.12', 'Global.MoneyRound(0.124, 2) == 0.12 -- Passed!');
		assert.ok(Global.MoneyRound(-0.155, 2) == '-0.16', 'Global.MoneyRound(-0.155, 2) == -0.16 -- Passed!');
		assert.ok(Global.MoneyRound(0.155, 2) == '0.16', 'Global.MoneyRound(0.155, 2) == 0.16 -- Passed!');

		assert.ok(Global.MoneyRound(-0.001, 2) == '0.00', 'Global.MoneyRound(-0.001, 2) == 0.00 -- Passed!');
		assert.ok(Global.MoneyRound(0.001, 2) == '0.00', 'Global.MoneyRound(0.001, 2) == 0.00 -- Passed!');
    });


	QUnit.test("Global.js sort-prefix", function (assert) {
		var res = Global.removeSortPrefix('-1234-11111111-1111-1111-1111-111111111111')
		assert.ok( res == '11111111-1111-1111-1111-111111111111' , 'stripped from synth uuid a.');

		var res = Global.removeSortPrefix('11111111-1111-1111-1111-111111111111')
		assert.ok( res == '11111111-1111-1111-1111-111111111111' , 'stripped from synth uuid no sort-prefix.');

		var res = Global.removeSortPrefix('-1234-111')
		assert.ok( res == '111' , 'stripped from int a.');

		var res = Global.removeSortPrefix('111')
		assert.ok( res == '111' , 'stripped from int with no sort-prefix.');

		var res = Global.removeSortPrefix('-1234-testStringGalrblyBlah')
		assert.ok( res == 'testStringGalrblyBlah' , 'stripped from string a.');

		var res = Global.removeSortPrefix('testStringGalrblyBlah')
		assert.ok( res == 'testStringGalrblyBlah' , 'stripped from string with no sort-prefix.');


		var res = Global.removeSortPrefixFromArray({'-1112-testStringGalrblyBlah':'string', '-1113-1234':'int', '-1234-11111111-1111-1111-1111-111111111111':'uuid' });
		var cnt = 0;

		assert.ok( res['testStringGalrblyBlah'] == 'string' , 'prefix stripped properly');
		assert.ok( res['1234'] == 'int' , 'prefix stripped properly');
		assert.ok( res['11111111-1111-1111-1111-111111111111'] == 'uuid' , 'prefix stripped properly');
	});

	var uuids = [];
	QUnit.test( "uuuid TIGHTLOOP(default logged in user seed)", function (assert) {
		var max = 3000;
		for ( var i = 0; i< max; i++ ){
			uuids.push ( TTUUID.generateUUID() );
		}
		assert.ok(  hasDuplicates(uuids) == false , 'has duplicates');
		for ( var i = 0; i< max; i++ ){
			assert.ok( TTUUID.isUUID( uuids[i] ), 'is UUID');
		}
	});

	uuids = [];
	QUnit.test( "uuuid TIGHTLOOP (random seed)", function (assert) {
		var user_id = LocalCacheData.loginUser.id;
		LocalCacheData.loginUser.id = null;
		var max = 3000;
		for ( var i = 0; i< max; i++ ){
			uuids.push ( TTUUID.generateUUID() );
		}
		assert.ok(  hasDuplicates(uuids) == false , 'has duplicates');
		for ( var i = 0; i< max; i++ ){
			assert.ok( TTUUID.isUUID( uuids[i] ), 'is UUID');
		}
		LocalCacheData.loginUser.id = user_id;
	});

	/**
	 *
	 * ASYNCHRONOUS TESTS GO AT THE BOTTOM
	 *
	 */

	QUnit.test("TTPromise Case 1: wait(category) on a single promise", function (assert) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys(TTPromise.promises).length == 0 , 'Callback: promises obj length = 0.');
		assert.ok( typeof(TTPromise.promises) == 'object' , "TTPromise.promises exists.");

		TTPromise.add('test','test1');
		assert.ok( typeof(TTPromise.promises['test']) == 'object' , "TTPromise.promises['test'] exists.");
		assert.ok( Object.keys(TTPromise.promises['test']).length == 1 , 'promises object length = 1.');
		assert.ok( TTPromise.filterPromiseArray('test').length == 1, 'TTPromise.filterPromiseArray(test).length == 1');

		TTPromise.wait('test',null, function(){
			//will be run on resolve()
			assert.ok(1 == "1", "TEST Promise test resolved.");
			assert.ok( typeof (TTPromise.promises['test']) == "undefined" , 'promises[test] is null.');
			assert.ok( TTPromise.filterPromiseArray('test').length == 0, 'filterPromiseArray(test).length == 0.');
			assert.ok( TTPromise.filterPromiseArray('test', 'test1') == false , 'filterPromiseArray("test","test1") length = 0.');
			done();
		});

		assert.ok( typeof (TTPromise.promises['test']['test1'])  == 'object', 'promises object length = 1.');
		TTPromise.resolve('test','test1');
	});

	QUnit.test("TTPromise Case 2: wait('one_of_many_categories').", function (assert) {
		var done = assert.async();
		TTPromise.clearAllPromises();

		assert.ok( Object.keys(TTPromise.promises).length == 0 , 'Callback: promises obj empty.');
		assert.ok( typeof(TTPromise.promises) == 'object' , "TTPromise.promises exists.");

		TTPromise.add('testa','test1');
		assert.ok( TTPromise.filterPromiseArray('testa').length == 1, 'filterPromiseArray(testa).length == 1.');
		assert.ok( TTPromise.filterPromiseArray('testa', 'test1').length == 1, 'filterPromiseArray("testa","test1") length = 1.');

		TTPromise.add('testa','test2');
		assert.ok( TTPromise.filterPromiseArray('testa').length == 2, 'filterPromiseArray(testa).length == 2.');
		assert.ok( TTPromise.filterPromiseArray('testa', 'test2').length == 1, 'filterPromiseArray("testa","test1") length = 1.');

		TTPromise.add('testb','test1b');
		assert.ok( TTPromise.filterPromiseArray('testb').length == 1, 'filterPromiseArray(testb).length == 2.');
		assert.ok( TTPromise.filterPromiseArray('testb', 'test1b').length == 1, 'filterPromiseArray("testb","test1b") length = 1.');

		TTPromise.add('testb','test2b');
		assert.ok( TTPromise.filterPromiseArray('testb').length ==2, 'filterPromiseArray(testb).length == 2.');
		assert.ok( TTPromise.filterPromiseArray('testb', 'test1b').length == 1, 'filterPromiseArray("testb","test1b") length = 1.');


		TTPromise.wait('testa',null, function(){
			//Debug.Arr(TTPromise,'Case2 TTPromise',null,null,null,10);
			assert.ok(1 == "1", "TEST Promise testa resolved.");
			//will be run on resolve()
			assert.ok( typeof (TTPromise.promises['testa']) == "undefined" , 'promises[testa] is null.');
			assert.ok( typeof (TTPromise.promises['testb']) == "object" , 'promises[testb] is not null.');
			assert.ok( TTPromise.filterPromiseArray('testb').length == 2, 'filterPromiseArray(testb).length == '+TTPromise.filterPromiseArray('testb').length);
			assert.ok( TTPromise.filterPromiseArray('testa', 'test1').length == 0, 'filterPromiseArray("testb","test1b") length = '+TTPromise.filterPromiseArray('testa', 'test1').length);
			assert.ok( TTPromise.filterPromiseArray('testa', 'test2').length == 0, 'filterPromiseArray("testb","test1b") length = '+TTPromise.filterPromiseArray('testa', 'test2').length);
			done();
		});

		TTPromise.resolve('testb','test1b');
		TTPromise.resolve('testa','test1');
		TTPromise.resolve('testa','test2');
	});

	QUnit.test("TTPromise Case 3: wait(null, null, callback) all cateogries.", function (assert) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys(TTPromise.promises).length == 0 , 'Callback: promises obj empty.');
		assert.ok( typeof(TTPromise.promises) == 'object' , "TTPromise.promises exists.");

		TTPromise.add('testc','test1');
		assert.ok( TTPromise.filterPromiseArray('testc','test1').length == 1, 'TTPromise.filterPromiseArray(testc,test1).length == 1,.');
		assert.ok( TTPromise.filterPromiseArray('testc').length == 1, 'TTPromise.filterPromiseArray(testc).length == 1,.');

		TTPromise.add('testc','test2');
		assert.ok( TTPromise.filterPromiseArray('testc','test2').length == 1, 'TTPromise.filterPromiseArray(testc,test1).length == 1,.');
		assert.ok( TTPromise.filterPromiseArray('testc').length == 2, 'TTPromise.filterPromiseArray(testc).length == 2,.');

		TTPromise.add('testd','test1b');
		assert.ok( TTPromise.filterPromiseArray('testd','test1b').length == 1, 'TTPromise.filterPromiseArray(testd,test1b).length == 1,.');
		assert.ok( TTPromise.filterPromiseArray('testc').length == 2, 'TTPromise.filterPromiseArray(testc).length == 2,.');
		assert.ok( TTPromise.filterPromiseArray('testd').length == 1, 'TTPromise.filterPromiseArray(testd).length == 1,.');

		TTPromise.add('testd','test2b');
		assert.ok( TTPromise.filterPromiseArray('testd','test2b').length == 1, 'TTPromise.filterPromiseArray(testd,test2b).length == 1,.');
		assert.ok( TTPromise.filterPromiseArray('testc').length == 2, 'TTPromise.filterPromiseArray(testc).length == 2,.');
		assert.ok( TTPromise.filterPromiseArray('testd').length == 2, 'TTPromise.filterPromiseArray(testd).length == 2,.');

		TTPromise.wait(null, null, function(){
			//will be run on resolve()
			assert.ok( typeof (TTPromise.promises['testc']) == "undefined", 'promises[testc] is null.');
			assert.ok( typeof (TTPromise.promises['testd']) == "undefined", 'promises[testd] is  null.');
			assert.ok( typeof(TTPromise.promises) == 'object' , "TTPromise.promises exists.");
			assert.ok( TTPromise.filterPromiseArray('testc').length == 0, 'TTPromise.filterPromiseArray(testc).length == 0,.');
			assert.ok( TTPromise.filterPromiseArray('testd').length == 0, 'TTPromise.filterPromiseArray(testd).length == 0,.');
			done();
		});

		assert.ok( TTPromise.filterPromiseArray().length == 4 , 'TTPromise.filterPromiseArray().length == 4');

		TTPromise.resolve('testd','test1b');
		TTPromise.resolve('testd','test2b');
		TTPromise.resolve('testc','test1');
		TTPromise.resolve('testc','test2');

	});

	QUnit.test("TTPromise Case 4: wait(category, key) on a single promise", function (assert) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys(TTPromise.promises).length == 0 , 'Callback: promises obj length = 0.');
		assert.ok( typeof(TTPromise.promises) == 'object' , "TTPromise.promises exists.");

		TTPromise.add('Reports','LoadReports');
		assert.ok( typeof(TTPromise.promises['Reports']) == 'object' , "TTPromise.promises['test'] exists.");
		assert.ok( Object.keys(TTPromise.promises['Reports']).length == 1 , 'promises object length = 1.');
		assert.ok( TTPromise.filterPromiseArray('Reports').length == 1, 'TTPromise.filterPromiseArray(test).length == 1');

		TTPromise.wait('Reports','LoadReports', function(){
			//will be run on resolve()
			assert.ok(1 == "1", "TEST Promise test resolved.");
			assert.ok( typeof (TTPromise.promises['Reports']) == "undefined" , 'promises[Reports] is null.');
			assert.ok( TTPromise.filterPromiseArray('Reports').length == 0, 'filterPromiseArray(Reports).length == 0.');
			assert.ok( TTPromise.filterPromiseArray('Reports', 'LoadReports') == false , 'filterPromiseArray("Reports","LoadReports") length = 0.');
			done();
		});

		assert.ok( typeof (TTPromise.promises['Reports']['LoadReports'])  == 'object', 'promises object length = 1.');
		TTPromise.resolve('Reports','LoadReports');
	});

	QUnit.test("TTPromise Case w: wait(category, key,function) vs wait(null,null,function)", function (assert) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();
		assert.ok( Object.keys(TTPromise.promises).length == 0 , 'Callback: promises obj length = 0.');
		assert.ok( typeof(TTPromise.promises) == 'object' , "TTPromise.promises exists.");

		TTPromise.add('groupone','one');
		assert.ok( typeof(TTPromise.promises['groupone']) == 'object' , "TTPromise.promises['groupone'] exists.");
		assert.ok( Object.keys(TTPromise.promises['groupone']).length == 1 , 'promises object length = 1.');
		assert.ok( TTPromise.filterPromiseArray('groupone').length == 1, 'TTPromise.filterPromiseArray(groupone).length == 1');

		TTPromise.wait('groupone','one', function(){
			//will be run on resolve()
			Debug.Text('SINGLE PROMISE test resolved second.','','','',10);
			assert.ok( group_promise_test == "1", "SINGLE PROMISE test resolved second.");
			group_promise_test = 2;
			done();
		});

		TTPromise.add('grouptwo','one');
		TTPromise.add('grouptwo','two');
		TTPromise.add('grouptwo','three');
		TTPromise.wait(null, null, function(){
			//will be run on resolve()
			assert.ok(group_promise_test == "0", "ALL PROMISE test resolved first.");
			group_promise_test = 1;
			Debug.Text('ALL PROMISE test resolved first.','','','',10);
		})

		TTPromise.resolve('grouptwo','one');
		TTPromise.resolve('grouptwo','two');
		TTPromise.resolve('groupone','one');
		TTPromise.resolve('grouptwo','three');


		TTPromise.resolve('Reports','LoadReports');
	});
}

function hasDuplicates(array) {
	var valuesSoFar = Object.create(null);
	for (var i = 0; i < array.length; ++i) {
		var value = array[i];
		if (value in valuesSoFar) {
			assert.ok( 1 == 2, value + ' is not unique' );
			return true;
		}
		valuesSoFar[value] = true;
	}
	return false;
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