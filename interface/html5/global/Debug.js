var Debug = function() {
};
//Global variables and functions will be used everywhere

Debug.data = {};
Debug.data.start_execution_time = (new Date()).getTime();

Debug.Arr = function( arr, text, file, class_name, function_name, verbosity ) {
	if ( this.getEnable() && verbosity <= this.getVerbosity() ) {
		this.Text( 'Array: ' + text, file, class_name, function_name, verbosity );
		console.debug( arr );
		console.info( '===============================================' );
	}
};

Debug.Text = function( text, file, class_name, function_name, verbosity ) {
	if ( this.getEnable() && verbosity <= this.getVerbosity() ) {
		var time = ('00000' + Debug.getExecutionTime()).slice( -5 );
		var output = ' DEBUG [' + time + 'ms] ';
		if ( file != undefined ) {
			output += file + '::';
		}
		if ( class_name != undefined ) {
			output += class_name + '.';
		}
		if ( function_name != undefined ) {
			output += function_name + '():';
		}
		if ( text != undefined ) {
			if ( file != undefined || class_name != undefined || function_name != undefined ) {
				output += ' ';
			}
			output += text + ' ';
		}
		console.info( output );
	}
};

// Used to temporarily highlight text and values during development
Debug.Highlight = function( text, variable, verbosity ) {
	verbosity = verbosity || 10;
	if ( this.getEnable() && verbosity <= this.getVerbosity() ) {
		var time = ('00000' + Debug.getExecutionTime()).slice( -5 );
		var output = '%c %c HIGHLIGHT [' + time + 'ms] %c'+ text;
		var style_boolean = [
			'border: 1px solid black',
			'border-right: none',
			'margin: 15px 0',
			'padding: 5px 1px'
		].join(';');
		var style_keyword = [
			'color: #375b7d',
			'font-weight: bold',
			'background: #c9def0',
			'border: 1px solid black',
			'margin: 15px 0',
			'padding: 5px'
		].join(';');
		var style_text = [
			'color: #000',
			'background: #c9def0',
			'border: 1px solid black',
			'border-left: none',
			'margin: 15px 0',
			'padding: 5px'
		].join(';');

		if(variable == true) { style_boolean += ';background:green'; }
		else if (variable == false) { style_boolean += ';background:red'; }

		console.info(output, style_boolean, style_keyword, style_text, variable);
	}
};

Debug.backTrace = function( verbosity ) {
	if ( verbosity == undefined ) {
		verbosity = 10;
	}
	if ( this.getEnable() == 1 && verbosity <= this.getVerbosity() ) {
		console.error( 'BACKTRACE:' );
	}
};

Debug.setVerbosity = function( verbosity ) {
	this.data.verbosity = verbosity;
	Debug.Text( '<<<DEBUG VERBOSITY SET TO ' + verbosity + '>>>', 'Debug.js', 'Debug', 'setVerbosity', 0 );
	this.save();
};

Debug.getVerbosity = function() {
	return this.data.verbosity;
};

Debug.getExecutionTime = function() {
	return (new Date()).getTime() - this.data.start_execution_time;
};

Debug.getEnable = function() {
	return this.data.enable;
};

Debug.setEnable = function( value ) {
	this.data.enable = value;
	if ( typeof this.data.verbosity == 'undefined' ) {
		this.data.verbosity = 10;
	}
	if ( value ) {
		Debug.Text( '<<<DEBUG ENABLED>>>', null, null, null, 10 );
	} else {
		console.log( '<<<DEBUG DISABLED>>>' );
	}
	this.save();
};

Debug.closePanel = function() {
	this.data.visible = 0;
	$( '#tt_debug_console' ).remove();
	this.save();
};

// Press CTRL+ALT+SHIFT+F12 to show the panel.
Debug.showPanel = function() {
	//need to check for dependencies before running this code due to Debug.js being loaded statically in the doc head.
	if ( typeof $ != 'undefined' && typeof Global != 'undefined' && typeof ProgressBar != 'undefined' && typeof ie != 'undefined' ) {
		//we can be sure jquery is loaded.
		if ( $( '#tt_debug_console' ).length > 0 ) {
			$( '#tt_debug_console' ).remove();
			this.data.visible = 0;
			this.save();
		} else {
			this.data.visible = 1;
			var $this = this;
			Global.loadViewSource( 'DeveloperTools', 'debugPanelView.html', function( view ) {
				$( 'body' ).append( view );
				Global.loadScript( 'views/developer_tools/debugPanelController.js', function() {
					$( '#tt_debug_enable_checkbox' ).prop( 'checked', Debug.getEnable() );
					$( '#tt_debug_exception_verbosity' ).val( Debug.getVerbosity() );
					$( '#tt_debug_console .tt_version' ).html( APIGlobal.pre_login_data.application_build );
					$( '#tt_overlay_disable_checkbox' ).prop( 'checked', Global.UNIT_TEST_MODE );
					$this.save();
				} );
			} );
		}
	} else {
		setTimeout( function() {
			console.log( 'debugger waiting on dependancies' );
			Debug.showPanel();
		}, 500 );
	}
};

Debug.showGridTest = function() {
	var $this = this;
	IndexViewController.goToView( 'GridTest' );
};

Debug.showAwesomeBoxTest = function() {
	var $this = this;
	IndexViewController.goToView( 'AwesomeboxTest' );
};

Debug.showWidgetTest = function() {
	var $this = this;
	IndexViewController.goToView( 'WidgetTest' );
};

/**
 * Pretty-print objects as an indented string
 *
 * @param obj
 * @returns string
 */
Debug.varDump = function( obj ) {
	var result = '';

	for ( var property in obj ) {
		var value = '\'' + obj[property] + '\'';
		result += '  \'' + property + '\' : ' + value + ',\n';
	}

	return result.replace( /,\n$/, '' );
};


Debug.save = function() {
	if ( typeof this.data != 'undefined' ) {
		//LocalCacheData object doesn't exist when this object is instantiated
		localStorage.debugger = JSON.stringify( this.data );
	}
};

Debug.load = function() {
	//LocalCacheData object doesn't exist when this object is instantiated
	if ( typeof localStorage.debugger != 'undefined' ) {
		var debugger_data = JSON.parse( localStorage.debugger );
		if ( typeof debugger_data == 'object' ) {
			this.data = debugger_data;
		} else {
			setDefaultData();
		}
	} else {
		setDefaultData();
	}

	function setDefaultData() {
		Debug.setVerbosity( 10 );
		Debug.setEnable( false );
		Debug.data.visible = 0;
		Debug.save();
	}
};

window.addEventListener( 'keydown', function( e ) {
	var evt = e ? e : event;
	var keyCode = evt.keyCode;
	//CTRL+ALT+SHIFT+F12(123)
	if ( evt.ctrlKey && evt.shiftKey && evt.altKey && keyCode == 123 ) {
		Debug.showPanel();
	}
} );

//on load get cookie data
{
	Debug.load();
	if ( Debug.data.visible ) {
		Debug.showPanel();
	}
}