(function($) {
    "use strict";
    var selector = $('#expuls'),
    grid = selector.find('#expuls-photos-grid'),
    gridVideos = selector.find('#expuls-videos-grid'),
    gridSettings = {
        itemSelector: '.expuls-masonry-item',
        percentPosition: true,
        gutter: 20
    },
    photoOrientation = '',
    photoColor = '',
    photoKeyword = '',
    videoOrientation = '',
    videoSize = '',
    videoKeyword = '';

    /* Set active menu item */
    selector.find("#expuls-menu a").first().addClass('active');
    selector.find("#expuls-content div").first().addClass('active');

    /* Mobile Menu Button */
    selector.find("#expuls-mobile-btn").on("click", function (e) {
        e.preventDefault();
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $("#expuls-menu-wrap").css('display', 'none');
        } else {
            $(this).addClass('active');
            $("#expuls-menu-wrap").css('display', 'flex');
        }
    });

    /* Main Menu */
    selector.find("#expuls-menu a").on("click", function (e) {
        e.preventDefault();
        window.dispatchEvent(new Event('resize'));
        var target = $(this).data('target');
        if ($(this).hasClass('active')) {
            $("#" + target).addClass('active');
        } else {
            selector.find("#expuls-menu a").removeClass('active');
            $(this).addClass('active');
            selector.find(".expuls-page").removeClass('active');
            $("#" + target).addClass('active');
        }
    });

    /* PHOTOS */

    /* Grid */
    grid.imagesLoaded( function() {    
        grid.masonry(gridSettings);
        grid.css('visibility', 'visible');
        grid.css('opacity', 1);
        selector.find("#expuls-photo-loader").css('display', 'none');
    });

    /* Masonry item info animation */
    grid.on('click','.expuls-masonry-item-inner',function(){
        grid.find('.expuls-masonry-item').removeClass('active');
        $(this).parent().addClass('active');
    });

    grid.on('click','.expuls-cancel',function(e){
        e.preventDefault();
        $(this).parent().removeClass('active');
    });

    /* Keyword Field */
    selector.find('#expuls-photo-keyword').on('keyup input', function () {
        var val = $(this).val();
        if (val == '') {
            selector.find('#expuls-photo-orientation').val('');
            selector.find('#expuls-photo-color').val('');
            selector.find('#expuls-photo-orientation').prop('disabled', true);
            selector.find('#expuls-photo-color').prop('disabled', true);
        } else {
            selector.find('#expuls-photo-orientation').prop('disabled', false);
            selector.find('#expuls-photo-color').prop('disabled', false);
        }
    });

    /* Photo Search */
    selector.find('#expuls-photo-search').on('click', function () {
        selector.find("#expuls-photos-loadmore").data('page', 1);
        var orientation = selector.find('#expuls-photo-orientation').val();
        var color = selector.find('#expuls-photo-color').val();
        var keyword = selector.find('#expuls-photo-keyword').val();
        photoOrientation = orientation;
        photoColor = color;
        photoKeyword = keyword;
        var data = {
            'action': 'expulsPhotoSearch',
            'nonce': expulsParams.nonce,
            'orientation': orientation,
            'color': color,
            'keyword': keyword,
            'page': '1'
        };
        grid.find('.expuls-notice').remove();
        $.ajax({
            url : expulsParams.ajaxurl,
            data : data,
            type : 'POST',
            beforeSend: function ( xhr ) {
                selector.find("#expuls-photos-grid-notice").hide();
                selector.find("#expuls-photos-grid-notice").html('');
                selector.find("#expuls-photos-output").addClass('loading');
                selector.find("#expuls-photo-loader").css('display', 'flex');
            },
            success: function(data){
                if(data) {
                    if (data === '404') {
                        grid.masonry( 'remove', grid.find('.expuls-masonry-item') );
                        selector.find("#expuls-photos-grid-notice").html(expulsParams.nothing);
                        selector.find("#expuls-photos-grid-notice").show();
                        selector.find("#expuls-photos-loadmore").hide();
                    } else {
                        selector.find("#expuls-photos-loadmore").show();
                        grid.masonry( 'remove', grid.find('.expuls-masonry-item') );
                        var content = $( data );
                        grid.append(content).masonry( 'appended', content);
                        grid.imagesLoaded( function() {
                            grid.masonry(gridSettings);
                        });
                    }
                }
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    alert(jqXHR.responseText);
                }else{
                    alert(errorThrown);
                }
                selector.find("#expuls-photos-output").removeClass('loading');
                selector.find("#expuls-photo-loader").css('display', 'none');
           }
        }).done(function( response ) {
            grid.imagesLoaded( function() {
                selector.find("#expuls-photos-output").removeClass('loading');
                selector.find("#expuls-photo-loader").css('display', 'none');
            });
        });
    });

    /* Load More Photos */
    selector.find("#expuls-photos-loadmore").on("click", function () {
        var page = $(this).data('page');
        $(this).data('page', parseInt(page) + 1);

        var data = {
            'action': 'expulsPhotoSearch',
            'nonce': expulsParams.nonce,
            'orientation': photoOrientation,
            'color': photoColor,
            'keyword': photoKeyword,
            'page': parseInt(page) + 1
        };
        $.ajax({
            url : expulsParams.ajaxurl,
            data : data,
            type : 'POST',
            beforeSend: function ( xhr ) {
                selector.find("#expuls-photos-grid-notice").hide();
                selector.find("#expuls-photos-grid-notice").html('');
                selector.find("#expuls-photos-loadmore").prop('disabled', true);
                selector.find("#expuls-photos-loadmore").html(expulsParams.loading);
            },
            success: function(data){
                if(data) {
                    if (data === '404') {
                        selector.find("#expuls-photos-loadmore").hide();
                    } else {
                        selector.find("#expuls-photos-loadmore").show();
                        var content = $( data );
                        grid.append(content).masonry( 'appended', content);
                        grid.imagesLoaded( function() {
                            grid.masonry(gridSettings);
                        });
                    }
                }
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    alert(jqXHR.responseText);
                }else{
                    alert(errorThrown);
                }
                selector.find("#expuls-photos-loadmore").html(expulsParams.loadmore);
                selector.find("#expuls-photos-loadmore").prop('disabled', false);
            }
        }).done(function( response ) {
            selector.find("#expuls-photos-loadmore").html(expulsParams.loadmore);
            selector.find("#expuls-photos-loadmore").prop('disabled', false);
        });
    });

    /* Import photo */
    grid.on('click','.expuls-import-img',function(){
        var btn = $(this);
        var selected = $(this).parent().find('.expuls-select').val();
        var filename = new Date().getTime();
        var url = $(this).data(selected);
        var original = $(this).data('original');
        var data = {
            'action': 'expulsPhotoImport',
            'nonce': expulsParams.nonce,
            'filename': filename,
            'original': original,
            'url': url
        };
        $.ajax({
            url : expulsParams.ajaxurl,
            data : data,
            type : 'POST',
            beforeSend: function ( xhr ) {
                btn.addClass('importing');
                btn.html('<div class="expuls-btn-loader"></div>');
            },
            success: function(data){
                btn.html('<span class="dashicons dashicons-yes"></span>');
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    alert(jqXHR.responseText);
                }else{
                    alert(errorThrown);
                }
                btn.removeClass('importing');
                btn.html('<span class="dashicons dashicons-download"></span>');
           }
        }).done(function( response ) {
            setTimeout(function() {
                btn.removeClass('importing');
                btn.html('<span class="dashicons dashicons-download"></span>');
            }, 4000);
        });
    });

    /* VIDEOS */

    /* Grid */
    gridVideos.imagesLoaded( function() {    
        gridVideos.masonry(gridSettings);
        gridVideos.css('visibility', 'visible');
        gridVideos.css('opacity', 1);
        selector.find("#expuls-video-loader").css('display', 'none');
    });

    /* Masonry item info animation */
    gridVideos.on('click','.expuls-masonry-item-inner',function(){
        gridVideos.find('.expuls-masonry-item').removeClass('active');
        $(this).parent().addClass('active');
    });

    gridVideos.on('click','.expuls-cancel',function(e){
        e.preventDefault();
        $(this).parent().removeClass('active');
    });

    /* Keyword Field */
    selector.find('#expuls-video-keyword').on('keyup input', function () {
        var val = $(this).val();
        if (val == '') {
            selector.find('#expuls-video-orientation').val('');
            selector.find('#expuls-video-size').val('');
            selector.find('#expuls-video-orientation').prop('disabled', true);
            selector.find('#expuls-video-size').prop('disabled', true);
        } else {
            selector.find('#expuls-video-orientation').prop('disabled', false);
            selector.find('#expuls-video-size').prop('disabled', false);
        }
    });

    /* Video Search */
    selector.find('#expuls-video-search').on('click', function () {
        selector.find("#expuls-videos-loadmore").data('page', 1);
        var orientation = selector.find('#expuls-video-orientation').val();
        var size = selector.find('#expuls-video-size').val();
        var keyword = selector.find('#expuls-video-keyword').val();
        videoOrientation = orientation;
        videoSize = size;
        videoKeyword = keyword;
        var data = {
            'action': 'expulsVideoSearch',
            'nonce': expulsParams.nonce,
            'orientation': orientation,
            'size': size,
            'keyword': keyword,
            'page': 1
        };
        gridVideos.find('.expuls-notice').remove();
        $.ajax({
            url : expulsParams.ajaxurl,
            data : data,
            type : 'POST',
            beforeSend: function ( xhr ) {
                selector.find("#expuls-videos-grid-notice").hide();
                selector.find("#expuls-videos-grid-notice").html('');
                selector.find("#expuls-videos-output").addClass('loading');
                selector.find("#expuls-video-loader").css('display', 'flex');
            },
            success: function(data){
                if(data) {
                    if (data === '404') {
                        gridVideos.masonry( 'remove', gridVideos.find('.expuls-masonry-item') );
                        selector.find("#expuls-videos-grid-notice").html(expulsParams.nothing);
                        selector.find("#expuls-videos-grid-notice").show();
                        selector.find("#expuls-videos-loadmore").hide();
                    } else {
                        selector.find("#expuls-videos-loadmore").show();
                        gridVideos.masonry( 'remove', gridVideos.find('.expuls-masonry-item') );
                        var content = $( data );
                        gridVideos.append(content).masonry( 'appended', content);
                        gridVideos.imagesLoaded( function() {
                            gridVideos.masonry(gridSettings);
                        });
                    }
                }
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    alert(jqXHR.responseText);
                }else{
                    alert(errorThrown);
                }
                selector.find("#expuls-videos-output").removeClass('loading');
                selector.find("#expuls-video-loader").css('display', 'none');
           }
        }).done(function( response ) {
            gridVideos.imagesLoaded( function() {
                selector.find("#expuls-videos-output").removeClass('loading');
                selector.find("#expuls-video-loader").css('display', 'none');
            });
        });
    });

    /* Load More Videos */
    selector.find("#expuls-videos-loadmore").on("click", function () {
        var page = $(this).data('page');
        $(this).data('page', parseInt(page) + 1);

        var data = {
            'action': 'expulsVideoSearch',
            'nonce': expulsParams.nonce,
            'orientation': videoOrientation,
            'size': videoSize,
            'keyword': videoKeyword,
            'page': parseInt(page) + 1
        };
        $.ajax({
            url : expulsParams.ajaxurl,
            data : data,
            type : 'POST',
            beforeSend: function ( xhr ) {
                selector.find("#expuls-videos-grid-notice").hide();
                selector.find("#expuls-videos-grid-notice").html('');
                selector.find("#expuls-videos-loadmore").prop('disabled', true);
                selector.find("#expuls-videos-loadmore").html(expulsParams.loading);
            },
            success: function(data){
                if(data) {
                    if (data === '404') {
                        selector.find("#expuls-videos-loadmore").hide();
                    } else {
                        selector.find("#expuls-photos-loadmore").show();
                        var content = $( data );
                        gridVideos.append(content).masonry( 'appended', content);
                        gridVideos.imagesLoaded( function() {
                            gridVideos.masonry(gridSettings);
                        });
                    }
                }
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    alert(jqXHR.responseText);
                }else{
                    alert(errorThrown);
                }
                selector.find("#expuls-videos-loadmore").prop('disabled', false);
                selector.find("#expuls-videos-loadmore").html(expulsParams.loadmore);
            }
        }).done(function( response ) {
            selector.find("#expuls-videos-loadmore").prop('disabled', false);
            selector.find("#expuls-videos-loadmore").html(expulsParams.loadmore);
        });
    });

    /* Import video */
    gridVideos.on('click','.expuls-import-video',function(){
        var btn = $(this);
        var url = $(this).parent().find('.expuls-select').val();
        var type = $(this).parent().find('.expuls-select option:selected').attr('data-type');
        var filename = new Date().getTime();
        var data = {
            'action': 'expulsVideoImport',
            'nonce': expulsParams.nonce,
            'filename': filename,
            'type': type,
            'url': url
        };
        $.ajax({
            url : expulsParams.ajaxurl,
            data : data,
            type : 'POST',
            beforeSend: function ( xhr ) {
                btn.addClass('importing');
                btn.html('<div class="expuls-btn-loader"></div>');
            },
            success: function(data){
                btn.html('<span class="dashicons dashicons-yes"></span>');
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    alert(jqXHR.responseText);
                }else{
                    alert(errorThrown);
                }
                btn.removeClass('importing');
                btn.html('<span class="dashicons dashicons-download"></span>');
           }
        }).done(function( response ) {
            console.log(response);
            setTimeout(function() {
                btn.removeClass('importing');
                btn.html('<span class="dashicons dashicons-download"></span>');
            }, 4000);
        });
    });

})(jQuery);