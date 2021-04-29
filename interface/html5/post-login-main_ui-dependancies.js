// JS
import { RibbonMenu } from '@/model/RibbonMenu';
import { RibbonSubMenu, RibbonSubMenuType } from '@/model/RibbonSubMenu';
import { RibbonSubMenuGroup } from '@/model/RibbonSubMenuGroup';
import { RibbonSubMenuNavItem } from '@/model/RibbonSubMenuNavItem';
import { ContextMenuIconName, Icons } from '@/global/ContextMenuConstant';

import '@/global/widgets/inside_editor/InsideEditor';
import '@/global/widgets/error_tip/ErrorTipBox';
import '@/global/widgets/feedback/TFeedback';
import '@/global/widgets/toggle_button/TToggleButton'; // only used in timesheet and schedule. Potentially refactor to imports in those views.
// import '@/global/widgets/switch_button/SwitchButton';
import '@/global/widgets/view_min_tab/ViewMinTabBar';
import '@/global/widgets/top_alert/TopNotification';
import '@/global/widgets/ribbon/RibbonSubMenuNavWidget';
import '@/global/widgets/search_panel/SearchPanel';

import { SearchField } from '@/model/SearchField';
import 'expose-loader?exposes=ALayoutCache|ALayoutCache!@/global/widgets/awesomebox/ALayoutCache';
import '@/global/widgets/awesomebox/ADropDown';
import '@/global/widgets/awesomebox/AComboBox';
import '@/global/widgets/awesomebox/ASearchInput';
import '@/global/widgets/column_editor/ColumnEditor';
import '@/global/widgets/message_box/SaveAndContinueBox';
import '@/global/widgets/message_box/NoHierarchyBox';
import '@/global/widgets/message_box/NoResultBox';
import '@/global/widgets/separated_box/SeparatedBox';

import { ReportBaseViewController } from '@/views/reports/ReportBaseViewController';

import { AuthorizationHistory } from '@/views/common/AuthorizationHistoryCommon';
import { RequestViewCommonController } from '@/views/common/RequestViewCommonController';
import { EmbeddedMessage } from '@/views/common/EmbeddedMessageCommon';

import { BaseTreeViewController } from '@/views/common/BaseTreeViewController';
import { UserGenericStatusWindowController } from '@/views/wizard/user_generic_data_status/UserGenericStatusWindowController';

import '@/global/widgets/tag_input/TTagInput';
import '@/global/widgets/datepicker/TDatePicker';
import '@/global/widgets/timepicker/TTimePicker';
import '@/global/widgets/datepicker/TRangePicker';
import '@/global/widgets/textarea/TTextArea';
import '@/global/widgets/text/TText';
import '@/global/widgets/list/TList';
import '@/global/widgets/checkbox/TCheckbox';

import '@/global/widgets/jqgrid/TGridHeader';
import '@/global/widgets/ttgrid/TTGrid';

window.RibbonMenu = RibbonMenu;
window.RibbonSubMenu = RibbonSubMenu;
window.RibbonSubMenuType = RibbonSubMenuType;
window.RibbonSubMenuGroup = RibbonSubMenuGroup;
window.RibbonSubMenuNavItem = RibbonSubMenuNavItem;
window.ContextMenuIconName = ContextMenuIconName;
window.Icons = Icons;
window.SearchField = SearchField;
window.ReportBaseViewController = ReportBaseViewController;

// To remove the need for Global.getViewPreloadPathByViewId, include all the preloads upfront.
window.AuthorizationHistory = AuthorizationHistory;
window.RequestViewCommonController = RequestViewCommonController;
window.EmbeddedMessage = EmbeddedMessage;

window.BaseTreeViewController = BaseTreeViewController;
window.UserGenericStatusWindowController = UserGenericStatusWindowController;

LocalCacheData.loadViewRequiredJSReady = true; // #2848 Moved from main.js:loadViewRequiredJS() to ensure that ALL dependancies are loaded, before the rest of the app is loaded.