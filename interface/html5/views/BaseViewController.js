/* jshint ignore:start */
//Don't check this file for now. Too many issues.
BaseViewController = Backbone.View.extend( {
	real_this: null, //For super call in second level sub class

	sub_view_mode: false,

	edit_only_mode: false,

	can_cache_controller: true, //if allow to cache current controller

	permission_id: '',
	api: null,
	user_generic_data_api: null,
	all_columns: [],
	display_columns: [],
	default_display_columns: [],
	script_name: '',
	filter_data: null, //current Filter data get from Search panel
	temp_basic_filter_data: null,
	temp_adv_filter_data: null,
	sortData: null, //Current Sort data get from search panel
	select_layout: null,
	layout_changed: false,
	search_panel: null,
	grid: null,
	context_menu_name: '',
	navigation_label: '',
	context_menu_array: [],
	t_grid_header_array: [],

	//Column Selector in search panel
	column_selector: null,

	sort_by_selector: null,

	save_search_as_input: null,

	previous_saved_layout_selector: null,

	previous_saved_layout_div: null,

	need_select_layout_name: '', //Set this when save new layout to choose the new layout

	search_fields: null,

	basic_search_field_ui_dic: {}, //Save AwesomeBox when they created

	adv_search_field_ui_dic: {}, //Save AwesomeBox when they created

	edit_view_ui_dic: {},

	edit_view_ui_validation_field_dic: {},

	edit_view_form_item_dic: {}, //Whole FormItem

	edit_view_error_ui_dic: {},

	edit_view: null,

	edit_view_tab: null,

	current_edit_record: null, //Current edit record

	refresh_id: null, //Set this to refresh one record in grid view.

	navigation: null, // Navigation widget in edit view

	is_mass_editing: false, //Set when mass edit

	is_viewing: false,
	is_edit: false,
	is_add: false,

	unique_columns: [], //Set when Mass edit, mark which fields need to be disable

	linked_fields: [],

	mass_edit_record_ids: [], // Mass edit records

	edit_view_tabs: [],

	refresh_sub_view: false,

	parent_key: null, //default filter when search

	parent_value: null, //default filter when search

	parent_edit_record: null,

	invisible_context_menu_dic: null,

	total_display_span: null,

	paging_widget: null,

	paging_widget_2: null, //Put in the bottom of data grid

	pager_data: null,

	viewId: null,

	init_options_complete: false,

	no_result_box: null, // No Result Found Black cover when no result in grid

	table_name_key: null,

	sub_log_view_controller: null,

	parent_view_controller: null, //Add this to call parent_view_controll cancel action when cancel from sub view

	ui_id: '',

	is_changed: false, // Track if modified any fields in edit view

	confirm_on_exit: false, //confirm before leaving the edit view even if no changes have been made

	edit_view_tpl: '', //Edit view html name

	subMenuNavMap: null,

	trySetGridSizeWhenTabShow: false, // Set sub view grid size when tab show instead when tab select

	copied_record_id: '', // When copy as new, save copied reord's id

	other_field_api: null,

	last_select_ids: null,

	saving_layout_in_layout_tab: false, //Mark if save layout from Saved and layout tab. if so, don't switch tabs when set values to search panel

	need_switch_to_context_menu: false,

	show_search_tab: true,

	grid_total_width: null,

	show_warning_when_validation: false,

	pulse_time_dic: false,

	edit_view_close_icon: null,

	enable_validation: true,

	_required_files: null,

	tab_model: null, //Tab definitions and a map to their callbacks.

	context_menu_model: null,

	grid_parent: null,

	getRequiredFiles: function() {
		//override in child class
		return [];
	},

	/**
	 * When changing this function, you need to look for all occurences of this function because it was needed in several bases
	 * BaseViewController, HomeViewController, BaseWizardController, QuickPunchBaseViewControler
	 *
	 * @returns {Array}
	 */
	filterRequiredFiles: function() {
		var retval = [];
		var required_files;

		if ( typeof this._required_files == 'object' ) {
			required_files = this._required_files;
		} else {
			required_files = this.getRequiredFiles();
		}

		if ( required_files && required_files[0] ) {
			retval = required_files;
		} else {
			for ( var edition_id in required_files ) {
				if ( Global.getProductEdition() >= edition_id ) {
					retval = retval.concat( required_files[edition_id] );
				}
			}
		}

		Debug.Arr( retval, 'RETVAL', 'BaseViewController.js', 'BaseViewController', 'filterRequiredFiles', 10 );
		return retval;
	},

	preInit: function() {
		//override in child class
	},

	initialize: function( options ) {
		Debug.Text( 'INITIALIZE', 'BaseViewController.js', 'BaseViewController', 'initialize', 10 );
		Global.setUINotready();

		TTPromise.add( 'init', 'init' );
		TTPromise.add( 'BaseViewController', 'initialize' );
		//trigger readystate update
		TTPromise.wait();

		var $this = this;
		this.layout_changed = false;
		var required_files = this.filterRequiredFiles();
		require( required_files, function() {

			$this.preInit( options );

			$this.options = options;
			if ( $this.options && Global.isSet( $this.options.can_cache_controller ) ) {
				$this.can_cache_controller = $this.options.can_cache_controller;
			}

			if ( $this.options && Global.isSet( $this.options.edit_only_mode ) ) {
				$this.edit_only_mode = $this.options.edit_only_mode;
			}

			if ( $this.options && Global.isSet( $this.options.sub_view_mode ) ) {
				$this.sub_view_mode = $this.options.sub_view_mode;
			} else {
				$this.sub_view_mode = false;
			}

			if ( $this.options && Global.isSet( $this.options.parent_view ) ) {
				$this.parent_view = $this.options.parent_view;
			}

			if ( $this.options && Global.isSet( $this.options.parent_view_controller ) ) {
				$this.parent_view_controller = $this.options.parent_view_controller;
			}

			if ( !$this.edit_only_mode ) {

				if ( $this.can_cache_controller ) {
					if ( !$this.sub_view_mode ) {
						LocalCacheData.current_open_primary_controller = $this;
					} else {
						LocalCacheData.current_open_sub_controller = $this;
					}
				}

				//Reset main container id so it won't duplicate when in sub view. Like Audit view.
				var root_container = $( $this.el );
				var new_id = root_container.attr( 'id' ) + '_' + Global.getRandomNum();
				root_container.attr( 'id', new_id );
				$this.el = '#' + new_id;
				$this.ui_id = new_id;

				$this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();

				$this.total_display_span = $( $( $this.el ).find( '.total-number-span' )[0] );

				//$this shouldn't be displayed as it caused "flashing" of text and it wasn't translated either.
				//if ( $this.total_display_span ) {
				//$this.total_display_span.text( 'Displaying 0 - 0 of 0 total. Selected: 0' );
				//}

				//JS load Optimize
				if ( LocalCacheData.loadViewRequiredJSReady ) {
					//Init paging widget, next step, add widget to UI and bind events in setSelectLayout
					if ( LocalCacheData.paging_type === 0 ) {
						$this.paging_widget = Global.loadWidgetByName( WidgetNamesDic.PAGING );
					} else {
						$this.paging_widget = Global.loadWidgetByName( WidgetNamesDic.PAGING_2 );
						$this.paging_widget_2 = Global.loadWidgetByName( WidgetNamesDic.PAGING_2 );
						$this.paging_widget = $this.paging_widget.Paging2();
						$this.paging_widget_2 = $this.paging_widget_2.Paging2();
					}
				}
			} else {
				$this.ui_id = Global.getRandomNum();
			}

			//init all dic or array, or it will extends last viewcontroller's value. Why?
			$this.sub_log_view_controller = null;
			$this.edit_view_ui_dic = {};
			$this.edit_view_ui_validation_field_dic = {};
			$this.basic_search_field_ui_dic = {};
			$this.adv_search_field_ui_dic = {};
			$this.invisible_context_menu_dic = {};
			$this.edit_view_tabs = [];
			$this.context_menu_array = [];

			$this.other_field_api = new (APIFactory.getAPIClass( 'APIOtherField' ))();

			$this.initKeyboardEvent(); // register keyboard events if it's a main view

			$this.init( options );
			$this.postInit( options );

			TTPromise.resolve( 'BaseViewController', 'initialize' );
			TTPromise.resolve( 'init', 'init' );
		} );

	},

	init: function() {
		//override in child class
	},

	postInit: function() {
		//override in child class
	},

	initKeyboardEvent: function() {

		var $this = this;
		if ( this.sub_view_mode || this.edit_only_mode ) {
			return;
		}

//		$( this.el ).unbind( 'keydown' ).bind( 'keydown', function( e ) {
//
//			if ( e.keyCode === 13 && !$this.search_panel.isCollapsed() ) {
//				$this.onSearch();
//			}
//
//		} );

		$( this.el ).unbind( 'keyup' ).bind( 'keydown', function( e ) {

			if ( e.keyCode === 13 && $this.search_panel && !$this.search_panel.isCollapsed() ) {

				$this.onSearch();
				$( ':focus' ).blur(); //Make focus out of current view. pevent search too much when user keep click enter
			}
		} );

	},

	//Speical permission check for views, need override
	initPermission: function() {

	},


	//Set this when setDefault menu
	setTotalDisplaySpan: function() {
		if ( !this.total_display_span ) {
			return;
		}
		var totalRows;
		var start;
		var end;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = 0;
		//Uncaught TypeError: Cannot read property 'length' of undefined
		if ( grid_selected_id_array ) {
			grid_selected_length = grid_selected_id_array.length;
		}

		var items_pre_page = 100;
		if ( LocalCacheData.getLoginUserPreference() ) {
			var items_per_page = parseInt( LocalCacheData.getLoginUserPreference().items_per_page );
		}

		if ( LocalCacheData.paging_type === 0 ) {
			if ( this.pager_data ) {
				totalRows = this.pager_data.total_rows;
				start = 1;
				end = this.grid.getData().length;
			} else {
				totalRows = 0;
				start = 0;
				end = 0;
			}
		} else {
			if ( this.pager_data ) {
				totalRows = this.pager_data.total_rows;
				start = 0;
				end = 0;

				if ( this.pager_data.last_page_number > 1 ) {
					if ( !this.pager_data.is_last_page ) {

						start = (this.pager_data.current_page - 1) * items_per_page + 1;
						end = start + items_per_page - 1;
					} else {
						start = (this.pager_data.current_page - 1) * items_per_page + 1;
						end = totalRows;
					}

				} else {
					start = 1;
					end = totalRows;
				}

			} else {

				totalRows = 0;
				start = 0;
				end = 0;
			}
		}

		//Counting pages can be disabled, in which case totalRows returns FALSE unless the user is on the last page.
		var totalInfo = start + ' - ' + end;
		if ( totalRows !== false ) {
			totalInfo = totalInfo + ' ' + $.i18n._( 'of' ) + ' ' + totalRows + ' ' + $.i18n._( 'total' ) + '.';
		}

		this.total_display_span.text( $.i18n._( 'Displaying' ) + ' ' + totalInfo + ' [ ' + $.i18n._( 'Selected' ) + ': ' + grid_selected_length + ' ]' );
	},

	//For Browser back/forward to set correct menu
	setSelectRibbonMenuIfNecessary: function() {
		//Try to fixed  Cannot read property 'setSelectMenu' of null，add TopMenuManager.ribbon_view_controlle

		if ( TopMenuManager.ribbon_view_controller && TopMenuManager.selected_sub_menu_id !== this.viewId && !this.sub_view_mode && !this.edit_only_mode ) {

			TopMenuManager.ribbon_view_controller.setSelectMenu( this.viewId );
			TopMenuManager.ribbon_view_controller.setSelectSubMenu( this.viewId );
		}
	},

	getContextIconByName: function( name ) {
		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			if ( id === name ) {
				if ( context_btn.hasClass( 'disable-image' ) || context_btn.hasClass( 'invisible-image' ) ) {
					return true;
				} else {
					return false;
				}
			}

		}

		return false;
	},

	//Set right click menu for list view grid
	initRightClickMenu: function( target_type ) {
		//Error: Object doesn't support property or method 'contextMenu' in /interface/html5/views/BaseViewController.js?v=7.4.6-20141027-132733 line 393
		if ( !$.hasOwnProperty( 'contextMenu' ) ) {
			return;
		}
		var $this = this;

		var selector = '';

		switch ( target_type ) {
			case RightClickMenuType.LISTVIEW:
				selector = '#gbox_' + this.ui_id + '_grid';
				break;
			case RightClickMenuType.EDITVIEW:
				selector = '#' + this.ui_id + '_edit_view_tab';
				break;
			case RightClickMenuType.NORESULTBOX:
				selector = '#' + this.ui_id + '_no_result_box';
				break;
			case RightClickMenuType.ABSENCE_GRID:
				selector = '#' + this.ui_id + '_absence_grid';
				break;
			case RightClickMenuType.VIEW_ICON:
				selector = '#' + ContextMenuIconName.view_html;
				break;
			default:
				selector = '#gbox_' + this.ui_id + '_grid';
				break;

		}

		if ( $( selector ).length == 0 ) {
			return;
		}

		var items = this.getRightClickMenuItems();

		if ( !items || $.isEmptyObject( items ) ) {
			return;
		}
		$.contextMenu( 'destroy', selector );
		$.contextMenu( {
			selector: selector,
			callback: function( key, options ) {
				$this.onContextMenuClick( null, key );
			},

			onContextMenu: function() {
				return false;
			},
			items: items
		} );
	},

	getRightClickMenuItems: function () {
		var $this = this;

		var items = {};
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );

			//Don't add sub menu context icon to right click
			if ( context_btn.children().eq( 0 ).hasClass( 'ribbon-sub-menu-nav-icon' ) ) {
				continue;
			}

			if ( context_btn.hasClass( 'invisible-image' ) ) {
				continue;
			}

			var html_content = $( context_btn.html() );
			label = this.replaceRightClickLabel( html_content );

			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			items[id] = {
				name: label,
				icon: id,
				disabled: function ( key ) {
					return $this.getContextIconByName( key );
				}
			};
		}

		return items;
	},

	replaceRightClickLabel: function( html_content ) {
		var label = html_content.children().eq( 1 ).html();

		label = Global.htmlDecode( label.replace( '<br>', ' ' ) );

		return label;
	},

	//Don't initOptions if edit_only_mode. Do it in sub views
	initData: function() {
		var $this = this;

		//Work around to init sub view after tab is shown.
		Global.removeViewTab( this.viewId );
		ProgressBar.showOverlay();
		if ( !$this.edit_only_mode ) {
			$this.initOptions();
			$this.getAllColumns( function() {
				$this.initLayout();
			} );
		}

	},

	initLayout: function() {
		var $this = this;
		$this.getAllLayouts( function() {
			$this.getDefaultDisplayColumns( function() {
				$this.setSelectLayout();
				//$this.setGridColumnsWidth(); //This is done in setSelectLayout() and searchDone(), so no point in doing it multiple times.
				$this.search();
			} );
		} );
	},

	// edit_only_mode call this when open edit view. Not in initData
	initOptions: function() {

	},

	getContextMenuModel: function() {
		return this.context_menu_model;
	},
	setContextMenuModel: function( model ) {
		this.context_menu_model = model;

		return true;
	},

	buildContextMenuModels: function() {
		var context_menu_model = this.getContextMenuModel();

		if ( context_menu_model != null ) {
			//Context Menu
			var menu = new RibbonMenu( {
				label: this.context_menu_name,
				id: this.viewId + 'ContextMenu',
				sub_menu_groups: []
			} );

			var default_context_menu_model = {
				'groups': {
					'editor': {
						label: $.i18n._( 'Editor' ),
						id: 'editor',
					},
					'navigation': {
						label: $.i18n._( 'Navigation' ),
						id: 'navigation',
						sort_order: 8000
					},
					'other': {
						label: $.i18n._( 'Other' ),
						id: 'other',
						sort_order: 9000
					},
				},

				'icons': {},
			};

			default_context_menu_model['icons'][ContextMenuIconName.add] = {
				label: $.i18n._( 'New' ),
				id: ContextMenuIconName.add,
				group: 'editor',
				icon: Icons.new_add,
			};

			default_context_menu_model['icons'][ContextMenuIconName.view] = {
				label: $.i18n._( 'View' ),
				id: ContextMenuIconName.view,
				group: 'editor',
				icon: Icons.view,
			};

			default_context_menu_model['icons'][ContextMenuIconName.edit] = {
				label: $.i18n._( 'Edit' ),
				id: ContextMenuIconName.edit,
				group: 'editor',
				icon: Icons.edit,
			};

			default_context_menu_model['icons'][ContextMenuIconName.mass_edit] = {
				label: $.i18n._( 'Mass<br>Edit' ),
				id: ContextMenuIconName.mass_edit,
				group: 'editor',
				icon: Icons.mass_edit,
			};

			default_context_menu_model['icons'][ContextMenuIconName.delete_icon] = {
				label: $.i18n._( 'Delete' ),
				id: ContextMenuIconName.delete_icon,
				group: 'editor',
				icon: Icons.delete_icon,
			};

			default_context_menu_model['icons'][ContextMenuIconName.delete_and_next] = {
				label: $.i18n._( 'Delete<br>& Next' ),
				id: ContextMenuIconName.delete_and_next,
				group: 'editor',
				icon: Icons.delete_and_next,
			};

			default_context_menu_model['icons'][ContextMenuIconName.copy] = {
				label: $.i18n._( 'Copy' ),
				id: ContextMenuIconName.copy,
				group: 'editor',
				icon: Icons.copy_as_new,
			};

			default_context_menu_model['icons'][ContextMenuIconName.copy_as_new] = {
				label: $.i18n._( 'Copy<br>as New' ),
				id: ContextMenuIconName.copy_as_new,
				group: 'editor',
				icon: Icons.copy,
			};

			default_context_menu_model['icons'][ContextMenuIconName.save] = {
				label: $.i18n._( 'Save' ),
				id: ContextMenuIconName.save,
				group: 'editor',
				icon: Icons.save,
			};

			default_context_menu_model['icons'][ContextMenuIconName.save_and_continue] = {
				label: $.i18n._( 'Save<br>& Continue' ),
				id: ContextMenuIconName.save_and_continue,
				group: 'editor',
				icon: Icons.save_and_continue,
			};

			default_context_menu_model['icons'][ContextMenuIconName.save_and_next] = {
				label: $.i18n._( 'Save<br>& Next' ),
				id: ContextMenuIconName.save_and_next,
				group: 'editor',
				icon: Icons.save_and_next,
			};

			default_context_menu_model['icons'][ContextMenuIconName.save_and_copy] = {
				label: $.i18n._( 'Save<br>& Copy' ),
				id: ContextMenuIconName.save_and_copy,
				group: 'editor',
				icon: Icons.save_and_copy,
			};

			default_context_menu_model['icons'][ContextMenuIconName.save_and_new] = {
				label: $.i18n._( 'Save<br>& New' ),
				id: ContextMenuIconName.save_and_new,
				group: 'editor',
				icon: Icons.save_and_new,
			};

			default_context_menu_model['icons'][ContextMenuIconName.cancel] = {
				label: $.i18n._( 'Cancel' ),
				id: ContextMenuIconName.cancel,
				group: 'editor',
				icon: Icons.cancel,
			};

			default_context_menu_model['icons'][ContextMenuIconName.export_excel] = {
				label: $.i18n._( 'Export' ),
				id: ContextMenuIconName.export_excel,
				group: 'other',
				icon: Icons.export_excel,
				sort_order: 9000
			};

			var final_context_menu_model = { 'icons': {}, 'groups': {} };

			if ( !context_menu_model['groups'] ) {
				context_menu_model['groups'] = {};
			}

			//Default to including all icons.
			if ( !context_menu_model['include'] ) {
				context_menu_model['include'] = [ 'all' ];
			}

			if ( context_menu_model['include'].indexOf('all') === -1 ) {
				context_menu_model['include'].push('all');
			}

			//Assign default groups.
			for( x in default_context_menu_model['groups'] ) {
				default_context_menu_model['groups'][x]['ribbon_menu'] = menu;
				default_context_menu_model['groups'][x]['sub_menus'] = [];

				final_context_menu_model['groups'][x] = default_context_menu_model['groups'][x];
			}

			for( x in context_menu_model['groups'] ) {
				context_menu_model['groups'][x]['ribbon_menu'] = menu;
				context_menu_model['groups'][x]['sub_menus'] = [];

				final_context_menu_model['groups'][x] = context_menu_model['groups'][x];
			}

			//Filter groups/icons
			if ( context_menu_model.hasOwnProperty('include') ) {
				if ( context_menu_model.include.constructor !== Array ) {
					context_menu_model.include = Array( context_menu_model.include );
				}

				for( i in context_menu_model.include ) {
					if ( context_menu_model.include[i] == 'all' ) {
						Debug.Text( 'Including All Default Icons...', 'BaseViewController.js', 'BaseViewController', 'buildContextMenuModels', 10 );

						for( x in default_context_menu_model['icons'] ) {
							final_context_menu_model['icons'][x] = default_context_menu_model['icons'][x];
						}
					} else {
						var include_icon_id = context_menu_model.include[i];
						if ( typeof include_icon_id !== 'object' && default_context_menu_model['icons'][include_icon_id] ) {
							final_context_menu_model['icons'][include_icon_id] = default_context_menu_model['icons'][include_icon_id];
						} else {
							final_context_menu_model['icons'][context_menu_model.include[i]['id']] = context_menu_model.include[i];
						}
					}
				}


			}

			//Must go after include, so they can include a few icons, then exclude all.
			if ( context_menu_model.hasOwnProperty('exclude') ) {
				if ( context_menu_model.exclude.constructor !== Array ) {
					context_menu_model.exclude = Array( context_menu_model.exclude );
				}

				for( i in context_menu_model.exclude ) {
					if ( context_menu_model.exclude[i] == 'all' ) {
						Debug.Text( 'Excluding All Default Icons...', 'BaseViewController.js', 'BaseViewController', 'buildContextMenuModels', 10 );

						for( x in default_context_menu_model['icons'] ) {
							if ( context_menu_model.include.indexOf(x) === -1 ) { //Make sure we don't exclude one that is included.
								if ( final_context_menu_model['icons'][x] ) {
									delete final_context_menu_model['icons'][x];
								}
							}
						}
					} else {
						var exclude_icon_id = context_menu_model.exclude[i];
						if ( final_context_menu_model['icons'][exclude_icon_id] ) {
							delete final_context_menu_model['icons'][exclude_icon_id];
						}
					}
				}
			}

			//Build Menu
			var groups = {};
			for( x in final_context_menu_model['groups'] ) {
				groups[x] = new RibbonSubMenuGroup( final_context_menu_model['groups'][x] );
				Debug.Text( 'Creating Ribbon Menu Group: '+ final_context_menu_model['groups'][x]['label'], 'BaseViewController.js', 'BaseViewController', 'buildContextMenuModels', 10 );
			}

			for( x in final_context_menu_model['icons'] ) {

				//Replace group string with object.
				if ( final_context_menu_model['icons'][x] && final_context_menu_model['icons'][x]['group'] ) {
					if ( final_context_menu_model['icons'][x]['group'].constructor === String ) {
						final_context_menu_model['icons'][x]['group'] = groups[final_context_menu_model['icons'][x]['group']];
					} else if ( typeof final_context_menu_model['icons'][x]['group'] === 'object' ) {
						//The 2nd time the edit view is opened, icons manually passed in through 'include' already have the groups converted to objects, but the icons don't appear until we re-assign the group object again.
						final_context_menu_model['icons'][x]['group'] = groups[final_context_menu_model['icons'][x]['group'].get('id')];
					}
				}

				if ( !final_context_menu_model['icons'][x]['permission_result'] ) {
					final_context_menu_model['icons'][x]['permission_result'] = true;
				}
				if ( !final_context_menu_model['icons'][x].hasOwnProperty('permission') == false ) {
					final_context_menu_model['icons'][x]['permission'] = null;
				}

				Debug.Text( 'Creating Ribbon Menu Icon: '+ final_context_menu_model['icons'][x]['label'], 'BaseViewController.js', 'BaseViewController', 'buildContextMenuModels', 10 );
				new RibbonSubMenu( final_context_menu_model['icons'][x] );
			}

			return [menu];
		} else {
			//Context Menu
			var menu = new RibbonMenu( {
				label: this.context_menu_name,
				id: this.viewId + 'ContextMenu',
				sub_menu_groups: []
			} );

			//menu group
			var editor_group = new RibbonSubMenuGroup( {
				label: $.i18n._( 'Editor' ),
				id: this.viewId + 'Editor',
				ribbon_menu: menu,
				sub_menus: []
			} );

			var other_group = new RibbonSubMenuGroup( {
				label: $.i18n._( 'Other' ),
				id: this.viewId + 'other',
				ribbon_menu: menu,
				sub_menus: [],
				sort_order: 9000
			} );
			var add = new RibbonSubMenu( {
				label: $.i18n._( 'New' ),
				id: ContextMenuIconName.add,
				group: editor_group,
				icon: Icons.new_add,
				permission_result: true,
				permission: null
			} );

			var view = new RibbonSubMenu( {
				label: $.i18n._( 'View' ),
				id: ContextMenuIconName.view,
				group: editor_group,
				icon: Icons.view,
				permission_result: true,
				permission: null
			} );

			var edit = new RibbonSubMenu( {
				label: $.i18n._( 'Edit' ),
				id: ContextMenuIconName.edit,
				group: editor_group,
				icon: Icons.edit,
				permission_result: true,
				permission: null
			} );

			var mass_edit = new RibbonSubMenu( {
				label: $.i18n._( 'Mass<br>Edit' ),
				id: ContextMenuIconName.mass_edit,
				group: editor_group,
				icon: Icons.mass_edit,
				permission_result: true,
				permission: null
			} );

			var del = new RibbonSubMenu( {
				label: $.i18n._( 'Delete' ),
				id: ContextMenuIconName.delete_icon,
				group: editor_group,
				icon: Icons.delete_icon,
				permission_result: true,
				permission: null
			} );

			var delAndNext = new RibbonSubMenu( {
				label: $.i18n._( 'Delete<br>& Next' ),
				id: ContextMenuIconName.delete_and_next,
				group: editor_group,
				icon: Icons.delete_and_next,
				permission_result: true,
				permission: null
			} );

			var copy = new RibbonSubMenu( {
				label: $.i18n._( 'Copy' ),
				id: ContextMenuIconName.copy,
				group: editor_group,
				icon: Icons.copy_as_new,
				permission_result: true,
				permission: null
			} );

			var copy_as_new = new RibbonSubMenu( {
				label: $.i18n._( 'Copy<br>as New' ),
				id: ContextMenuIconName.copy_as_new,
				group: editor_group,
				icon: Icons.copy,
				permission_result: true,
				permission: null
			} );

			var save = new RibbonSubMenu( {
				label: $.i18n._( 'Save' ),
				id: ContextMenuIconName.save,
				group: editor_group,
				icon: Icons.save,
				permission_result: true,
				permission: null
			} );

			var save_and_continue = new RibbonSubMenu( {
				label: $.i18n._( 'Save<br>& Continue' ),
				id: ContextMenuIconName.save_and_continue,
				group: editor_group,
				icon: Icons.save_and_continue,
				permission_result: true,
				permission: null
			} );

			var save_and_next = new RibbonSubMenu( {
				label: $.i18n._( 'Save<br>& Next' ),
				id: ContextMenuIconName.save_and_next,
				group: editor_group,
				icon: Icons.save_and_next,
				permission_result: true,
				permission: null
			} );

			var save_and_copy = new RibbonSubMenu( {
				label: $.i18n._( 'Save<br>& Copy' ),
				id: ContextMenuIconName.save_and_copy,
				group: editor_group,
				icon: Icons.save_and_copy,
				permission_result: true,
				permission: null
			} );

			var save_and_new = new RibbonSubMenu( {
				label: $.i18n._( 'Save<br>& New' ),
				id: ContextMenuIconName.save_and_new,
				group: editor_group,
				icon: Icons.save_and_new,
				permission_result: true,
				permission: null
			} );

			var cancel = new RibbonSubMenu( {
				label: $.i18n._( 'Cancel' ),
				id: ContextMenuIconName.cancel,
				group: editor_group,
				icon: Icons.cancel,
				permission_result: true,
				permission: null
			} );

			var export_csv = new RibbonSubMenu( {
				label: $.i18n._( 'Export' ),
				id: ContextMenuIconName.export_excel,
				group: other_group,
				icon: Icons.export_excel,
				permission_result: true,
				permission: null,
				sort_order: 9000
			} );

			return [menu];
		}
	},

	buildContextMenu: function( setFocus ) {
		if ( this.current_edit_record && this.edit_only_mode == true && LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.viewId != LocalCacheData.current_open_edit_only_controller.viewId ) { // #2542 - prevent early menu setup for views that have not been loaded into memory yet.
			return null;
		}
		var $this = this;
		if ( !Global.isSet( setFocus ) ) {
			setFocus = true;
		}

		if ( !this.sub_view_mode ) {
			LocalCacheData.current_open_sub_controller = null; //Clean sub controller if current view is a main view
		}

		this.context_menu_array = [];

		var ribbon_menu_array = this.buildContextMenuModels();

		var ribbon_menu_label_node = $( '.ribbonTabLabel' );
		var ribbon_menu_root_node = $( '.ribbon' );

		var len = ribbon_menu_array.length;

		var ribbon_menu;

		for ( var i = 0; i < len; i++ ) {
			ribbon_menu = ribbon_menu_array[i];
			var ribbon_menu_group_array = ribbon_menu.getSubMenuGroups();

			var ribbon_menu_ui = $( '<div id="' + ribbon_menu.get( 'id' ) + '" class="context-ribbon-menu ribbon-tab-out-side ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"><div class="context-ribbon-tab"><div class="ribbon-sub-menu"></div></div></div>' );

			//make sure only one context menu shown at a time
			if ( Global.isSet( LocalCacheData.currentShownContextMenuName ) && LocalCacheData.currentShownContextMenuName !== ribbon_menu.get( 'id' ) ) {
				var needRemovedContextMenuName = LocalCacheData.currentShownContextMenuName;
				this.context_menu_array = [];

			}
			//#2353 - prevent lost context menu for accrual balances
			// else if ( Global.isSet( LocalCacheData.currentShownContextMenuName ) && LocalCacheData.currentShownContextMenuName === ribbon_menu.get( 'id' ) ) {
			// 	return;
			// }

			$( '.context-ribbon-menu' ).remove(); //prevent dupes

			this.subMenuNavMap = {};

			LocalCacheData.currentShownContextMenuName = ribbon_menu.get( 'id' );
			var len1 = ribbon_menu_group_array.length;
			for ( var x = 0; x < len1; x++ ) {

				var ribbon_menu_group = ribbon_menu_group_array[x];
				var ribbon_sub_menu_array = ribbon_menu_group.getSubMenus();
				var sub_menu_ui_nodes = $( '<ul></ul>' );
				var ribbon_menu_group_ui = $( '<div class="menu top-ribbon-menu" />' );

				var len2 = ribbon_sub_menu_array.length;
				for ( var y = 0; y < len2; y++ ) {

					var ribbon_sub_menu = ribbon_sub_menu_array[y];

					//Do not add context menu which in invisible_context_menu_dic
					if ( this.invisible_context_menu_dic[ribbon_sub_menu.get( 'id' )] ) {
						continue;
					}
//					var sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					if ( ribbon_sub_menu.get( 'selected' ) ) {

						var sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon selected-menu" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					} else {
						sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					}

					sub_menu_ui_node.addClass( 'disable-image' );
					this.context_menu_array.push( sub_menu_ui_node );
					if ( ribbon_sub_menu.get( 'type' ) === RibbonSubMenuType.NAVIGATION ) {

						sub_menu_ui_node.children().eq( 0 ).addClass( 'ribbon-sub-menu-nav-icon' );
						$this.subMenuNavMap[ribbon_sub_menu.get( 'id' )] = ribbon_sub_menu;

						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onSubMenuNavClick( this, id );
						} );

					} else {
						//defend empty block error when comments following codes

						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onContextMenuClick( this );
						} );

					}

					sub_menu_ui_nodes.append( sub_menu_ui_node );
				}

				if ( sub_menu_ui_nodes.children().length > 0 ) {
					ribbon_menu_group_ui.append( sub_menu_ui_nodes );
					ribbon_menu_group_ui.append( $( '<div class="menu-bottom"><span>' + ribbon_menu_group.get( 'label' ) + '</span></div>' ) );
					ribbon_menu_ui.find( '.ribbon-sub-menu' ).append( ribbon_menu_group_ui );
				}

			}

			$( '.ribbonTabLabel .context-menu' ).remove();
			//When added a new context menu tab, make it ui-state-active by default if setFocus=TRUE, since its going to be active soon anyways and this prevents flashing.
			var default_tab_label_state = 'default';
			if ( setFocus ) {
				default_tab_label_state = 'active';
			}

			ribbon_menu_label_node.append( $( '<li class="context-menu ui-state-'+ default_tab_label_state +' ui-corner-top"><a ref="' + ribbon_menu.get( 'id' ) + '" href="#' + ribbon_menu.get( 'id' ) + '">' + ribbon_menu.get( 'label' ) + '</a></li>' ) );
			ribbon_menu_root_node.append( ribbon_menu_ui );
		}

		if ( needRemovedContextMenuName ) {
			this.removeContentMenuByName( needRemovedContextMenuName );
		}

		if ( setFocus ) {
			this.need_switch_to_context_menu = true;

			// //#2530 - stop the flashing!!!
			// if ( this.edit_only_mode == true ) {
			// 	this.setEditMenu(); //prevents some flashing of edit-only view context menus
			// } else {
			// 	//Perform any context menu permission checks and set the default menu icons based on the fact that *no* records are selected in the grid yet.
			// 	// As long as this occurs before selectContextMenu() it helps prevent "flashing" of all icons down to just the icons they have permissions to see.
			// 	// Specifically when logged in as a regular employee and going to Payroll -> Pay Stubs, it would show all icons an Administrator would see, then hide most of them.
			// 	this.setDefaultMenu();
			//
			// 	//Don't select context menu until search complete
			// 	//this.selectContextMenu(); //Moved this is now done in search() when this.need_switch_to_context_menu = true;
			// }
		}
	},

	getContextMenuGroupByName: function( menu, name, name_prefix ) {
		var group;
		if ( name_prefix == undefined ) {
			name_prefix = this.viewId;
		}

		for ( var i = 0; i < menu.attributes.sub_menu_groups.length; i++ ) {
			if ( menu.attributes.sub_menu_groups[i].id == name_prefix + name ) {
				group = menu.attributes.sub_menu_groups[i];
			}
		}

		return group;
	},

	onSubMenuNavClick: function( target, id ) {
		var $this = this;
		var sub_menu = this.subMenuNavMap[id];

		if ( LocalCacheData.openRibbonNaviMenu ) {

			if ( LocalCacheData.openRibbonNaviMenu.attr( 'id' ) === 'sub_nav' + id ) {
				LocalCacheData.openRibbonNaviMenu.close();
				return;
			} else {
				LocalCacheData.openRibbonNaviMenu.close();
			}

		}

		showNavItems();

		function showNavItems() {
			var items = sub_menu.get( 'items' );
			var box = $( '<ul id=\'sub_nav' + id + '\' class=\'ribbon-sub-menu-nav\'> </ul>' );

			for ( var i = 0; i < items.length; i++ ) {
				var item = items[i];
				var item_node = $( '<li class=\'ribbon-sub-menu-nav-item\' id=\'' + item.get( 'id' ) + '\'><span class=\'label\'>' + item.get( 'label' ) + '</span></li>' );
				box.append( item_node );

				item_node.unbind( 'click' ).click( function() {
					var id = $( this ).attr( 'id' );
					$this.onReportMenuClick( id );
				} );

			}

			box = box.RibbonSubMenuNavWidget();

			LocalCacheData.openRibbonNaviMenu = box;

			$( target ).append( box );

			if ( box.offset().left + box.width() > Global.bodyWidth() ) {
				box.css( 'left', Global.bodyWidth() - box.width() - 10 );
			}
		}

	},

	onReportMenuClick: function( id ) {

	},

	//Overridden in ReportBaseViewController.
	onContextMenuClick: function( context_btn, menu_name ) {
		var id;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			//Check if icon is disabled and do nothing.
			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		ProgressBar.showOverlay();
		//this flag is turned off in ProgressBarManager::closeOverlay, or 1 second whichever happens first
		if ( window.clickProcessing == true ) {
			return;
		} else {
			window.clickProcessing = true;
			window.clickProcessingHandle = window.setTimeout( function() {
				if ( window.clickProcessing == true ) {
					window.clickProcessing = false;
					ProgressBar.closeOverlay();
					TTPromise.wait();
				}
			}, 1000 );
		}

		//Debug.Text( 'Context Menu Click: '+ id, 'BaseViewController.js', 'BaseViewController', 'onContextMenuClick', 10 );

		/**
		 *  Here where you see ProgressBar.showOverlay() it is how we prevent doubleclick from firing two single clicks
		 */

		switch ( id ) {
			case ContextMenuIconName.add:
				this.onAddClick();
				break;
			case ContextMenuIconName.view:
				this.onViewClick();
				break;
			case ContextMenuIconName.save:
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_next:
				this.onSaveAndNextClick();
				break;
			case ContextMenuIconName.save_and_continue:
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_new:
				this.onSaveAndNewClick();
				break;
			case ContextMenuIconName.save_and_copy:
				this.onSaveAndCopy();
				break;
			case ContextMenuIconName.edit:
				this.onEditClick();
				break;
			case ContextMenuIconName.mass_edit:
				this.onMassEditClick();
				break;
			case ContextMenuIconName.delete_icon:
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.copy:
				this.onCopyClick();
				break;
			case ContextMenuIconName.copy_as_new:
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				ProgressBar.closeOverlay();
				break;
			case ContextMenuIconName.export_excel:
				this.onExportClick( 'export' + this.api.key_name );
				ProgressBar.closeOverlay();
				break;
			case ContextMenuIconName.map:
				this.onMapClick();
				ProgressBar.closeOverlay();
				break;
			default:
				this.onCustomContextClick( id, context_btn );
				ProgressBar.closeOverlay(); //FIXME: This may be closing the overlay too soon, allowing double-clicks to get through. For example when in Request Authorizations and hammer clicking "Authorize".
				//Debug.Text( 'Context Menu Click: '+ id +' Overlay closing...', 'BaseViewController.js', 'BaseViewController', 'onContextMenuClick', 10 );
				break;
		}

		Global.triggerAnalyticsContextMenuClick( context_btn, menu_name );
	},

	onCustomContextClick: function( id ) {
		return false; //FALSE tells onContextMenuClick() to keep processing.
	},

	onNavigationClick: function( id ) {
		this.onContextMenuClick( id );
	},

	setCurrentEditViewState: function( state ) {
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = false;
		this.is_mass_editing = false;
		this.is_mass_adding = false;
		switch ( state ) {
			case 'view':
				this.is_viewing = true;
				break;
			case 'new':
				this.is_add = true;
				break;
			case 'edit':
				this.is_edit = true;
				break;
			case 'mass_edit':
				this.is_mass_editing = true;
				break;
		}
		LocalCacheData.current_doing_context_action = state;
	},

	onAddClick: function() {
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		$this.openEditView();
		//Error: Uncaught TypeError: undefined is not a function in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 897
		if ( $this.api && typeof $this.api['get' + $this.api.key_name + 'DefaultData'] === 'function' ) {
			$this.api['get' + $this.api.key_name + 'DefaultData']( {
				onResult: function( result ) {
					$this.onAddResult( result );
				}
			} );
		}
	},

	onAddResult: function( result ) {
		var $this = this;
		var result_data = {};

		if ( result.getResult ) {
			result_data = result.getResult();
		} else {
			//if not an api result, assume object is already the result of a call to getResult() and we will use it verbatim.
			//useful for passing in default values when adding new records before this function is called.
			result_data = result;
		}

		if ( !result_data || result_data === true ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	onDeleteAndNextClick: function() {
		var $this = this;
		$this.is_add = false;

		TAlertManager.showConfirmAlert( $.i18n._( Global.delete_confirm_message ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			}

			if ( result ) {

				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {
						$this.onDeleteAndNextResult( result, remove_ids );

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	onDeleteAndNextResult: function( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();
		if ( result.isValid() ) {
			var next_select_item = $this.navigation.getSelectIndex() + 1;
			var source_data = $this.navigation.getSourceData();
			if ( source_data && next_select_item < source_data.length ) {
				$this.refresh_id = $this.navigation.getItemByIndex( next_select_item );
			} else {
				$this.refresh_id = null;
			}

			$this.search( false, null, null, function( result ) {
				var current_grid_source = result.getResult();

				if ( $.type( current_grid_source ) !== 'array' || current_grid_source.length < 1 ) {
					$this.removeEditView();
					$this.setDefaultMenu();
				} else {
					$this.navigation.setSourceData( current_grid_source );
					$this.navigation.setPagerData( $this.pager_data );

					if ( $this.refresh_id ) {
						$this.onRightOrLeftArrowClickCallBack( $this.refresh_id );
					} else {
						$this.onCancelClick();
					}

					$this.onDeleteAndNextDone( result );
				}
			} );
		} else {
			TAlertManager.showErrorAlert( result );
		}
	},

	resetNavigationSourceData: function() {

	},

	onDeleteClick: function() {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			} else {
				remove_ids = $this.getGridSelectIdArray().slice(); //Use .slice() to make a copy of the IDs.
			}
			if ( result ) {
				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {
						$this.onDeleteResult( result, remove_ids );
					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );

	},

	onDeleteResult: function( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();
		if ( result.isValid() ) {
			$this.search();
			$this.onDeleteDone( result );
			if ( $this.edit_view ) {
				$this.removeEditView();
			}
		} else {
			// If some valid records were deleted, we need to refresh the search grid.
			if ( result.getRecordDetails().valid && result.getRecordDetails().valid > 0 ) {
				$this.search();
			}
			TAlertManager.showErrorAlert( result );
		}
	},

	removeDeletedRows: function( remove_ids ) {
		var $this = this;
		$.each( remove_ids, function( index, value ) {
			$this.grid.grid.deleteRow( value );
			$this.paging_widget.minus();

		} );
//
//		if ( this.grid.getGridParam( 'data' ).length === 0 ) {
//			this.search();
//		}

//		this.search();

//		var grid_selected_id_array = this.getGridSelectIdArray();
//		var grid_selected_length = grid_selected_id_array.length;
//
//		if ( grid_selected_length === 0 ) {
//			this.search();
//		}

	},

	clearNavigationData: function() {
		if ( this.navigation && this.navigation.setSourceData ) {
			this.navigation.setSourceData( null );
		}
	},

	onSaveAndCopy: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndCopyResult( result );

			}
		} );
	},

	onSaveAndCopyResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true && $this.current_edit_record && $this.current_edit_record.id ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.search( false );
			$this.onCopyAsNewClick();
		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	onSaveAndNewClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndNewResult( result );

			}
		} );
	},

	onSaveAndNewResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true && $this.current_edit_record && $this.current_edit_record.id ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.search( false );
			$this.onAddClick( true );
		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	onSaveAndContinue: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}
		} );
	},

	onSaveAndContinueResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true && $this.current_edit_record && $this.current_edit_record.id ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;

			}
			$this.onEditClick( $this.refresh_id, true );
			$this.onSaveAndContinueDone( result );
			$this.search( false );
		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	onSaveAndNextClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = false;
		this.is_changed = false;

		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_next';
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndNextResult( result );

			}
		} );
	},

	onSaveAndNextResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true && $this.current_edit_record && $this.current_edit_record.id ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.onRightArrowClick();
			$this.search( false );
			$this.onSaveAndNextDone( result );

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	uniformVariable: function( records ) {
		return records;
	},

	onSaveClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		var record;
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveResult( result );

			}
		} );
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			$this.is_add = false;
			var result_data = result.getResult();
			if ( !this.edit_only_mode ) {
				if ( result_data === true && $this.current_edit_record && $this.current_edit_record.id ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
					$this.refresh_id = result_data;
				} else {
					$this.refresh_id = null;
				}

				$this.search();
			}

			var on_save_done_result = $this.onSaveDone( result ); //post hook for onSaveResult
			if ( on_save_done_result == undefined || on_save_done_result == true ) {
				$this.removeEditView();
			}
		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );
		}
	},

	//post hook for onSaveResult
	onSaveDone: function( result ) {
		return true;
	},

	onSaveAndContinueDone: function( result ) {

	},

	onSaveAndNextDone: function( result ) {

	},

	onDeleteDone: function( result ) {
		this.removeDeletedRows()
	},

	onDeleteAndNextDone: function( result ) {

	},

	onMassEditClick: function() {

		var $this = this;
		this.setCurrentEditViewState( 'mass_edit' );
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {
								$this.linked_columns = result1.getResult();
								if ( $this.linked_columns === true ) {
									//there are no columns, you should be an empty array.
									$this.linked_columns = [];
								}
								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								$this.current_edit_record = result_data;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		this.setCurrentEditViewState( 'view' );
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];
		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist.' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		this.setCurrentEditViewState( 'edit' );
		$this.openEditView();
		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist.' ) );
					$this.onCancelClick();
					return;
				}

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	onCopyClick: function() {
		var $this = this;
		var copyIds = [];
		$this.is_add = false;
		if ( $this.edit_view ) {
			copyIds.push( $this.current_edit_record.id );
			if ( this.is_changed ) {
				TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
					if ( flag === true ) {
						$this.is_changed = false;
						$this._continueDoCopy( copyIds );
					}
					ProgressBar.closeOverlay();
				} );
			} else {
				$this._continueDoCopy( copyIds );
			}
		} else {
			copyIds = $this.getGridSelectIdArray().slice();
			$this._continueDoCopy( copyIds );
		}
	},

	_continueDoCopy: function( copyIds ) {
		var $this = this;
		ProgressBar.showOverlay();
		$this.api['copy' + $this.api.key_name]( copyIds, {
			onResult: function( result ) {
				$this.onCopyResult( result );

			}
		} );
	},

	onCopyResult: function( result ) {
		var $this = this;

		if ( result.isValid() ) {
			$this.search();
			if ( $this.edit_view ) {
				$this.removeEditView();
			}

		} else {

			TAlertManager.showErrorAlert( result );

			if ( result.getRecordDetails().total > 1 ) {
				$this.search();
			}
		}
	},

	onCopyAsNewClick: function() {
		var $this = this;
		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					$this._continueDoCopyAsNew();
				}
				ProgressBar.closeOverlay();
			} );
		} else {
			this._continueDoCopyAsNew();
		}
	},

	copyAsNewResetIds: function( data ) {
		//override where needed.
		data.id = '';
		return data;
	},

	getCopyAsNewFilter: function ( filter ) {
		// override where needed.
		return filter;
	},

	_continueDoCopyAsNew: function() {
		var $this = this;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {
			this.current_edit_record = this.copyAsNewResetIds( this.current_edit_record );
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus(); // Show tabs based on permission. setCurrentEditRecordData has functions to set by record type. See #2687 - setTabStatus() must go before setCurrentEditRecordData(), otherwise Premium Policy Tabs incorrectly shown.
			if ( !this.editor ) {
				// #2687 if an editor exists in the view/tabs, we do not want to call setCurrentEditRecordData() as it wipes out the editor data in one of its child functions initInsideEditorData().
				this.setCurrentEditRecordData();
			}
			this.is_changed = false;
			ProgressBar.closeOverlay();
		} else {
			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;
			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}
			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			filter = this.getCopyAsNewFilter( filter );

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {
					$this.onCopyAsNewResult( result );
				}
			} );
		}
	},

	onCopyAsNewResult: function( result ) {
		var result_data = result.getResult();

		if ( typeof result_data != 'object' ) {
			this.onAddClick();
			return;
		}

		this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];

		result_data = this.copyAsNewResetIds( result_data );

		if ( this.sub_view_mode && this.parent_key ) {
			result_data[this.parent_key] = this.parent_value;
		}

		this.current_edit_record = result_data;
		var $this = this;

		$('.PunchesEditView .edit-view-tab-bar').css('opacity', 1);
		$('.PunchesEditView .edit-view-tab').css('opacity', 1);
		$this.initEditView();
	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.

	 'job' argument must be an object, or false/null
	 */
	setJobItemValueWhenJobChanged: function( job, job_item_id_col_name, filter_data ) {
		var $this = this;

		//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in /interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
		if ( !$this.current_edit_record ) {
			return;
		}

		TTPromise.add( 'BaseViewController', 'setJobItemValueWhenJobChanged' );

		if ( job_item_id_col_name == undefined ) {
			job_item_id_col_name = 'job_item_id';
		}

		if ( filter_data == undefined ) {
			filter_data = { status_id: 10 };
		}

		if ( job != undefined && job != false ) {
			filter_data['job_id'] = job.id; //Always filter by job
		}

		var job_item_widget = $this.edit_view_ui_dic[job_item_id_col_name];
		var current_job_item_id = job_item_widget.getValue();
		job_item_widget.setSourceData( null );
		job_item_widget.setCheckBox( true );
		this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );

		var args = {};
		args.filter_data = filter_data;
		$this.edit_view_ui_dic[job_item_id_col_name].setDefaultArgs( args );

		//Make sure if current task is selected, that its still available on the new job.
		if ( current_job_item_id && current_job_item_id != TTUUID.zero_id ) {
			var new_arg = Global.clone( args );

			new_arg.filter_data.job_id = job.id;
			new_arg.filter_columns = $this.edit_view_ui_dic[job_item_id_col_name].getColumnFilter();
			$this.job_item_api.getJobItem( new_arg, {
				onResult: function( task_result ) {
					//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in /interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
					if ( !$this.current_edit_record ) {
						return;
					}

					var data = task_result.getResult();

					if ( data.length > 0 ) {
						job_item_widget.setValue( current_job_item_id );
						$this.current_edit_record[job_item_id_col_name] = current_job_item_id;
					} else {
						setDefaultData( job_item_id_col_name );
					}
				}
			} );

		} else {
			setDefaultData( job_item_id_col_name );
		}

		function setDefaultData( job_item_id_col_name ) {
			if ( job_item_id_col_name == undefined ) {
				job_item_id_col_name = 'job_item_id';
			}
			if ( $this.current_edit_record.hasOwnProperty( job_item_id_col_name ) ) {
				job_item_widget.setValue( job.default_item_id );
				$this.current_edit_record[job_item_id_col_name] = job.default_item_id;

				if ( job.default_item_id === false || job.default_item_id === 0 || job.default_item_id === TTUUID.zero_id || job.default_item_id === TTUUID.not_exist_id ) {
					$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );
				}

			} else {
				job_item_widget.setValue( '' );
				$this.current_edit_record[job_item_id_col_name] = false;
				$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );
			}

			TTPromise.resolve( 'BaseViewController', 'setJobItemValueWhenJobChanged' );
		}
	},

	onJobQuickSearch: function( key, value, job_id_field, job_item_id_field, filter_data ) {
		var $this = this;

		var args = {};

		TTPromise.add( 'BaseViewController', 'onJobQuickSearch' );

		if ( job_id_field == undefined ) {
			job_id_field = 'job_id';
		}
		if ( job_item_id_field == undefined ) {
			job_item_id_field = 'job_item_id';
		}

		//Error: Uncaught TypeErro: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
		if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic[job_id_field] ) {
			return;
		}

		if ( key === 'job_quick_search' ) {
			args.filter_data = { manual_id: value, user_id: this.current_edit_record.user_id, status_id: '10' };

			this.job_api.getJob( args, {
				onResult: function( result ) {
					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
					if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic[job_id_field] ) {
						return;
					}

					var result_data = result.getResult();

					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic[job_id_field].setValue( result_data[0].id );
						$this.current_edit_record[job_id_field] = result_data[0].id;
						$this.setJobItemValueWhenJobChanged( result_data[0], job_item_id_field, filter_data );
					} else {
						$this.edit_view_ui_dic[job_id_field].setValue( '' );
						$this.current_edit_record[job_id_field] = false;
						$this.setJobItemValueWhenJobChanged( false, job_item_id_field, filter_data );
					}

					TTPromise.resolve( 'BaseViewController', 'onJobQuickSearch' );
				}
			} );

			$this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
			$this.edit_view_ui_dic[job_id_field].setCheckBox( true );
		} else if ( key === 'job_item_quick_search' ) {
			args.filter_data = { manual_id: value, job_id: this.current_edit_record[job_id_field], status_id: '10' };

			this.job_item_api.getJobItem( args, {
				onResult: function( result ) {
					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
					if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic[job_item_id_field] ) {
						return;
					}

					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic[job_item_id_field].setValue( result_data[0].id );
						$this.current_edit_record[job_item_id_field] = result_data[0].id;

					} else {
						$this.edit_view_ui_dic[job_item_id_field].setValue( '' );
						$this.current_edit_record[job_item_id_field] = false;
					}

					TTPromise.resolve( 'BaseViewController', 'onJobQuickSearch' );
				}
			} );

			this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
			this.edit_view_ui_dic[job_item_id_field].setCheckBox( true );
		}
	},

	onCancelClick: function( force_no_confirm, cancel_all, callback ) {
		TTPromise.add( 'base', 'onCancelClick' );
		var $this = this;
		//#2342 This logic is also in onSubMenuClick click in RibbonViewController
		if ( !force_no_confirm
				&&
				(
						$this.is_changed == true
						|| ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.edit_view && LocalCacheData.current_open_primary_controller.is_changed == true )
						|| ( LocalCacheData.current_open_report_controller && LocalCacheData.current_open_report_controller.is_changed == true )
						|| ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.is_changed == true )
						|| ( LocalCacheData.current_open_sub_controller && LocalCacheData.current_open_sub_controller.edit_view && LocalCacheData.current_open_sub_controller.is_changed == true )

				) ) {
			this.confirm_on_exit = true;
		}

		LocalCacheData.current_doing_context_action = 'cancel';
		if ( this.confirm_on_exit == true ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( clicked_yes ) {
				if ( clicked_yes === true ) {
					doNext( force_no_confirm, cancel_all, callback );
				} else {
					TTPromise.reject( 'base', 'onCancelClick' );
				}
			} );
		} else {
			doNext( force_no_confirm, cancel_all, callback );
		}

		function doNext( force_no_confirm, cancel_all, callback ) {
			if ( !$this.edit_view && $this.parent_view_controller && $this.sub_view_mode ) {
				$this.parent_view_controller.is_changed = false;
				$this.parent_view_controller.confirm_on_exit = false;
				$this.parent_view_controller.buildContextMenu( true );
				$this.parent_view_controller.onCancelClick( true ); //Force no confirm so we don't get two messages when cancelling from Edit Employee -> Wage (tab) -> Edit Wage.
			} else {
				$this.removeEditView( true );
			}

			if ( cancel_all ) {
				if ( LocalCacheData.current_open_edit_only_controller ) {
					LocalCacheData.current_open_edit_only_controller.onCancelClick( force_no_confirm, cancel_all );
				} else if ( LocalCacheData.current_open_sub_controller && LocalCacheData.current_open_sub_controller.edit_view ) {
					LocalCacheData.current_open_sub_controller.onCancelClick( force_no_confirm, cancel_all );
				} else if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.edit_view ) {
					LocalCacheData.current_open_primary_controller.onCancelClick( force_no_confirm, cancel_all );
				} else if ( LocalCacheData.current_open_report_controller ) {
					LocalCacheData.current_open_report_controller.onCancelClick( force_no_confirm, cancel_all );
				}
			}
			if ( callback ) {
				callback();
			}
			TTPromise.resolve( 'base', 'onCancelClick' );
		}
	},

	//Don't call super if override this function.
	onFormItemChange: function( target, doNotValidate ) {
		// Error: TypeError: this.current_edit_record is undefined in interface/html5/views/BaseViewController.js?v=9.0.7-20160202-113244 line 1691
		if ( !this.current_edit_record ) {
			return;
		}

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		this.current_edit_record[key] = target.getValue();

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setIsChanged: function( target ) {
		var key = target.getField();
		if ( this.current_edit_record && this.current_edit_record[key] != target.getValue() ) {
			this.is_changed = true;
		}
	},

	onFormItemKeyUp: function( target ) {

	},

	onFormItemKeyDown: function( target ) {

	},

	setMassEditingFieldsWhenFormChange: function( target ) {
		var $this = this;

		if ( this.is_mass_editing ) {
			var field = target.getField();
			var linked_fields = [];
			var is_linked_field = false;
			$.each( this.linked_columns, function( index, value ) {
				if ( value !== field ) {
					linked_fields.push( value );
				} else {
					is_linked_field = true;
				}
			} );

			if ( is_linked_field ) {
				$.each( linked_fields, function( index, value ) {
					var is_checked = $this.edit_view_ui_dic[field].isChecked();
					$this.edit_view_ui_dic[value].setCheckBox( is_checked );
				} );
			}

		}
	},

	setTabLabels: function( source ) {
		for ( var key in source ) {
			this.edit_view.find( 'a[ref=' + key + ']' ).text( source[key] );
		}
	},

	getTabModel: function() {
		return this.tab_model;
	},
	setTabModel: function( model ) {
		var tab_labels = {};

		for ( i in model ) {
			//If the model is "true", then use default models for audit/attachment tabs.
			if ( i == 'tab_audit' && model[i] === true ) {
				model['tab_audit'] = {
					'label': $.i18n._( 'Audit' ),
					'init_callback': 'initSubLogView',
					'display_on_mass_edit': false,
					'display_on_add': false
				};
			} else if ( i == 'tab_attachment' && model[i] === true ) {
				model['tab_attachment'] = {
					'label': $.i18n._( 'Attachment' ),
					'init_callback': 'initSubDocumentView',
					'display_on_mass_edit': false
				};
			}

			if ( model[i].hasOwnProperty( 'label' ) && model[i].label != '' ) {
				tab_labels[i] = model[i].label;
			}
		}

		this.tab_model = model;
		this.setTabLabels( tab_labels );

		return true;
	},

	onTabShow: function( e, ui ) {
		if ( !this.current_edit_record ) {
			return;
		}

		var key = this.getEditViewTabIndex();
		this.editFieldResize( key );

		var tab_model = this.getTabModel();

		if ( tab_model != null ) {
			if ( ui && ui.oldTab ) {
				var prev_tab_name = ui.oldTab.find('a')[0].getAttribute('href').substring(1);
				if ( tab_model[prev_tab_name] && tab_model[prev_tab_name].hasOwnProperty('on_exit_callback') && tab_model[prev_tab_name].on_exit_callback != '' ) {
					this[tab_model[prev_tab_name].on_exit_callback]( prev_tab_name ); //Call mapped function to initialize the tab.
				}
			}

			//ReFactored path to handle tabs based on a tab model mapping defined in each view class.
			//This abstracts the entry point for all tabs initializations to help with hiding/showing them and to reduce code duplication.
			var tab_name = null;
			var sub_view_div = null;

			var tab_bar = this.edit_view.find( '.edit-view-tab-bar li.ui-tabs-active' ).find('a');
			if ( tab_bar.length > 0 ) {
				tab_name = tab_bar[0].getAttribute( 'href' ).substring( 1 ); //Remove the '#';
				sub_view_div = this.edit_view_tab.find( '#' + tab_name ).find( '.first-column-sub-view' );
			}

			if ( sub_view_div && sub_view_div.length > 0 && this.tab_model[tab_name] && !this.tab_model[tab_name].initialized ) { //Only hide grid on first initialization as it has to load all the data. Otherwise the 2nd time the user goes to the tab they will see some minor "flashing"
				TTPromise.add( 'BaseViewController', 'onTabShow' );
				TTPromise.wait( 'BaseViewController', 'onTabShow', function() {
					sub_view_div.css('opacity', '1');
				} );

				sub_view_div.css( 'opacity', '0' ); //Hide the grid while its loading/sizing.
				this.tab_model[tab_name].initialized = true;
			}

			if ( tab_model[tab_name] ) {
				//this.edit_view_tab.find( '#'+ tab_name ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				//Call the init_callback even if we are editing an existing record or creating a new one.
				// As some views (ie: OverTime Policy) need to control whats shown on each tab regardless of if we are editing or adding.
				if ( tab_model[tab_name].hasOwnProperty('init_callback') && tab_model[tab_name].init_callback != '' ) {
					this[tab_model[tab_name].init_callback]( tab_name ); //Call mapped function to initialize the tab.
				} else {
					//Assume primary tab and build context menu.
					if ( this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) {
						this.buildContextMenu( true );
						this.setEditMenu();
					} else {
						//this.edit_view_tab.find( '#'+ tab_name ).find( '.first-column-sub-view' ).css( 'display', 'none' ); //This would prevent the grid from showing in Attendance -> TimeSheet, Accumulated Time view.
						//this.edit_view_tab.find( '#'+ tab_name ).find( '.first-column-sub-view' ).css( 'display', 'block' );
						this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
					}
				}

				// if ( this.current_edit_record.id ) {
				// 	//this.edit_view_tab.find( '#'+ tab_name ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				// 	if ( tab_model[tab_name].hasOwnProperty('init_callback') && tab_model[tab_name].init_callback != '' ) {
				// 		this[tab_model[tab_name].init_callback]( tab_name ); //Call mapped function to initialize the tab.
				// 	} else {
				// 		//Assume primary tab and build context menu.
				// 		this.buildContextMenu( true );
				// 		this.setEditMenu();
				// 	}
				// } else {
				// 	//this.edit_view_tab.find( '#'+ tab_name ).find( '.first-column-sub-view' ).css( 'display', 'none' ); //This would prevent the grid from showing in Attendance -> TimeSheet, Accumulated Time view.
				// 	//this.edit_view_tab.find( '#'+ tab_name ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				// 	this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				// }
			} else {
				//Assume primary tab and build context menu.
				this.buildContextMenu( true );
				this.setEditMenu();
			}
		} else {
			//Handle most cases that one tab and on audit tab
			if ( key === 1 ) {

				if ( this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) {
					this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.initSubLogView( 'tab_audit' );
				} else {

					this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				}

			} else {
				this.buildContextMenu( true );
				this.setEditMenu();
			}
		}
	},

	//When overriding this function, always call super() so it can handle tab_audit/tab_attachment on its own.
	checkTabPermissions: function( tab ) {
		var retval = true; //Most tabs are shown, so default to true.

		switch ( tab ) {
			case 'tab_audit':
				retval = this.subAuditValidate();
				break;
			case 'tab_attachment':
				retval = this.subDocumentValidate();
				break;
		}

		return retval;
	},

	setTabStatus: function() {
		// exception that edit_view_tab is null
		if ( !this.edit_view_tab ) {
			return;
		}

		var tab_model = this.getTabModel();

		if ( tab_model != null ) {
			var visible_tab_indexes = Array();

			//ReFactored path to handle tabs based on a tab model mapping defined in each view class.
			//This abstracts the entry point for all tabs initializations to help with hiding/showing them and to reduce code duplication.
			for( i in tab_model ) {
				var tab_index = $( this.edit_view_tab.find( 'ul li a[ref="' + i + '"]' ) ).parent().index();

				if ( ( this.is_mass_editing && tab_model[i].hasOwnProperty('display_on_mass_edit') && tab_model[i].display_on_mass_edit == false )
					|| ( ( this.is_add || this.is_mass_adding ) && tab_model[i].hasOwnProperty('display_on_add') && tab_model[i].display_on_add == false ) ) {
					$( this.edit_view_tab.find( 'ul li a[ref="' + i + '"]' ) ).parent().hide();
				} else {
					if ( this.checkTabPermissions( i ) == true ) {
						$( this.edit_view_tab.find( 'ul li a[ref="' + i + '"]' ) ).parent().show();
						visible_tab_indexes.push( tab_index );
					} else {
						$( this.edit_view_tab.find( 'ul li a[ref="' + i + '"]' ) ).parent().hide();
					}
				}
			}

			//Always start with the first tab that actually has permissions to be shown. This is important for sub-views like Edit Employee, Tax, New icon, where it shows only a single tab where there are really 5+ tabs just hidden.
			visible_tab_indexes = visible_tab_indexes.sort( function( a, b ) { return a - b } ); //numeric sort.

			if ( visible_tab_indexes[0] ) {
				this.edit_view_tab.tabs( 'option', 'active', visible_tab_indexes[0] );
			}
		} else {
			//Handle most cases that one tab and on audit tab
			if ( this.is_mass_editing ) {

				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'option', 'active', 0 );

			} else {

				if ( this.subAuditValidate() ) {
					$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().show();
				} else {
					$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
					this.edit_view_tab.tabs( 'option', 'active', 0 );
				}

			}
		}

		this.editFieldResize( 0 );
	},

	onTabIndexChange: function( e ) {
		TTPromise.add( 'BaseViewController', 'onTabIndexChange' );
		TTPromise.wait();

		if ( ( !this.sub_view_mode && !this.edit_only_mode ) || typeof this.initReport == 'function' ) {
			var current_url = window.location.href;

			if ( current_url.indexOf( '&tab' ) > 0 ) {
				current_url = current_url.substring( 0, current_url.indexOf( '&tab' ) );
			}
			var tab_name = this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.getEditViewTabIndex() ).text();
			tab_name = tab_name.replace( /\/|\s+/g, '' );
			current_url = current_url + '&tab=' + tab_name;

			Global.setURLToBrowser( current_url );

		}

		this.hideErrorTips();
		TTPromise.resolve( 'BaseViewController', 'onTabIndexChange' );
	},

	hideErrorTips: function() {
		for ( var key in this.edit_view_error_ui_dic ) {
			//#2581 - Uncaught TypeError: this.edit_view_error_ui_dic[key].hideErrorTip is not a function
			if ( this.edit_view_error_ui_dic[key] && typeof this.edit_view_error_ui_dic[key].hideErrorTip == 'function' ) {
				this.edit_view_error_ui_dic[key].hideErrorTip();
			}
		}
		this.removeEditViewErrorTip();
	},

	//removed workarounds and comments for qtip1 when upgrading to qtip2.
	removeEditViewErrorTip: function() {
		if ( $( '.qtip2-error-tip:visible' ) ) {
			$( '.qtip2-error-tip' ).remove();
		}
	},

	onCountryChange: function() {
		var selectVal = this.edit_view_ui_dic['country'].getValue();
		this.eSetProvince( selectVal, true );
		this.clearErrorTips();
		this.setEditMenu();
	},

	//Make sure this.current_edit_record is updated before validate
	validate: function( api ) {
		if ( this.enable_validation ) {
			//Allow alternate api to be validated.
			if ( api == undefined ) {
				var api = this.api;
			}

			var $this = this;
			var record = {};
			if ( this.is_mass_editing ) {
				for ( var key in this.edit_view_ui_dic ) {

					if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						continue;
					}
					var widget = this.edit_view_ui_dic[key];
					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() && widget.getEnabled() ) {
							record[key] = widget.getValue();
						}
					}
				}
			} else {
				record = this.current_edit_record;
			}
			record = this.uniformVariable( record );
			api['validate' + api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		} else {
			Debug.Text( 'Validation disabled', 'BaseViewController.js', 'BaseViewController', 'validate', 10 );
		}
	},

	validateResult: function( result ) {
		var $this = this;
		$this.clearErrorTips(); //Always clear error
		if ( !$this.edit_view ) {
			return;
		}

		if ( !result ) {
			return;
		}

		if ( result.isValid() ) {
			$this.edit_view.attr( 'validate_complete', true );
			$this.setEditMenu();
		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result, this.show_warning_when_validation );

		}
	},

	clearErrorTips: function() {

		for ( var key in this.edit_view_error_ui_dic ) {
			//Error: Uncaught TypeError: Cannot read property 'clearErrorStyle' of undefined in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 1779
			if ( !this.edit_view_error_ui_dic.hasOwnProperty( key ) || !this.edit_view_error_ui_dic[key] ) {
				continue;
			}
			this.edit_view_error_ui_dic[key].clearErrorStyle();
		}

		// Error: Uncaught TypeError: Cannot read property 'interfaces' of undefined in interface/html5/framework/jquery.qtip.min.js?v=9.0.0-20150918-221906 line 15
		this.removeEditViewErrorTip();
		$( '.error-tab' ).removeClass( 'error-tab' );
		$( '.error-tab-hide' ).removeClass( 'error-tab-hide' );
		$( '.warning-tab' ).removeClass( 'warning-tab' );
		$( '.warning-tab-hide' ).removeClass( 'warning-tab-hide' );
		// Clear pulse on tabs
		if ( this.pulse_time_dic ) {
			for ( var key1 in this.pulse_time_dic ) {
				clearInterval( this.pulse_time_dic[key1] );
			}
			this.pulse_time_dic = {};
		}
		this.edit_view_error_ui_dic = {};

		$('.qtip').remove();
	},

	//Override this if more than one tab
	setErrorTips: function( result, show_warning ) {
		this.clearErrorTips();
		if ( !Global.isSet( show_warning ) ) {
			show_warning = true;
		}
		//Error: Unable to get property 'find' of undefined or null reference in http://timeclock:8085/interface/html5/views/BaseViewController.js?v=7.4.3-20140926-105827 line 1769
		if ( !this.edit_view_tab ) {
			return;
		}
		var details = result.getDetails();
		// Only check first item
		// Error: Uncaught TypeError: Cannot call method 'hasOwnProperty' of undefined in /interface/html5/views/BaseViewController.js?v=9.0.0-20150822-134259 line 1879
		// Zero is not always the first element;
		var first_el = 0;
		for ( first_el in details ) {
			break;
		}

		if ( details && details[first_el] && details[first_el].hasOwnProperty( 'error' ) ) {
			this.setErrorTipsError( result );
		} else if ( details && details[first_el] && details[first_el].hasOwnProperty( 'warning' ) ) { //Error: TypeError: details[0] is undefined in https://greenacres.timetrex.com/interface/html5/views/BaseViewController.js?v=9.0.0-20150822-105118 line 1883
			if ( show_warning ) {
				this.setErrorTipsWarning( result );
			}
			this.setEditMenu();
		} else {
			// Make sure current codes work.
			this.setErrorTipsError( result );
		}

	},

	setErrorTipsWarning: function( result ) {
		var $this = this;
		var widget;
		// when do validation, only show warning no alert
		var $current_doing_context_action = LocalCacheData.current_doing_context_action; //#2474 - LocalCacheData.current_doing_context_action can change to "validate" while waiting for user to respond to warning box.
		if ( $current_doing_context_action != 'validate' ) {
			TAlertManager.showWarningAlert( result, function( flag ) {
				if ( flag ) {
					switch ( $current_doing_context_action ) {
						case 'save':
							$this.onSaveClick( true );
							break;
						case 'save_and_continue':
							$this.onSaveAndContinue( true );
							break;
						case 'save_and_next':
							$this.onSaveAndNextClick( true );
							break;
						case 'save_and_copy':
							$this.onSaveAndCopy( true );
							break;
						case 'new':
							$this.onSaveAndNewClick( true );
							break;
					}
				} else {
					$this.show_warning_when_validation = true;
				}
			} );
		}

		//Error: Unable to get property 'warning' of undefined or null reference
		var error_list = [];
		if ( result.getDetails().length == 1 ) {
			error_list = result.getDetails()[0].warning;
		}
		var found_in_current_tab = false;
		for ( var key in error_list ) {
			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}
			if ( Global.isSet( this.edit_view_ui_dic[key] ) && this.edit_view_ui_dic[key].closest( document.documentElement ).length > 0 ) {
				widget = this.edit_view_ui_dic[key];
			} else if ( Global.isSet( this.edit_view_ui_validation_field_dic[key] ) ) {
				if ( Global.isArray( this.edit_view_ui_validation_field_dic[key] ) ) {
					var len = this.edit_view_ui_validation_field_dic[key].length;
					for ( var i = 0; i < len; i++ ) {
						var item = this.edit_view_ui_validation_field_dic[key][i];
						if ( item.closest( document.documentElement ).length > 0 ) {
							widget = item;
							break;
						}
					}
				} else if ( this.edit_view_ui_validation_field_dic[key].closest( document.documentElement ).length > 0 ) {
					widget = this.edit_view_ui_validation_field_dic[key];
				} else {
					continue;
				}
			} else if ( key.indexOf( '_id' ) < 0 && Global.isSet( this.edit_view_ui_dic[key + '_id'] ) && this.edit_view_ui_dic[key + '_id'].closest( document.documentElement ).length > 0 ) {
				widget = this.edit_view_ui_dic[key + '_id'];
			} else {
				continue;
			}
			if ( error_list[key] ) {
				var show_error = false;
				if ( widget.is( ':visible' ) ) {
					show_error = true;
					found_in_current_tab = true;
				}

				if ( typeof widget.setErrorStyle === 'function' ) { //Fix JS exception: Uncaught TypeError: widget.setErrorStyle is not a function
					widget.setErrorStyle( error_list[key], show_error, true );
				}

			}
			this.showErrorStatusOnTab( widget, false );
			this.edit_view_error_ui_dic[key] = widget;
		}
		if ( !found_in_current_tab ) {
			this.showEditViewError( result );
		}
	},

	showErrorStatusOnTab: function( widget, isError ) {
		var parentContainer = widget.parent();
		var i = 0;
		while ( !parentContainer.hasClass( 'edit-view-tab-outside' ) && i < 5 ) {
			i = i + 1;
			parentContainer = parentContainer.parent();
		}
		if ( parentContainer.hasClass( 'edit-view-tab-outside' ) ) {
			var id = parentContainer.attr( 'id' );
			var tab = this.edit_view.find( 'a[ref="' + id + '"]' );
			if ( isError ) {
				tab.parent().addClass( 'error-tab' );
				this.startPulse( id, tab.parent() );
			} else {
				tab.parent().addClass( 'warning-tab' );
				this.startPulse( id, tab.parent(), true );
			}

		}
	},

	startPulse: function( tab_id, target, is_warning ) {
		var $this = this;
		if ( !this.pulse_time_dic ) {
			this.pulse_time_dic = {};
		}
		if ( this.pulse_time_dic[tab_id] ) {
			cleanTimer( tab_id );
		}
		this.pulse_time_dic[tab_id] = setInterval( function() {
			if ( is_warning ) {
				if ( target.hasClass( 'warning-tab-hide' ) ) {
					target.removeClass( 'warning-tab-hide' );
				} else if ( target.hasClass( 'warning-tab' ) ) {
					target.addClass( 'warning-tab-hide' );
				} else {
					cleanTimer( tab_id );
				}
			} else {
				if ( target.hasClass( 'error-tab-hide' ) ) {
					target.removeClass( 'error-tab-hide' );
				} else if ( target.hasClass( 'error-tab' ) ) {
					target.addClass( 'error-tab-hide' );
					cleanTimer( tab_id );
					setTimeout( function() {
						if ( target.hasClass( 'error-tab-hide' ) ) {
							target.removeClass( 'error-tab-hide' );
						}
						$this.startPulse( tab_id, target, is_warning );
					}, 1700 );
				} else {
					cleanTimer( tab_id );
				}
			}
		}, 2000 );

		function cleanTimer( tab_id ) {
			clearInterval( $this.pulse_time_dic[tab_id] );
			$this.pulse_time_dic[tab_id] = null;
		}
	},

	setErrorTipsError: function( result ) {

		//Error: TypeError: details[0] is undefined in interface/html5/views/BaseViewController.js?v=9.0.0-20150822-105118 line 1883
		// Zero is not always the firwst index.
		var result_array = result.getDetails() ? result.getDetails() : {};
		var first_el = 0;
		for ( first_el in result_array ) {
			break;
		}
		var error_list = result_array ? result_array[first_el] : {};
		var widget;
		if ( error_list && error_list.hasOwnProperty( 'error' ) ) {
			error_list = error_list.error;
		}
		var found_in_current_tab = false;
		for ( var key in error_list ) {
			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}
			if ( Global.isSet( this.edit_view_ui_dic[key] ) && this.edit_view_ui_dic[key].closest( document.documentElement ).length > 0 ) {
				widget = this.edit_view_ui_dic[key];
			} else if ( Global.isSet( this.edit_view_ui_validation_field_dic[key] ) ) {
				if ( Global.isArray( this.edit_view_ui_validation_field_dic[key] ) ) {
					var len = this.edit_view_ui_validation_field_dic[key].length;
					for ( var i = 0; i < len; i++ ) {
						var item = this.edit_view_ui_validation_field_dic[key][i];
						if ( item.closest( document.documentElement ).length > 0 ) {
							widget = item;
							break;
						}
					}
				} else if ( this.edit_view_ui_validation_field_dic[key].closest( document.documentElement ).length > 0 ) {
					widget = this.edit_view_ui_validation_field_dic[key];
				} else {
					continue;
				}
			} else if ( key.indexOf( '_id' ) < 0 && Global.isSet( this.edit_view_ui_dic[key + '_id'] ) && this.edit_view_ui_dic[key + '_id'].closest( document.documentElement ).length > 0 ) {
				widget = this.edit_view_ui_dic[key + '_id'];
			} else {
				continue;
			}
			if ( widget.is( ':visible' ) ) {
				// Error: Uncaught TypeError: widget.setErrorStyle is not a function
				if ( widget.setErrorStyle && typeof widget.setErrorStyle == 'function' ) {
					widget.setErrorStyle( error_list[key], true );
					found_in_current_tab = true;
				} else {
					Debug.Text( 'ERROR: widget.setErrorStyle is not a function.', 'BaseViewController.js', 'BaseViewController', null, 10 );
				}
			} else {
				// Error: Uncaught TypeError: widget.setErrorStyle is not a function
				if ( widget.setErrorStyle && typeof widget.setErrorStyle == 'function' ) {
					widget.setErrorStyle( error_list[key] );
				} else {
					Debug.Text( 'ERROR: widget.setErrorStyle is not a function.', 'BaseViewController.js', 'BaseViewController', null, 10 );
				}
			}
			this.showErrorStatusOnTab( widget, true );
			this.edit_view_error_ui_dic[key] = widget;
		}
		if ( !found_in_current_tab ) {
			this.showEditViewError( result );
		}
	},

	showEditViewError: function( result ) {
		var details = result.getDetails()[0];
		var isError = true;
		//Error: TypeError: details is undefined in interface/html5/views/BaseViewController.js?v=9.0.0-20150908-081451 line 2078
		if ( !details ) {
			return;
		}
		if ( details.hasOwnProperty( 'error' ) ) {
			details = details.error;
			isError = true;
		} else if ( details.hasOwnProperty( 'warning' ) ) {
			isError = false;
			details = details.warning;
		}
		var error_string = '';
		var background_color = isError ? '#cb2e2e' : '#ffff00';
		var color = isError ? '#fff' : '#000';
		var border_color = isError ? '#CB2E2E' : '#e7be00';

		error_string = Global.convertValidationErrorToString( details );

		this.removeEditViewErrorTip();
		this.edit_view.children().eq( 0 ).children().eq( 2 ).qtip( {
			show: {
				when: false,
				ready: true
			},
			events: {
				hide: function( event, api ) {
					event.preventDefault();
				}
			},
			content: error_string,
			style: {
				classes: isError?'qtip2-error-tip':'qtip2-warning-tip', //used for styling and removal.
				tip: {
					corner: 'bottom center'
				}
			},
			position: {
				my: 'bottom left',
				at: 'top center'
			}
		} );
	},

	selectContextMenu: function() {
		//This code is also in HomeViewController
		//Error: Uncaught TypeError: Cannot read property 'el' of null in /interface/html5/views/BaseViewController.js?v=8.0.0-20141230-113526 line 1880
		if ( TopMenuManager.selected_menu_id !== this.viewId + 'ContextMenu' && TopMenuManager.ribbon_view_controller ) {
			var ribbon = $( TopMenuManager.ribbon_view_controller.el );
			// Error: Object doesn't support property or method 'tabs' in /interface/html5/views/BaseViewController.js?v=8.0.6-20150417-083849 line 2032
			if ( ribbon ) {
				ribbon.tabs( 'refresh' );
				ribbon.find('[aria-controls='+ this.viewId + 'ContextMenu] a').trigger('click');
			}
		}
	},

	openEditView: function() {
		if ( !this.edit_view ) {
			this.initEditViewUI( this.viewId, this.edit_view_tpl );
		}
	},

	setTabOVisibility: function( flag ) {
		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab' )[0] );
		if ( flag ) {
			tab0.css( 'opacity', 1 );
			this.setEditViewTabSize();
			if ( this.edit_view_close_icon ) {
				this.edit_view_close_icon.show();
			}
		} else {
			this.edit_view_tab.find( 'ul li' ).hide();
			tab0.css( 'opacity', 0 );
		}

	},
	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}
		}
	},

	//Call this when edit view open
	initEditViewUI: function( view_id, edit_view_file_name ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();

		var $this = this;
		if ( this.edit_view ) {
			this.edit_view.remove();
		}

		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );

		//#2353 - commented out because it breaks subgrid menus in the employee qualifications tab
		//calls the context menu click every time that the edit view is clicked.
		// this.edit_view.unbind( 'click' ).bind( 'click', function() {
		// 	$this.selectContextMenu();
		// } );

		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );
		this.edit_view_tab.css('opacity', 0);

		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );

		this.setTabOVisibility( false );

		this.edit_view_tab = this.edit_view_tab.tabs( {
			activate: function( e, ui ) {
				if ( !$this.edit_view_tab || !$this.edit_view_tab.is( ':visible' ) ) {
					return;
				}

				$this.onTabShow( e, ui );
				Global.triggerAnalyticsTabs( e, ui );
			}
		} );

		this.edit_view_tab.off( 'click' ).on( 'click', function( e ) {
			$this.onTabIndexChange( e );
		} );

		Global.contentContainer().append( this.edit_view );

		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		this.buildEditViewUI();

		$this.setEditViewTabHeight();
		TTPromise.wait('init', 'init',function(){
			$('.edit-view-tab-bar').css('opacity', 1);
		});
	},

	setEditViewTabHeight: function() {
		var $this = this;
	},

	//Call this after initEditViewUI, usually after current_edit_record is set
	initEditView: function() {
		this.show_warning_when_validation = false;
		//Uncaught TypeError: Cannot read property 'find' of null in Timehseet Authorization view when quickly click Cancel from replay
		if ( !this.edit_view_tab ) {
			return;
		}
		this.setURL();

		this.setEditMenu(); //This is done in onTabeShow() later on, so it can probably be removed from here?
		//Remove cover once edit menu is set
		ProgressBar.closeOverlay();

		//Error: Unable to get property 'find' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=7.4.6-20141027-074127 line 2055
		if ( this.edit_view_tab ) {
			this.edit_view_tab.find( 'ul li' ).show(); // All tabs are hidden when initEditView UI, show all of them before set status
		}
		this.setTabStatus();
		this.clearEditViewData();
		this.setEditViewWidgetsMode();
		this.setEditViewData();
		this.setOtherFields();
		this.setFocusToFirstInput();
	},

	setOtherFields: function() {

		var $this = this;
		var type_id = this.getOtherFieldTypeId();

		if ( type_id === 0 ) {
			return;
		}

		var filter = { filter_data: { type_id: type_id } };

		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab-outside' )[0] );
		var tab0_column1 = tab0.find( '.first-column' );

		this.other_field_api.getOtherField( filter, true, {
			onResult: function( result ) {
				var res_data = result.getResult();
				if ( $.type( res_data ) === 'array' && res_data.length > 0 ) {
					res_data = res_data[0];

					for ( var i = 1; i < 10; i++ ) {
						if ( res_data['other_id' + i] ) {
							$this.buildOtherFieldUI( 'other_id' + i, res_data['other_id' + i] );
						}
					}

					$this.editFieldResize( 0 );
				}

				$this.resetLastWidgetStyle();

			}
		} );

	},


	getOtherFieldReferenceField: function() {
		return false;
	},


	buildOtherFieldUI: function( field, label ) {
		//Not all views have other_id# permissions.
		//Therefore fail open to allow this to be applied on a per-view basis as permissions are added down the road.
		var permission = PermissionManager.getPermissionData();
		if ( Global.isSet( permission[this.permission_id]['edit_' + field] ) ) {
			if ( PermissionManager.validate( this.permission_id, 'edit_' + field ) == false ) {
				return false;
			}
		}


		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab-outside' )[0] );
		var tab0_column1 = tab0.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: field } );

			var input_div = $this.addEditFieldToColumn( label, form_item_input, tab0_column1 );
			if ( this.getOtherFieldReferenceField() !== false && $this.edit_view_ui_dic[this.getOtherFieldReferenceField()] != undefined ) {
				input_div.insertBefore( $this.edit_view_ui_dic[this.getOtherFieldReferenceField()].parent().parent() );
			}

			if ( this.current_edit_record ) {
				form_item_input.setValue( this.current_edit_record[field] );
			}
		}
		form_item_input.css( 'opacity', 1 );
		form_item_input.css( 'minWidth', 300 );

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}

	},

	resetLastWidgetStyle: function() {

		if ( !this.edit_view_tab || !this.edit_view ) {
			return;
		}

	},

	getOtherFieldTypeId: function() {
		var res = 0;
		switch ( this.viewId ) {
			case 'Employee':
				res = 10;
				break;
			case 'Document':
				res = 80;
				break;
			case 'Job':
				res = 20;
				break;
			case 'JobItem':
				res = 30;
				break;
			case 'Product':
				res = 60;
				break;
			case 'Invoice':
				res = 70;
				break;
			case 'ClientContact':
				res = 55;
				break;
			case 'Client':
				res = 50;
				break;
			case 'UserTitle':
				res = 12;
				break;
			case 'Department':
				res = 5;
				break;
			case 'Company':
			case 'Companies':
				res = 2;
				break;
			case 'Branch':
				res = 4;
				break;
			case 'Schedule':
			case 'ScheduleShift':
				res = 18;
				break;
			case 'TimeSheet':
			case 'Punches':
			case 'InOut':
				res = 15;
				break;

		}

		return res;
	},

	setURL: function() {
		var a = '';
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'new':
			case 'edit':
			case 'view':
				a = LocalCacheData.current_doing_context_action;
				break;
			case 'copy_as_new':
				a = 'new';
				break;
		}
		if ( this.canSetURL() ) {

			var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.getEditViewTabIndex() ).text() : '';
			tab_name = tab_name.replace( /\/|\s+/g, '' );

			//Error: Unable to get property 'id' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-132941 line 2234
			if ( this.current_edit_record && this.current_edit_record.id ) {
				if ( a ) {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + a + '&id=' + this.current_edit_record.id + '&tab=' + tab_name );
				} else {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&id=' + this.current_edit_record.id );
				}

				Global.trackView( this.viewId, LocalCacheData.current_doing_context_action );
			} else {
				if ( a ) {

					//Edit a record which don't have id, schedule view Recurring Scedule
					if ( a === 'edit' ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + 'new' +
								'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + a +
								'&tab=' + tab_name );
					}

				} else {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId );
				}
			}

		}

	},

	canSetURL: function() {
		if ( this.sub_view_mode || this.edit_only_mode ) {
			return false;
		}

		return true;
	},

	setFocusToFirstInput: function() {
		//Do not set focus to first input in unit test mode as it causes a blink that is inconsistent in screenshots. Also disable on mobile mode so its not a jarring experience with the zoom changes on each page
		if ( Global.UNIT_TEST_MODE || $('body').hasClass('mobile-device-mode') ) {
			return;
		}

		if ( !this.is_viewing ) {
			if ( this.script_name === 'ScheduleView' ) {
				if ( this.edit_view_ui_dic.start_time ) {
					this.edit_view_ui_dic.start_time.children().eq( 0 ).focus();
					this.edit_view_ui_dic.start_time.children().eq( 0 )[0].select();
				}
			} else {
				for ( var key in this.edit_view_ui_dic ) {

					if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						continue;
					}
					var widget = this.edit_view_ui_dic[key];

					if ( widget.is( ':visible' ) === true ) {
						if ( widget.hasClass( 't-text-input' ) && !widget.attr( 'readonly' ) ) {
							widget.focus();
							widget[0].select();
							break;
						} else if ( widget.hasClass( 't-time-picker-div' ) && !widget.children().eq( 0 ).attr( 'readonly' ) ) {
							widget.children().eq( 0 ).focus();
							widget.children().eq( 0 )[0].select();
							break;
						} else if ( widget.hasClass( 't-date-picker-div' ) && !widget.children().eq( 0 ).attr( 'readonly' ) ) {
							widget.children().eq( 0 ).focus();
							widget.children().eq( 0 )[0].select();
							break;
						}

					}

				}
			}

		}
	},

	initNavigationWidget: function( navigation_widget_div ) {
		if ( !this.navigation ) {
			this.navigation = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			navigation_widget_div.append( this.navigation );
		} else {
			navigation_widget_div.append( this.navigation );
		}
	},

	buildEditViewUI: function() {
		var $this = this;

		//No navigation when edit only mode

		if ( this.edit_view ) {
			if ( !this.edit_only_mode ) {
				var navigation_div = this.edit_view.find( '.navigation-div' );
				var label = navigation_div.find( '.navigation-label' );
				var left_click = navigation_div.find( '.left-click' );
				var right_click = navigation_div.find( '.right-click' );
				var navigation_widget_div = navigation_div.find( '.navigation-widget-div' );
				this.initNavigationWidget( navigation_widget_div );
				left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.png' ) );
				right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.png' ) );
				label.text( this.navigation_label );

			}

			this.edit_view_close_icon = this.edit_view.find( '.close-icon' );
			this.edit_view_close_icon.hide();

			this.edit_view_close_icon.click( function() {
				$this.onCloseIconClick();
			} );
		}

		this.edit_view_ui_dic = {};
		this.edit_view_ui_validation_field_dic = {};
		this.edit_view_form_item_dic = {};
		this.edit_view_error_ui_dic = {};

	},

	onCloseIconClick: function() {
		if ( LocalCacheData.current_open_sub_controller ) {
			LocalCacheData.current_open_sub_controller.onCancelClick();
		} else {
			this.onCancelClick();
		}
		Global.triggerAnalyticsNavigationOther( 'close-X', 'click', this.viewId );
	},

	setWidgetVisible: function( widgets ) {
		var widget = widgets;
		if ( Global.isArray( widgets ) ) {
			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				widget.css( 'opacity', 1 );
			}
		} else {
			widget.css( 'opacity', 1 );
		}
	},

	//widgetContainer: add widget to custom container
	//saveFormItemDiv: if cache current formItemDiv and use it later
	addEditFieldToColumn: function( label, widgets, column, firstOrLastRecord, widgetContainer, saveFormItemDiv, setResizeEvent, saveFormItemDivKey, hasKeyEvent, customLabelWidget ) {

		var $this = this;
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		var widget = widgets;

		if ( Global.isArray( widgets ) ) {
			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				widget.css( 'opacity', 0 );
			}
		} else {
			widget.css( 'opacity', 0 );
		}

		if ( customLabelWidget ) {
			form_item_label.parent().append( customLabelWidget );
			form_item_label.remove();
		} else {
			form_item_label.text( label + ': ' );
		}

		if ( Global.isSet( widgetContainer ) ) {

			form_item_input_div.append( widgetContainer );

		} else {
			form_item_input_div.append( widget );
		}

		column.append( form_item );

		//set height to text area
		if ( form_item.height() > 35 ) {
			form_item_label_div.css( 'height', form_item.height() );
		} else if ( widget.hasClass( 'a-dropdown' ) ) {
			form_item_label_div.css( 'height', 240 );
		}

		//these aren't hit uniformly for every field so the vertical resize events will be disabled in unit test mode.
		if ( setResizeEvent && !Global.UNIT_TEST_MODE ) {
			form_item.unbind( 'resize' ).bind( 'resize', function() {
				//When switching tabs, the heights are all -1, which causes "flashing" when the user returns back to the original tab and all the heights need to be set again.
				//  To prevent this, only change heights if they are > 0.
				if ( form_item_label_div.height() !== form_item.height() && form_item.height() > 0 ) {
					form_item_label_div.css( 'height', form_item.height() );
				}
			} );
			widget.unbind( 'setSize' ).bind( 'setSize', function() {
				form_item_label_div.css( 'height', widget.height() + 10 );
			} );
		}

		if ( !label ) {
			form_item_input_div.remove();
			form_item_label_div.remove();

			form_item.append( widget );
			widget.css( 'opacity', 1 );

			if ( saveFormItemDiv && saveFormItemDivKey ) {
				this.edit_view_form_item_dic[saveFormItemDivKey] = form_item;
			}

			return;
		}

		if ( saveFormItemDiv ) {

			if ( Global.isArray( widgets ) ) {
				this.edit_view_form_item_dic[widgets[0].getField()] = form_item;
			} else {
				this.edit_view_form_item_dic[widget.getField()] = form_item;
			}

		}
		if ( Global.isArray( widgets ) ) {

			for ( i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				this.edit_view_ui_dic[widget.getField()] = widget;
				setValidationDic();

				widget.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );

				if ( hasKeyEvent ) {
					widget.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
						$this.onFormItemKeyUp( target );
					} );

					widget.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
						$this.onFormItemKeyDown( target );
					} );
				}
			}
		} else {
			this.edit_view_ui_dic[widget.getField()] = widget;
			setValidationDic();

			widget.bind( 'formItemChange', function( e, target, doNotValidate ) {
				$this.onFormItemChange( target, doNotValidate );
			} );

			if ( hasKeyEvent ) {
				widget.bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				widget.bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );
			}
		}

		function setValidationDic() {
			if ( widget.hasOwnProperty( 'getValidationField' ) && widget.getValidationField() ) {
				if ( $this.edit_view_ui_validation_field_dic[widget.getValidationField()] ) {
					if ( !Global.isArray( $this.edit_view_ui_validation_field_dic[widget.getValidationField()] ) ) {
						$this.edit_view_ui_validation_field_dic[widget.getValidationField()] = [$this.edit_view_ui_validation_field_dic[widget.getValidationField()], widget];
					} else {
						$this.edit_view_ui_validation_field_dic[widget.getValidationField()].push( widget );
					}
				} else {
					$this.edit_view_ui_validation_field_dic[widget.getValidationField()] = widget;
				}

			}
		}

		return form_item;

	},

	//Set fields label to same size
	editFieldResize: function( index ) {

		if ( Global.isSet( index ) ) {

		} else {
			index = this.getEditViewTabIndex();
		}

		if ( Global.isSet( this.edit_view_tabs[index] ) && !Global.isFalseOrNull( this.edit_view_tabs[index] ) && this.edit_view_tabs[index].length > 0 ) {
			var tab_div = this.edit_view_tabs[index];
			for ( var i = 0; i < tab_div.length; i++ ) {
				var tab_column_div = tab_div[i].find( '.edit-view-form-item-label-div' );
				var tab_column_sub_div = tab_div[i].find( '.edit-view-form-item-sub-label-div > span' );
				if ( Global.isSet( tab_column_sub_div ) && tab_column_sub_div.length > 0 ) {
					this.setEditFieldSize( tab_column_sub_div );
				}
				this.setEditFieldSize( tab_column_div );
			}
		}
	},

	setEditFieldSize: function( tab_column_div, width ) {

		if ( Global.isSet( width ) ) {

			tab_column_div.each( function() {
				$( this ).width( width );
			} );

		} else {

			var item_label_div_width = [];
			tab_column_div.each( function() {

				if ( $( this ).width() === 0 ) {
					return true;
				}

				$( this ).css( 'width', 'auto' );

				item_label_div_width.push( $( this ).width() );
			} );

			item_label_div_width.sort( function( a, b ) {
				return (b - a);
			} );

			tab_column_div.each( function() {
				if ( item_label_div_width[0] >= 0 ) { // #2701 - Do not set width if value is negative. Happens when trying to calculate width of something on another tab not currently visible.
					$( this ).width( item_label_div_width[0] + 1 );
				}
			} );
		}
	},

	setNavigation: function() {

		var $this = this;

		//Error: Unable to get value of the property 'getGridParam': object is null or undefined in /interface/html5/views/BaseViewController.js?v=8.0.0-20141230-103725 line 2575
		if ( !this.grid ) {
			return;
		}

		this.navigation.setPossibleDisplayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getColumnModel() ), this.buildDisplayColumns( this.default_display_columns ) );

		this.navigation.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {

			var key = target.getField();
			var next_select_item_id = target.getValue();

			if ( !next_select_item_id ) {
				return;
			}

			if ( next_select_item_id !== $this.current_edit_record.id ) {
				ProgressBar.showOverlay();

				if ( $this.is_viewing ) {
					$this.onViewClick( next_select_item_id ); //Dont refresh UI
				} else {
					$this.onEditClick( next_select_item_id ); //Dont refresh UI
				}

			}

			$this.setNavigationArrowsEnabled();
			Global.triggerAnalyticsEditViewNavigation('navigation', $this.viewId );

		} );

	},

	clearEditViewData: function() {
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			if ( _.isFunction( this.edit_view_ui_dic[key].setEmptyValueAndShowLoading ) ) {
				this.edit_view_ui_dic[key].setEmptyValueAndShowLoading();
			} else {
				this.edit_view_ui_dic[key].setValue( null );
			}
			this.edit_view_ui_dic[key].clearErrorStyle();
		}
	},

	//Called after set current_edit_record
	setEditViewData: function() {
		this.is_changed = false;
		this.initEditViewData();
		this.initTabData();
		this.switchToProperTab();

	},

	switchToProperTab: function() {
		if ( LocalCacheData.all_url_args &&
				LocalCacheData.all_url_args.hasOwnProperty( 'tab' ) &&
				LocalCacheData.all_url_args.tab.length > 0 &&
				LocalCacheData.current_open_primary_controller.viewId === this.viewId ) {

			var target_node = this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().filter( function() {
				var value = $( this ).text().replace( /\/|\s+/g, '' );
				return value === LocalCacheData.all_url_args.tab;
			} );

			var target_index = 0;
			if ( target_node.length > 0 ) {
				target_node = $( target_node[0] );
				target_index = target_node.index();
			}
			this.edit_view_tab.tabs( 'option', 'active', target_index );
		}
	},

	//Call this from setEditViewData
	// This is called to initialize data for the first/primary tab, and is called from many views. So it needs to stay even after fully refactored to use tab_model.
	initTabData: function() {
		var tab_model = this.getTabModel();
		if ( tab_model != null ) {
			this.onTabShow();
		} else {
			var current_tab_index = this.getEditViewTabIndex();
			//Handle most case that one tab and one audit tab
			if ( current_tab_index === 1 ) {
				if ( this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) {
					this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.initSubLogView( 'tab_audit' );
				} else {
					this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				}
			}
		}
	},

	getEditViewTabIndex: function() {
		return this.edit_view.find( '.edit-view-tab-bar li.ui-tabs-active' ).index();
	},

	getEditViewActiveTabName: function() {
		return this.edit_view.find( '.edit-view-tab-bar li.ui-tabs-active' ).attr('aria-controls');
	},

	needShowNavigation: function() {
		if ( this.current_edit_record && Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			return true;
		} else {
			return false;
		}
	},

	//Call this from setEditViewData
	initEditViewData: function() {
		var $this = this;

		//add this.grid to fix exception
		//Error: Unable to get property 'getGridParam' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 2523
		if ( !this.edit_only_mode && this.navigation && this.grid ) {

			var grid_current_page_items = this.grid.getData();

			var navigation_div = this.edit_view.find( '.navigation-div' );

			//Error: TypeError: this.current_edit_record is undefined in /interface/html5/views/BaseViewController.js?v=8.0.0-20141230-103725 line 2673
			if ( this.needShowNavigation() ) {
				navigation_div.css( 'display', 'block' );
				//Set Navigation Awesomebox

				//#2349 - update source data every time so that it doesn't go unrefreshed in the case of saving a new record or deleting exiting
				this.navigation.setSourceData( grid_current_page_items );
				this.navigation.setPagerData( this.pager_data );
				//init navigation only when open edit view
				if ( !this.navigation.getSourceData() ) {
					if ( LocalCacheData.getLoginUserPreference() ) {
						this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
					}
					this.navigation.setPagerData( this.pager_data );

					var default_args = {};
					default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
					default_args.filter_sort = this.select_layout.data.filter_sort;
					this.navigation.setDefaultArgs( default_args );
				}

				this.navigation.setValue( this.current_edit_record );

			} else {
				navigation_div.css( 'display', 'none' );
			}
		}

		this.setUIWidgetFieldsToCurrentEditRecord();

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}
				//JS Exception: "this.unique_columns.indexOf is not a function"
				if ( this.unique_columns && this.unique_columns.length > 0 && this.unique_columns.indexOf( key ) != -1 ) {
					$this.edit_view_ui_dic[key].css( 'opacity', '0' );
					if ( $this.edit_view_ui_dic[key].setEnabled ) {
						$this.edit_view_ui_dic[key].setEnabled( false );
					}
					if ( $this.edit_view_ui_dic[key].setMassEditMode ) {
						$this.edit_view_ui_dic[key].setMassEditMode( false );
					}
				} else {
					var widget = this.edit_view_ui_dic[key];
					if ( Global.isSet( widget.setMassEditMode ) ) {
						widget.setMassEditMode( true );
					}
					$this.edit_view_ui_dic[key].css( 'opacity', '1' );
				}
			}
		} else {
			for ( var key in this.edit_view_ui_dic ) {
				$this.edit_view_ui_dic[key].css( 'opacity', '1' );
			}
		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( { related_view_controller: this } );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	},

	setUIWidgetFieldsToCurrentEditRecord: function() {

		var $this = this;
		$this.current_edit_record === true && ($this.current_edit_record = {});
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
//			//Set all UI field to current edit record, we need validate all UI field when save and validate
			//use != to ingore string or number, value from html is string.
			//Error: TypeError: $this.current_edit_record is undefined in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-122453 line 2702
			if ( $this.current_edit_record && !Global.isSet( $this.current_edit_record[key] ) ) {
				$this.current_edit_record[key] = false;
			}

		}
	},

	/**
	 * Set default data into current_edit_record
	 *
	 * @param columnsArr
	 * @param force
	 *
	 * if force is true set the current_edit_record and populate edit_view_ui_dic
	 * this is used in view controllers (RequestViewController::setRequestFormDefaultData) where the api call for default values is late
	 *
	 */
	setDefaultData: function( columnsArr, force ) {
		var $this = this;
		$.each( columnsArr, function( field, value ) {
			if ( force != true && Global.isSet( $this.current_edit_record[field] ) ) {
				//do nothing
			} else {
				if ( force == true ) {
					if ( $this.edit_view_ui_dic[field] ) {
						$this.edit_view_ui_dic[field].setValue( value );
						$this.current_edit_record[field] = value;
					}
				} else {
					$this.current_edit_record[field] = value;
				}
			}
		} );
	},

	collectUIDataToCurrentEditRecord: function() {
		if ( this.is_mass_editing ) {
			return;
		}
		var $this = this;
		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];

			//only check dropdownlist
			if ( !widget.hasClass( 't-select' ) ) {
				continue;
			}

			var value = widget.getValue();

