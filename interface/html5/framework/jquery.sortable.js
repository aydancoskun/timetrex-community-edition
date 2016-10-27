/*
 * HTML5 Sortable jQuery Plugin
 * http://farhadi.ir/projects/html5sortable
 * 
 * Copyright 2012, Ali Farhadi
 * Released under the MIT license.
 */
(function( $ ) {
	var dragging, placeholders = $();
	$.fn.sortable = function( options ) {
		var method = String( options );
		options = $.extend( {
			connectWith: false
		}, options );
		return this.each( function() {
			if ( /^enable|disable|destroy$/.test( method ) ) {
				var items = $( this ).children( $( this ).data( 'items' ) ).attr( 'draggable', method == 'enable' );
				if ( method == 'destroy' ) {
					items.add( this ).removeData( 'connectWith items' )
						.off( 'dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s' );
				}
				return;
			}
			var isHandle, index, items = $( this ).children( options.items );
			var placeholder = $( '<' + (/^ul|ol$/i.test( this.tagName ) ? 'li' : 'div') + ' class="sortable-placeholder">' );
			items.find( options.handle ).mousedown( function() {
				isHandle = true;
			} ).mouseup( function() {
				isHandle = false;
			} );
			$( this ).data( 'items', options.items );
			placeholders = placeholders.add( placeholder );
			if ( options.connectWith ) {
				$( options.connectWith ).add( this ).data( 'connectWith', options.connectWith );
			}
			items.attr( 'draggable', 'true' ).on( 'dragstart.h5s', function( e ) {
				if ( options.handle && !isHandle ) {
					return false;
				}
				isHandle = false;
				var dt = e.originalEvent.dataTransfer;
				dt.effectAllowed = 'move';
				dt.setData( 'Text', 'dummy' );
				index = (dragging = $( this )).addClass( 'sortable-dragging' ).index();

			} ).on( 'dragend.h5s', function() {
				dragging.removeClass( 'sortable-dragging' ).show();
				placeholders.detach();
				//if ( index != dragging.index() ) {
				//items.parent().trigger( 'sortupdate', {item: dragging} );
				//}
				items.parent().trigger( 'sortupdate', {item: dragging} );
				dragging = null;
			} ).not( 'a[href], img' ).on( 'selectstart.h5s', function() {
				this.dragDrop && this.dragDrop();
				return false;
			} ).end().add( [this, placeholder] ).on( 'dragover.h5s dragenter.h5s drop.h5s', function( e ) {

				if ( !items.is( dragging ) && options.connectWith !== $( dragging ).parent().data( 'connectWith' ) ) {
					return true;
				}

				if ( e.type == 'drop' ) {
					e.stopPropagation();
					placeholders.filter( ':visible' ).after( dragging );
					$( '.dashlet-cover--display-red' ).removeClass( 'dashlet-cover--display-red' );
					$( '.dashlet-cover--display-green' ).removeClass( 'dashlet-cover--display-green' );
					return false;
				}
				if ( e.type == 'dragover' ) {
					var target = $( e.currentTarget );
					var direction = 'LEFT';
					if ( target.attr( 'class' ).indexOf('dashlet-container') >= 0) {
						$( '.dashlet-cover--display-red' ).removeClass( 'dashlet-cover--display-red' );
						$( '.dashlet-cover--display-green' ).removeClass( 'dashlet-cover--display-green' );
						var mouseOffset = e.originalEvent.pageX - target.offset().left;
						if ( mouseOffset > target.width() / 2 ) {
							direction = 'RIGHT';
							if ( $( placeholder ).index() == index ) {
								target.find( '.dashlet-right-cover' ).addClass( 'dashlet-cover--display-red' );
							} else {
								target.find( '.dashlet-right-cover' ).addClass( 'dashlet-cover--display-green' );
							}
						} else {
							direction = 'LEFT';
							if ( $( placeholder ).index() == index + 1 ) {
								target.find( '.dashlet-left-cover' ).addClass( 'dashlet-cover--display-red' );
							} else {
								target.find( '.dashlet-left-cover' ).addClass( 'dashlet-cover--display-green' );
							}

						}
					}
				}
				e.preventDefault();
				e.originalEvent.dataTransfer.dropEffect = 'move';
				if ( items.is( this ) ) {
					if ( options.forcePlaceholderSize ) {
						placeholder.height( dragging.outerHeight() );
					}
					dragging.hide();
					if ( direction === 'LEFT' ) {
						$( this )['before']( placeholder );
					} else {
						$( this )['after']( placeholder );
					}

					placeholders.not( placeholder ).detach();
				} else if ( !placeholders.is( this ) && !$( this ).children( options.items ).length ) {
					placeholders.detach();
					$( this ).append( placeholder );
				}
				return false;
			} );
		} );
	};
})( jQuery );
