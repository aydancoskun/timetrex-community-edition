import { Base } from '@/model/Base';

export class RibbonSubMenuNavItem extends Base {
	constructor( options = {} ) {
		_.defaults( options, {
			label: null,
			id: null,
			nav: null
		} );
		super( options );

		this.get( 'nav' ).get( 'items' ).push( this );
	}

}
