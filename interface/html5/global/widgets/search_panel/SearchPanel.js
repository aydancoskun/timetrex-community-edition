( function( $ ) {

	$.fn.SearchPanel = function( options ) {
		var opts = $.extend( {}, $.fn.SearchPanel.defaults, options );

		var is_collapsed = true;

		var layouts_array = null;

		var related_view_controller = null;

		var $this = this;

		var tab;

		var select_tab_index = 0;

		var select_tab = null;

		var last_select_tab_index = 0;

		var last_select_tab = null;

		var trigger_change_event = true;

		var hidedAdvTab = false;

//		Global.addCss( 'global/widgets/search_panel/SearchPanel.css' );

		this.isAdvTabVisible = function() {
			return !hidedAdvTab;
		};

		this.isCollapsed = function() {
			return is_collapsed;
		};

		this.hideAdvSearchPanel = function() {
			$( tab ).find( 'li[aria-controls=adv_search]' ).remove();
			hidedAdvTab = true;
		};

		this.setSearchFlag = function( filter ) {
			var basic_tab = this.find( 'a[href=\'#basic_search\']' );
			var adv_tab = this.find( 'a[href=\'#adv_search\']' );

			basic_tab.removeClass( 'active-label' );
			adv_tab.removeClass( 'active-label' );

			var hasFilter = false;
			for ( var key in filter ) {
				if ( key === 'country' && filter[key].value == TTUUID.not_exist_id ) {
					continue;
				}
				//For Documents view
				if ( key === 'template' && filter[key].value == false ) {
					continue;
				}
				// For Authorizations views
				if ( key === 'hierarchy_level' && ( filter[key].value == 1 || filter[key].value.value == 1 ) ) {
					continue;
				}
				hasFilter = true;
			}

			if ( hasFilter ) {
				$( this ).find( '.search-flag' ).remove();
				if ( select_tab_index === 0 || hidedAdvTab ) {
					basic_tab.addClass( 'active-label' );
					basic_tab.html( $.i18n._( 'BASIC SEARCH' ) + '<img title=\'' + $.i18n._( 'Search is currently active' ) + '\' src=\'' + Global.getRealImagePath( 'css/global/widgets/ribbon/icons/alert-16x16.png' ) + '\' class=\'search-flag\'> </img>' );
				} else {
					adv_tab.addClass( 'active-label' );
					adv_tab.html( $.i18n._( 'ADVANCED SEARCH' ) + '<img title=\'' + $.i18n._( 'Search is currently active' ) + '\' src=\'' + Global.getRealImagePath( 'css/global/widgets/ribbon/icons/alert-16x16.png' ) + '\' class=\'search-flag\'> </img>' );
				}
			} else {
				$( this ).find( '.search-flag' ).remove();
			}

		};

		//Don't trgiiger tab event in some case. Like first set filter to search panel
		this.setSelectTabIndex = function( val, triggerEvent ) {

			if ( select_tab_index === val ) {
				return;
			}

			if ( Global.isSet( triggerEvent ) ) {
				trigger_change_event = triggerEvent;
			} else {
				trigger_change_event = true;
			}

			$( tab ).tabs( 'option', 'active', val );
		};

		this.getLastSelectTabIndex = function() {
			return last_select_tab_index;
		};

		this.getLastSelectTabId = function() {

			if ( !last_select_tab ) {
				return 'basic_search';
			}

			return $( last_select_tab.tab ).attr( 'ref' );
		};

		this.getSelectTabIndex = function() {
			return select_tab_index;
		};

		this.getLayoutsArray = function() {
			return layouts_array;
		};

		function setGridSize() {
			if ( related_view_controller.grid ) {
				related_view_controller.setGridSize();
				related_view_controller.setGridColumnsWidth();

				if ( related_view_controller.column_selector ) { //This doesn't exist on Attendance -> TimeSheet, Basic Search. JS exception: TypeError: related_view_controller.column_selector is null
					related_view_controller.column_selector.setGridColumnsWidths(); //It seems that browser optimize out scrollBar calculations until the DOM is actually visible, so need to do this at the last moment.
				}
			}
		}

		//Set Select Layout combobox
		this.setLayoutsArray = function( val ) {
			layouts_array = val;
			var layout_selector = $( this ).find( '#layout_selector' );
			var layout_selector_div = $( this ).find( '.layout-selector-div' );

			$( layout_selector ).empty();

			if ( layouts_array && layouts_array.length > 0 ) {
				var len = layouts_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = layouts_array[i];
					$( layout_selector ).append( '<option value="' + item.id + '">' + item.name + '</option>' );
				}

				$( $( layout_selector ).find( 'option' ) ).filter( function() {

					//Saved layout id should always be number
					return $( this ).attr( 'value' ) == related_view_controller.select_layout.id;
				} ).prop( 'selected', true ).attr( 'selected', true );

				$( layout_selector_div ).css( 'display', 'block' );

			} else {
				$( layout_selector_div ).css( 'display', 'none' );
			}

		};

		this.setReleatedViewController = function( val ) {
			related_view_controller = val;
		};

		//For multiple items like .xxx could contains a few widgets.
		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			var basic_tab = $( this ).find( 'a[href=\'#basic_search\']' );
			var adv_tab = $( this ).find( 'a[href=\'#adv_search\']' );
			var layout_tab = $( this ).find( 'a[href=\'#saved_layout\']' );

			var current_view_label = $( this ).find( '.current-view-label' );

			basic_tab.html( $.i18n._( 'BASIC SEARCH' ) );
			adv_tab.html( $.i18n._( 'ADVANCED SEARCH' ) );
			layout_tab.html( $.i18n._( 'SAVED SEARCH & LAYOUT' ) );
			current_view_label.html( $.i18n._( 'Current View' ) + ':' );

			tab = $( this ).find( '.search-panel-tab-bar' );

			var collapseBtn = $( this ).find( '#collapseBtn' );

			var tabDiv = $( this ).find( '.search-panel-tab-outside' );

			var tabContentDiv = $( this ).find( '.search-panel-tab' );

			var layout_selector = $( this ).find( '#layout_selector' );

			var column_selector = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );

			var searchButtonDiv = $( this ).find( '.search-btn-div' );

			var refresh_btn = $( this ).find( '#refreshBtn' );

			refresh_btn.bind( 'click', function() {
				refresh_btn.addClass( 'button-rotate' );
				related_view_controller.search();
				Global.triggerAnalyticsNavigationOther( 'click:refresh_data', 'click', related_view_controller.viewId );
			} );

			$( searchButtonDiv ).css( 'display', 'none' );
			var searchBtn = $( searchButtonDiv ).find( '#searchBtn' );
			var clearSearchBtn = $( searchButtonDiv ).find( '#clearSearchBtn' );

			searchBtn.text( $.i18n._( 'Search' ) );
			clearSearchBtn.text( $.i18n._( 'Clear Search' ) );

			searchBtn.click( function() {
				//Delay 100 to make sure awesomebox values are set in select value
				setTimeout( function() {
					related_view_controller.onSearch();
				}, 100 );

			} );

			clearSearchBtn.click( function() {
				related_view_controller.onClearSearch();
			} );

			related_view_controller = o.viewController;

			$( layout_selector ).on( 'change', function() {
				related_view_controller.layout_changed = true;

				var selectId = $( layout_selector ).find( 'option:selected' ).attr( 'value' );
				var len = layouts_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = layouts_array[i];

					if ( item.id == selectId ) {
						related_view_controller.select_layout = item;
						related_view_controller.setSelectLayout();
						related_view_controller.search();
						break;
					}

				}
				Global.triggerAnalyticsNavigationOther( 'searchpanel:layout_change', 'click', related_view_controller.viewId );
			} );

			// Switch and expand search panel on tab changes.
			$( tab ).tabs( { 'activate': onMenuSelect } );

			// Expand search panel on an already active tab click.
			$( tab ).find( 'li' ).click( function( e ) {
				if ( is_collapsed ) {
					onCollapseBtnClick();
				}
			} );

			function onMenuSelect( e, ui ) {
				last_select_tab_index = select_tab_index;

				last_select_tab = select_tab;

				select_tab_index = ui.newTab.index();

				select_tab = ui;

				if ( trigger_change_event ) {

					$this.trigger( 'searchTabSelect', [e, ui.newPanel] );
				} else {
					trigger_change_event = true;
				}

				if ( is_collapsed ) {
					onCollapseBtnClick();
				}

				setGridSize();

				//delayCaller would cause "flashing" when switching between Basic/Advanced/Saved Search tabs.
				// var delayCaller = setInterval( function() {
				// 	if ( related_view_controller.grid ) {
				// 		clearInterval( delayCaller );
				// 		setGridSize();
				// 	}
				// }, 1 );

				Global.triggerAnalyticsTabs( e, ui );
			}

			$( collapseBtn ).click( onCollapseBtnClick );

			function onCollapseBtnClick() {
				if ( is_collapsed ) {
					is_collapsed = false;
					$( collapseBtn ).removeClass( 'expend-btn' );

					$( tabDiv ).removeClass( 'search-panel-tab-outside-collapse' );
					$( tabContentDiv ).removeClass( 'search-panel-tab-collapse' );

					$( searchButtonDiv ).css( 'display', 'block' );

				} else {
					is_collapsed = true;
					$( collapseBtn ).addClass( 'expend-btn' );
					$( tabDiv ).addClass( 'search-panel-tab-outside-collapse' );
					$( tabContentDiv ).addClass( 'search-panel-tab-collapse' );

					$( searchButtonDiv ).css( 'display', 'none' );
				}

				setGridSize();
			}

			// function resizeGrid() {
			// 	if (!interval) {
			// 		var interval = setInterval(function () {
			// 			if ( related_view_controller.grid ) {
			// 				clearInterval(interval);
			// 				var offset = $('.search-panel').outerHeight() + $('.search-btn-div').outerHeight() + $('.total-number-div').outerHeight() + $('.grid-top-border').outerHeight() + $('.grid-bottom-border').outerHeight() + $('.search-panel-tab-bar').outerHeight() + $('.copyright-container').outerHeight();
			// 				var height = parseInt($('#contentContainer').height() - offset);
			// 				if ( height != related_view_controller.grid.getGridHeight() ) {
			// 					related_view_controller.grid.setGridHeight(height);
			// 					$('.grid-div').height('auto');
			// 				}
			// 			}
			// 		}, 100 );
			// 	}
			// }

		} );

		return this;

	};

	$.fn.SearchPanel.defaults = {};

} )( jQuery );