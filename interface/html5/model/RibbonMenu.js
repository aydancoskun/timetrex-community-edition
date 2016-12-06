var RibbonMenu = Base.extend( {

	defaults: {
		label: null,
		id: null,
		sub_menu_groups: null,
		permission_result: true
	},

    getSubMenuGroups: function() {
        var sub_menu_group = this.get('sub_menu_groups');
        return sub_menu_group.sort(Global.compareMenuItems);
    }
} );