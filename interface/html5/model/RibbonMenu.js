import { Base } from '@/model/Base';

export class RibbonMenu extends Base {
	constructor( options = {} ) {
		_.defaults( options, {
			label: null,
			id: null,
			sub_menu_groups: null,
			permission_result: true
		} );
		super( options );
	}

	getSubMenuGroups() {
		var sub_menu_group = this.get( 'sub_menu_groups' );
		return sub_menu_group.sort( Global.compareMenuItems );
	}
}
