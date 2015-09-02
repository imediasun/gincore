<?php

// генерим шаблоны и скрипты для видео

function gen_video_block($video){ // $youtube_videos=null использовалось для встраивания видоса в левую колонку
    global $prefix, $template_vars;
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $video, $matches );
    
//    style="background-image: url(\'http://img.youtube.com/vi/' . $matches[1] . '/mqdefault.jpg\');"
    
    $video_block_preview = '<div class="video_block">
                                <div class="video-word"><i></i>'.$template_vars['l_video_block_title'].'</div>
                                <div data-rel="' . $matches[1] . '"'
                                    .' class="thumb_video on_load_popup"'
                                    .' data-content="show_video"'
                                    .' style="background-image: url(\''.$prefix.'images/flayer_video.jpg\');"'
                                    .'>
                                        <img alt="video" src="//img.youtube.com/vi/' . $matches[1] . '/default.jpg">
                                </div>
                            </div>';
    $video_block_show = '
        <div class="show_video sm_content">
            <div class="top">
                <div class="sm_close"></div>
            </div>
            <div class="bottom">
                <div id="show_video"></div>
            </div>
        </div>
        <div id="blackout"></div>';
    
    $video_scripts = "
        <script type='text/javascript'>
        jQuery(document).ready(function($) {
            jQuery.fn.center = function () {
                var _this = jQuery(this);
                this.css({
                    marginTop: _this.outerHeight() / -2,
                    marginLeft: _this.outerWidth() / -2
                });
                return this;
            };
            jQuery('#blackout').click(function(e) {
                jQuery(this).hide();
                jQuery('.sm_content').hide();

                var content = jQuery('div.wishlist_no_auth');

                if ( content.length > 0 ) {
                    content.removeClass('wishlist_no_auth');
                    jQuery('input.wishlist').attr('checked', false);
                }

                if (jQuery('#show_video').length > 0)
                    jQuery('#show_video').html('');

            });
            jQuery('.thumb_video').click(function () {
                var video = jQuery(this).data('rel');
                var video_width = 480;//Math.round(jQuery(document).width() / 2);
                var video_height = 390;//video_width - 90;

                //$('#show_video').html('<iframe width=\"'+video_width+'\" height=\"'+video_height+'\" src=\"//www.youtube.com/v/'+video+'&autoplay=1\" frameborder=\"0\" allowfullscreen></iframe>');
                $('#show_video').html('<object width=\"'+video_width+'\" height=\"'+video_height+'\" data=\"//www.youtube.com/v/'+video+'&autoplay=1\" frameborder=\"0\" type=\"application/x-shockwave-flash\"><param name=\"src\" value=\"http://www.youtube.com/v/'+video+'\" /><param value=\"true\" name=\"allowFullScreen\" /></object>');
            });
            jQuery('.sm_close').click(function(e) {
                var content = jQuery(this).parents('.sm_content');
                jQuery('.sm_content').hide();
                if(content.hasClass('wishlist_no_auth')){
                    content.removeClass('wishlist_no_auth');
                    jQuery('#auth_pass_field, #submit_auth').show();
                    jQuery('#save_auth').hide();
                    jQuery('input.wishlist').attr('checked', false);
                }
                jQuery('#blackout').hide();

                if (jQuery('#show_video').length > 0)
                    jQuery('#show_video').html('');

            });
            jQuery('.on_load_popup').click(function(e) {
                jQuery('.error-popup').html('');
                jQuery('.sm_content').hide();
                var rel = jQuery(this).attr('data-content'),
                    el = jQuery('.' + rel),
                    doc_h = jQuery(window).height();

                if(!rel) rel = jQuery(this).attr('data');

                el.show();
                el.children('.bottom').css({
                    maxHeight: doc_h - el.children('.top').outerHeight() - 100
                });
                el.center();

                jQuery('#blackout').show();
                return false;
            });
        });
        </script>
        ";
    
    $video_block_show = '';
    $video_scripts = ''; // moved to html_template_h_default.html
    return $video_block_preview.$video_block_show.$video_scripts;
}

