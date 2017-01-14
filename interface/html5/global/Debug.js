var Debug = function() {
};
//Global variables and functions will be used everywhere

Debug.verbosity = 0;
Debug.enable = 0;
Debug.visible = 0;
Debug.start_execution_time = (new Date()).getTime();

Debug.Arr = function (arr, text, file, class_name, function_name, verbosity) {
    if ( this.getEnable() == 1 && verbosity <= this.getVerbosity() ) {
        this.Text('Array: '+text, file, function_name, class_name, verbosity);
        console.debug(arr);
        console.info('===============================================');
    }
};

Debug.Text = function (text, file, class_name, function_name, verbosity) {
    if ( this.getEnable() == 1 && verbosity <= this.getVerbosity() ) {
        var time = ("00000"+Debug.getExecutionTime()).slice(-5);
        var output = ' DEBUG ['+time+'ms] ';
        if ( file != undefined ) {
            output += file+'::';
        }
        if ( class_name != undefined ) {
            output += class_name+'.';
        }
        if ( function_name != undefined ) {
            output += function_name+'():';
        }
        if ( text != undefined ) {
            if ( file != undefined || class_name != undefined || function_name != undefined ) {
                output += ' ';
            }
            output += text+' ';
        }
        console.info(output);
    }
};

Debug.backTrace = function(verbosity) {
    if ( verbosity == undefined ) {
        verbosity = 10
    }
    if ( this.getEnable() == 1 && verbosity <= this.getVerbosity() ) {
        console.error('BACKTRACE:')
    }
};

Debug.setVerbosity = function (verbosity ) {
    this.verbosity = verbosity;
    Debug.Text('<<<DEBUG VERBOSITY SET TO '+verbosity+'>>>', 'Debug.js', 'Debug', 'setVerbosity', 0);
};

Debug.getVerbosity = function () {
    return this.verbosity;
};

Debug.getExecutionTime = function() {
    return (new Date()).getTime() - this.start_execution_time;
};

Debug.getEnable = function(){
    return this.enable;
};

Debug.setEnable = function(value){
    if (value) {
        document.cookie  = 'debug=1';
        this.enable = 1;
        if ( this.verbosity == 0 ) {
            this.verbosity = 10;
        }
        Debug.Text('<<<DEBUG ENABLED>>>', null, null, null, 10);
    } else {
        document.cookie = 'debug=0';
        this.enable = 0;
        console.log('<<<DEBUG DISABLED>>>');
    }
};

// Press CTRL+ALT+SHIFT+F12 to show the panel.
Debug.showPanel = function() {
    //we can be sure jquery is loaded.
    if ($('#tt_debug_console').length > 0) {
        $('#tt_debug_console').remove();
    }

    Global.loadViewSource('DeveloperTools', 'debugPanelView.html', function (view) {
        $('body').append(view);
        Global.loadScript('views/developer_tools/debugPanelController.js');
        $('#tt_debug_enable_checkbox').prop('checked', Debug.getEnable());
        $('#tt_debug_exception_verbosity').val( Debug.getVerbosity() );
        $('#tt_debug_console .tt_version').html( APIGlobal.pre_login_data.application_build );
    });
};

/**
 * Pretty-print objects as an indented string
 *
 * @param obj
 * @returns string
 */
Debug.varDump = function (obj) {
    var result = "";

    for (var property in obj) {
        var value = "'" + obj[property] + "'";
        result += "  '" + property + "' : " + value + ",\n";
    }

    return result.replace(/,\n$/, "");
};

window.addEventListener('keydown',function(e){
    var evt = e ? e:event;
    var keyCode = evt.keyCode;
    //CTRL+ALT+SHIFT+F12(123)
    if ( evt.ctrlKey && evt.shiftKey && evt.altKey && keyCode == 123 ) {
        Debug.showPanel();
    }
});

//on load get cookie data
{
    var cookie = document.cookie;
    if ( cookie.indexOf('debug=1') >= 0 ) {
        Debug.setVerbosity(10);
        Debug.setEnable(1);
    } else {
        Debug.setEnable(0);
    }
}