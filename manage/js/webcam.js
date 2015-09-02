window.onload = function() {

    var canvas;
    var video;
    var context;

    var videoStreamUrl = false;
    var coordinates = {};
    var base64dataUrl;

    var jcrop_api;

    function updateCoords(c) {
        coordinates = c;
    }

    function checkCoords()
    {
        if (parseInt(coordinates.w))
            return true;
        alert('Please select a crop region then press submit.');
        return false;
    }


    function upload_img(_this) {
        var url = prefix + module + "/ajax?act=webcam_upload" +
            '&w=' + coordinates.w +
            '&h=' + coordinates.h +
            '&x=' + coordinates.x +
            '&y=' + coordinates.y +
            '&order_id=' + $(_this).data('order_id');
        $.post(url, {base64dataUrl: base64dataUrl})
            .done(function(data) {
                if (data) {
                    if (data['state'] == false) {
                        if (data['msg']) {
                            alert(data['msg']);
                        }
                    }
                    if (data['state'] == true && data['imgprefix'] && data['imgname'] && data['imgid']) {
                        $('.order-fotos').append('<div class="order-foto">' +
                            '<i class="icon-remove cursor-pointer" onclick="remove_order_image(this, ' + data['imgid'] + ')"></i>' +
                            '<img data-toggle="lightbox" href="#order-image-' + data['imgid'] + '" src="' + data['imgprefix'] + data['imgname'] + '" />' +
                            '<div id="order-image-' + data['imgid'] + '" class="lightbox hide fade"  tabindex="-1" role="dialog" aria-hidden="true">' +
                            '<div class="lightbox-content"><img src="' + data['imgprefix'] + data['imgname'] + '"></div></div></div>');
                    }
                }
                $(_this).button('reset');

            });
    }

    function show_modal_webcam(_this) {
        revert_to_crop();

        canvas = document.getElementById('webcanvas');
        video = document.getElementById('webvideo');
        context = canvas.getContext('2d');

        //if (!videoStreamUrl) {

            // navigator.getUserMedia  и   window.URL.createObjectURL (смутные времена браузерных противоречий 2012 :) )
            navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            window.URL.createObjectURL = window.URL.createObjectURL || window.URL.webkitCreateObjectURL || window.URL.mozCreateObjectURL || window.URL.msCreateObjectURL;
            console.log('request', navigator.getUserMedia);

            // запрашиваем разрешение на доступ к поточному видео камеры
            navigator.getUserMedia({video: true}, function(stream) {
                // разрешение от пользователя получено
                // скрываем подсказку
                //allow.style.display = "none";
                // получаем url поточного видео
                videoStreamUrl = window.URL.createObjectURL(stream);
                // устанавливаем как источник для  video
                video.src = videoStreamUrl;

                $(_this).hide();
                $('.btn-capture').button('reset');
                $('.btn-capture').show();
            }, function() {
                console.log('что-то не так с видеостримом :P');
            });
        //}

        if (videoStreamUrl) {
            $(_this).hide();
            $('.btn-capture').button('reset');
            $('.btn-capture').show();
        }
    }

    function close_modal_webcam() {
        //$(".modal-backdrop").remove();
    }

    function prepare_to_crop(_this) {
        $(_this).hide();
        $('#takefoto, .preview-lastfoto').hide();
        $('.lastfoto').show();
    }

    function prepare_to_crop_footer() {
        //$('#myModal .modal-footer').show();
        $('.btn-show-webcam').show();
        $('#btn-upload-and-crop').show();
    }

    function revert_to_crop() {
        $('.lastfoto').hide();
        //$('.lastfoto, #myModal .modal-footer').hide();
        $('.preview-lastfoto').hide();
        $('#btn-upload-and-crop').hide();
        $('#takefoto').show();
    }

    $(".btn-revert").click(function() {
        revert_to_crop();
    });


    $(".btn-show-webcam").live('click', function() {
        show_modal_webcam(this);
    });

    $("#btn-upload-and-crop").live('click', function() {
        var _this = this;
        $(_this).button('loading');

        if (checkCoords()) {
            upload_img(_this);
            //loading...
            //$('#myModal').modal('hide');
        } else {
            $(_this).button('reset');
        }

    });

    var captureMe = function(_this) {
        if (!videoStreamUrl) {
            alert('Откройте вебкамеру и разрешите в верху окна');
            //alert('То-ли вы не нажали "разрешить" в верху окна, то-ли что-то не так с вашим видео стримом');
        } else {
            $(_this).button('loading');
            // переворачиваем канвас зеркально по горизонтали
            //context.translate(canvas.width, 0);
            //context.scale(-1, 1);
            // отрисовываем на канвасе текущий кадр видео
            //console.log('video.width ' + video.width);
            context.drawImage(video, 0, 0, video.width, video.height);
            // получаем data: url изображения на c canvas
            base64dataUrl = canvas.toDataURL('image/png');
            context.setTransform(1, 0, 0, 1, 0, 0); // убираем все кастомные трансформации canvas
            // на этом этапе можно спокойно отправить  base64dataUrl на сервер и сохранить его там как файл (ну или типа того)



            // но мы добавим эти тестовые снимки в наш пример:
            var img = new Image();
            img.src = base64dataUrl;
            img.width = 720;
            img.height = 540;

            $(".lastfoto").html(img);
            $('.lastfoto img').Jcrop({
                //onRelease: releaseCheck
                onChange: updateCoords,
                onSelect: updateCoords
            }, function() {
                jcrop_api = this;
                jcrop_api.animateTo([30, 30, 320, 240]);
                prepare_to_crop_footer();
            });

            prepare_to_crop(_this);
        }


    };

    $(".btn-capture").live('click', function() {
        //prepare_to_crop();
        captureMe(this);
    });


};

function remove_order_image(_this, order_image_id) {
    if (!$(_this).prop('disabled')) {
        $(_this).prop('disabled', true);

        $.post(prefix + module + "/ajax?act=remove-order-image", {order_image_id: order_image_id})
            .done(function (data) {
                $(_this).parents('.order-foto').remove();
            });
    }
}