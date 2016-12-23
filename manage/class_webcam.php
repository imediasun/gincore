<?php

class Products_webcam
{

    private $all_configs;
    private $conf;

    function __construct($all_configs)
    {
        $this->all_configs = $all_configs;

        $this->conf = array(
            'canvas_width' => 320,
            'canvas_height' => 240,
        );
    }

    public function set_conf($key, $value)
    {
        $this->conf[$key] = $value;
    }

    public function gen_html_body()
    {
        return
            '<div id="takefoto">
                <div class="wc-item">
                  <video id="webvideo" width="' . $this->conf['canvas_width'] . '" height="' . $this->conf['canvas_height'] . '" autoplay="autoplay" ></video>
                </div>

                <div class="item" style="display:none">
                  <span> canvas </span>
                  <canvas id="webcanvas" width="' . $this->conf['canvas_width'] . '" height="' . $this->conf['canvas_height'] . '" ></canvas>
                </div>
            </div>

            <div class="lastfoto">
                <div class="preview-lastfoto"></div>
            </div>';
    }

    public function gen_html_wrapper()
    {

        $out = '
            <!-- Modal -->
            <div id="myModal" class="modal modal-wide hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="myModalLabel">Создание фото для товара <span class="h4-goods"></span></h4>
              </div>
              <div class="modal-body">
              
                
                <div id="takefoto">
                    <div class="wc-item">
                      <video id="video" width="1920" height="1440" autoplay="autoplay" ></video>
                    </div>
                    <button type="button" class="btn btn-primary btn-capture" data-loading-text="Фотографирование...">Сфотографировать</button>

                    <div class="item" style="display:none">
                      <span> canvas </span>
                      <canvas id="canvas" width="1920" height="1440" ></canvas>
                    </div>
                </div>
                
                <div class="lastfoto">
//                    <div class="preview-lastfoto"></div>

                </div>
                
                <div class="preview-lastfoto">
                    <div class="preview-lastfoto-img"></div>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>
                    <button class="btn btn-revert">Загрузить еще</button>
                </div>

              </div>
              <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>
                <button class="btn btn-revert">Повторить</button>
                <button class="btn btn-primary" id="btn-upload-and-crop">Загрузить и прикрепить</button>
              </div>
            </div>
        ';

        $out .= '';

        return $out;
    }

    public function upload_image($base64dataUrl, $src_w, $src_h, $src_x, $src_y, $object_id)
    {
        $jpeg_quality = 90;
        $coef = 2.6666666;

        $dst_w = round($src_w * $coef);
        $dst_h = round($src_h * $coef);

        $new_img_path = $this->all_configs['sitepath'] . $this->all_configs['configs']['orders-images-path'] . $object_id . '/';
        $new_img_prefix = $this->all_configs['siteprefix'] . $this->all_configs['configs']['orders-images-path'] . $object_id . '/';

        if (!is_dir($new_img_path)) {
            if (!@mkdir($new_img_path, 0770, true)) {
                $error = error_get_last();
                return array('state' => false, 'msg' => 'Нет доступа к директории', 'error' => $error['message']);
            }
        }

        $new_img_name = $object_id . '-img-' . time() .  rand(0, 100) . '.jpg';

        $base64 = str_replace('data:image/png;base64,', '', $base64dataUrl);

        $imgstring = base64_decode($base64);

        try {
            $image = imagecreatefromstring($imgstring);
            $dst_r = imagecreatetruecolor($dst_w, $dst_h);
            imagecopyresampled($dst_r, $image, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
            imagejpeg($dst_r, $new_img_path . $new_img_name, $jpeg_quality);
        } catch (Exception $e) {
            return array('state' => false, 'msg' => 'Произошла ошибка при сохранении');
        }

        return array(
            'state' => true,
            'imgpath' => $new_img_path,
            'imgprefix' => $new_img_prefix,
            'imgname' => $new_img_name,
        );
    }

}