/*
 * $License$
 */
EmbeddedMessage = {
    message_control_api: null,
    /**
     * Initializes embedded messages with a call to EmbeddedMessage.init()
     * Requires that initUI be called when the editviewui is built
     *
     * @param item_id (current_edit_record.id)
     * @param object_type (50 for requests)
     */
    init: function ( item_id, object_type, view_object, edit_view, edit_view_tab, edit_view_ui_dic, callback ) {
        var args = {};
        args.filter_data = {};
        args.filter_data.object_type_id = object_type;
        args.filter_data.object_id = item_id;
        var read_ids = [];
        this.message_control_api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();

        var $this = this;
        this.message_control_api['getEmbeddedMessage']( args, {
            onResult: function ( res ) {
                // Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in interface/html5/#!m=RequestAuthorization&id=1306 line 1547
                if ( !edit_view || !edit_view_ui_dic['from'] ) {
                    return;
                }

                var data = res.getResult();
                if ( Global.isArray(data) ) {
                    $(edit_view.find('.separate')).css('display', 'block');

                    view_object.messages = data;

                    var container = $('<div></div>');

                    for ( var key in data ) {

                        var currentItem = data[key];
                        /* jshint ignore:start */
                        if ( currentItem.status_id == 10 ) {
                            read_ids.push( currentItem.id );
                        }
                        /* jshint ignore:end */
                        /**
                         * This can be a little confusing to look at so here's the process:
                         * 1. Set the hidden fields' values
                         * 2. Clone the message template
                         * 3. Append the message templage to container
                         * 4. Append the contents of the the container variable to the visible form
                         */
                        var from = currentItem.from_first_name + ' ' + currentItem.from_last_name + ' @ ' + currentItem.updated_date;
                        edit_view_ui_dic['from'].setValue( from );
                        edit_view_ui_dic['subject'].setValue( currentItem.subject );
                        edit_view_ui_dic['body'].setValue( currentItem.body );
                        var cloneMessageControl = $(edit_view_tab.find('#tab_request').find('.edit-view-tab').find('.embedded-message-template')).clone();
                        cloneMessageControl.removeClass( 'embedded-message-template' );
                        cloneMessageControl.addClass( 'embedded-message-container' );
                        cloneMessageControl.css( 'display', 'block' );
                        cloneMessageControl.css( 'margin', '0px' );
                        cloneMessageControl.appendTo( container );
                    }

                    if ( read_ids.length > 0 ) {
                        $this.message_control_api['markRecipientMessageAsRead']( read_ids, {
                            onResult: function ( res ) {
                                //commented out as it is needed on the message screen, but not here and results in a big api call we'd rather avoid.
                                //$this.search( false );
                            }
                        });
                    }

                    $(edit_view_tab.find('#tab_request').find('.edit-view-tab').find('.embedded-message-column')).hide();
                    edit_view_tab.find('#tab_request').find('.edit-view-tab').find('.embedded-message-container').hide();
                    edit_view_tab.find('#tab_request').find('.edit-view-tab').find('.embedded-message-container').remove();
                    edit_view_tab.find('#tab_request').find('.edit-view-tab').append( container.html() );
                } else {
                    $(edit_view.find('.separate')).css('display', 'none');
                }

                callback();
            }
        });
    },

    /**
     * Requires a full width column with the class embedded-message-template
     *
     * @param view_object
     * @param tab_object
     */
    initUI: function ( view_object, tab_object ) {

        var separate_box = tab_object.find('.separate').css('display', 'none');

        // Messages title bar
        var form_item_input = Global.loadWidgetByName(FormItemType.SEPARATED_BOX);
        form_item_input.SeparatedBox({label: $.i18n._('Messages')});
        view_object.addEditFieldToColumn(null, form_item_input, separate_box);

        var column = tab_object.find( '.embedded-message-template' );

        // From
        form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
        form_item_input.TText( {field: 'from', selected_able: true}) ;
        view_object.addEditFieldToColumn( $.i18n._('From'), form_item_input, column, '' );

        // Subject
        form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
        form_item_input.TText( {field: 'subject', selected_able: true} );
        view_object.addEditFieldToColumn( $.i18n._('Subject'), form_item_input, column );

        // Body
        form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
        form_item_input.TText( {field: 'body', width: 600, height: 400, selected_able: true} );
        view_object.addEditFieldToColumn( $.i18n._('Body'), form_item_input, column, '', null, null, true );

        // Tab 0 second column end
        view_object.edit_view_tabs[0].push( column );
        column.css( 'display', 'none' );
        return;
    },

    /**
     * The record array must be an array containing a single record
     * The callback function must take the result object as an argument
     *
     * @param record_array
     * @param ignoreWarning
     * @param callback
     */
    reply: function ( record_array , ignoreWarning, callback ) {
        this.message_control_api['setMessageControl']( record_array, false, ignoreWarning, {
            onResult: function(result){
                if ( callback ) {
                    callback( result );
                }
            }
        });
    }
};