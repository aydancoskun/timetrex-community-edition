module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    concat: {
      options: {
        separator: ';\n',
      },
      login: { //Minimum files just required for the Login screen.
        src: [
                '../../interface/html5/framework/stacktrace.js',
                '../../interface/html5/framework/jquery.min.js',
                '../../interface/html5/framework/jquery.form.min.js',
                '../../interface/html5/framework/jqueryui/js/jquery-ui.custom.min.js',
                '../../interface/html5/framework/jquery.i18n.js',
                '../../interface/html5/framework/backbone/underscore-min.js',
                '../../interface/html5/framework/backbone/backbone-min.js',
                '../../interface/html5/framework/jquery.masonry.min.js',
                '../../interface/html5/framework/interact.min.js',
                '../../interface/html5/framework/jquery.sortable.js',
                '../../interface/html5/global/Global.js',
                '../../interface/html5/framework/rightclickmenu/rightclickmenu.js',
                '../../interface/html5/framework/rightclickmenu/jquery.ui.position.js',
                '../../interface/html5/services/APIFactory.js',
                '../../interface/html5/global/LocalCacheData.js'

                ],
        dest: '../../interface/html5/login.composite.js',
      },
      base: { //Files downloaded async while user sits on Login screen.
        src: [
                //Primary Require.js files
                '../../interface/html5/framework/jquery.cookie.js',
                '../../interface/html5/framework/jquery.json.js',
                '../../interface/html5/framework/jquery.tablednd.js',
                '../../interface/html5/framework/jquery.ba-resize.js',
                '../../interface/html5/framework/fastclick.js',
                '../../interface/html5/framework/html2canvas.js',
                '../../interface/html5/framework/date.js',
                '../../interface/html5/model/Base.js',
                '../../interface/html5/services/ServiceCaller.js',
                '../../interface/html5/views/BaseViewController.js',
                '../../interface/html5/views/BaseWindowController.js',

                //Secondary Require.js files
                '../../interface/html5/services/core/APIProgressBar.js',
                '../../interface/html5/framework/jquery.imgareaselect.js',
                '../../interface/html5/framework/widgets/jqgrid/grid.locale-en.js',
                '../../interface/html5/framework/widgets/jqgrid/jquery.jqGrid.src.js',
                '../../interface/html5/framework/widgets/jqgrid/jquery.jqGrid.extend.js',
                '../../interface/html5/framework/widgets/datepicker/jquery-ui-timepicker-addon.js',

                '../../interface/html5/global/widgets/**/*.js', //All widgets

                '../../interface/html5/views/BaseViewController.js', //Must come before ContextMenuConstant
                '../../interface/html5/global/ContextMenuConstant.js',
                '../../interface/html5/global/ProgressBarManager.js',
                '../../interface/html5/global/TAlertManager.js',
                '../../interface/html5/global/PermissionManager.js',
                '../../interface/html5/global/TopMenuManager.js',
                '../../interface/html5/IndexController.js',
                '../../interface/html5/model/Base.js',
                '../../interface/html5/model/SearchField.js',
                '../../interface/html5/model/ResponseObject.js',
                '../../interface/html5/model/RibbonMenu.js',
                '../../interface/html5/model/RibbonSubMenu.js',
                '../../interface/html5/model/RibbonSubMenuGroup.js',
                '../../interface/html5/model/RibbonSubMenuNavItem.js',
                '../../interface/html5/services/ServiceCaller.js',
                '../../interface/html5/services/core/APIProgressBar.js',
                '../../interface/html5/model/APIReturnHandler.js',
                '../../interface/html5/views/BaseViewController.js',
                '../../interface/html5/views/BaseWindowController.js',
                '../../interface/html5/views/wizard/BaseWizardController.js',
                '../../interface/html5/views/wizard/user_generic_data_status/UserGenericStatusWindowController.js',
                '../../interface/html5/views/reports/ReportBaseViewController.js',
                '../../interface/html5/framework/sonic.js',
                '../../interface/html5/framework/jquery.qtip.min.js',

                '../../interface/html5/framework/require.js',
                '../../interface/html5/main.js',

                
                ],
        dest: '../../interface/html5/base.composite.js',
      },
      universe: { //All other .JS files.
        src: [
                '../../interface/html5/**/*.js',
      
                '!../../interface/html5/framework/require.js',
                '!../../interface/html5/framework/fastclick.js',
      
                //Below are already included in login.composite.js
                '!../../interface/html5/framework/stacktrace.js',
                '!../../interface/html5/framework/jquery.min.js',
                '!../../interface/html5/framework/jquery.form.min.js',
                '!../../interface/html5/framework/jqueryui/js/jquery-ui.custom.min.js',
                '!../../interface/html5/framework/jquery.i18n.js',
                '!../../interface/html5/framework/backbone/underscore-min.js',
                '!../../interface/html5/framework/backbone/backbone-min.js',
                '!../../interface/html5/framework/jquery.masonry.min.js',
                '!../../interface/html5/framework/interact.min.js',
                '!../../interface/html5/framework/jquery.sortable.js',
                '!../../interface/html5/global/Global.js',
                '!../../interface/html5/framework/rightclickmenu/rightclickmenu.js',
                '!../../interface/html5/framework/rightclickmenu/jquery.ui.position.js',
                '!../../interface/html5/services/APIFactory.js',
                '!../../interface/html5/global/LocalCacheData.js',
                ],
        dest: '../../interface/html5/universe.composite.js',
      },
    },
    
    concat_css: {
      login: { //Minimum files just required for the Login screen.
        src: [
                '../../interface/html5/theme/default/css/application.css',
                '../../interface/html5/theme/default/css/jquery-ui/jquery-ui.custom.css',
                '../../interface/html5/theme/default/css/ui.jqgrid.css',
                '../../interface/html5/theme/default/css/views/login/LoginView.css',
                '../../interface/html5/theme/default/css/global/widgets/ribbon/RibbonView.css',
                '../../interface/html5/theme/default/css/global/widgets/search_panel/SearchPanel.css',
                '../../interface/html5/theme/default/css/views/attendance/timesheet/TimeSheetView.css',
                '../../interface/html5/theme/default/css/views/attendance/schedule/ScheduleView.css',
                '../../interface/html5/theme/default/css/global/widgets/timepicker/TTimePicker.css',
                '../../interface/html5/theme/default/css/global/widgets/datepicker/TDatePicker.css',
                '../../interface/html5/theme/default/css/right_click_menu/rightclickmenu.css',
                '../../interface/html5/theme/default/css/views/wizard/Wizard.css',
                '../../interface/html5/theme/default/css/image_area_select/imgareaselect-default.css',
                ],
        dest: '../../interface/html5/theme/default/css/login.composite.css',
      },
      universe: { //All other .CSS files.
        //expand: true,
        cwd: '../../interface/html5',
        //src : '**/*.css',
        src : [ '../../interface/html5/theme/default/css/**/*.css',
                '!../../interface/html5/theme/default/css/application.css',
                '!../../interface/html5/theme/default/css/jquery-ui/jquery-ui.custom.css',
                '!../../interface/html5/theme/default/css/ui.jqgrid.css',
                '!../../interface/html5/theme/default/css/views/login/LoginView.css',
                '!../../interface/html5/theme/default/css/global/widgets/ribbon/RibbonView.css',
                '!../../interface/html5/theme/default/css/global/widgets/search_panel/SearchPanel.css',
                '!../../interface/html5/theme/default/css/views/attendance/timesheet/TimeSheetView.css',
                '!../../interface/html5/theme/default/css/views/attendance/schedule/ScheduleView.css',
                '!../../interface/html5/theme/default/css/global/widgets/timepicker/TTimePicker.css',
                '!../../interface/html5/theme/default/css/global/widgets/datepicker/TDatePicker.css',
                '!../../interface/html5/theme/default/css/right_click_menu/rightclickmenu.css',
                '!../../interface/html5/theme/default/css/views/wizard/Wizard.css',
                '!../../interface/html5/theme/default/css/image_area_select/imgareaselect-default.css',                
              ],
        dest : '../../interface/html5/theme/default/css/universe.composite.css'
      }
    },

    cssmin: {
      universe: {
        expand: true,
        cwd: '../../interface/html5',
        src : '**/*.css',
        dest : '../../interface/html5'
      }
    },

    uglify: {
      login: {
        src : '../../interface/html5/login.composite.js',
        dest : '../../interface/html5/login.composite.js'
      },
      base: {
        src : '../../interface/html5/base.composite.js',
        dest : '../../interface/html5/base.composite.js'
      },
      universe: {
        src : '../../interface/html5/universe.composite.js',
        dest : '../../interface/html5/universe.composite.js'
      },
      all_files: {
        expand: true,
        cwd: '../../interface/html5',
        src : '**/*.js',
        dest : '../../interface/html5/'
      }
    }
    
    
  });
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-concat-css');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.registerTask('default', ['concat', 'concat_css', 'cssmin', 'uglify']);
};