//			//Set all UI field to current edit record, we need validate all UI field when save and validate
			//use != to ingore string or number, value from html is string.
			//is visible make sure the widget is shown on screen of current select type

			//Error: TypeError: undefined is not an object (evaluating '$this.current_edit_record[key]') in /interface/html5/views/BaseViewController.js?v=8.0.0-20141230-124906 line 2792
			if ( value && $this.current_edit_record && $this.current_edit_record[key] != value ) {

				if ( !value || value === '0' || (Global.isArray( value ) && value.length === 0) ) {
					$this.current_edit_record[key] = false;
				} else {
					$this.current_edit_record[key] = value;
				}

			}

		}
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country': //popular case
						this.setCountryValue( widget, key );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setCountryValue: function( widget, key ) {
		if ( !this.current_edit_record['province'] ) {
			this.eSetProvince( this.current_edit_record[key], true );
		} else {
			this.eSetProvince( this.current_edit_record[key] );
		}
		widget.setValue( this.current_edit_record[key] );
	},

	putInputToInsideFormItem: function( form_item_input, label ) {
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM ) );
//		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
//
//		form_item_label_div.attr( 'class', 'edit-view-form-item-sub-label-div' );

		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		form_item.addClass( 'remove-margin' );

		form_item_label.text( $.i18n._( label ) + ': ' );

		form_item_input_div.append( form_item_input );

		return form_item;
	},

	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone: function() {
		// Remove this on 14.9.14 because adding tab url support, ned set url when tab index change and
		// need know waht's current doing action. See if this cause any problem
		//LocalCacheData.current_doing_context_action = '';
		this.setTabOVisibility( true );
		TTPromise.resolve( 'init', 'init' );

		$('.edit-view-tab-bar').css('opacity', 1);
	},

	setNavigationArrowsStatus: function() {
		if ( !this.edit_view ) {
			return;
		}

		var left_arrow = this.edit_view.find( '.left-click' );
		var right_arrow = this.edit_view.find( '.right-click' );
		var $this = this;

		left_arrow.unbind( 'click' ).click( function() {

			if ( !left_arrow.hasClass( 'disabled' ) ) {
				$this.onLeftArrowClick();
			}

		} );

		right_arrow.unbind( 'click' ).click( function() {

			if ( !right_arrow.hasClass( 'disabled' ) ) {
				$this.onRightArrowClick();
			}

		} );

		this.setNavigationArrowsEnabled();

	},

	setNavigationArrowsEnabled: function() {
		if ( !this.edit_view ) {
			return;
		}

		var left_arrow = this.edit_view.find( '.left-click' );
		var right_arrow = this.edit_view.find( '.right-click' );

		left_arrow.removeClass( 'disabled' );
		right_arrow.removeClass( 'disabled' );

		//TypeError: this.navigation.getSelectIndex is not a function
		//navigation could not be initial in cases, for example in Request new view
		if ( !this.navigation || !(this.navigation.hasOwnProperty( 'getSelectIndex' )) ) {
			return;
		}

		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();

		if ( !source_data ) {
			//No records in navigation box, so make sure arrows are disbled.
			left_arrow.addClass( 'disabled' );
			right_arrow.addClass( 'disabled' );
			return;
		}

		var current_pager_data = this.navigation.getPagerData();

		// It's possible the navigation don't have a pager data, like Timesheet edit view, so it's become a no page navigation.
		if ( !current_pager_data ) {
			if ( selected_index === 0 ) {
				left_arrow.addClass( 'disabled' );
			}

			if ( selected_index === source_data.length - 1 ) {
				right_arrow.addClass( 'disabled' );
			}
		} else {
			if ( selected_index === 0 && current_pager_data.current_page === 1 ) {
				left_arrow.addClass( 'disabled' );
			}

			if ( selected_index === source_data.length - 1 && current_pager_data.current_page === current_pager_data.last_page_number ) {
				right_arrow.addClass( 'disabled' );
			}
		}

	},

	onLeftArrowClick: function( cancel_callback ) {
		var $this = this;

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					$this.is_changed = false;
					doLeftArrowClick();
				}
				ProgressBar.closeOverlay();
			} );
		} else {
			doLeftArrowClick();
		}

		Global.triggerAnalyticsEditViewNavigation( 'left-arrow', this.viewId );

		function doLeftArrowClick() {
			var selected_index = $this.navigation.getSelectIndex();
			var source_data = $this.navigation.getSourceData();
			var current_pager_data = $this.navigation.getPagerData();
			var next_select_item;
			if ( selected_index > 0 ) {
				next_select_item = $this.navigation.getItemByIndex( selected_index - 1 );
				$this.onRightOrLeftArrowClickCallBack( next_select_item );
			} else if ( selected_index === 0 && current_pager_data && current_pager_data.current_page > 1 ) {
				$this.navigation.onADropDownSearch( 'unselect_grid', current_pager_data.current_page - 1, 'last', function( result ) {
					next_select_item = result;
					$this.onRightOrLeftArrowClickCallBack( next_select_item );
				} );
			} else {
				$this.onCancelClick( null, null, cancel_callback );
				return;
			}
		}
	},

	refreshCurrentRecord: function() {
		var next_select_item = this.navigation.getItemByIndex( this.navigation.getSelectIndex() );
		ProgressBar.showOverlay();
		if ( this.is_viewing ) {
			this.onViewClick( next_select_item.id ); //Dont refresh UI
		} else {
			this.onEditClick( next_select_item.id ); //Dont refresh UI
		}

		this.setNavigationArrowsEnabled();
	},

	//exists for RecurringScheduleControlView due to the unique way we handle the ids there.
	getRightArrowClickSelectedIndex: function( selected_index ) {
		return selected_index;
	},

	onRightArrowClick: function( cancel_callback ) {
		var $this = this;
		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					$this.is_changed = false;
					doRightArrowClick();
				}
				ProgressBar.closeOverlay();
			} );
		} else {
			doRightArrowClick();
		}

		Global.triggerAnalyticsEditViewNavigation( 'right-arrow', this.viewId );

		function doRightArrowClick() {
			var selected_index = $this.getRightArrowClickSelectedIndex( $this.navigation.getSelectIndex() );
			var source_data = $this.navigation.getSourceData();
			var current_pager_data = $this.navigation.getPagerData();
			var next_select_item;
			//Error: Uncaught TypeError: Cannot read property 'length' of null in /interface/html5/views/BaseViewController.js?v=8.0.0-20141230-125919 line 2956
			if ( !source_data ) {
				return;
			}

			if ( selected_index < (source_data.length - 1) ) {
				// next_select_item = $this.navigation.getItemByIndex( (selected_index + 1) );
				next_select_item = $this.navigation.getItemByIndex( $this.navigation.getSelectIndex() + 1 )
				$this.onRightOrLeftArrowClickCallBack( next_select_item );

				//Error: Unable to get property 'current_page' of undefined or null reference in interface/html5/views/BaseViewController.js?v=9.0.0-20151016-102254 line 3204
			} else if ( selected_index === (source_data.length - 1) && current_pager_data && current_pager_data.current_page < current_pager_data.last_page_number ) {
				$this.navigation.onADropDownSearch( 'unselect_grid', current_pager_data.current_page + 1, 'first', function( result ) {
					next_select_item = result;
					$this.onRightOrLeftArrowClickCallBack( next_select_item );
				} );
			} else {
				$this.onCancelClick( null, null, cancel_callback );
				return;
			}
		}

	},

	onRightOrLeftArrowClickCallBack: function( next_select_item ) {
		ProgressBar.showOverlay();
		if ( this.is_viewing ) {
			this.onViewClick( next_select_item.id ); //Dont refresh UI
		} else {
			this.onEditClick( next_select_item.id ); //Dont refresh UI
		}
		this.setNavigationArrowsEnabled();
		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.search();
		}

	},

	setParentContextMenuAfterSubViewClose: function() {
		//Error: Uncaught TypeError: Cannot read property 'buildContextMenu' of null in /interface/html5/views/BaseViewController.js?v=7.4.6-20141027-085016 line 2887
		if ( !this.parent_view_controller ) {
			return;
		}

		this.parent_view_controller.buildContextMenu();

		if ( this.parent_view_controller.edit_view ) {
			this.parent_view_controller.setEditMenu();
		} else {
			this.parent_view_controller.setDefaultMenu();
		}
	},


	//This should only be used on its own if removeEditView is causing flashing.
	//This function should only really be called from onViewClick (see RequestViewCommonController.js)
	clearEditView: function() {
		if ( this.edit_view ) {
			this.clearErrorTips();
			this.edit_view.remove();
		}
		this.edit_view = null;
		this.edit_view_tab = null;
	},

	removeEditView: function() {
		this.clearEditView();
		this.setCurrentEditViewState( '' );
		this.is_changed = false;
		this.confirm_on_exit = false;
		this.mass_edit_record_ids = [];

		if ( this.edit_only_mode ) {
			var current_url = window.location.href;
			if ( current_url.indexOf( '&sm' ) > 0 ) {
				current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
				Global.setURLToBrowser( current_url );
			}

			LocalCacheData.current_open_edit_only_controller = null;
		}

		// reset parent context menu if edit only mode
		if ( !this.edit_only_mode ) {
			this.setDefaultMenu();
		} else {
			this.setParentContextMenuAfterSubViewClose();
		}
		this.reSetURL();
		//If there is a action in url, add it back. So we have correct url when set tabs urls
		//This caused a bug where whenever saving a punch on Attendance ->TimeSheet, it would re-open the edit view, same with navigating between weeks, or even deleting punches in some cases.
		//This need to put under reSetUrl and need clean url_agrs until it set from onViewChange in router again
		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.a ) {
			LocalCacheData.current_doing_context_action = LocalCacheData.all_url_args.a;
		}

		this.sub_log_view_controller = null;
		this.edit_view_ui_dic = {};
		this.edit_view_ui_validation_field_dic = {};
		this.edit_view_form_item_dic = {};
		this.edit_view_error_ui_dic = {};
		this.current_edit_record = null;

		if ( this.sub_document_view_controller ) {
			this.sub_document_view_controller = null;
		}
	},

	reSetURL: function() {
		if ( this.canSetURL() ) {
			var args = '#!m=' + this.viewId;
			Global.setURLToBrowser( Global.getBaseURL() + args );
			LocalCacheData.all_url_args = IndexViewController.instance.router.buildArgDic( args.split( '&' ) );
		}
	},

	getGridSelectIdArray: function() {
		if ( !this.grid ) {
			return []; //Return empty array so .length on the result doesn't fail with Cannot read property 'length' of undefined
		}

		return this.grid.getSelectedRows();
	},

	setDefaultMenuAddIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.viewOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuMassEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuCopyIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.copyPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDeleteAndNextIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndNextIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndCopyIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndContinueIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndAddIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuCopyAsNewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.copyAsNewPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuLoginIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.validate( 'company', 'login_other_user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.getGridSelectIdArray().length !== 1 ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuCancelIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.sub_view_mode ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuExportIcon: function( context_btn, grid_selected_length, pId ) {
		if ( grid_selected_length == 0 ||this.is_add ||this.is_viewing || this.is_mass_adding || this.is_edit || this. grid == undefined ) {
			context_btn.addClass('disable-image');
		}
	},

	setDefaultMenuImportIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setDefaultMenuPermissionWizardIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuPermissionWizardIcon: function( context_btn, pId ) {

	},

	setEditMenuImportIcon: function( context_btn, pId ) {
		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setEditMenuAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		//if ( this.is_changed ) {
		context_btn.addClass( 'disable-image' );
		//}
	},

	setEditMenuEditIcon: function( context_btn, pId ) {

		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		//if ( !this.is_viewing || !this.editOwnerOrChildPermissionValidate( pId ) ) {
		context_btn.addClass( 'disable-image' );
		//}
	},

	setEditMenuNavEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setEditMenuNavViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setEditMenuMassEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setEditMenuDeleteIcon: function( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCopyIcon: function( context_btn, pId ) {
		if ( !this.copyPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCopyAndAddIcon: function( context_btn, pId ) {
		if ( !this.copyAsNewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveIcon: function( context_btn, pId ) {

		this.saveValidate( context_btn, pId );

		if ( this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_adding || this.is_mass_editing || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndCopyIcon: function( context_btn, pId ) {
		this.saveAndCopyValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndNextIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndAddIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn, pId );

		if ( this.is_viewing || this.is_mass_editing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCancelIcon: function( context_btn, pId ) {

	},

	ifContextButtonExist: function( value ) {
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			if ( id === value && !context_btn.hasClass( 'invisible-image' ) ) {
				return true;
			}
		}
		return false;
	},

	//Call this when select grid row
	//Call this when setLayout
	setDefaultMenu: function( doNotSetFocus ) {
		//Check if there is a current_company object at all.
		if ( LocalCacheData.isLocalCacheExists( 'current_company' ) == false ) {
			return false;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Client line 308
		if ( !this.context_menu_array ) {
			return;
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.login:
					this.setDefaultMenuLoginIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.permission_wizard:
					this.setDefaultMenuPermissionWizardIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.map:
					this.setDefaultMenuMapIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenu: function() {
		//this.selectContextMenu(); //This is done in setContextMenuGroupVisibility() at the bottom of this function instead, which makes more sense since thats after the permission checks are done.
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );
			context_btn.removeClass( 'invisible-image' );

			if ( this.is_mass_editing ) {
				switch ( id ) {
					case ContextMenuIconName.save:
						this.setEditMenuSaveIcon( context_btn );
						break;
					case ContextMenuIconName.cancel:
						break;
					default:
						context_btn.addClass( 'disable-image' );
						break;
				}

				continue;
			}

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.import_icon:
					this.setEditMenuImportIcon( context_btn );
					break;
				case ContextMenuIconName.permission_wizard:
					this.setEditMenuPermissionWizardIcon( context_btn );
					break;
				case ContextMenuIconName.login:
					this.setEditMenuLoginIcon( context_btn );
					break;
				case ContextMenuIconName.map:
					this.setEditMenuMapIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();
	},

	setDefaultMenuMapIcon: function( context_btn ) {
		if ( Global.getProductEdition() <= 10 ) {
			context_btn.addClass( 'invisible-image' );
		}

		var show = false;
		if ( this.grid ) {
			var selected_items = this.getSelectedItems();
			Debug.Arr( selected_items, 'selected items', 'BaseViewController.js', 'BaseViewController', 'setDefaultMenuMapIcon', 10 );
			if ( selected_items.length > 0 ) {
				for ( var x = 0; x < selected_items.length; x++ ) {
					if ( selected_items[x] && selected_items[x].latitude && selected_items[x].longitude ) {
						show = true;
						break;
					}
				}
			}
		}

		if ( show ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuMapIcon: function( context_btn ) {
		this.setDefaultMenuMapIcon( context_btn );
	},

	setEditMenuLoginIcon: function( context_btn ) {
		context_btn.addClass( 'disable-image' );
	},

	//Disable context menu if no visible item
	setContextMenuGroupVisibility: function() {

		var ribbon_menu = Global.topContainer().find( '#' + this.viewId + 'ContextMenu' );
		var menus = ribbon_menu.find( '.menu' );

		var len = menus.length;

		for ( var i = 0; i < len; i++ ) {
			var menu = $( menus[i] );
			var li_array = menu.find( 'li' );

			var all_invisible = true;

			var li_len = li_array.length;

			for ( var j = 0; j < li_len; j++ ) {
				var li = $( li_array[j] );
				if ( !li.hasClass( 'invisible-image' ) ) {
					all_invisible = false;
					break;
				}
			}

			if ( all_invisible ) {
				menu.addClass( 'invisible-image' );
			} else {
				menu.removeClass( 'invisible-image' );
			}

		}

		//go context menu after everything set done
		if ( this.need_switch_to_context_menu ) {
			this.selectContextMenu();
		}

		//set right click menu to list view grid
		this.initRightClickMenu();

	},

	setErrorMenu: function() {
		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.cancel:
					break;
				default:
					context_btn.addClass( 'disable-image' );
					break;
			}

		}
	},

	render: function() {
		var $this = this;

		$( window ).resize( function() {
			if ( $this.edit_view ) {
				$this.setEditViewTabSize();
			}
		} );

		//Create search panel only when show as a main view

		if ( !this.sub_view_mode && !this.edit_only_mode && !this.tree_mode ) {
			var searchPanelWidget = Global.loadWidget( 'global/widgets/search_panel/SearchPanel.html' );
			var search_panel_w = $( searchPanelWidget );

			$( this.el ).prepend( search_panel_w );

			if ( !this.show_search_tab ) {
				search_panel_w.hide();
			}

			this.search_panel = search_panel_w.SearchPanel( { viewController: this } );

			this.search_panel.on( 'searchTabSelect', function() {
				$this.onSearchTabSelect;
			} );

			this.buildSearchFields();

			this.buildBasicSearchUI();

			this.buildAdvancedSearchUI();

			this.buildSearchAndLayoutUI();

			//Work around that the li offset is empty in chrome
			setTimeout( function() {
				$this.setCurrentViewPosition();

			}, 500 );

		}

	},

	setCurrentViewPosition: function() {
		var current_view_div = this.search_panel.find( '.layout-selector-div' );
		var saved_layout_li = this.search_panel.find( 'a[ref=\'saved_layout\']' ).parent();
		// Error: Unable to get property 'left' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=8.0.6-20150417-083849 line 3691
		if ( !current_view_div || !saved_layout_li || !saved_layout_li.offset() ) {
			return;
		}
		current_view_div.css( 'left', saved_layout_li.offset().left + saved_layout_li.width() + 20 );
	},

	//Build fields when search tab change
	onSearchTabSelect: function( e, e1, ui ) {
		var tab_id = $( ui ).prop( 'id' );

		switch ( tab_id ) {
			case 'basic_search':

				if ( this.search_panel.getLastSelectTabId() !== 'saved_layout' ) {
					this.getSearchPanelFilter( 1, true );
					this.buildBasicSearchUI();
					this.setSearchPanelFilter( false, 0 );
				}

				break;
			case 'adv_search':
				if ( this.search_panel.getLastSelectTabId() !== 'saved_layout' ) {
					this.getSearchPanelFilter( 0, true );
					this.buildAdvancedSearchUI();
					this.setSearchPanelFilter( false, 1 );
				}

				break;
			case 'saved_layout':
				this.getSearchPanelFilter( this.search_panel.getLastSelectTabIndex() );
		}

	},

	initDropDownOptions: function( options, callBack ) {
		var len = options.length;
		var complete_count = 0;
		var option_result = [];

		for ( var i = 0; i < len; i++ ) {
			var option_info = options[i];

			this.initDropDownOption( option_info.option_name, option_info.field_name, option_info.api, onGetOptionResult );

		}

		function onGetOptionResult( result ) {

			option_result.push( result );

			complete_count = complete_count + 1;

			if ( complete_count === len ) {

				callBack( option_result );
			}
		}

	},

	buildWidgetContainerWithTextTip: function( widget, tip ) {
		var h_box = $( '<div class=\'h-box\'></div>' );

		var text_box = Global.loadWidgetByName( FormItemType.TEXT );
		text_box.css( 'margin-left', '10px' );
		text_box.TText();
		text_box.setValue( tip );

		h_box.append( widget );
		h_box.append( text_box );

		return h_box;

	},

	//Set option list for search panel and edit view
	initDropDownOption: function( option_name, field_name, api, callBack, array_name ) {
		var $this = this;
		if ( !Global.isSet( api ) ) {
			api = this.api;
		}

		if ( !Global.isSet( field_name ) || !field_name ) {
			field_name = option_name + '_id';
		}
		api.getOptions( option_name, {
			onResult: function( res ) {
				var result = res.getResult();

				if ( array_name ) {
					$this[array_name] = Global.buildRecordArray( result );
				} else {

					$this[option_name + '_array'] = Global.buildRecordArray( result );
				}

				if ( !$this.sub_view_mode ) {

					if ( Global.isSet( $this.basic_search_field_ui_dic[field_name] ) ) {
						$this.basic_search_field_ui_dic[field_name].setSourceData( Global.buildRecordArray( result ) );
					}

					if ( Global.isSet( $this.adv_search_field_ui_dic[field_name] ) ) {
						$this.adv_search_field_ui_dic[field_name].setSourceData( Global.buildRecordArray( result ) );
					}
				}
				if ( Global.isSet( callBack ) ) {
					callBack( res );
				}

			}
		} );
	},

	clearSearchPanel: function() {

		for ( var key in this.basic_search_field_ui_dic ) {
			var search_input = this.basic_search_field_ui_dic[key];
			search_input.setValue( null );
		}

		for ( key in this.adv_search_field_ui_dic ) {
			search_input = this.adv_search_field_ui_dic[key];
			search_input.setValue( null );
		}

	},

	onSearch: function() {
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();

		var do_update = false;

		//don't keep temp filter any more, set them when change tab
		this.temp_adv_filter_data = null;
		this.temp_basic_filter_data = null;
		this.getSearchPanelFilter();
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains(\'' + BaseViewController.default_layout_name + '\')' ).attr( 'value' );

			if ( !default_layout_id ) {
				this.onSaveNewLayout( BaseViewController.default_layout_name );
				return;
			}
			var layout_name = BaseViewController.default_layout_name;

		} else if ( this.show_search_tab ) {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		} else if ( !this.show_search_tab ) {
			this.search();
			this.setGridHeaderStyle();
			return;
		}

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.filter_sort = sort_filter;

		ProgressBar.showOverlay();
		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.clearAwesomeboxLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}
			}
		} );

	},

	onClearSearch: function() {
		var do_update = false;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains(\'' + BaseViewController.default_layout_name + '\')' ).attr( 'value' );

			if ( !default_layout_id ) {
				this.clearSearchPanel();
				this.filter_data = null;
				this.temp_adv_filter_data = null;
				this.temp_basic_filter_data = null;
				this.column_selector.setSelectGridData( this.default_display_columns );
				this.sort_by_selector.setValue( null );

				this.onSaveNewLayout( BaseViewController.default_layout_name );
				return;
			}

			var layout_name = BaseViewController.default_layout_name;
			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			do_update = true;

		} else {

			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			this.column_selector.setSelectGridData( this.default_display_columns );
			this.sort_by_selector.setValue( null );

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

//		this.column_selector.setSelectGridData( this.default_display_columns );

		this.sort_by_selector.setValue( null );

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		if ( do_update ) {
			var args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.display_columns = selected_display_columns;
			args.data.filter_data = filter_data;
			args.data.filter_sort = sort_filter;

		}

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onSaveNewLayout: function( default_layout_name ) {

		if ( Global.isSet( default_layout_name ) ) {
			var layout_name = default_layout_name;
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.filter_sort = sort_filter;

		var $this = this;

		var a_layout_name = ALayoutCache.layout_dic[this.script_name];
		if ( a_layout_name && ALayoutCache.layout_dic[a_layout_name] ) {
			ALayoutCache.layout_dic[a_layout_name] = null;
		}

		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();

				} else {
					TAlertManager.showErrorAlert( res );
				}

			}
		} );

	},

	onUpdateLayout: function() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.filter_sort = sort_filter;

		var $this = this;

		var a_layout_name = ALayoutCache.layout_dic[this.script_name];
		if ( a_layout_name && ALayoutCache.layout_dic[a_layout_name] ) {
			ALayoutCache.layout_dic[a_layout_name] = null;
		}

		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	clearViewLayoutCache: function() {
		if ( LocalCacheData.view_layout_cache && LocalCacheData.view_layout_cache[this.script_name] ) {
			LocalCacheData.view_layout_cache[this.script_name] = null;
		}
	},

	clearAwesomeboxLayoutCache: function() {
		// Removed saved view layout for awesomebox if it existed.
		if ( ALayoutCache.layout_dic && ALayoutCache.layout_dic[this.script_name] ) {
			ALayoutCache.layout_dic[ALayoutCache.layout_dic[this.script_name]] = null;
		}
	},

	onDeleteLayout: function() {
		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );

		var $this = this;
		this.user_generic_data_api.deleteUserGenericData( selectId, {
			onResult: function( res ) {
				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = $this.select_layout.name;
					$this.initLayout();
				}

			}
		} );
	},

	buildSearchFields: function() {
		//Override in all subview
	},

	buildBasicSearchUI: function() {
		if ( !this.search_fields ) {
			return;
		}

		var basic_search_div = this.search_panel.find( 'div #basic_search_content_div' );

		var len = this.search_fields.length;
		var $this = this;

		var column1 = basic_search_div.find( '.first-column' );
		var column2 = basic_search_div.find( '.second-column' );
		var column3 = basic_search_div.find( '.third-column' );

		var already_created_ui = false;
		$.each( this.search_fields, function( index, search_field ) {
			if ( Global.isSet( $this.basic_search_field_ui_dic[search_field.get( 'field' )] ) ) {
				already_created_ui = true;
				return false;
			}

			if ( !search_field.get( 'basic_search' ) ) {
				return true;
			}

			var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
			var form_item_label = form_item.find( '.form-item-label' );
			var form_item_input_div = form_item.find( '.form-item-input-div' );
			var form_item_input = $this.getFormItemInput( search_field );
			form_item_label.text( search_field.get( 'label' ) + ': ' );
			form_item_input_div.append( form_item_input );

			switch ( search_field.get( 'in_column' ) ) {
				case 1:
					column1.append( form_item );
					column1.append( '<div class=\'clear-both-div\'></div>' );
					break;
				case 2:
					column2.append( form_item );
					column2.append( '<div class=\'clear-both-div\'></div>' );
					break;
				case 3:
					column3.append( form_item );
					column3.append( '<div class=\'clear-both-div\'></div>' );
					break;
			}

			$this.basic_search_field_ui_dic[search_field.get( 'field' )] = form_item_input;
		} );

		if ( !already_created_ui ) {
			this.onBuildBasicUIFinished();
		}

	},

	buildAdvancedSearchUI: function() {
		if ( !this.search_fields ) {
			return;
		}

		var advSearchDiv = this.search_panel.find( 'div #adv_search_content_div' );

		var $this = this;

		var column1 = advSearchDiv.find( '.first-column' );
		var column2 = advSearchDiv.find( '.second-column' );
		var column3 = advSearchDiv.find( '.third-column' );

		var already_created_ui = false;
		var no_adv_ui = true;

		$.each( this.search_fields, function( index, search_field ) {

			if ( Global.isSet( $this.adv_search_field_ui_dic[search_field.get( 'field' )] ) ) {
				already_created_ui = true;
				no_adv_ui = false;
				return false;
			}

			if ( !search_field.get( 'adv_search' ) ) {
				return true;
			}

			var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
			var form_item_label = form_item.find( '.form-item-label' );
			var form_item_input_div = form_item.find( '.form-item-input-div' );
			var form_item_input = $this.getFormItemInput( search_field );
			form_item_label.text( search_field.get( 'label' ) + ': ' );
			form_item_input_div.append( form_item_input );

			switch ( search_field.get( 'in_column' ) ) {
				case 1:
					column1.append( form_item );
					column1.append( '<div class=\'clear-both-div\'></div>' );
					break;
				case 2:
					column2.append( form_item );
					column2.append( '<div class=\'clear-both-div\'></div>' );
					break;
				case 3:
					column3.append( form_item );
					column3.append( '<div class=\'clear-both-div\'></div>' );
					break;
			}

			$this.adv_search_field_ui_dic[search_field.get( 'field' )] = form_item_input;
			no_adv_ui = false;
		} );

		if ( no_adv_ui ) {

			this.search_panel.hideAdvSearchPanel();
		}

		if ( !already_created_ui ) {
			this.onBuildAdvUIFinished();
		}

	},

	onSetSearchFilterFinished: function() {

	},

	onBuildAdvUIFinished: function() {
		//Always override in sub class
	},

	onBuildBasicUIFinished: function() {
		//Always override in sub class
	},

	getFormItemInput: function( search_field ) {
		var input;
		var form_type = search_field.get( 'form_item_type' );

		switch ( form_type ) {
			case FormItemType.AWESOME_BOX:
				input = Global.loadWidget( 'global/widgets/awesomebox/AComboBox.html' );
				input = $( input );
				var show_search = false;
				var key;

				if ( search_field.get( 'layout_name' ) !== ALayoutIDs.OPTION_COLUMN && search_field.get( 'layout_name' ) !== ALayoutIDs.TREE_COLUMN ) {
					show_search = true;
					key = 'id';
				} else {

					if ( search_field.get( 'layout_name' ) === ALayoutIDs.TREE_COLUMN ) {
						key = 'id';
					} else {
						key = 'value';
					}

				}

				input.AComboBox( {
					api_class: search_field.get( 'api_class' ),
					allow_multiple_selection: search_field.get( 'multiple' ),
					layout_name: search_field.get( 'layout_name' ),
					tree_mode: search_field.get( 'tree_mode' ),
					default_args: search_field.get( 'default_args' ),
					show_search_inputs: show_search,
					set_any: search_field.get( 'set_any' ),
					addition_source_function: search_field.get( 'addition_source_function' ),
					script_name: search_field.get( 'script_name' ),
					custom_first_label: search_field.get( 'custom_first_label' ),
					key: key,
					search_panel_model: true,
					field: search_field.get( 'field' )
				} );

				if ( search_field.get( 'customSearchFilter' ) ) {
					input.customSearchFilter = search_field.get( 'customSearchFilter' );
				}

				break;
			case FormItemType.TEXT_INPUT:
				input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
				input = $( input );
				input.TTextInput( {
					field: search_field.get( 'field' )
				} );
				break;
			case FormItemType.PASSWORD_INPUT:
				input = Global.loadWidget( 'global/widgets/text_input/TPasswordInput.html' );
				input = $( input );
				input.TTextInput( {
					field: search_field.get( 'field' )
				} );
				break;
			case FormItemType.COMBO_BOX:
				input = Global.loadWidget( 'global/widgets/combobox/TComboBox.html' );
				input = $( input );
				input.TComboBox( {
					field: search_field.get( 'field' ),
					set_any: true
				} );
				break;
			case FormItemType.TAG_INPUT:
				input = Global.loadWidget( 'global/widgets/tag_input/TTagInput.html' );
				input = $( input );
				input.TTagInput( {
					field: search_field.get( 'field' ),
					object_type_id: search_field.get( 'object_type_id' )
				} );

				break;
			case FormItemType.DATE_PICKER:
				input = Global.loadWidgetByName( form_type );
				input = $( input );
				input.TDatePicker( {
					field: search_field.get( 'field' )
				} );

				break;
			case FormItemType.CHECKBOX:
				input = Global.loadWidget( 'global/widgets/checkbox/TCheckbox.html' );
				input = $( input );
				input.TCheckbox( {
					field: search_field.get( 'field' )
				} );

				break;
		}

		return input;
	},

	buildSearchAndLayoutUI: function() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		//Display Columns

		var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		var column_selector = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );

		this.column_selector = $( column_selector );

		this.column_selector = this.column_selector.ADropDown( {
			display_show_all: false,
			id: this.ui_id + '_column_selector',
			key: 'value',
			allow_drag_to_order: true,
			display_close_btn: false,
			display_column_settings: false,
			max_height: 150
		} );
		this.column_selector.on( 'formItemChange', function() {
			$this.layout_changed = true;
		} );

		form_item_label.text( $.i18n._( 'Display Columns' ) + ':' );
		form_item_label.addClass( 'SearchPanel-displayColumns-label' );
		form_item_input_div.append( this.column_selector );

		layout_div.append( form_item );

		layout_div.append( '<div class=\'clear-both-div\'></div>' );

		this.column_selector.setColumns( [
			{ name: 'label', index: 'label', label: $.i18n._( 'Column Name' ), width: 100, sortable: false }
		] );

		//Sort By
		form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		form_item_label = form_item.find( '.form-item-label' );
		form_item_input_div = form_item.find( '.form-item-input-div' );
		this.sort_by_selector = $( Global.loadWidget( 'global/widgets/awesomebox/AComboBox.html' ) );
		this.sort_by_selector = this.sort_by_selector.AComboBox( {
			allow_drag_to_order: true,
			allow_multiple_selection: true,
			set_empty: true,
			layout_name: ALayoutIDs.SORT_COLUMN
		} );

		form_item_label.text( $.i18n._( 'Sort By' ) + ':' );
		form_item_input_div.append( this.sort_by_selector );

		layout_div.append( form_item );

		layout_div.append( '<div class=\'clear-both-div\'></div>' );

		//Save and update layout

		form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		form_item_label = form_item.find( '.form-item-label' );
		form_item_input_div = form_item.find( '.form-item-input-div' );

		form_item_label.text( $.i18n._( 'Save Search As' ) + ':' );

		this.save_search_as_input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
		this.save_search_as_input = $( this.save_search_as_input );
		this.save_search_as_input.TTextInput();

		var save_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Save' ) + '\' />' );

		form_item_input_div.append( this.save_search_as_input );
		form_item_input_div.append( save_btn );

		var $this = this;
		save_btn.click( function() {
			$this.saving_layout_in_layout_tab = true;
			$this.onSaveNewLayout();
			$this.search();
		} );

		//Previous Saved Layout

		this.previous_saved_layout_div = $( '<div class=\'previous-saved-layout-div\'></div>' );

		form_item_input_div.append( this.previous_saved_layout_div );

		form_item_label = $( '<span style=\'margin-left: 5px\' >' + $.i18n._( 'Previous Saved Searches' ) + ':</span>' );
		this.previous_saved_layout_div.append( form_item_label );

		this.previous_saved_layout_selector = $( '<select style=\'margin-left: 5px\' class=\'t-select\'>' );
		var update_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Update' ) + '\' />' );
		var del_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Delete' ) + '\' />' );

		update_btn.click( function() {
			$this.onUpdateLayout();
			$this.onSearch();
		} );

		del_btn.click( function() {
			$this.onDeleteLayout();
			$this.onSearch();
		} );

		this.previous_saved_layout_div.append( this.previous_saved_layout_selector );
		this.previous_saved_layout_div.append( update_btn );
		this.previous_saved_layout_div.append( del_btn );

		layout_div.append( form_item );

		this.previous_saved_layout_div.css( 'display', 'none' );

	},

	onGridSelectRow: function() {
		$( '#ribbon_view_container .context-menu:visible a' ).click();
		this.setDefaultMenu();
	},

	setPreviousSavedSearchSourcesAndValue: function( layouts_array ) {
		var $this = this;

		if ( this.previous_saved_layout_selector ) {
			this.previous_saved_layout_selector.empty();

			if ( layouts_array && layouts_array.length > 0 ) {
				this.previous_saved_layout_div.css( 'display', 'inline' );

				var len = layouts_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = layouts_array[i];
					this.previous_saved_layout_selector.append( '<option value="' + item.id + '">' + item.name + '</option>' );
				}

				$( this.previous_saved_layout_selector.find( 'option' ) ).filter( function () {
					return parseInt( $( this ).attr( 'value' ) ) === $this.select_layout.id;
				} ).prop( 'selected', true ).attr( 'selected', true );

			} else {
				this.previous_saved_layout_div.css( 'display', 'none' );
			}
		}
	},

	setSelectLayout: function( exclude_column ) {
		var $this = this;
		var grid;

		var grid_id = 'grid';
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid_id = this.ui_id + '_grid';
		}

		var column_info_array = [];

		if ( !this.select_layout ) { //Set to default layout if no layout at all
			this.select_layout = { id: '' };
			this.select_layout.data = { filter_data: {}, filter_sort: {} };
			this.select_layout.data.display_columns = this.default_display_columns;
		}

		var layout_data = this.select_layout.data;

		if ( !layout_data.display_columns || layout_data.display_columns.length == 0 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		if ( !this.sub_view_mode && this.search_panel ) {

			//Set Display Column in layout panel
			//Error: TypeError: null is not an object (evaluating 'this.column_selector.setSelectGridData')
			if ( this.column_selector ) {
				this.column_selector.setSelectGridData( display_columns );
				//this.column_selector.setGridColumnsWidths(); //This is called in SearchPanel.setGridSize() on expand instead, as browsers seem to optimize out scrollbar calculations until the DOM element is visible.
			}

			//Set Sort by awesomebox in layout panel
			//Error: TypeError: null is not an object (evaluating 'this.sort_by_selector.setSourceData')
			if ( this.sort_by_selector ) {
				this.sort_by_selector.setSourceData( this.buildSortSelectorUnSelectColumns( display_columns ) );
				this.sort_by_selector.setValue( this.buildSortBySelectColumns() );
			}

			//Set Previoous Saved layout combobox in layout panel
			var layouts_array = this.search_panel.getLayoutsArray();

			this.setPreviousSavedSearchSourcesAndValue( layouts_array );

		}

		//Set Data Grid on List view
		var len = display_columns.length;

		for ( var i = 0; i < len; i++ ) {
			var view_column_data = display_columns[i];

			if ( $.inArray( view_column_data.value, exclude_column ) !== -1 ) {
				continue;
			}

			var column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );
		}

		var grid_needs_reload = false;
		if ( this.grid ) {
			grid_id = this.ui_id + '_grid';

			if ( this.layout_changed == true ) {
				$this.layout_changed = false;
				this.grid.grid.jqGrid( 'GridUnload' );
				this.grid = null;
			} else {
				//This is done in BaseViewControler->search() right before the data is set, which prevents "flashing".
				//  However some views override this function and need to be fixed manually.
				//this.grid.clearGridData();
			}
		}

		this.showGridBorders();

		if ( !this.grid ) {
			var grid_setup = this.getGridSetup();
			if ( this.sub_view_mode ) {
				grid_setup.height = 1;
			}
			this.grid = new TTGrid( grid_id, grid_setup, column_info_array );

			if ( this.sub_view_mode ) {
				this.grid.grid.hide();
			}

			this.setGridColumnsWidth(); //Helps makes changing layouts "flash" less, especially when going from only a few columns to many.
			this.setGridSize( this.ui_id, this.sub_view_mode, this.sub_view_grid_autosize, this.pager_data );
		}

		if ( this.grid && grid_needs_reload ) {
			this.grid.reloadGrid();
		}

		//Add widget on UI and bind events. Next set data in it in search result
		if ( LocalCacheData.paging_type === 0 ) {
			if ( this.paging_widget.parent().length > 0 ) {
				this.paging_widget.remove();
			}

			this.paging_widget.css( 'width', this.grid.grid.width() );
			this.grid.grid.append( this.paging_widget );

			this.paging_widget.click( $this.onPaging() );

		} else {
			$( this.el ).find( '.total-number-div' ).append( this.paging_widget );
			$( this.el ).find( '.bottom-div' ).append( this.paging_widget_2 );

			this.paging_widget.on( 'paging', function(e, action, page_number) {
				$this.onPaging2(e, action, page_number);
			} );
			this.paging_widget_2.bind( 'paging', function(e, action, page_number) {
				$this.onPaging2(e, action, page_number);
			} );
		}

		this.bindGridColumnEvents();

		this.setGridHeaderStyle(); //Set Sort Style
		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		if ( !this.sub_view_mode ) {
			this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.
		}
	},

	getGridSetup: function(){
		var $this = this;

		var container = this.grid_parent ? this.grid_parent : '.grid-div';
		if ( !this.grid_parent && this.sub_view_mode ) {
			if (  $( '#' + this.ui_id + '_grid' ).parents('.sub-view').length > 0 ) {
				container = '.sub-grid-view-div';
			} else {
				container = '.edit-view-tab-bar';
			}
		}

		return {
			container_selector: container,
			sub_grid_mode: this.sub_view_mode,
			onResizeGrid: true,
			onSelectRow: function() {
				$this.onGridSelectRow();
			},
			onCellSelect: function() {
				$this.onGridSelectRow();
			},
			onSelectAll: function() {
				$this.onGridSelectAll();
			},
			ondblClickRow: function( e ) {
				$this.onGridDblClickRow( e );
			},
			onRightClickRow: function( rowId ) {
				var id_array = $this.getGridSelectIdArray();
				if ( id_array.indexOf( rowId ) < 0 ) {
					$this.grid.grid.resetSelection();
					$this.grid.grid.setSelection( rowId );
					$this.onGridSelectRow();
				}
			},
			height: 1, //Start really small to reduce flashing, as height is changed with setGridSize() shortly after anyways.
		};
	},

	onGridSelectAll: function() {
		this.setDefaultMenu();
	},

	unSelectAll: function() {
		this.grid.grid.resetSelection();
	},

	onGridDblClickRow: function( e ) {
		this.grid.grid.resetSelection();
		this.grid.setSelection( e, false );
		this.setDefaultMenu( true );
		var len = this.context_menu_array.length;
		var need_break = false;
		for ( var i = 0; i < len; i++ ) {
			if ( need_break ) {
				break;
			}
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).prop( 'id' );
			switch ( id ) {
				case ContextMenuIconName.edit:
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onEditClick();
						return;
					}
					break;
			}
		}
		for ( i = 0; i < len; i++ ) {
			if ( need_break ) {
				break;
			}
			context_btn = $( this.context_menu_array[i] );
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).prop( 'id' );
			switch ( id ) {
				case ContextMenuIconName.view:
					need_break = true;
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onViewClick();
						return;
					}
					break;
			}
		}
		for ( i = 0; i < len; i++ ) {
			context_btn = $( this.context_menu_array[i] );
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).prop( 'id' );
			switch ( id ) {
				case ContextMenuIconName.add:
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onAddClick();
						return;
					}
					break;
			}
		}
	},

	onPaging: function() {
		this.search( true, 'next' );
	},

	onPaging2: function( e, action, page_number ) {
		this.search( true, action, page_number );
	},

	//Bind column click event to change sort type and save columns to t_grid_header_array to use to set column style (asc or desc)
	bindGridColumnEvents: function() {
		var display_columns = this.grid.getColumnModel();

		if ( !display_columns ) {
			return;
		}

		var len = display_columns.length;

		this.t_grid_header_array = [];

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			var column_header = $( $( this.el ).find( '#gbox_' + this.ui_id + '_grid' ).find( 'div #jqgh_' + this.ui_id + '_grid_' + column_info.name ) );

			this.t_grid_header_array.push( column_header.TGridHeader() );
			if ( this.search_panel ) {
				column_header.on( 'click', onColumnHeaderClick );
			}
		}

		var $this = this;

		function onColumnHeaderClick( e ) {
			var field = $( this ).attr( 'id' );
			field = field.substring( 10 + $this.ui_id.length + 1, field.length );

			if ( field === 'cb' ) { //first column, check box column.
				return;
			}

			e.preventDefault(); //can't be cancelled before cb is detected as we need the default event in that case.

			if ( !$this.sorting_rows ) {
				$this.sorting_rows = true;
				TTPromise.add( 'init', 'init' );
				TTPromise.wait( null, null, function() {
					$this.sorting_rows = false; //prevent doubling up events ( which loops forever )
				} );



				if ( e.metaKey || e.ctrlKey ) {
					$this.buildSortCondition( false, field );
				} else {
					$this.buildSortCondition( true, field );

				}

				if ( $this.sub_view_mode ) {
					$this.search();
					$this.setGridHeaderStyle();
				} else {
					if ( $this.sort_by_selector ) {
						$this.sort_by_selector.setValue( $this.buildSortBySelectColumns() );
					}
					$this.onSearch();
				}
			} else {
				Debug.Text( 'Skipping column sort call ', '', 'BaseViewController', 'onColumnHeaderClick', 10 );
			}

		}

	},

	getValidSearchFilter: function() {
		var validFilterData = {};
		for ( var key in  this.filter_data ) {
			// Error: Unable to get property 'value' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=8.0.6-20150417-143734 line 4727
			if ( Global.isSet( this.filter_data[key] ) && Global.isSet( this.filter_data[key].value ) && this.filter_data[key].value !== '' ) {
				validFilterData[key] = this.filter_data[key];
			}
		}

		return validFilterData;
	},

	getSearchPanelDisplayColumns: function() {
		var display_columns = [];

		var select_items = this.column_selector.getSelectItems();

		if ( select_items && select_items.length > 0 ) {
			$.each( select_items, function( index, content ) {
				display_columns.push( content.value );
			} );
		}

		if ( !display_columns || display_columns.length == 0 ) {
			display_columns = this.default_display_columns;
		}

		return display_columns;
	},

	getSearchPanelSortFilter: function() {
		var sort_filter = [];
		var select_items = this.sort_by_selector.getValue( true );

		if ( select_items && select_items.length > 0 ) {
			$.each( select_items, function( index, content ) {
				var sort = {};
				sort[content.value] = content.sort;
				sort_filter.push( sort );
			} );
		}

		return sort_filter;
	},

	getSearchPanelFilter: function( getFromTabIndex, save_temp_filter ) {
		if ( !this.search_panel ) {
			return;
		}
		if ( Global.isSet( getFromTabIndex ) ) {
			var search_tab_select_index = getFromTabIndex;
		} else {
			search_tab_select_index = this.search_panel.getSelectTabIndex();
		}

//		var basic_fields_len = this.search_fields.length;
		var target_ui_dic = null;

		if ( search_tab_select_index === 0 ) {
			this.filter_data = [];
			target_ui_dic = this.basic_search_field_ui_dic;
		} else if ( search_tab_select_index === 1 && this.search_panel.isAdvTabVisible() ) {
			this.filter_data = [];
			target_ui_dic = this.adv_search_field_ui_dic;
		} else {
			return;
		}

		var $this = this;
		$.each( target_ui_dic, function( key, content ) {
			$this.filter_data[key] = { field: key, id: '', value: target_ui_dic[key].getValue( true ) };

			if ( $this.temp_basic_filter_data ) {
				$this.temp_basic_filter_data[key] = $this.filter_data[key];
			}

			if ( $this.temp_adv_filter_data ) {
				$this.temp_adv_filter_data[key] = $this.filter_data[key];
			}
		} );

		if ( save_temp_filter ) {
			if ( search_tab_select_index === 0 ) {
				$this.temp_basic_filter_data = Global.clone( $this.filter_data );
			} else if ( search_tab_select_index === 1 ) {
				$this.temp_adv_filter_data = Global.clone( $this.filter_data );
			}

		}

	},

	//Set value to field UI in search tab
	setSearchPanelFilter: function( autoChangeTab, tab_index ) {

		this.clearSearchPanel();

		if ( !Global.isSet( autoChangeTab ) ) {
			autoChangeTab = false;
		}

		var filter = this.filter_data;

		if ( Global.isSet( tab_index ) ) {
			if ( tab_index === 0 && this.temp_basic_filter_data ) {
				filter = this.temp_basic_filter_data;
			} else if ( tab_index === 1 && this.temp_adv_filter_data ) {
				filter = this.temp_adv_filter_data;
			}
		}

		if ( !Global.isSet( filter ) || !this.search_fields ) {
			return;
		}

		var basic_fields_len = this.search_fields.length;

		for ( var i = 0; i < basic_fields_len; i++ ) {
			var field = this.search_fields[i];
			var field_name = field.get( 'field' );

			var search_input = this.basic_search_field_ui_dic[field_name];
			var search_input_1 = this.adv_search_field_ui_dic[field_name];

			if ( Global.isSet( filter[field_name] ) ) {

				if ( Global.isSet( search_input ) ) {

					if ( $.type( filter[field_name] ) === 'string' || $.type( filter[field_name] ) === 'number' ) {
						search_input.setValue( filter[field_name] );
					} else {

						if ( filter[field_name].hasOwnProperty( 'value' ) ) { // when set default filter don't have 'value' in it, For example Invoice edit view
							search_input.setValue( filter[field_name].value );
						} else {
							search_input.setValue( filter[field_name] );
						}

					}

				} else if ( autoChangeTab && !this.saving_layout_in_layout_tab ) {
					if ( this.search_panel.getSelectTabIndex() !== 1 ) {
						this.search_panel.setSelectTabIndex( 1, false );
					}
				}

				if ( Global.isSet( search_input_1 ) ) {

					if ( $.type( filter[field_name] ) === 'string' || $.type( filter[field_name] ) === 'number' ) {
						search_input_1.setValue( filter[field_name] );
					} else {
						if ( filter[field_name].hasOwnProperty( 'value' ) ) { // when set default filter don't have 'value' in it, For example Invoice edit view
							search_input_1.setValue( filter[field_name].value );
						} else {
							search_input_1.setValue( filter[field_name] );
						}
					}
//					search_input_1.setValue( filter[field_name].value );
				}

			}

		}

		this.getSearchPanelFilter(); //Make sure filter only has fields on current display ab

		this.search_panel.setSearchFlag( this.getValidSearchFilter() ); // Add ! to tab which has search condition in it

		this.onSetSearchFilterFinished();

	},

	//Set Grid header style for asc or desc
	setGridHeaderStyle: function() {
		for ( var i = 0; i < this.t_grid_header_array.length; i++ ) {
			var t_grid_header = this.t_grid_header_array[i];

			var field = t_grid_header.attr( 'id' );
			if ( typeof field === 'string' || field instanceof String ) {
				field = field.substring( 10 + this.ui_id.length + 1, field.length );

				t_grid_header.cleanSortStyle();

				if ( this.select_layout.data.filter_sort ) {
					var sort_array_len = this.select_layout.data.filter_sort.length;

					for ( var j = 0; j < sort_array_len; j++ ) {
						var sort_item = this.select_layout.data.filter_sort[j];
						var sortField = Global.getFirstKeyFromObject( sort_item );
						if ( sortField === field ) {
							if ( sort_array_len > 1 ) {
								t_grid_header.setSortStyle( sort_item[sortField], j + 1 );
							} else {
								t_grid_header.setSortStyle( sort_item[sortField], 0 );
							}
						}
					}
				}
			}
		}
	},

	buildSortCondition: function( reset, field ) {
		var next_sort = 'desc';

		if ( reset ) {

			if ( this.select_layout.data.filter_sort && this.select_layout.data.filter_sort.length > 0 ) {
				var len = this.select_layout.data.filter_sort.length;
				var found = false;

				for ( i = 0; i < len; i++ ) {
					var sort_item = this.select_layout.data.filter_sort[i];
					for ( var key in sort_item ) {

						if ( !sort_item.hasOwnProperty( key ) ) {
							continue;
						}

						if ( key === field ) {
							if ( sort_item[key] === 'asc' ) {
								next_sort = 'desc';
							} else {
								next_sort = 'asc';
							}

							found = true;
						}
					}

					if ( found ) {
						break;
					}

				}

			}

			this.select_layout.data.filter_sort = [
				{}
			];
			this.select_layout.data.filter_sort[0][field] = next_sort;

		} else {
			if ( !this.select_layout.data.filter_sort ) {
				this.select_layout.data.filter_sort = [
					{}
				];
				this.select_layout.data.filter_sort[0][field] = 'asc';
			} else {
				len = this.select_layout.data.filter_sort.length;
				found = false;
				for ( var i = 0; i < len; i++ ) {
					sort_item = this.select_layout.data.filter_sort[i];
					for ( key in sort_item ) {

						if ( !sort_item.hasOwnProperty( key ) ) {
							continue;
						}

						if ( key === field ) {
							if ( sort_item[key] === 'asc' ) {
								sort_item[key] = 'desc';
							} else {
								sort_item[key] = 'asc';
							}

							found = true;
						}
					}

					if ( found ) {
						break;
					}

				}

				if ( !found ) {
					this.select_layout.data.filter_sort.push( {} );
					this.select_layout.data.filter_sort[len][field] = 'asc';
				}
			}

		}

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined
		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {
				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}
			}
		} else {
			filter.filter_page = 1;
		}
		//Error: Uncaught TypeError: Cannot read property 'data' of null
		if ( typeof this.select_layout != 'undefined' && this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}
		//Error: Uncaught TypeError: Cannot read property 'data' of null
		//If sub view controller set custom filters, get it
		if ( typeof this.select_layout != 'undefined' && Global.isSet( this.getSubViewFilter ) ) {
			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );
		}

		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		//Error: Uncaught TypeError: Cannot read property 'data' of null
		if ( this.select_layout && this.select_layout.data ) {
			filter.filter_sort = this.select_layout.data.filter_sort;
		}

		if ( TTUUID.isUUID( this.refresh_id ) ) {
			filter.filter_data = {};
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;

		} else {
			this.last_select_ids = [];
			var ids = this.getGridSelectIdArray();
			//ensure detached reference to value source or lose this.last_select_ids when grid is cleared.
			for ( var i = 0; i < ids.length; i++ ) {
				this.last_select_ids.push( ids[i] );
			}
		}

		var $this = this;
		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				var len;
				if ( set_default_menu ) {
					$this.setDefaultMenu( true );
				}
				if ( !Global.isArray( result_data ) && ( !TTUUID.isUUID( $this.refresh_id ) || $this.refresh_id == TTUUID.zero_id || $this.refresh_id == TTUUID.not_exist_id ) ) {
					$this.refresh_id = null;
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					if ( Global.isSet( $this.showGridOptionFields ) ) {
						result_data = $this.showGridOptionFields( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );
					len = result_data.length;
				}

				if ( TTUUID.isUUID( $this.refresh_id ) ) {
					$this.refresh_id = null;
					var grid_source_data = $this.grid.getData();
					len = grid_source_data.length;
					if ( $.type( grid_source_data ) !== 'array' ) {
						grid_source_data = [];
					}
					var found = false;
					var new_record = result_data[0];
					//Error: Uncaught TypeError: Cannot read property 'id' of undefined in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
					if ( new_record ) {
						for ( var i = 0; i < len; i++ ) {
							var record = grid_source_data[i];
							//Fixed === issue. The id set by jQGrid is string type.
							//Commented out as we now expect the variable type of the ids to be UUID (string in javascript)
							//if ( !isNaN( parseInt( record.id ) ) ) {
							//	record.id = parseInt( record.id );
							//}
							if ( record.id == new_record.id ) {
								$this.grid.setRowData( new_record.id, new_record );
								grid_source_data[i] = new_record;
								found = true;
								break;
							}
						}
						if ( !found ) {
							$this.grid.setData( grid_source_data.concat( new_record ) );
							$this.setGridColumnsWidth();
							if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGrid ) ) {
								len = Global.isSet( len ) ? len : 0;
								$this.resizeSubGrid( len + 1 );
							}
							$this.highLightGridRowById( new_record.id );
							$this.reSelectLastSelectItems();
						}
					}
				} else {
					//Set Page data to widget, next show display info when setDefault Menu
					$this.pager_data = result.getPagerData();
					//CLick to show more mode no need this step
					if ( LocalCacheData.paging_type !== 0 && $this.paging_widget && $this.paging_widget_2 ) {
						$this.paging_widget.setPagerData( $this.pager_data );
						$this.paging_widget_2.setPagerData( $this.pager_data );
					}
					if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
						var current_data = $this.grid.getData();
						result_data = current_data.concat( result_data );
					}

					// Process result_data if necessary, this always needs override.
					result_data = $this.processResultData( result_data );

					if ( $this.grid ) {
						$this.grid.setData( result_data ); //This calls clearGridData and reloadGrid.

						//$this.setGridColumnsWidth(); //Handle in searchDone() instead.
						if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGrid ) ) {
							$this.resizeSubGrid( len );
						}
						$this.reSelectLastSelectItems();
					}

				}
				$this.setGridCellBackGround(); //Set cell background for some views
				ProgressBar.closeOverlay(); //Add this in initData
				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}
				if ( callBack ) {
					callBack( result );
				}
				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}
				$this.searchDone();
			}
		} );

	},

	resizeSubGrid: function( length ) {
		var height = ( length * 26 >= 200 ) ? 200 : length * 26;
		if ( $( '.edit-view-tab:visible .grid-div' ).length > 1 ) {
			if ( height < 100 ) {
				height = 100;
			}
		} else {
			height = ( $( '.edit-view-tab:visible' ).parent().height() - 85 );
		}
		this.setGridColumnsWidth();
		this.setGridHeight( height );
	},

	setGridColumnsWidth: function() {
		if ( this.grid ) {
			this.grid.setGridColumnsWidth();
		}
	},

	setGridHeight: function( height ) {
		if ( this.grid ) {
			this.grid.setGridHeight( height );
		}
	},

	setGridWidth: function( width ) {
		if ( this.grid ) {
			this.grid.setGridWidth( width );
		}
	},


	processResultData: function( result_data ) {
		//Always needs override
		return result_data;
	},

	searchDone: function() {
		//the rotate icon from search panel
		var $this = this;
		$( '.button-rotate' ).removeClass( 'button-rotate' );

		this.setTotalDisplaySpan();

		this.setGridColumnsWidth();
		this.setGridSize( this.ui_id, this.sub_view_mode, this.sub_view_grid_autosize, this.pager_data );

		if ( this.sub_view_mode && this.grid ) {
			this.grid.grid.show();
		}

		TTPromise.resolve( 'BaseViewController', 'onTabShow' );
		TTPromise.resolve( 'init', 'init' );
	},

	reSelectLastSelectItems: function() {
		var $this = this;
		if ( this.last_select_ids && this.last_select_ids.length > 0 ) {
			$.each( this.last_select_ids, function( index, content ) {
				$this.grid.grid.setSelection( content, false );

				if ( $this.grid_select_id_array ) {
					$this.grid_select_id_array.push( content );
				}

			} );

			this.last_select_ids = [];
			if ( !this.edit_view ) {
				this.setDefaultMenu();
			}
		}

	},

	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'edit':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onEditClick( LocalCacheData.edit_id_for_next_open_view );
					LocalCacheData.edit_id_for_next_open_view = null;
				}

				break;
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onViewClick( LocalCacheData.edit_id_for_next_open_view );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				if ( !this.edit_view ) {
					this.onAddClick();
				}
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();

	},

	autoOpenEditOnlyViewIfNecessary: function() {

		//Don't try to open anything if current loading a sub view
		if ( this.sub_view_mode ) {
			return;
		}
		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.sm && !LocalCacheData.current_open_edit_only_controller ) {

			if ( LocalCacheData.all_url_args.sm.indexOf( 'Report' ) < 0 ) {
				IndexViewController.openEditView( this, LocalCacheData.all_url_args.sm, LocalCacheData.all_url_args.sid );
			} else {
				IndexViewController.openReport( this, LocalCacheData.all_url_args.sm );

				if ( LocalCacheData.all_url_args.sid ) {
					LocalCacheData.default_edit_id_for_next_open_edit_view = LocalCacheData.all_url_args.sid;
				}
			}

		}
	},

	setGridCellBackGround: function() {

		//Set backgournd for all policy view and RecurringScheduleTemplateControlView
		if ( this.script_name.indexOf( 'Policy' ) >= 0 ||
				this.script_name === 'RecurringScheduleTemplateControlView' ||
				this.script_name === 'PayCodeView' ||
				this.script_name === 'RecurringHolidayView' ||
				this.script_name === 'LegalEntityView' ||
				this.script_name === 'RemittanceSourceAccountView' ||
				this.script_name === 'PayrollRemittanceAgencyView' ||
				this.script_name === 'RemittanceDestinationAccountView'
		) {

			var data = this.grid.getData();

			//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}

			var len = data.length;

			for ( var i = 0; i < len; i++ ) {
				var item = data[i];

				if ( item.is_in_use === false ) {
					$( 'tr[id=\'' + item.id + '\']' ).addClass( 'policy-not-in-use' );
				}
			}

		}
	},

	showGridBorders: function() {
		var top_border = $( this.el ).find( '.grid-top-border' );
		var bottom_border = $( this.el ).find( '.grid-bottom-border' );

		top_border.css( 'display', 'block' );
		bottom_border.css( 'display', 'block' );
	},


	_setGridSizeGroupheight: function( header_size ) {
		this.grid.grid.setGridHeight( ($( this.el ).height() - (this.search_panel && this.search_panel.is( ':visible' ) ? this.search_panel.height() : 0) - 43 - header_size) );
	},
	setEditViewTabSize: function() {
		var $this = this;
		var tab_bar_label = this.edit_view_tab.find( '.edit-view-tab-bar-label' );
		var tab_width = this.edit_view_tab.width();
		var nav_width = this.edit_view_tab.find( '.navigation-div' ).width();
		var wrap_div = this.edit_view.find( '.tab-label-wrap' );

		var total_tab_width = 0;
		tab_bar_label.children().each( function() {
			total_tab_width += $( this ).width();
		} );

		if ( total_tab_width > (tab_width - nav_width - 25) ) {

			tab_bar_label.width( total_tab_width + 10 );

			if ( wrap_div.length === 0 ) {
				var right_arrow = $( '<img class="tab-arrow tab-right-arrow" style="display: none" src="theme/default/images/right_big_arrow.png" >' );
				var left_arrow = $( '<img class="tab-arrow tab-left-arrow" style="display: none" src="theme/default/images/left_big_arrow.png" >' );
				wrap_div = $( '<div class="tab-label-wrap"><div class="label-wrap"></div><div class="btn-wrap"></div></div>' );
				wrap_div.insertBefore( tab_bar_label );
				wrap_div.width( tab_width - nav_width - 25 );
				wrap_div.children().eq( 0 ).width( tab_width - nav_width - 100 );
				wrap_div.children().eq( 0 ).append( tab_bar_label );
				wrap_div.children().eq( 1 ).append( left_arrow );
				wrap_div.children().eq( 1 ).append( right_arrow );

				right_arrow.bind( 'click', function() {
					wrap_div.children().eq( 0 ).scrollLeft( wrap_div.children().eq( 0 ).scrollLeft() + 500 );
					setArrowStatus();
				} );
				left_arrow.bind( 'click', function() {
					wrap_div.children().eq( 0 ).scrollLeft( wrap_div.children().eq( 0 ).scrollLeft() - 500 );
					setArrowStatus();
				} );
			} else {
				wrap_div.width( tab_width - nav_width - 25 );
				wrap_div.children().eq( 0 ).width( tab_width - nav_width - 100 );
			}

			if ( tab_bar_label.children().eq( 0 ).is( ':visible' ) && !this.is_mass_editing ) {
				this.edit_view_tab.find( '.tab-arrow' ).show();
			} else {
				this.edit_view_tab.find( '.tab-arrow' ).hide();
			}

			setArrowStatus();

		} else {
			tab_bar_label.width( 'auto' );
			if ( wrap_div.length > 0 ) {
				tab_bar_label.insertBefore( wrap_div );
				wrap_div.remove();

			}
		}

		function setArrowStatus() {
			var left_arrow = $this.edit_view_tab.find( '.tab-left-arrow' );
			var right_arrow = $this.edit_view_tab.find( '.tab-right-arrow' );
			var label_wrap = wrap_div.children().eq( 0 );

			left_arrow.removeClass( 'disable-image' );
			right_arrow.removeClass( 'disable-image' );

			if ( label_wrap.scrollLeft() === 0 ) {
				left_arrow.addClass( 'disable-image' );
			}

			if ( label_wrap.scrollLeft() === label_wrap[0].scrollWidth - label_wrap.width() ) {
				right_arrow.addClass( 'disable-image' );
			}

		}

	},

	getFilterColumnsFromDisplayColumns: function( column_filter, enable_system_columns ) {
		return this._getFilterColumnsFromDisplayColumns( column_filter, enable_system_columns );
	},

	/**
	 * super for getFilterColumnsFromDisplayColumns
	 * used when function is overridden by child class.
	 *
	 * @param column_filter
	 * @param enable_system_columns TRUE
	 * @returns {*}
	 * @private
	 */
	_getFilterColumnsFromDisplayColumns: function( column_filter, enable_system_columns ) {
		if ( !column_filter ) {
			column_filter = {};
		}

		if ( enable_system_columns == undefined || enable_system_columns == true ) {
			column_filter.is_owner = true;
			column_filter.id = true;
			column_filter.is_child = true;
			column_filter.in_use = true;
			column_filter.first_name = true;
			column_filter.last_name = true;
		}

		var display_columns = {};

		if ( Global.isSet( LocalCacheData.view_layout_cache[this.script_name] ) ) {
			var result = LocalCacheData.view_layout_cache[this.script_name].getResult();
			if ( result != undefined && result.length > 0 ) {
				display_columns = result[0].data.display_columns;
			}
		}


		if ( this.select_layout && this.select_layout.data && this.select_layout.data.display_columns ) {
			for ( var n in this.select_layout.data.display_columns ) {
				display_columns[n] = this.select_layout.data.display_columns[n];
			}
		}

		//get the default display columns if no columns have been defined.
		if ( display_columns.length == undefined || (display_columns.length == 0 && Global.isSet( this.default_display_columns )) ) {
			display_columns = this.default_display_columns;
		}

		//Fixed possible exception -- Error: Unable to get property 'length' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 5031
		if ( display_columns.length != undefined && display_columns.length > 0 ) {
			var len = display_columns.length;
			for ( var i = 0; i < len; i++ ) {
				column_filter[display_columns[i]] = true;
			}
		}

		return column_filter;
	},

	getAllLayouts: function( callBack ) {

		var $this = this;

		var current_select_layout_name;

		if ( this.need_select_layout_name ) {
			current_select_layout_name = this.need_select_layout_name;
			this.need_select_layout_name = '';
		} else {
			current_select_layout_name = BaseViewController.default_layout_name;
		}

		if ( this.sub_view_mode || !this.show_search_tab ) {

			$this.select_layout = null;
			if ( callBack ) {
				callBack();
			}

			return;
		}
		// Check view layout cache.
		if ( LocalCacheData.view_layout_cache[this.script_name] ) {
			//Make this async way
			setTimeout( function() {
				onGetUserGenericDataResult( LocalCacheData.view_layout_cache[$this.script_name] );
			}, 0 );
		} else {
			if ( !this.user_generic_data_api ) {
				this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();
			}
			this.user_generic_data_api.getUserGenericData( {
				filter_data: {
					script: this.script_name,
					deleted: false
				}
			}, {
				onResult: function( results ) {
					onGetUserGenericDataResult( results );
				}
			} );
		}

		function onGetUserGenericDataResult( results ) {
			var result_data = results.getResult();
			$this.select_layout = null; //Reset select layout;
			LocalCacheData.view_layout_cache[$this.script_name] = results;
			if ( result_data && result_data.length > 0 ) {
				result_data.sort( function( a, b ) {
							return Global.compare( a, b, 'name' );
						}
				);
				var len = result_data.length;
				for ( var i = 0; i < len; i++ ) {
					var layout = result_data[i];
					if ( layout.name === current_select_layout_name ) {
						$this.select_layout = layout;
						break;
					}
				}
				if ( !$this.select_layout ) {
					$this.select_layout = result_data[0];
				}
				$this.search_panel.setLayoutsArray( result_data );
			} else {
				$this.select_layout = null;
				if ( $this.search_panel ) {
					$this.search_panel.setLayoutsArray( null );
				}
			}
			if ( callBack ) {
				callBack();
			}
		}

	},

	getAllColumns: function( callBack ) {
		var $this = this;
		this.api.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();

				$this.all_columns = Global.buildColumnArray( columns_result_data );
				if ( !$this.sub_view_mode && $this.column_selector ) {
					$this.column_selector.setUnselectedGridData( $this.all_columns );
					$this.column_selector.setHeight( $this.all_columns.length * 32 );
				}

				if ( callBack ) {
					callBack();
				}

			}
		} );

	},

	getDefaultDisplayColumns: function( callBack ) {

		var $this = this;
		this.api.getOptions( 'default_display_columns', {
			onResult: function( columns_result ) {

				var columns_result_data = columns_result.getResult();

				$this.default_display_columns = columns_result_data;

				if ( callBack ) {
					callBack();
				}

			}
		} );

	},

	buildSortBySelectColumns: function() {
		var sort_by_array = this.select_layout.data.filter_sort;
		var sort_by_select_columns = [];
		var sort_by_unselect_columns = this.sort_by_selector.getSourceData();

		if ( sort_by_array ) {
			$.each( sort_by_array, function( index, content ) {

				for ( var key in content ) {

					$.each( sort_by_unselect_columns, function( index1, content1 ) {
						if ( content1.value === key ) {
							content1.sort = content[key];
							sort_by_select_columns.push( content1 );
							return false;
						}
					} );
				}

			} );
		}

		return sort_by_select_columns;

	},

	buildSortSelectorUnSelectColumns: function( display_columns ) {
		var fina_array = [];
		var i = 100;
		$.each( display_columns, function( index, content ) {
			var new_content = $.extend( {}, content );
			new_content.id = i; //Need
			new_content.sort = 'asc';
			fina_array.push( new_content );
			i = i + 1;
		} );

		return fina_array;
	},

	buildDisplayColumns: function( apiDisplayColumnsArray ) {

		var len = this.all_columns.length;
		var len1 = apiDisplayColumnsArray.length;
		var display_columns = [];

		for ( var j = 0; j < len1; j++ ) {
			for ( var i = 0; i < len; i++ ) {
				if ( apiDisplayColumnsArray[j] === this.all_columns[i].value ) {
					display_columns.push( this.all_columns[i] );
				}
			}
		}
		return display_columns;

	},

	buildDisplayColumnsByColumnModel: function( colModel ) {

		if ( !colModel ) {
			return;
		}

		var len = colModel.length;
		var display_columns = [];
		var id = 2000; // Makse sure the id not duplicate with all_columns, this wiil be used in acombox, set possible columns in navigation mode
		for ( var i = 0; i < len; i++ ) {
			var column = colModel[i];
			if ( column.name === 'cb' ) {
				continue;
			}
			display_columns.push( { label: column.label, value: column.name, id: id } );
			id = id + 1;
		}

		return display_columns;
	},

	removeContentMenuByName: function( name ) {
		if ( !LocalCacheData.current_open_primary_controller ) {
			return;
		}
		var primary_view_id = LocalCacheData.current_open_primary_controller.viewId;
		//var select_menu_id = TopMenuManager.menus_quick_map[primary_view_id];
		//if ( TopMenuManager.ribbon_view_controller && TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) !== -1 ) {
		//	TopMenuManager.ribbon_view_controller.setSelectMenu( select_menu_id );
		//}

		if ( !Global.isSet( name ) ) {
			name = this.context_menu_name;
		}

		var tab = $( '#ribbon ul a' ).filter( function() {
			return $( this ).attr( 'ref' ) === name;
		} ).parent();

		var index = $( 'li', $( '#ribbon' ) ).index( tab );
		if ( index >= 0 ) {
			// $( '#ribbon_view_container' ).tabs( {'remove': index} );
			$( '#ribbon_view_container' ).tabs( 'refresh' );
		}

	},

	movePermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.addPermissionValidate( p_id ) && this.deletePermissionValidate( p_id ) ) {
			return true;
		}

		return false;

	},

	subAuditValidate: function() {
		if ( this.editPermissionValidate() ) {
			return true;
		}

		return false;
	},

	subDocumentValidate: function() {
		if ( ( Global.getProductEdition() >= 20 ) && PermissionManager.checkTopLevelPermission( 'Document' ) ) {
			return true;
		}

		return false;
	},

	addPermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'add' ) ) {
			return true;
		}

		return false;

	},

	getRecordFromGridById: function( id ) {

		var data = this.grid.getData();
		var result = null;
		/* jshint ignore:start */
		//id could be string or number.
		$.each( data, function( index, value ) {

			if ( value.id == id ) {
				result = Global.clone( value );
				return false;
			}

		} );
		/* jshint ignore:end */
		return result;

	},

	getSelectedItems: function() {
		var $this = this;
		var selected_items = [];
		if ( this.edit_view ) {
			selected_items = [this.current_edit_record];
		} else {
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;
			selected_items = _.map( grid_selected_id_array, function( id ) {
				return $this.getRecordFromGridById( id );
			} );
		}
		return selected_items;
	},

	getSelectedItem: function() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
			}

		}

		if ( selected_item ) {
			return Global.clone( selected_item );
		} else {
			return null;
		}

	},

	deleteOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
				PermissionManager.validate( p_id, 'delete' ) ||
				(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'delete_own' )) ||
				(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'delete_child' )) ) {

			return true;

		}

		return false;

	},

	viewOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectedItem();
		}

		if (
				PermissionManager.validate( p_id, 'view' ) ||
				(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'view_own' )) ||
				(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'view_child' )) ) {

			return true;

		}

		return false;

	},

	editOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
				PermissionManager.validate( p_id, 'edit' ) ||
				(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'edit_own' )) ||
				(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'edit_child' )) ) {

			return true;

		}

		return false;

	},

	ownerOrChildPermissionValidate: function( p_id, permission_name, selected_item ) {

		var field;
		if ( permission_name.indexOf( 'child' ) > -1 ) {
			field = 'is_child';
		} else {
			field = 'is_owner';
		}
//
//		if ( PermissionManager.validate( p_id, permission_name ) &&
//			(!selected_item ||
//				( selected_item && (selected_item[field] || (!selected_item.id && !selected_item.hasOwnProperty( field )) ) ) ) ) {
//			return true;
//		}

		if ( PermissionManager.validate( p_id, permission_name ) ) {
			return true;
		}

		return false;
	},

	editChildPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !PermissionManager.validate( p_id, 'enabled' ) ) {
			return false;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) ||
				this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) ) {

			return true;
		}

		return false;

	},

	editPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) || this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) || this.ownerOrChildPermissionValidate( p_id, 'edit_own', selected_item ) ) {

			return true;
		}

		return false;

	},

	copyPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.viewPermissionValidate( p_id, selected_item ) && this.addPermissionValidate( p_id, selected_item ) ) {
			return true;
		}

		return false;

	},

	copyAsNewPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.viewPermissionValidate( p_id, selected_item ) && this.addPermissionValidate( p_id, selected_item ) ) {
			return true;
		}

		return false;
	},

	viewPermissionValidate: function( p_id, selected_item ) {

		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'view' ) || this.ownerOrChildPermissionValidate( p_id, 'view_child', selected_item ) || this.ownerOrChildPermissionValidate( p_id, 'view_own', selected_item ) ) {
			return true;
		}

		return false;

	},

	deletePermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'delete' ) || this.ownerOrChildPermissionValidate( p_id, 'delete_child', selected_item ) || this.ownerOrChildPermissionValidate( p_id, 'delete_own', selected_item ) ) {
			return true;
		}

		return false;

	},

	saveValidate: function( context_btn, p_id ) {
		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (( !this.current_edit_record || !this.current_edit_record.id ) && this.is_mass_editing) || this.current_edit_record.id ) {

			if ( !this.editPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}
	},

	saveAndCopyValidate: function( context_btn, p_id ) {

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (( !this.current_edit_record || !this.current_edit_record.id ) && this.is_mass_editing) || this.current_edit_record.id ) {

			if ( !this.editPermissionValidate( p_id ) || !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}

		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	saveAndContinueValidate: function( context_btn, p_id ) {
		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) || !this.editPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (( !this.current_edit_record || !this.current_edit_record.id ) && this.is_mass_editing) || this.current_edit_record.id ) {

			if ( !this.editPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}

		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	saveAndNewValidate: function( context_btn, p_id ) {
		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (!Global.isSet( this.current_edit_record.id ) && this.is_mass_editing) || Global.isSet( this.current_edit_record.id ) ) {

			if ( !this.editPermissionValidate( p_id ) || !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}

		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	initSubLogView: function( tab_id ) {
		var $this = this;

		if ( !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) {
			return;
		}

		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;

			this.sub_log_view_controller.search();
		} else {

			Global.loadScript( 'views/core/log/LogViewController.js', function() {
				if ( !$this.edit_view_tab ) {
					return;
				}
				var tab = $this.edit_view_tab.find( '#' + tab_id );
				var firstColumn = tab.find( '.first-column-sub-view' );

				TTPromise.add( 'initSubAudit', 'init' );
				TTPromise.wait( 'initSubAudit', 'init', function() {
					firstColumn.css('opacity', '1');
				} );

				firstColumn.css('opacity', '0'); //Hide the grid while its loading/sizing.

				Global.trackView( 'Sub' + 'Log' + 'View', LocalCacheData.current_doing_context_action );
				LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
			} );
		}

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_key = 'object_id';
			$this.sub_log_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.parent_view_controller = $this;

			$this.sub_log_view_controller.postInit = function() {
				this.initData();
			};

		}
	},

	showNoResultCover: function( show_new_btn ) {
		if (  !show_new_btn ) {
			show_new_btn = this.ifContextButtonExist(ContextMenuIconName.add);
		}

		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( { related_view_controller: this, is_new: show_new_btn } );
		this.no_result_box.attr( 'id', this.ui_id + '_no_result_box' );

		var grid_div = $( this.el ).find( '.grid-div' );

		grid_div.append( this.no_result_box );

		this.initRightClickMenu( RightClickMenuType.NORESULTBOX );
	},

	removeNoResultCover: function() {

		if ( this.no_result_box && this.no_result_box.length > 0 ) {
			this.no_result_box.remove();
		}
		this.no_result_box = null;

	},

	cleanWhenUnloadView: function( callBack ) {

		this.removeContentMenuByName();
		if ( Global.isSet( callBack ) ) {
			callBack();
		}
	},

	gridScrollTop: function() {

		if ( this.viewId === 'TimeSheet' || this.viewId === 'Schedule' ) {
			return;
		}

		if ( !this.grid ) {
			return;
		}

		this.grid.grid.parent().parent().scrollTop( 0 );
	},

	gridScrollDown: function() {

		if ( this.viewId === 'TimeSheet' || this.viewId === 'Schedule' ) {
			return;
		}

		if ( !this.grid ) {
			return;
		}

		this.grid.grid.parent().parent().scrollTop( 10000 );
	},

	selectAll: function() {
		if ( this.viewId === 'TimeSheet' || this.viewId === 'Schedule' ) {
			return;
		}

		if ( !this.grid ) {
			return;
		}

		this.grid.grid.resetSelection();
		var source_data = this.grid.getData();
		var len = source_data.length;
		for ( var i = 0; i < len; i++ ) {
			var item = source_data[i];
			if ( Global.isSet( item.id ) ) {
				this.grid.grid.setSelection( item.id, false );
			} else {
				this.grid.grid.setSelection( i + 1, false );
			}

		}

		this.grid.grid.parent().parent().parent().find( '.cbox-header' ).prop( 'checked', true );
		this.setDefaultMenu();
	},

	detachElement: function( key ) {
		//Error: Uncaught TypeError: Cannot read property 'detach' of undefined in interface/html5/views/BaseViewController.js?v=9.0.0-20150824-110300 line 6441
		if ( !this.edit_view_form_item_dic || !this.edit_view_form_item_dic[key] ) {
			return;
		}

		var place_holder = $( '<p style="display: none">' );
		place_holder.addClass( '.edit-view:visible place_holder_' + key );
		place_holder.insertBefore( this.edit_view_form_item_dic[key] );
		this.edit_view_form_item_dic[key].detach();
	},

	attachElement: function( key ) {
		//Error: Uncaught TypeError: Cannot read property 'insertBefore' of undefined in interface/html5/views/BaseViewController.js?v=9.0.0-20150822-210544 line 6439
		if ( !this.edit_view_form_item_dic || !this.edit_view_form_item_dic[key] ) {
			return;
		}

		//var place_holder = $( '.edit-view:visible .edit-view-tab:visible .place_holder_' + key);
		var place_holder = $( '.edit-view:visible .place_holder_' + key );
		this.edit_view_form_item_dic[key].insertBefore( place_holder );
		place_holder.remove();
	},

	getBalanceHandler: function( result, last_date_stamp ) {
		var $this = this;
		var available_balance_value, current_time_value, remaining_balance_value, summary_available_value;
		var result_data = result.getResult();
		//Error: TypeError: this.edit_view_ui_dic.available_balance is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-091433 line 2 > eval line 6570
		if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic['available_balance'] ) {
			return;
		}
		if ( !result_data ) {
			$this.detachElement( 'available_balance' );
			return;
		}
		$this.attachElement( 'available_balance' );

		available_balance_value = Global.getTimeUnit( result_data.available_balance );
		current_time_value = Global.getTimeUnit( result_data.current_time );
		remaining_balance_value = Global.getTimeUnit( result_data.remaining_balance );
		summary_available_value = Global.getTimeUnit( result_data.projected_remaining_balance );
		if ( result_data.hasOwnProperty( 'remaining_dollar_balance' ) ) {
			available_balance_value = available_balance_value + ' / ' + LocalCacheData.getCurrentCurrencySymbol() + result_data.available_dollar_balance;
			current_time_value = current_time_value + ' / ' + LocalCacheData.getCurrentCurrencySymbol() + result_data.current_dollar_amount;
			remaining_balance_value = remaining_balance_value + ' / ' + LocalCacheData.getCurrentCurrencySymbol() + result_data.remaining_dollar_balance;
			summary_available_value = summary_available_value + ' / ' + LocalCacheData.getCurrentCurrencySymbol() + result_data.remaining_dollar_balance;
		}
		$this.edit_view_ui_dic['available_balance'].setValue( summary_available_value );

		$this.available_balance_info.qtip(
				{
					show: {
						event: 'click',
						delay: 10,
						effect: true
					},

					hide: {
						event: 'unfocus'
					},

					style: {
						//classes: 'cream',
						width: 340 //Dynamically changing the width causes display bugs when switching between Absence Policies and thereby widths.
					},
					content: '<div style="width:100%;">' +
					'<div style="width:100%; clear: both;"><span style="float:left;">' + $.i18n._( 'Available Balance' ) + ': </span><span style="float:right;">' + available_balance_value + '</span></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">' + $.i18n._( 'Current Time' ) + ': </span><span style="float:right;">' + current_time_value + '</span></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">' + $.i18n._( 'Remaining Balance' ) + ': </span><span style="float:right;">' + remaining_balance_value + '</span></div>' +
					'<div style="width:100%; height: 20px; clear: both;"></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">' + $.i18n._( 'Projected Balance by' ) + ' ' + last_date_stamp + ': </span><span style="float:right;">' + Global.getTimeUnit( result_data.projected_balance ) + '</span></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">' + $.i18n._( 'Projected Remaining Balance' ) + ':</span><span style="float:right;">' + Global.getTimeUnit( result_data.projected_remaining_balance ) + '</span></div>' +
					'</div>'
				} );
	},

	onExportClick: function( method ) {
		ProgressBar.showOverlay();
		if ( method == undefined ) {
			method = this.api['export' + this.api.key_name];
		}

		//Debug.Text('Exporting Grid To CSV: '+method, 'BaseViewController.js', 'BaseViewController', 'onExportClick', 10);

		var args = {};
		args.filter_columns = this._getFilterColumnsFromDisplayColumns( null, false );
		args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		if ( Global.isSet( this.sort_by_selector ) ) {
			args.filter_sort = this.getSearchPanelSortFilter();
		}
		var post_data = { 0: 'csv', 1: args, 2: true };

		Global.APIFileDownload( this.api.className, method, post_data );
	},


	highLightGridRowById: function( id ) {
		if ( this.grid && this.grid.grid ) {
			this.grid.grid.find( 'tr#' + id ).addClass( 'flashBackground' );
			this.gridScrollDown();
		}
	},

	setConversionRateExampleText: function( conversion_rate, iso_code, currency_id ) {
		var data = {};
		data.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		var api = new (APIFactory.getAPIClass( 'APICurrency' ))();
		var my_currencies = api.getCurrency( data, { async: false } ).getResult();
		var base_currency_iso_code = '';
		if ( this.edit_view_ui_dic.round_decimal_places ) {
			var decimal_places = this.edit_view_ui_dic.round_decimal_places.getValue();
		}
		for ( var i = 0; i < my_currencies.length; i++ ) {
			if ( my_currencies[i].is_base ) {
				base_currency_iso_code = my_currencies[i].iso_code;

			}
			if ( currency_id && !iso_code && my_currencies[i].id == currency_id ) {
				iso_code = my_currencies[i].iso_code;
				if ( !decimal_places ) {
					decimal_places = my_currencies[i].round_decimal_places;
				}
			}
		}

		//need different id on the subview for rate.
		if ( iso_code != base_currency_iso_code ) {
			if ( this.sub_view_mode ) {
				$( '#rate_conversion_rate_clarification_box' ).html( '&nbsp;&nbsp;1.00 ' + base_currency_iso_code + ' = ' + Global.removeTrailingZeros( conversion_rate, decimal_places ) + ' ' + iso_code );
			} else {
				$( '#conversion_rate_clarification_box' ).html( '&nbsp;&nbsp;1.00 ' + base_currency_iso_code + ' = ' + Global.removeTrailingZeros( conversion_rate, decimal_places ) + ' ' + iso_code );
			}
		} else {
			$( '#conversion_rate_clarification_box' ).hide();
		}

	},

	/**
	 * gets default coordinates object for maps.
	 */
	startMapCoordinates: function() {
		var lat = 39.50;
		var lng = -98.35;

		if ( LocalCacheData.getCurrentCompany().latitude != 0 && LocalCacheData.getCurrentCompany().longitude != 0 ) {
			lat = LocalCacheData.getCurrentCompany().latitude;
			lng = LocalCacheData.getCurrentCompany().longitude;
			Debug.Text( 'Using company coordinates.', 'BaseViewController.js', 'BaseViewController', 'startMapCoordinates', 10 );
		} else if ( LocalCacheData.getLoginUser().latitude != 0 && LocalCacheData.getLoginUser().longitude != 0 ) {
			lat = LocalCacheData.getLoginUser().latitude;
			lng = LocalCacheData.getLoginUser().longitude;
			Debug.Text( 'Using user coordinates.', 'BaseViewController.js', 'BaseViewController', 'startMapCoordinates', 10 );
		} else {
			var company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
			var country_arr = company_api.getOptions( 'country', { async: false } ).getResult();
			var province_arr = company_api.getOptions( 'province', LocalCacheData.getCurrentCompany().country, { async: false } ).getResult();

			if ( APIGlobal.pre_login_data.map_geocode_url && province_arr && country_arr && province_arr[LocalCacheData.getCurrentCompany().province] && country_arr[LocalCacheData.getCurrentCompany().country] ) {
				var query = LocalCacheData.getCurrentCompany().city + ' ' + province_arr[LocalCacheData.getCurrentCompany().province] + ', ' + country_arr[LocalCacheData.getCurrentCompany().country];
				var url = APIGlobal.pre_login_data.map_geocode_url + '?q=' + query + '&format=json&tt_key=' + APIGlobal.pre_login_data.registration_key;
				var result = jQuery.ajax( { url: url, async: false } );
				Debug.Arr( 'Geocoding address: ' + query, result, 'BaseViewController.js', 'BaseViewController', 'startMapCoordinates', 10 );
			}

			if ( result && result.responseJSON && result.responseJSON[0] && result.responseJSON[0].lat && result.responseJSON[0].lon ) {
				lat = result.responseJSON[0].lat;
				lng = result.responseJSON[0].lon;
				Debug.Text( 'Using company address coordinates.', 'BaseViewController.js', 'BaseViewController', 'startMapCoordinates', 10 );
			} else {
				Debug.Text( 'Using default coordinates.', 'BaseViewController.js', 'BaseViewController', 'startMapCoordinates', 10 );
			}
		}

		Debug.Text( 'Coordinates (lat,long): ' + lat + ',' + lng, 'BaseViewController.js', 'BaseViewController', 'startMapCoordinates', 10 );
		return new L.LatLng( lat, lng );
	},

	initSubDocumentView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			return;
		}

		if ( this.sub_document_view_controller ) {
			this.sub_document_view_controller.buildContextMenu( true );
			this.sub_document_view_controller.setDefaultMenu();
			$this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_document_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/document/DocumentViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_contact_info = $this.edit_view_tab.find( '#tab_attachment' );
			var firstColumn = tab_contact_info.find( '.first-column-sub-view' );

			TTPromise.add( 'initSubDocumentView', 'init' );
			TTPromise.wait( 'initSubDocumentView', 'init', function() {
				firstColumn.css('opacity', '1');
			} );

			firstColumn.css('opacity', '0'); //Hide the grid while its loading/sizing.

			Global.trackView( 'SubDocumentView' );
			DocumentViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_document_view_controller = subViewController;
			$this.sub_document_view_controller.parent_key = 'object_id';
			$this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_document_view_controller.document_object_type_id = $this.document_object_type_id;
			$this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_document_view_controller.parent_view_controller = $this;
			$this.sub_document_view_controller.initData();
		}

	},

	onDeleteImage: function( callback ) {
		var $this = this;
		this.api.deleteImage( this.current_edit_record.id, {
			onResult: function( result ) {
				$this.onEditClick( $this.current_edit_record.id, true );
			}
		} );
	},

	onTreeGridNavigationRowSelect: function( id ) {
		if ( !id ) {
			return;
		}

		//don't close on collapse of tree mode element
		if ( LocalCacheData.currently_collapsing_navigation_tree_element != true ) {
			this.onEditClick( id );
			$( '.a-dropdown-div' ).remove();
			LocalCacheData.openAwesomeBox = null;
		} else {
			LocalCacheData.currently_collapsing_navigation_tree_element = false;
			this.onEditClick( id, true );
			this.setNavigation();
		}

	},

    parserDatesRange: function( date ) {
        var dates = date.split( " - " );
        var resultArray = [];
        var beginDate = Global.strToDate( dates[0] );
        var endDate = Global.strToDate( dates[1] );

        var nextDate = beginDate;

        while ( nextDate.getTime() < endDate.getTime() ) {
            resultArray.push( nextDate.format() );
            nextDate = new Date( new Date( nextDate.getTime() ).setDate( nextDate.getDate() + 1 ) );
        }

        resultArray.push( dates[1] );

        return resultArray;
		},

	baseViewSubTabGridResize: function(id) {
		if ( !id ) {
			id = '.edit-view-tab-outside-sub-view';
		} else if ( id.indexOf('#') === -1 && id.indexOf('.') != 0 ) {
			id = '#' + id;
		}

		if ( this.grid.grid.parents( id ).length > 0 ) {
			var offset = 53;
			if ( this.grid.grid.parents(id).find('.audit-info').length > 0 ) {
				//audit info div height
				offset += this.grid.grid.parents(id).find('.audit-info').height() + 20;
			}

			if ( this.grid.grid.parents(id).parents('.wizard').length > 0 ) {
				//Wizard view.
				offset += this.getDefaultHeightOffset() - 90;
			}

			if ( this.paging_widget && this.paging_widget.getPagerData() && ( this.paging_widget.getPagerData().total_rows == false || this.paging_widget.getPagerData().total_rows >= LocalCacheData.getLoginUserPreference().items_per_page ) ) {
				offset += 25;
			}

			var height = (this.grid.grid.parents( id ).innerHeight() - offset );

			this.grid.setup.container_selector = id;

			Debug.Text( 'Special SubView ID: '+ id +' Height: '+ height +' Offset: '+ offset, 'TTGrid.js', 'TTGrid', 'baseViewSubTabGridResize', 10 );
		} else {
			var offset = this.getDefaultHeightOffset();
			var height = ( this.grid.grid.parents('.edit-view-tab').innerHeight() - offset );

			Debug.Text( 'Normal SubView ID: '+ id +' Height: '+ height +' Offset: '+ offset, 'TTGrid.js', 'TTGrid', 'baseViewSubTabGridResize', 10 );
		}

		this.setGridHeight( height );
		this.setGridWidth();
	},
	/**
	 *  sets the grid's height and width.
	 * @param ui_id
	 * @param sub_view_mode
	 * @param sub_view_grid_autosize
	 * @param pager_data
	 */
	setGridSize: function( ui_id, sub_view_mode, sub_view_grid_autosize, pager_data ) {
		if ( this.grid && this.grid.setup && this.grid.setup.setGridSize && typeof this.grid.setup.setGridSize == 'function' ) {
			this.grid.setup.setGridSize();
			return;
		}

		if ( (!ui_id && !this.ui_id) || !this.grid ) {
			Debug.Text( 'ERROR: You must provide at least a ui_id for setGridSize()', 'TTGrid.js', 'TTGrid', 'setGridSize', 10 );
			return;
		}

		if ( !ui_id && this.ui_id ) {
			ui_id = this.ui_id;
		}

		//this.setGridWidth ( this.setGridColumnsWidth() );

		var height = 100;

		if ( sub_view_mode &&
				this.grid.grid.parents('.grid-div').find('.no-result-div:visible').length > 0 &&
				this.grid.grid.parents('#tab_history, #tab_qualifications').length > 0
		) {
			height = 100;
		} else if ( this.grid.setup.static_height ) {
			height = this.grid.setup.static_height;
		} else if ( this.grid.setup.verticalResize && this.grid.setup.verticalResize === true ) {

			var header_table = $( 'table[aria-labelledby=gbox_' + ui_id + '_grid]' );
			var header_size = header_table.height();
			if ( this.grid.setup.tree_mode ) {
				header_size = 0;
			}
			if ( sub_view_mode ) {
				Debug.Text( 'SubView resize triggered', 'BaseViewController.js', 'BaseViewController', 'setGridSize', 10 );
				if ( sub_view_grid_autosize && sub_view_grid_autosize === true ) {

					var length = this.grid.getRecordCount();

					var cell_height = this.grid.grid.find( 'tr:last td:first' ).height();
					if ( cell_height < 18 ) {
						cell_height = 22;
					}
					var height;
					if ( length == 0 ) {
						height = 1; //lower bound
					} else {
						if ( length > 10 ) {
							height = 10 * cell_height; //upper bound
						} else {
							height = length * cell_height;
						}
					}

					if ( height < 100 ) {
						height += 32;
					}
				} else {
					var edit_view_height = this.grid.grid.parents( '.edit-view-tab-outside-sub-view' ).height();
					if ( pager_data && pager_data.last_page_number > 1 ) {
						height = ( edit_view_height - header_size - 56 );
					} else {
						height = ( edit_view_height - header_size - 31 ); //fixing wage inset in employee edit view
					}
				}
			} else {
				var offset = this.getDefaultHeightOffset();

				height = this.grid.grid.parents('#contentContainer').height() - offset;
				Debug.Text( 'NEW GRID HEIGHT: this.grid.grid.parents(\'.view\').height() - offset: ('+ this.grid.grid.parents('.view').height() +' - '+ offset +') '+ (this.grid.grid.parents('.view').height() - offset), 'BaseViewController.js', 'BaseViewController', 'setGridSize', 10 );
			}
		}

		//if ( $('.grid-top-border:visible').length > 0 && this.grid.width() > $('.grid-top-border:visible').width() ) {
		// if ( this.grid.parents('.ui-jqgrid-bdiv').width() < this.grid.parents('.ui-jqgrid-bdiv')[0].scrollWidth ) {
		// 	height -= 15;
		// }

		this.grid.grid.setGridHeight( height );

		//this looks odd, but css does not have a has selector.
		$( '.sub-view .bottom-div:has(.paging-2-div:visible)' ).css( 'height', '20px' );
		$( '.sub-view .bottom-div:has(.paging-2-div:hidden)' ).css( 'height', 'auto' );
		//this.reloadGrid(); //slows down awesomeboxes
	},

	getDefaultHeightOffset:  function () {
		//protect against NaNs

		var offset = this.grid.grid.parents('.ui-jqgrid-jquery-ui').find('.ui-jqgrid-hbox').height() - 22; // 22 is default cell height. we just want the overage here.
		Debug.Text( 'Initial offset: '+ offset, 'BaseViewController.js', 'BaseViewController', 'getDefaultHeightOffset', 10 );

		//getting these selectors right for every grid was a lot of trial and error.
		if ( this.grid.grid.parents('.ui-jqgrid-bdiv').width() > this.grid.grid.parents('.ui-jqgrid-jquery-ui').width() ) {
			offset += 15; //scrollbar offset
			Debug.Text( 'Scrollbar offset detected: 15', 'BaseViewController.js', 'BaseViewController', 'getDefaultHeightOffset', 10 );
		}

		if ( this.search_panel && this.search_panel.is( ':visible' ) == true ) {
			offset += this.search_panel.height();
			Debug.Text( 'Search panel detected: '+ this.search_panel.height(), 'BaseViewController.js', 'BaseViewController', 'getDefaultHeightOffset', 10 );
		}

		var total_number_div_height = ( $( '.total-number-div:visible' ).length > 0 ) ? $( '.total-number-div:visible' ).height() : 0;
		if ( total_number_div_height || total_number_div_height === 0 ) {
			offset += total_number_div_height;
			Debug.Text( 'Total number DIV height offset detected: '+ $( '.total-number-div:visible' ).height(), 'BaseViewController.js', 'BaseViewController', 'getDefaultHeightOffset', 10 );
		}

		var footer_height = $('.bottom-div').height() + 5;
		if ( footer_height || footer_height === 0 ) {
			offset += footer_height;
			Debug.Text( 'Footer height offset detected: '+ footer_height, 'BaseViewController.js', 'BaseViewController', 'getDefaultHeightOffset', 10 );
		}

		var red_border_height = $( '.grid-top-border' ).height() * 2;
		if ( red_border_height || red_border_height === 0 ) {
			offset += red_border_height;
			Debug.Text( 'Red border height offset detected: '+ red_border_height, 'BaseViewController.js', 'BaseViewController', 'getDefaultHeightOffset', 10 );
		}

		return offset;
	}

} );
//Don't check the file for now. Too many issues
/* jshint ignore:end */

