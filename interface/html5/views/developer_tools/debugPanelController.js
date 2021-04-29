$( document ).on( 'click', '.tt_debug_close_btn', function( e ) {
	e.preventDefault();
	Debug.closePanel();
} );

$( document ).on( 'change', '#tt_debug_enable_checkbox', function( e ) {
	e.preventDefault();
	Debug.setEnable( $( this ).is( ':checked' ) );
} );

$( document ).on( 'click', '#trigger_js_exception_button', function( e ) {
	e.preventDefault();
	var exception_type = $( '#tt_debug_exception_type_select' ).val();

	switch ( exception_type ) {
		case 'js_error':
			non_existant_variable.non_existant_function();
			break;
		case 'js_load_script_parser_error':
			var script_path = Global.getViewPathByViewId( 'DeveloperTools' ) + 'triggerParserError.js';
			//remove from cache to ensure that we're sending a totally new request
			delete LocalCacheData.loadedScriptNames[script_path];
			//change the js version number to trigger forced reload
			APIGlobal.pre_login_data.application_build += '_FORCE';
			return Global.loadScript( script_path, function( result ) {
				Debug.Arr( result, 'no error happened.' );
			} );
			break;
		case 'js_load_script_404_error':
			Global.loadScript( 'nonexistantscript.js', function( result ) {
				Debug.Arr( result, 'no error happened.' );
			} );
			break;
	}
} );

$( document ).on( 'click', '#trigger_js_timeout_button', function( e ) {
	e.preventDefault();
	Global.idle_time = 100;
	Global.doPingIfNecessary();
} );

$( document ).on( 'change', '#tt_debug_exception_verbosity', function( e ) {
	e.preventDefault();
	Debug.setVerbosity( $( this ).val() );
} );

$( document ).on( 'change', '#tt_overlay_disable_checkbox', function( e ) {
	e.preventDefault();
	Global.UNIT_TEST_MODE = $( this ).is( ':checked' );
} );

$( document ).on( 'click', '#qunit_test_button', function( e ) {
	e.preventDefault();
	$( '#tt_debug_console' ).css( 'width', '80%' );
	$( '#tt_debug_console' ).css( 'margin', '0 auto' );
	runUnitTests();
} );

/**
 * Put all unit tests in this function
 */

$( document ).on( 'change', '#tt_output_variable_select', function( e ) {
	e.preventDefault();
	output_system_data( $( this ).val() );
} );

$( document ).on( 'click', '#trigger_output_variable_select', function( e ) {
	e.preventDefault();
	output_system_data( $( '#tt_output_variable_select' ).val() );
} );

$( document ).on( 'click', '#trigger_arbitrary_script', function( e ) {
	e.preventDefault();
	var script = $( '#arbitrary_script' ).val();
	script = script.replace( /(\r\n|\n|\r)/gm, '' ); //strip all line-ends
	console.log( eval( script ) );
} );

$( document ).on( 'click', '#awesomebox_test', function( e ) {
	e.preventDefault();
	Debug.showAwesomeBoxTest();
} );

$( document ).on( 'click', '#grid_test', function( e ) {
	e.preventDefault();
	Debug.showGridTest();
} );

$( document ).on( 'click', '#WidgetTest_test', function( e ) {
	e.preventDefault();
	Debug.showWidgetTest();
} );

function breakOn( obj, propertyName, mode, func ) {
	// this is directly from https://github.com/paulmillr/es6-shim
	function getPropertyDescriptor( obj, name ) {
		var property = Object.getOwnPropertyDescriptor( obj, name );
		var proto = Object.getPrototypeOf( obj );
		while ( property === undefined && proto !== null ) {
			property = Object.getOwnPropertyDescriptor( proto, name );
			proto = Object.getPrototypeOf( proto );
		}
		return property;
	}

	function verifyNotWritable() {
		if ( mode !== 'read' )
			throw "This property is not writable, so only possible mode is 'read'.";
	}

	var enabled = true;
	var originalProperty = getPropertyDescriptor( obj, propertyName );
	var newProperty = { enumerable: originalProperty.enumerable };

	// write
	if ( originalProperty.set ) {// accessor property
		newProperty.set = function( val ) {
			if ( enabled && ( !func || func && func( val ) ) )
				debugger;

			originalProperty.set.call( this, val );
		};
	} else if ( originalProperty.writable ) {// value property
		newProperty.set = function( val ) {
			if ( enabled && ( !func || func && func( val ) ) )
				debugger;

			originalProperty.value = val;
		};
	} else {
		verifyNotWritable();
	}

	// read
	newProperty.get = function( val ) {
		if ( enabled && mode === 'read' && ( !func || func && func( val ) ) )
			debugger;

		return originalProperty.get ? originalProperty.get.call( this, val ) : originalProperty.value;
	};

	Object.defineProperty( obj, propertyName, newProperty );

	return {
		disable: function() {
			enabled = false;
		},

		enable: function() {
			enabled = true;
		}
	};
};

