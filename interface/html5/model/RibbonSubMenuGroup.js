import { Base } from '@/model/Base';

export class RibbonSubMenuGroup extends Base {
	constructor( options = {} ) {
		_.defaults( options, {
			label: null,
			id: null,
			ribbon_menu: null,
			sub_menus: null
		} );

		super( options );

		this.attributes.add_order = this.get( 'ribbon_menu' ).get( 'sub_menu_groups' ).length + 1; // gives us a default sort order if one isn't specified. (used as a tie breaker)
		this.get( 'ribbon_menu' ).get( 'sub_menu_groups' ).push( this );
	}

	getSubMenus() {
		var sub_menu = this.get( 'sub_menus' );
		return sub_menu.sort( Global.compareMenuItems );
	}
}