BaseViewController.loadView = function( view_id ) {
	Global.loadViewSource( view_id, view_id + 'View.html', function( result ) {
		var args = {};
		switch ( view_id ) {
			case 'TimeSheet':
				Global.loadViewSource( view_id, view_id + 'View.css' );
				args = {
					accumulated_time: $.i18n._( 'Accumulated Time' ),
					verify: $.i18n._( 'Verify' ),
					timesheet_verification: $.i18n._( 'TimeSheet Verification' )
				};
				break;
			case 'Login':
				$( 'body' ).addClass( 'login-bg' );
				$( 'body' ).removeClass( 'application-bg' );
				Global.loadViewSource( view_id, view_id + 'View.css' );
				args = {
					secure_login: $.i18n._( 'Secure Login' ),
					user_name: $.i18n._( 'User Name' ),
					password: $.i18n._( 'Password' ),
					forgot_your_password: $.i18n._( 'Forgot Your Password' ),
					quick_punch: $.i18n._( 'Quick Punch' ),
					login: $.i18n._( 'Login' ),
					language: $.i18n._( 'Language' )
				};
				break;
			case 'PortalJobVacancyDetail':
				args = {
					search_label: $.i18n._( 'Search' )
				};
				break;
			case 'PortalJobVacancy':
				args = {
					search_label: $.i18n._( 'Search' ),
					load_more: $.i18n._( 'Loading' ) + '...'
				};
				break;
			case 'MyJobApplication':
			case 'MyProfile':
				break;
			case 'Schedule':
				Global.loadViewSource( view_id, view_id + 'View.css' );
				break;
		}
		IndexViewController.instance.router.removeCurrentView();
		var template = _.template( result );
		Global.contentContainer().html( template( args ) );
		LocalCacheData.current_open_view_id = view_id;
		Global.trackView( view_id, LocalCacheData.current_doing_context_action );
	} );

};

BaseViewController.default_layout_name = $.i18n._( '-Default-' );
