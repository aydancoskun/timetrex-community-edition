import { Base } from '@/model/Base';

export class RibbonSubMenu extends Base {
	constructor( options = {} ) {
		_.defaults( options, {
			label: null,
			id: null,
			icon: null,
			group: null,
			visible: null,
			type: null,
			items: null, //For Nav Type
			permission: null,
			permission_result: true
		} );

		super( options );

		if ( !this.get( 'type' ) ) {
			this.set( 'type', RibbonSubMenuType.NORMAL );
		}

		this.set( 'icon', this.icon = Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + this.get( 'icon' ) ) );

		if ( this.get( 'permission_result' ) ) { //Only save maps for menus passed validation
			TopMenuManager.menus_quick_map[this.get( 'id' )] = this.get( 'group' ).get( 'ribbon_menu' ).get( 'id' );
			this.attributes.add_order = this.get( 'group' ).get( 'sub_menus' ).length + 1; // gives us a default sort order if one isn't specified. (used as a tie breaker)
			this.get( 'group' ).get( 'sub_menus' ).push( this );
		}
	}
}

export var RibbonSubMenuType = ( function() {
	var normal = '1';
	var nav = '2';

	return { NORMAL: normal, NAVIGATION: nav };
} )();
