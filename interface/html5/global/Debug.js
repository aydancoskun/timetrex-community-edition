/*
 * $License$
 */

var Debug = function() {
};
//Global variables and functions will be used everywhere

Debug.verbosity = 0;
Debug.enable = 0;

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
    this.enable = value;
};

window.addEventListener('keydown',function(e){
    var evt = e ? e:event;
    var keyCode = evt.keyCode;
    //CTRL+ALT+SHIFT+F12(123)
    if ( evt.ctrlKey && evt.shiftKey && evt.altKey && keyCode == 123 ) {
        if ( Debug.getEnable() == 1 ) {
            Debug.Text('<<<DEBUG DISABLED>>>', null, null, null, 10);
            Debug.setEnable(0);
            Debug.setVerbosity(0);
            cookie = 'debug=0';
        } else {
            Debug.setEnable(1);
            Debug.setVerbosity(10);
            cookie  = 'debug=1';
            Debug.Text('<<<DEBUG ENABLED>>>', null, null, null, 10);
        }
        document.cookie = cookie;

    }
});

//on load get cookie data
{
    var cookie = document.cookie;
    if ( cookie.indexOf('debug=1') >= 0 ) {
        Debug.setEnable(1);
        Debug.setVerbosity(10);
        Debug.Text('<<<DEBUG ENABLED>>>', null, null, null, 10);
    } else {
        Debug.setEnable(0);
        Debug.setVerbosity(0);
    }
}
