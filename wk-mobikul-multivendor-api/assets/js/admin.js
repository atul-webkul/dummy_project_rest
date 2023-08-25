"use strict";

var mkwc = jQuery.noConflict();

mkwc(document).ready(function(){
    var banner_image = document.getElementsByClassName('mkwc-banner-img');
    var banner_span  = document.getElementsByClassName('mkwc-close-icon');

    if( banner_image.length ){
      var src = banner_image[0].src;
      removeCloseBtn( src );
    }

    if ( mkwc("table.banners #the-list").length ) {
      mkwc("table.banners #the-list").sortable({
        cursor:'move',
        update:function(a, e){

        }});
      mkwc("#the-list").disableSelection();
    }

    if ( mkwc(".mkwc_upload").length ) {
      mkwc('.mkwc_upload').on( 'click', function (e) {
        var bannerUpload;
        e.preventDefault();

        var bannerUpload = wp.media( {
          title: mkwcObject.keywords['banner'],
          button: {
            text: mkwcObject.keywords['uploadText'],
          },
          multiple: false
        } ).on( 'select', function () {
          var attachments = bannerUpload.state().get( 'selection' ).first().toJSON();
          mkwc('.mkwc-banner-img').attr( 'src', attachments.url );
          mkwc('#mkwc-banner-image').val( attachments.id );
          removeCloseBtn( attachments.url );
        } ).open();
      });
    }

    if ( mkwc(".mkwc-close-icon").length ) {
      mkwc('.mkwc-close-icon').on( 'click', function (e) {
        var placeholder = mkwc('.mkwc-close-icon').data('src');
        mkwc('.mkwc-banner-img').attr( 'src', placeholder );
        mkwc('#mkwc-banner-image').val('');
        removeCloseBtn( placeholder );
      });
    }

    if( mkwc("table.add-banner #mkwc-banner-type").length ) {
      mkwc("#mkwc-banner-procat").select2();
      mkwc("#mkwc-banner-type").on( "change", function(){
        var mkwc_banner_procat = mkwc("#mkwc-banner-procat");
        mkwc.ajax({
          url: mkwcObject.mkwcAjaxUrl,
          type: 'POST',
          data: {
            action: 'mkwc_select_banner_type',
            type: mkwc( this ).val(),
            selected: mkwc_banner_procat.data( 'selected' )
          },
          success: function( result ){
            if( result == 'image_only'){
              mkwc_banner_procat.closest('tr').addClass('display-none');
            } else {
              if( mkwc_banner_procat.closest('tr').hasClass('display-none')){
                mkwc_banner_procat.closest('tr').removeClass('display-none');
                mkwc("#mkwc-banner-procat").select2();          
              }
              mkwc_banner_procat.removeAttr( 'disabled' );
              mkwc_banner_procat.empty().append( result.trim() );
            }
          }
        });
      });
    }

    if( mkwc("table.add-carousels #mkwc-carousel-type").length ) {
      mkwc("#mkwc-carousel-procat").select2();
      mkwc("#mkwc-carousel-type").on( "change", function(){
        var mkwc_banner_procat = mkwc("#mkwc-carousel-procat");
        mkwc.ajax({
          url: mkwcObject.mkwcAjaxUrl,
          type: 'POST',
          data: {
            action: 'mkwc_select_carousels_type',
            type: mkwc( this ).val(),
            selected: mkwc_banner_procat.data( 'selected' )
          },
          success: function( result ){
            if( result == 'images_only'){
              mkwc_banner_procat.closest('tr').addClass('display-none');
            } else {
              if( mkwc_banner_procat.closest('tr').hasClass('display-none')){
                mkwc_banner_procat.closest('tr').removeClass('display-none');
                mkwc("#mkwc-carousel-procat").select2();          
              }
              mkwc_banner_procat.removeAttr( 'disabled' );
              mkwc_banner_procat.empty().append( result.trim() );
            }
          }
        });
      });
    }

    if( mkwc(".external-links").length ) {
      mkwc('#add-more-external-links').on('click', function ( evt ) {
        evt.preventDefault();
        var data = {}
        var linkCount = mkwc(this).data('link-count');
        data.linkCount = linkCount + 1;
        mkwc(this).data('link-count', data.linkCount);
        var linkRow = wp.template('wk_api_external_link_row');
        mkwc('table.external-links tbody').append(linkRow(data));
      });

      mkwc(document).on('click', '.remove-link-row', function () {
        var linkCount = mkwc('#add-more-external-links').data('link-count');
        linkCount = linkCount - 1;
        mkwc('#add-more-external-links').data('link-count', linkCount);
        mkwc(this).parent().parent('tr').remove()
      });
    }

    if( mkwc(".term-icon-wrap").length ) {
				// Only show the "remove image" button when needed
				if ( ! mkwc( '#product_cat_icon_id' ).val() ) {
					mkwc( '.mkwc_remove_icon' ).hide();
				}

				// Uploading files
				var file_frame;

				mkwc( document ).on( 'click', '.mkwc_upload_icon', function( event ) {

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: mkwcObject.keywords['icon'],
						button: {
							text: mkwcObject.keywords['uploadText']
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
						var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

						mkwc( '#product_cat_icon_id' ).val( attachment.id );
						mkwc( '#product_cat_icon' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
						mkwc( '.mkwc_remove_icon' ).show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				mkwc( document ).on( 'click', '.mkwc_remove_icon', function() {
					mkwc( '#product_cat_icon' ).find( 'img' ).attr( 'src', mkwcObject.placeholder );
					mkwc( '#product_cat_icon_id' ).val( '' );
					mkwc( '.mkwc_remove_icon' ).hide();
					return false;
				});

				mkwc( document ).ajaxComplete( function( event, request, options ) {
					if ( request && 4 === request.readyState && 200 === request.status
						&& options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

						var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
						if ( ! res || res.errors ) {
							return;
						}
						// Clear Thumbnail fields on submit
						mkwc( '#product_cat_icon' ).find( 'img' ).attr( 'src', mkwcObject.placeholder );
						mkwc( '#product_cat_icon_id' ).val( '' );
						mkwc( '.mkwc_remove_icon' ).hide();
						// Clear Display type field on submit
						mkwc( '#display_type' ).val( '' );
						return;
					}
				} );
    }

    if( mkwc(".term-images-wrap").length ) {
      // Uploading files
      var file_frames;
      var multiple_img = true;
      var single_img   = false;
      var replace_img = '';
      var img_this = '';
      mkwc( document ).on( 'click', '.mkwc_upload_images, .image_remove img', function( event ) {

        event.preventDefault();
  
        if( mkwc(this).is('img') ){
          single_img   = true;
          multiple_img = false; 
          replace_img  = mkwc(this).attr("data-id");
          img_this     = mkwc(this);
        }
        // If the media frame already exists, reopen it.
        if ( file_frames ) {
          file_frames.open();
          return;
        }
        // Create the media frame.
        file_frames = wp.media.frames.downloadable_file = wp.media({
          title: mkwcObject.keywords['icon'],
          button: {
            text: mkwcObject.keywords['uploadText']
          },
          multiple: multiple_img
        });

        // When an image is selected, run a callback.
        file_frames.on( 'select', function() {
          var img_ids = [];
          var img = '';
          var main_div = '';
          if( single_img ){
            var attachment           = file_frames.state().get( 'selection' ).first().toJSON();
						var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;
            mkwc('#product_cat_images').append('<span class="image_remove"><span class="image_remove_x" data-id="'+ attachment.id +'">x</span></span>');
              img = mkwc( document.createElement('img') );
              img.attr('src', attachment_thumbnail.url);
              img.attr('width', '60');
              img.attr('height', '60');
              img.attr('style', 'margin:5px; border:1px solid #ddd;');
              img.attr('data-id', attachment.id);
              img.appendTo('.image_remove:last-child');
              var image_arr = mkwc('#product_cat_images_id').val();
              image_arr = image_arr.split(",");
              var single_index = image_arr.indexOf(replace_img);
              if (single_index > -1) {
                image_arr.splice(single_index, 1);
              }
              mkwc('#product_cat_images_id').val( image_arr );
              img_ids.push(attachment.id);
              img_this.parent().remove();
          } else {
            var attachment = file_frames.state().get( 'selection' ).toJSON();
            attachment.forEach(element => {
              var attachment_thumbnail = element.sizes.thumbnail || element.sizes.full;
              mkwc('#product_cat_images').append('<span class="image_remove"><span class="image_remove_x" data-id="'+ element.id +'">x</span></span>');
              img = mkwc( document.createElement('img') );
              img.attr('src', attachment_thumbnail.url);
              img.attr('width', '60');
              img.attr('height', '60');
              img.attr('style', 'margin:5px; border:1px solid #ddd;');
              img.attr('data-id', element.id);
              img.appendTo('.image_remove:last-child');
              img_ids.push(element.id);
            });
            mkwc('#placeholder-img').remove();
          }
          if( mkwc("#product_cat_images_id").val().length != 0 ){
            mkwc("#product_cat_images_id").val(function() {
                return this.value + ',' + img_ids;
            });
          } else {
            mkwc( '#product_cat_images_id' ).val( img_ids );
          }
        });

        // Finally, open the modal.
        file_frames.open();
      });
    }
  
    mkwc( document ).on( 'click ', function( i ){
      var pl_img = '';
      if( i.target.matches('.image_remove_x') ){
          var images_arr = mkwc('#product_cat_images_id').val();
          images_arr = images_arr.split(",");
          var image_index = mkwc( i.target ).attr("data-id");
          var index = images_arr.indexOf(image_index);
          if (index > -1) {
            images_arr.splice(index, 1);
          }
          mkwc('#product_cat_images_id').val( images_arr );
          mkwc( i.target ).parent().remove();
          if( mkwc('.image_remove').length == 0 ){
            pl_img = mkwc( document.createElement('img') );
            pl_img.attr('id','placeholder-img');
            pl_img.attr('src', mkwcObject.placeholder);
            pl_img.attr('width', '60');
            pl_img.attr('height', '60');
            pl_img.appendTo('#product_cat_images');
          }
      }
    });

    function removeCloseBtn( str ){
      if( str.indexOf('placeholder') > -1 ){
        if( banner_span.length ){
          banner_span[0].style.display = 'none';
        }
      } else {
        if( banner_span.length ){
          banner_span[0].style.display = 'block';
        }
      }
    }
});