function runUnitTests() {
	if ( $( '#qunit_script' ).length == 0 ) {
		$( '<script id=\'qunit_script\' src=\'framework/qunit/qunit.js\'></script>' ).appendTo( 'head' );
		$( '<link rel=\'stylesheet\' type=\'text/css\' href=\'framework/qunit/qunit.css\'>' ).appendTo( 'head' );
		QUnit.config.autostart = false;
		$( '#qunit_container' ).css( 'width', '100vw' );
		$( '#qunit_container' ).css( 'height', '100vh' );
		$( '#qunit_container' ).css( 'overflow-y', 'scroll' );
		$( '#qunit_container' ).css( 'position', 'fixed' );
		$( '#qunit_container' ).css( 'top', '0px' );
		$( '#qunit_container' ).css( 'left', '0px' );
		$( '#qunit_container' ).css( 'z-index', '100' );
		$( '#qunit_container' ).css( 'background', '#fff' );
		$( '#qunit_container' ).show();

		$( '#tt_debug_console' ).remove();
	}
	if ( !window.qunit_initiated ) {
		window.qunit_initiated = true;
		QUnit.start();
	}

	QUnit.module( 'QUnit Sanity' );
	QUnit.test( 'QUnit test', function( assert ) {
		assert.ok( 1 == '1', 'QUnit is loaded and sane!' );
	} );

	QUnit.module( 'Global.js' );
	QUnit.test( 'Global.MoneyRound', function( assert ) {
		assert.ok( Global.MoneyRound( 1.005, 2 ) == '1.01', 'Global.MoneyRound(1.005, 2) == 1.01 -- Passed!' );
		assert.ok( Global.MoneyRound( 1.77777777, 2 ) == '1.78', 'Global.MoneyRound(1.77777777, 2) == 1.78 -- Passed!' );
		assert.ok( Global.MoneyRound( 9.1, 2 ) == '9.10', 'Global.MoneyRound(9.1, 2) == 9.10 -- Passed!' );
		assert.ok( Global.MoneyRound( 1.0049999999999999, 2 ) == '1.01', 'Global.MoneyRound(1.0049999999999999, 2) == 1.01 -- Passed!' );

		assert.ok( Global.MoneyRound( -28.120, 2 ) == '-28.12', 'Global.MoneyRound(-28.120, 2) == -28.12 -- Passed!' );
		assert.ok( Global.MoneyRound( 28.120, 2 ) == '28.12', 'Global.MoneyRound(28.120, 2) == 28.12 -- Passed!' );

		assert.ok( Global.MoneyRound( -28.124, 2 ) == '-28.12', 'Global.MoneyRound(-28.124, 2) == -28.12 -- Passed!' );
		assert.ok( Global.MoneyRound( 28.124, 2 ) == '28.12', 'Global.MoneyRound(28.124, 2) == 28.12 -- Passed!' );
		assert.ok( Global.MoneyRound( -28.125, 2 ) == '-28.13', 'Global.MoneyRound(-28.125, 2) == -28.13 -- Passed!' );
		assert.ok( Global.MoneyRound( 28.125, 2 ) == '28.13', 'Global.MoneyRound(28.125, 2) == 28.13 -- Passed!' );

		assert.ok( Global.MoneyRound( -28.129, 2 ) == '-28.13', 'Global.MoneyRound(-28.129, 2) == -28.13 -- Passed!' );
		assert.ok( Global.MoneyRound( 28.129, 2 ) == '28.13', 'Global.MoneyRound(28.129, 2) == 28.13 -- Passed!' );

		assert.ok( Global.MoneyRound( -0.124, 2 ) == '-0.12', 'Global.MoneyRound(-0.124, 2) == -0.12 -- Passed!' );
		assert.ok( Global.MoneyRound( 0.124, 2 ) == '0.12', 'Global.MoneyRound(0.124, 2) == 0.12 -- Passed!' );
		assert.ok( Global.MoneyRound( -0.155, 2 ) == '-0.16', 'Global.MoneyRound(-0.155, 2) == -0.16 -- Passed!' );
		assert.ok( Global.MoneyRound( 0.155, 2 ) == '0.16', 'Global.MoneyRound(0.155, 2) == 0.16 -- Passed!' );

		assert.ok( Global.MoneyRound( -0.001, 2 ) == '0.00', 'Global.MoneyRound(-0.001, 2) == 0.00 -- Passed!' );
		assert.ok( Global.MoneyRound( 0.001, 2 ) == '0.00', 'Global.MoneyRound(0.001, 2) == 0.00 -- Passed!' );
	} );

	QUnit.test( 'Global.js sort-prefix', function( assert ) {
		var res = Global.removeSortPrefix( '-1234-11111111-1111-1111-1111-111111111111' );
		assert.ok( res == '11111111-1111-1111-1111-111111111111', 'stripped from synth uuid a.' );

		var res = Global.removeSortPrefix( '11111111-1111-1111-1111-111111111111' );
		assert.ok( res == '11111111-1111-1111-1111-111111111111', 'stripped from synth uuid no sort-prefix.' );

		var res = Global.removeSortPrefix( '-1234-111' );
		assert.ok( res == '111', 'stripped from int a.' );

		var res = Global.removeSortPrefix( '111' );
		assert.ok( res == '111', 'stripped from int with no sort-prefix.' );

		var res = Global.removeSortPrefix( '-1234-testStringGalrblyBlah' );
		assert.ok( res == 'testStringGalrblyBlah', 'stripped from string a.' );

		var res = Global.removeSortPrefix( 'testStringGalrblyBlah' );
		assert.ok( res == 'testStringGalrblyBlah', 'stripped from string with no sort-prefix.' );

		var res = Global.removeSortPrefixFromArray( {
			'-1112-testStringGalrblyBlah': 'string',
			'-1113-1234': 'int',
			'-1234-11111111-1111-1111-1111-111111111111': 'uuid'
		} );
		var cnt = 0;

		assert.ok( res['testStringGalrblyBlah'] == 'string', 'prefix stripped properly' );
		assert.ok( res['1234'] == 'int', 'prefix stripped properly' );
		assert.ok( res['11111111-1111-1111-1111-111111111111'] == 'uuid', 'prefix stripped properly' );
	} );

	QUnit.test( 'Global.js parseTimeUnit HH:MM:SS', function( assert ) {
		assert.equal( Global.parseTimeUnit( '00:01', 10 ), 60 );
		assert.equal( Global.parseTimeUnit( '-00:01', 10 ), -60 );

		assert.equal( Global.parseTimeUnit( '01:00', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '10:00', 10 ), 36000 );
		assert.equal( Global.parseTimeUnit( '100:00', 10 ), 360000 );
		assert.equal( Global.parseTimeUnit( '1000:00', 10 ), 3600000 );
		assert.equal( Global.parseTimeUnit( '10000:00', 10 ), 36000000 );
		assert.equal( Global.parseTimeUnit( '10000:01.5', 10 ), 36000060 );

		assert.equal( Global.parseTimeUnit( '01', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '-1', 10 ), -3600 );
		assert.equal( Global.parseTimeUnit( '1:', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1:00:00', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1:00:01', 10 ), 3601 );

		assert.equal( Global.parseTimeUnit( '00:60', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( ':60', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( ':1', 10 ), 60 );

		assert.equal( Global.parseTimeUnit( '1:00:01.5', 10 ), 3601 );
		assert.equal( Global.parseTimeUnit( '1:1.5', 10 ), 3660 );

		//Hybrid mode.
		assert.equal( Global.parseTimeUnit( '1.000', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1.00', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1', 10 ), 3600 );
		assert.equal( Global.parseTimeUnit( '-1', 10 ), -3600 );
		assert.equal( Global.parseTimeUnit( '01', 10 ), 3600 );

		assert.equal( Global.parseTimeUnit( '0.25', 10 ), 900 );
		assert.equal( Global.parseTimeUnit( '0.50', 10 ), 1800 );

		assert.equal( Global.parseTimeUnit( '0.34', 10 ), 1200 ); //Automatically rounds to nearest 1min
	} );

	QUnit.test( 'Global.js parseTimeUnit Hours', function( assert ) {
		assert.equal( Global.parseTimeUnit( '1.000', 20 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1.00', 20 ), 3600 );
		assert.equal( Global.parseTimeUnit( '1', 20 ), 3600 );
		assert.equal( Global.parseTimeUnit( '-1', 20 ), -3600 );
		assert.equal( Global.parseTimeUnit( '01', 20 ), 3600 );

		assert.equal( Global.parseTimeUnit( '0.25', 20 ), 900 );
		assert.equal( Global.parseTimeUnit( '0.50', 20 ), 1800 );

		assert.equal( Global.parseTimeUnit( '0.34', 20 ), 1200 ); //Automatically rounds to nearest 1min

		//Hybrid mode
		assert.equal( Global.parseTimeUnit( '00:01', 20 ), 60 );
		assert.equal( Global.parseTimeUnit( '-00:01', 20 ), -60 );

		assert.equal( Global.parseTimeUnit( ':60', 20 ), 3600 );
		assert.equal( Global.parseTimeUnit( ':1', 20 ), 60 );

		assert.equal( Global.parseTimeUnit( '1:00:01.5', 20 ), 3600 ); //These don't match PHP due to how
		assert.equal( Global.parseTimeUnit( '1:1.5', 20 ), 3660 );

	} );

	QUnit.test( 'Global.js parseTimeUnit Hours Rounding', function( assert ) {
		assert.equal( Global.parseTimeUnit( '0.02', 20 ), ( 1 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.03', 20 ), ( 2 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.05', 20 ), ( 3 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.06', 20 ), ( 4 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.08', 20 ), ( 5 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.10', 20 ), ( 6 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.12', 20 ), ( 7 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.13', 20 ), ( 8 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.15', 20 ), ( 9 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.17', 20 ), ( 10 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.18', 20 ), ( 11 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.20', 20 ), ( 12 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.22', 20 ), ( 13 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.23', 20 ), ( 14 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.25', 20 ), ( 15 * 60 ) );

		assert.equal( Global.parseTimeUnit( '0.27', 20 ), ( 16 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.28', 20 ), ( 17 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.30', 20 ), ( 18 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.32', 20 ), ( 19 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.33', 20 ), ( 20 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.35', 20 ), ( 21 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.37', 20 ), ( 22 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.39', 20 ), ( 23 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.40', 20 ), ( 24 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.42', 20 ), ( 25 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.43', 20 ), ( 26 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.45', 20 ), ( 27 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.47', 20 ), ( 28 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.48', 20 ), ( 29 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.50', 20 ), ( 30 * 60 ) );

		assert.equal( Global.parseTimeUnit( '0.52', 20 ), ( 31 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.53', 20 ), ( 32 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.55', 20 ), ( 33 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.57', 20 ), ( 34 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.58', 20 ), ( 35 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.60', 20 ), ( 36 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.62', 20 ), ( 37 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.63', 20 ), ( 38 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.65', 20 ), ( 39 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.67', 20 ), ( 40 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.68', 20 ), ( 41 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.70', 20 ), ( 42 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.72', 20 ), ( 43 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.73', 20 ), ( 44 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.75', 20 ), ( 45 * 60 ) );

		assert.equal( Global.parseTimeUnit( '0.77', 20 ), ( 46 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.78', 20 ), ( 47 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.80', 20 ), ( 48 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.82', 20 ), ( 49 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.84', 20 ), ( 50 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.85', 20 ), ( 51 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.87', 20 ), ( 52 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.89', 20 ), ( 53 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.90', 20 ), ( 54 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.92', 20 ), ( 55 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.94', 20 ), ( 56 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.95', 20 ), ( 57 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.97', 20 ), ( 58 * 60 ) );
		assert.equal( Global.parseTimeUnit( '0.99', 20 ), ( 59 * 60 ) );
		assert.equal( Global.parseTimeUnit( '1.00', 20 ), ( 60 * 60 ) );
	} );

	QUnit.test( 'Global.js getTimeUnit', function( assert ) {
		assert.equal( Global.getTimeUnit( 3600, 10 ), '01:00' );
		assert.equal( Global.getTimeUnit( 3660, 10 ), '01:01' );
		assert.equal( Global.getTimeUnit( 36060, 10 ), '10:01' );
		assert.equal( Global.getTimeUnit( 36660, 10 ), '10:11' );
		assert.equal( Global.getTimeUnit( 360660, 10 ), '100:11' );
		assert.equal( Global.getTimeUnit( 3600660, 10 ), '1000:11' );
		assert.equal( Global.getTimeUnit( 36000660, 10 ), '10000:11' );
		assert.equal( Global.getTimeUnit( 360000660, 10 ), '100000:11' );
		assert.equal( Global.getTimeUnit( 3600000660, 10 ), '1000000:11' );

		assert.equal( Global.getTimeUnit( -3600, 10 ), '-01:00' );
		assert.equal( Global.getTimeUnit( -3660, 10 ), '-01:01' );
		assert.equal( Global.getTimeUnit( -36060, 10 ), '-10:01' );
		assert.equal( Global.getTimeUnit( -36660, 10 ), '-10:11' );
		assert.equal( Global.getTimeUnit( -360660, 10 ), '-100:11' );
		assert.equal( Global.getTimeUnit( -3600660, 10 ), '-1000:11' );
		assert.equal( Global.getTimeUnit( -36000660, 10 ), '-10000:11' );
		assert.equal( Global.getTimeUnit( -360000660, 10 ), '-100000:11' );
		assert.equal( Global.getTimeUnit( -3600000660, 10 ), '-1000000:11' );

		assert.equal( Global.getTimeUnit( 3600, 12 ), '01:00:00' );
		assert.equal( Global.getTimeUnit( 3661, 12 ), '01:01:01' );
		assert.equal( Global.getTimeUnit( 36060, 12 ), '10:01:00' );
		assert.equal( Global.getTimeUnit( 36660, 12 ), '10:11:00' );

		assert.equal( Global.getTimeUnit( 3600, 23 ), '1.0000' );
		assert.equal( Global.getTimeUnit( 3660, 23 ), '1.0167' );
		assert.equal( Global.getTimeUnit( 3600660, 23 ), '1000.1833' );

		assert.equal( Global.getTimeUnit( 603, 99 ), '10:03' );
		assert.equal( Global.getTimeUnit( 3600, 99 ), '60:00' );
	} );

	QUnit.module( 'UUID Generation' );
	var uuids = [];
	QUnit.test( 'UUID TIGHTLOOP (default logged in user seed)', function( assert ) {
		var max = 3000;
		for ( var i = 0; i < max; i++ ) {
			uuids.push( TTUUID.generateUUID() );
		}
		assert.ok( hasDuplicates( uuids, assert ) == false, 'Duplicate UUIDs!' );
		for ( var i = 0; i < max; i++ ) {
			assert.ok( TTUUID.isUUID( uuids[i] ), 'is UUID' );
		}
	} );

	uuids = [];
	QUnit.test( 'UUID TIGHTLOOP (random seed)', function( assert ) {
		var user_id = LocalCacheData.loginUser.id;
		LocalCacheData.loginUser.id = null;
		var max = 3000;
		for ( var i = 0; i < max; i++ ) {
			uuids.push( TTUUID.generateUUID() );
		}
		assert.ok( hasDuplicates( uuids, assert ) == false, 'Duplicate UUIDs!' );
		for ( var i = 0; i < max; i++ ) {
			assert.ok( TTUUID.isUUID( uuids[i] ), 'is UUID' );
		}
		LocalCacheData.loginUser.id = user_id;
	} );

	QUnit.test( 'Global.isNumeric()', function( assert ) {
		assert.ok( Global.isNumeric( 1483228800 ) == true, '1483228800 is an epoch and numeric' );
		assert.ok( Global.isNumeric( '1483228800' ) == true, '1483228800 is an epoch and a numeric string' );

		//assert.ok(  Global.isNumeric("1,483,228,800") == false , '1,483,228,800 has commas and so is not numeric'); //does not handle commas

		assert.ok( Global.isNumeric( 2.1234 ) == true, '2.1234 is a float and numeric' );
		assert.ok( Global.isNumeric( -2.1234 ) == true, '-2.1234 is a negative float and numeric' );
		assert.ok( Global.isNumeric( '2.1234' ) == true, '"2.1234" is numeric string' );
		assert.ok( Global.isNumeric( -3 ) == true, '-3 is numeric' );
		assert.ok( Global.isNumeric( 0 ) == true, '0 is numeric' );
		assert.ok( Global.isNumeric( 1 ) == true, '1 is numeric' );
		assert.ok( Global.isNumeric( '1' ) == true, '"1" is a numeric string' );
		assert.ok( Global.isNumeric( 'asdf' ) == false, '"asdf" is not numeric' );
		assert.ok( Global.isNumeric( '' ) == false, '"" is not numeric' );
	} );

	/**
	 *
	 * ASYNCHRONOUS TESTS GO AT THE BOTTOM
	 *
	 */

	QUnit.module( 'TTPromise.js' );
	QUnit.test( 'TTPromise Case 1: wait(category) on a single promise', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'test', 'test1' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'TTPromise.filterPromiseArray(test).length == 1' );

		TTPromise.wait( 'test', null, function() {
			//will be run on resolve()
			assert.ok( 1 == '1', 'TEST Promise test resolved.' );
			assert.ok( typeof ( TTPromise.promises['test'] ) == 'undefined', 'promises[test] is null.' );
			assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 0, 'filterPromiseArray(test).length == 0.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test1' ) == false, 'filterPromiseArray("test","test1") length = 0.' );
			done();
		} );

		assert.ok( typeof ( TTPromise.promises['test']['test1'] ) == 'object', 'promises object length = 1.' );
		TTPromise.resolve( 'test', 'test1' );
	} );

	QUnit.test( 'TTPromise Case 1b: wait(category) on a single promise with reject', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'test', 'test1' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'TTPromise.filterPromiseArray(test).length == 1' );

		TTPromise.wait( 'test', null, function() {
			//will be run on resolve()
			assert.ok( 0 == '1', 'TEST Promise test resolved.' ); //THIS SHOULD NOT BE CALLED.
			done();
		}, function() {
			//will be run on reject()
			assert.ok( 1 == '1', 'TEST Promise test rejected.' );
			assert.ok( typeof ( TTPromise.promises['test'] ) == 'undefined', 'promises[test] is null.' );
			assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 0, 'filterPromiseArray(test).length == 0.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test1' ) == false, 'filterPromiseArray("test","test1") length = 0.' );
			done();
		} );

		assert.ok( typeof ( TTPromise.promises['test']['test1'] ) == 'object', 'promises object length = 1.' );
		TTPromise.reject( 'test', 'test1' );
	} );

	QUnit.test( 'TTPromise Case 1c: wait(category) two promises with two rejects on category', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'test', 'test1' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'TTPromise.filterPromiseArray(test).length == 1' );

		TTPromise.add( 'test', 'test2' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 2, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 2, 'TTPromise.filterPromiseArray(test).length == 1' );

		remaining_reject_promises = 0;
		TTPromise.wait( 'test', null, function() {
			//will be run on resolve()
			assert.ok( 0 == '1', 'TEST Promise test resolved.' ); //Fail the test if this is called, since there is a reject.
			done();
		}, function() {
			//will be run on reject()
			assert.ok( 1 == '1', 'TEST Promise test rejected.' );

			assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'filterPromiseArray(test).length == 1.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test1' ) == false, 'filterPromiseArray("test","test1") length = 0.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test2' ).length == 1, 'filterPromiseArray("test","test2") length = 0.' );

			assert.ok( remaining_reject_promises == 0, 'Make sure error callback is not called more than once.' );

			remaining_reject_promises++;

			done(); //Only finish once all promises are rejected.
		} );

		assert.ok( typeof ( TTPromise.promises['test']['test1'] ) == 'object', 'promises object length = 2.' );
		assert.ok( typeof ( TTPromise.promises['test']['test2'] ) == 'object', 'promises object length = 2.' );
		TTPromise.reject( 'test', 'test1' );
		TTPromise.reject( 'test', 'test2' );
	} );

	QUnit.test( 'TTPromise Case 1d: wait(category) two promises with one reject one resolve on category', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'test', 'test1' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'TTPromise.filterPromiseArray(test).length == 1' );

		TTPromise.add( 'test', 'test2' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 2, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 2, 'TTPromise.filterPromiseArray(test).length == 1' );

		remaining_reject_promises = 0;
		TTPromise.wait( 'test', null, function() {
			//will be run on resolve()
			assert.ok( 0 == '1', 'TEST Promise test resolved.' ); //Fail the test if this is called, since there is a reject.
			done();
		}, function() {
			//will be run on reject()
			assert.ok( 1 == '1', 'TEST Promise test rejected.' );

			assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'filterPromiseArray(test).length == 1.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test1' ) == false, 'filterPromiseArray("test","test1") length = 0.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test2' ).length == 1, 'filterPromiseArray("test","test2") length = 0.' );

			assert.ok( remaining_reject_promises == 0, 'Make sure error callback is not called more than once.' );

			remaining_reject_promises++;

			done(); //Only finish once all promises are rejected.
		} );

		assert.ok( typeof ( TTPromise.promises['test']['test1'] ) == 'object', 'promises object length = 2.' );
		assert.ok( typeof ( TTPromise.promises['test']['test2'] ) == 'object', 'promises object length = 2.' );
		TTPromise.reject( 'test', 'test1' );
		TTPromise.resolve( 'test', 'test2' );
	} );

	QUnit.test( 'TTPromise Case 1e: wait(category) two promises with one resolve and one reject on category', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'test', 'test1' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'TTPromise.filterPromiseArray(test).length == 1' );

		TTPromise.add( 'test', 'test2' );
		assert.ok( typeof ( TTPromise.promises['test'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['test'] ).length == 2, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 2, 'TTPromise.filterPromiseArray(test).length == 1' );

		remaining_reject_promises = 0;
		TTPromise.wait( 'test', null, function() {
			//will be run on resolve()
			assert.ok( 0 == '1', 'TEST Promise test resolved.' ); //Fail the test if this is called, since there is a reject.
			done();
		}, function() {
			//will be run on reject()
			assert.ok( 1 == '1', 'TEST Promise test rejected.' );

			assert.ok( TTPromise.filterPromiseArray( 'test' ).length == 1, 'filterPromiseArray(test).length == 1.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test1' ).length == 1, 'filterPromiseArray("test","test1") length = 0.' );
			assert.ok( TTPromise.filterPromiseArray( 'test', 'test2' ) == false, 'filterPromiseArray("test","test2") length = 0.' );

			assert.ok( remaining_reject_promises == 0, 'Make sure error callback is not called more than once.' );

			remaining_reject_promises++;

			done(); //Only finish once all promises are rejected.
		} );

		assert.ok( typeof ( TTPromise.promises['test']['test1'] ) == 'object', 'promises object length = 2.' );
		assert.ok( typeof ( TTPromise.promises['test']['test2'] ) == 'object', 'promises object length = 2.' );
		TTPromise.resolve( 'test', 'test1' );
		TTPromise.reject( 'test', 'test2' );
	} );

	QUnit.test( 'TTPromise Case 2: wait(\'one_of_many_categories\').', function( assert ) {
		var done = assert.async();
		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj empty.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'testa', 'test1' );
		assert.ok( TTPromise.filterPromiseArray( 'testa' ).length == 1, 'filterPromiseArray(testa).length == 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'testa', 'test1' ).length == 1, 'filterPromiseArray("testa","test1") length = 1.' );

		TTPromise.add( 'testa', 'test2' );
		assert.ok( TTPromise.filterPromiseArray( 'testa' ).length == 2, 'filterPromiseArray(testa).length == 2.' );
		assert.ok( TTPromise.filterPromiseArray( 'testa', 'test2' ).length == 1, 'filterPromiseArray("testa","test1") length = 1.' );

		TTPromise.add( 'testb', 'test1b' );
		assert.ok( TTPromise.filterPromiseArray( 'testb' ).length == 1, 'filterPromiseArray(testb).length == 1' );
		assert.ok( TTPromise.filterPromiseArray( 'testb', 'test1b' ).length == 1, 'filterPromiseArray("testb","test1b") length = 1.' );

		TTPromise.add( 'testb', 'test2b' );
		assert.ok( TTPromise.filterPromiseArray( 'testb' ).length == 2, 'filterPromiseArray(testb).length == 2.' );
		assert.ok( TTPromise.filterPromiseArray( 'testb', 'test1b' ).length == 1, 'filterPromiseArray("testb","test1b") length = 1.' );

		TTPromise.wait( 'testa', null, function() {
			//Debug.Arr(TTPromise,'Case2 TTPromise',null,null,null,10);
			assert.ok( 1 == '1', 'TEST Promise testa resolved.' );
			//will be run on resolve()
			assert.ok( typeof ( TTPromise.promises['testa'] ) == 'undefined', 'promises[testa] is null.' );
			assert.ok( typeof ( TTPromise.promises['testb'] ) == 'object', 'promises[testb] is not null.' );

			assert.ok( TTPromise.filterPromiseArray( 'testb' ).length == 2, 'filterPromiseArray(testb).length == 1' ); //one is resolved. should return only 1

			assert.ok( TTPromise.filterPromiseArray( 'testa', 'test1' ).length == 0, 'filterPromiseArray("testb","test1b") length = ' + TTPromise.filterPromiseArray( 'testa', 'test1' ).length );
			assert.ok( TTPromise.filterPromiseArray( 'testa', 'test2' ).length == 0, 'filterPromiseArray("testb","test1b") length = ' + TTPromise.filterPromiseArray( 'testa', 'test2' ).length );
			done();
		} );

		TTPromise.resolve( 'testb', 'test1b' );
		TTPromise.resolve( 'testa', 'test1' );
		TTPromise.resolve( 'testa', 'test2' );
	} );

	QUnit.test( 'TTPromise Case 3: wait(null, null, callback) all cateogries.', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj empty.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'testc', 'test1' );
		assert.ok( TTPromise.filterPromiseArray( 'testc', 'test1' ).length == 1, 'TTPromise.filterPromiseArray(testc,test1).length == 1,.' );
		assert.ok( TTPromise.filterPromiseArray( 'testc' ).length == 1, 'TTPromise.filterPromiseArray(testc).length == 1,.' );

		TTPromise.add( 'testc', 'test2' );
		assert.ok( TTPromise.filterPromiseArray( 'testc', 'test2' ).length == 1, 'TTPromise.filterPromiseArray(testc,test1).length == 1,.' );
		assert.ok( TTPromise.filterPromiseArray( 'testc' ).length == 2, 'TTPromise.filterPromiseArray(testc).length == 2,.' );

		TTPromise.add( 'testd', 'test1b' );
		assert.ok( TTPromise.filterPromiseArray( 'testd', 'test1b' ).length == 1, 'TTPromise.filterPromiseArray(testd,test1b).length == 1,.' );
		assert.ok( TTPromise.filterPromiseArray( 'testc' ).length == 2, 'TTPromise.filterPromiseArray(testc).length == 2,.' );
		assert.ok( TTPromise.filterPromiseArray( 'testd' ).length == 1, 'TTPromise.filterPromiseArray(testd).length == 1,.' );

		TTPromise.add( 'testd', 'test2b' );
		assert.ok( TTPromise.filterPromiseArray( 'testd', 'test2b' ).length == 1, 'TTPromise.filterPromiseArray(testd,test2b).length == 1,.' );
		assert.ok( TTPromise.filterPromiseArray( 'testc' ).length == 2, 'TTPromise.filterPromiseArray(testc).length == 2,.' );
		assert.ok( TTPromise.filterPromiseArray( 'testd' ).length == 2, 'TTPromise.filterPromiseArray(testd).length == 2,.' );

		TTPromise.wait( null, null, function() {
			//will be run on resolve()
			assert.ok( typeof ( TTPromise.promises['testc'] ) == 'undefined', 'promises[testc] is null.' );
			assert.ok( typeof ( TTPromise.promises['testd'] ) == 'undefined', 'promises[testd] is  null.' );
			assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );
			assert.ok( TTPromise.filterPromiseArray( 'testc' ).length == 0, 'TTPromise.filterPromiseArray(testc).length == 0,.' );
			assert.ok( TTPromise.filterPromiseArray( 'testd' ).length == 0, 'TTPromise.filterPromiseArray(testd).length == 0,.' );
			done();
		} );

		assert.ok( TTPromise.filterPromiseArray().length == 4, 'TTPromise.filterPromiseArray().length == 4' );

		TTPromise.resolve( 'testd', 'test1b' );
		TTPromise.resolve( 'testd', 'test2b' );
		TTPromise.resolve( 'testc', 'test1' );
		TTPromise.resolve( 'testc', 'test2' );

	} );

	QUnit.test( 'TTPromise Case 4: wait(category, key) on a single promise', function( assert ) {
		var done = assert.async();

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'Reports', 'LoadReports' );
		assert.ok( typeof ( TTPromise.promises['Reports'] ) == 'object', 'TTPromise.promises[\'test\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['Reports'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'Reports' ).length == 1, 'TTPromise.filterPromiseArray(test).length == 1' );

		TTPromise.wait( 'Reports', 'LoadReports', function() {
			//will be run on resolve()
			assert.ok( 1 == '1', 'TEST Promise test resolved.' );
			assert.ok( typeof ( TTPromise.promises['Reports'] ) == 'undefined', 'promises[Reports] is null.' );
			assert.ok( TTPromise.filterPromiseArray( 'Reports' ).length == 0, 'filterPromiseArray(Reports).length == 0.' );
			assert.ok( TTPromise.filterPromiseArray( 'Reports', 'LoadReports' ) == false, 'filterPromiseArray("Reports","LoadReports") length = 0.' );
			done();
		} );

		assert.ok( typeof ( TTPromise.promises['Reports']['LoadReports'] ) == 'object', 'promises object length = 1.' );
		TTPromise.resolve( 'Reports', 'LoadReports' );
	} );

	QUnit.test( 'TTPromise Case 5: wait(category, key,function) vs wait(null,null,function)', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();
		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'groupone', 'one' );
		assert.ok( typeof ( TTPromise.promises['groupone'] ) == 'object', 'TTPromise.promises[\'groupone\'] exists.' );
		assert.ok( Object.keys( TTPromise.promises['groupone'] ).length == 1, 'promises object length = 1.' );
		assert.ok( TTPromise.filterPromiseArray( 'groupone' ).length == 1, 'TTPromise.filterPromiseArray(groupone).length == 1' );

		TTPromise.wait( 'groupone', 'one', function() {
			//will be run on resolve()
			Debug.Text( 'SINGLE PROMISE test resolved second.', '', '', '', 10 );
			assert.ok( group_promise_test == '1', 'SINGLE PROMISE test resolved second.' );
			group_promise_test = 2;
			done();
		} );

		TTPromise.add( 'grouptwo', 'one' );
		TTPromise.add( 'grouptwo', 'two' );
		TTPromise.add( 'grouptwo', 'three' );
		TTPromise.wait( null, null, function() {
			//will be run on resolve()
			assert.ok( group_promise_test == '0', 'ALL PROMISE test resolved first.' );
			group_promise_test = 1;
			Debug.Text( 'ALL PROMISE test resolved first.', '', '', '', 10 );
		} );

		TTPromise.resolve( 'grouptwo', 'one' );
		TTPromise.resolve( 'grouptwo', 'two' );
		TTPromise.resolve( 'groupone', 'one' );
		TTPromise.resolve( 'grouptwo', 'three' );

		TTPromise.resolve( 'Reports', 'LoadReports' );
	} );

	QUnit.test( 'TTPromise Case 6: resolve 2, wait on 1, global wait.', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'a', 'a' );
		TTPromise.add( 'a', 'b' );
		TTPromise.add( 'a', 'c' );

		TTPromise.resolve( 'a', 'a' );
		TTPromise.resolve( 'a', 'b' );
		TTPromise.resolve( 'a', 'c' );

		TTPromise.add( 'b', 'a' );
		TTPromise.add( 'b', 'b' );

		var callbacks = 1;
		TTPromise.wait( 'a', 'a', function() {
			//will be run on resolve()
			assert.ok( callbacks == 1, 'already resolved promise resolved first.' );
			callbacks++;
		} );

		TTPromise.wait( null, null, function() {

			assert.ok( callbacks == 2, 'null wait resolves after first resolution' );
			callbacks++;
			done();
		} );

		TTPromise.resolve( 'b', 'a' );
		TTPromise.resolve( 'b', 'b' );
	} );

	QUnit.test( 'TTPromise Case 7: resolve already resolved stack when other promises exist.', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'a', 'a' );
		TTPromise.add( 'a', 'b' );
		TTPromise.add( 'a', 'c' );

		TTPromise.resolve( 'a', 'a' );
		TTPromise.resolve( 'a', 'b' );
		TTPromise.resolve( 'a', 'c' );

		TTPromise.add( 'b', 'a' );
		TTPromise.add( 'b', 'b' );

		var callbacks = 1;
		TTPromise.wait( 'a', 'a', function() {
			//will be run on resolve()
			assert.ok( callbacks == 1, 'already resolved promise resolved before resolving unrelated pending promises.' );
			done();
		} );
		callbacks = 2;
		TTPromise.resolve( 'b', 'a' );
		TTPromise.resolve( 'b', 'b' );
	} );

	QUnit.test( 'TTPromise Case 8: resolve already resolved when other promises exist.', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'a', 'a' );
		TTPromise.add( 'a', 'b' );
		TTPromise.add( 'a', 'c' );

		TTPromise.resolve( 'a', 'a' );
		TTPromise.resolve( 'a', 'b' );
		TTPromise.resolve( 'a', 'c' );

		TTPromise.add( 'b', 'a' );
		TTPromise.add( 'b', 'b' );

		var callbacks = 1;
		TTPromise.wait( 'a', null, function() {
			//will be run on resolve()
			assert.ok( callbacks == 1, 'already resolved promise stack resolved before resolving unrelated pending promises.' );
			done();
		} );
		callbacks = 2;
		TTPromise.resolve( 'b', 'a' );
		TTPromise.resolve( 'b', 'b' );
	} );

	QUnit.test( 'TTPromise Case 9: 3 parallel stacks only 1 and 3 resolve..', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'a', 'a' );
		TTPromise.add( 'a', 'b' );
		TTPromise.add( 'a', 'c' );

		TTPromise.add( 'b', 'a' );
		TTPromise.add( 'b', 'b' );
		TTPromise.add( 'b', 'c' );

		TTPromise.add( 'c', 'a' );
		TTPromise.add( 'c', 'b' );
		TTPromise.add( 'c', 'c' );

		TTPromise.resolve( 'a', 'a' );
		TTPromise.resolve( 'a', 'b' );
		TTPromise.resolve( 'a', 'c' );

		var callbacks = 1;
		TTPromise.wait( 'a', null, function() {
			//will be run on resolve()
			assert.ok( callbacks == 1, 'already resolved promise resolved before resolving unrelated pending promises.' );
			callbacks++;
		} );

		TTPromise.wait( 'b', null, function() {
			//will be run on resolve()
			assert.ok( callbacks == 999, 'should NEVER reolve.' );
			callbacks++;
		} );
		TTPromise.wait( 'c', null, function() {
			//will be run on resolve()
			assert.ok( callbacks == 2, 'already resolved promise resolved before resolving unrelated pending promises.' );
			callbacks++;
			done();
		} );

		TTPromise.resolve( 'a', 'a' );
		TTPromise.resolve( 'a', 'b' );
		//TTPromise.resolve('a','c'); //do not fully resolve b

		TTPromise.resolve( 'c', 'a' );
		TTPromise.resolve( 'c', 'b' );
		TTPromise.resolve( 'c', 'c' );

	} );

	QUnit.test( 'TTPromise Case 10: identical waits fail..', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'a', 'a' );

		var callbacks = 1;
		TTPromise.wait( 'a', 'a', function() {
			//will be run on resolve()
			assert.ok( callbacks == 1, 'first resolution.' );
			callbacks++;
		} );

		TTPromise.wait( 'a', 'a', function() {
			//will be run on resolve()
			assert.ok( callbacks == 2, 'does not resolve in error case.' );
			callbacks++;
		} );

		window.setTimeout( function() {
			assert.ok( callbacks == 3, 'should complete after both callbacks.' );
			done();
		}, 4000 );

		window.setTimeout( function() {
			TTPromise.resolve( 'a', 'a' );
		}, 2000 );

	} );

	QUnit.test( 'TTPromise Case 11: identical waits fail after resolution of first.', function( assert ) {
		var done = assert.async();
		var group_promise_test = 0;

		TTPromise.clearAllPromises();

		assert.ok( Object.keys( TTPromise.promises ).length == 0, 'Callback: promises obj length = 0.' );
		assert.ok( typeof ( TTPromise.promises ) == 'object', 'TTPromise.promises exists.' );

		TTPromise.add( 'a', 'a' );

		var callbacks = 1;
		TTPromise.wait( 'a', 'a', function() {
			//will be run on resolve()
			assert.ok( callbacks == 1, 'first resolution.' );
			callbacks++;
		} );

		TTPromise.resolve( 'a', 'a' );

		window.setTimeout( function() {
			TTPromise.wait( 'a', 'a', function() {
				//will be run on resolve()
				assert.ok( callbacks == 2, 'does not resolve in error case: callbacks: ' + callbacks );
				callbacks++;
			} );

			window.setTimeout( function() {
				assert.ok( callbacks == 3, 'should complete after both callbacks.' );
				done();
			}, 8000 );

			window.setTimeout( function() {
				TTPromise.resolve( 'a', 'a' );
			}, 5000 );
		}, 2000 );

	} );
}

