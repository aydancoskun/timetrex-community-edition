// JS
import 'jquery-ui';
import '@/framework/jquery.imgareaselect';
import '@/framework/jquery.json';
import '@/framework/jquery.tablednd';

import '@/framework/widgets/jqgrid/jquery.jqgrid.min';
import '@/framework/widgets/jqgrid/jquery.jqgrid.winmultiselect';
import '@/framework/widgets/jquery.qtip/jquery.qtip.min';

import { TTPDFViewer } from '@/components/pdf-viewer/ttpdfviewer';

// Load jQueryUI plugins.
import 'jquery-ui/ui/widgets/datepicker'; // also needed for jquery-ui-timepicker-addon
import 'jquery-ui/ui/widgets/slider'; // also needed for jquery-ui-timepicker-addon
import 'jquery-ui/ui/widgets/tabs';
import 'jquery-ui/ui/widgets/resizable';
import 'jquery-ui/ui/widgets/sortable';
import 'jquery-ui/ui/widgets/autocomplete';

import '@/framework/widgets/datepicker/jquery-ui-timepicker-addon';
import '@/framework/rightclickmenu/rightclickmenu'; // cant use npm, as this version has been customized by TT.

window.TTPDFViewer = TTPDFViewer;

// after all post login vendor dependancies loaded, import post login app dependancies.
import(
	/* webpackChunkName: "post-login-app-dependancies" */
	'@/post-login-main_ui-dependancies'
).catch( Global.importErrorHandler ); // TODO: Investigate strange error where if we dont have the catch error handler, we get an error (ypeError: Cannot set property 'parseDateTime' of undefined) for ui-timepicker, where undef is the datepicker.