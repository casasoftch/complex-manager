var $ = jQuery;

var cm = {};
cm.media = {};


cm.media.documentUpload = {

    get: function() {
        //console.log('get');
        //return wp.media.view.settings.post.featuredImageId;
    },

    set: function( id ) {
        //console.log('set');
        /*var settings = wp.media.view.settings;

        settings.post.featuredImageId = id;

        wp.media.post( 'set-post-thumbnail', {
            json:         true,
            post_id:      settings.post.id,
            thumbnail_id: settings.post.featuredImageId,
            _wpnonce:     settings.post.nonce
        }).done( function( html ) {
            $( '.inside', '#postimagediv' ).html( html );
        });*/
    },

    frame: function() {
        if ( this._frame ) {
            return this._frame;
        }

        this._frame = wp.media({
            multiple    :   false,
            title : 'title herer',
            //state: 'featured-image',
            //states: [ new wp.media.controller.FeaturedImage() , new wp.media.controller.EditImage() ]
        });

        this._frame.on( 'toolbar:create:featured-image', function( toolbar ) {
            /**
             * @this wp.media.view.MediaFrame.Select
             */
            this.createSelectToolbar( toolbar, {
                text: i18n.button
            });
        }, this._frame );

        this._frame.on( 'content:render:edit-image', function() {
            var selection = this.state('featured-image').get('selection'),
                view = new wp.media.view.EditImage( { model: selection.single(), controller: this } ).render();

            this.content.set( view );

            // after bringing in the frame, load the actual editor via an ajax call
            view.loadEditor();

        }, this._frame );

        this._frame.state('featured-image').on( 'select', this.select );
        return this._frame;
    },

    select: function() {
        var selection = this.get('selection').single();

        if ( ! wp.media.view.settings.post.featuredImageId ) {
            return;
        }

        cm.media.documentUpload.set( selection ? selection.id : -1 );
    },
    init: function() {
        $('#complexmanager_unit_box').on( 'click', '#complexmanager_unit_document-button', function( event ) {
            event.preventDefault();
            // Stop propagation to prevent thickbox from activating.
            event.stopPropagation();

            cm.media.documentUpload.frame().open();
        }).on( 'click', '#complexmanager_unit_document-removebutton', function() {
            //console.log('remove');
        });
    }
};


$( cm.media.documentUpload.init );

//post type overlay upload
jQuery(document).ready(function($){
    var _custom_media = true,
    _orig_send_attachment = wp.media.editor.send.attachment;

    $('#complexmanager_unit_box .button, #complexmanager_unit_graphic_box .button').click(function(e) {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        var id = button.attr('id').replace('_button', '');
        _custom_media = true;
        wp.media.editor.send.attachment = function(props, attachment){
            if ( _custom_media ) {
                $("#"+id).val(attachment.url);
            } else {
                return _orig_send_attachment.apply( this, [props, attachment] );
            }
        };

        wp.media.editor.open(button);
        return false;
    });

    $('.add_media').on('click', function(){
        _custom_media = false;
    });
});


jQuery(document).ready(function($){
    //$('#complexmanager_unit_graphic_hover_color').wpColorPicker();
    $('#complexmanager_unit_graphic_poly').hide().canvasAreaDraw();
})(jQuery);