function hasDuplicates( array, assert ) {
	var valuesSoFar = Object.create( null );
	for ( var i = 0; i < array.length; ++i ) {
		var value = array[i];
		if ( value in valuesSoFar ) {
			assert.ok( 1 == 2, value + ' Is not unique.' );
			return true;
		}
		valuesSoFar[value] = true;
	}
	return false;
}

function output_system_data( val ) {
	switch ( val ) {
		case '0':
			if ( LocalCacheData.current_open_primary_controller ) {
				console.log( LocalCacheData.current_open_primary_controller.current_edit_record );
			} else {
				Debug.Text( 'Primary current_edit_record does not exist.', 'debugPanelController.js', '', '$(document).on(\'change\', \'#tt_output_variable_select\')', 10 );
			}
			break;
		case '1':
			if ( LocalCacheData.current_open_report_controller ) {
				console.log( LocalCacheData.current_open_report_controller.current_edit_record );
			} else {
				Debug.Text( 'Report current_edit_record does not exist.', 'debugPanelController.js', '', '$(document).on(\'change\', \'#tt_output_variable_select\')', 10 );
			}
			break;
		case '2':
			if ( LocalCacheData.current_open_sub_controller ) {
				console.log( LocalCacheData.current_open_sub_controller.current_edit_record );
			} else {
				Debug.Text( 'Sub Controller current_edit_record does not exist.', 'debugPanelController.js', '', '$(document).on(\'change\', \'#tt_output_variable_select\')', 10 );
			}
			break;
		case '3':
			if ( LocalCacheData.current_open_edit_only_controller ) {
				console.log( LocalCacheData.current_open_edit_only_controller.current_edit_record );
			} else {
				Debug.Text( 'Edit Only current_edit_record does not exist.', 'debugPanelController.js', '', '$(document).on(\'change\', \'#tt_output_variable_select\')', 10 );
			}
			break;
		case '4':
			if ( LocalCacheData.current_open_wizard_controller ) {
				console.log( LocalCacheData.current_open_wizard_controller.current_edit_record );
			} else {
				Debug.Text( 'Wizard current_edit_record does not exist.', 'debugPanelController.js', '', '$(document).on(\'change\', \'#tt_output_variable_select\')', 10 );
			}
			break;
	}
}