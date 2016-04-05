<h2><?= $title ?></h2>
<?php if (!empty($widget)): ?>
    <pre>
        <?= htmlspecialchars(
            "<script>\n" .
            "    (function () {\n" .
            "        var s = document.createElement(\"script\");\n" .
            "            s.type = \"text/javascript\";\n" .
            "            s.async = true;\n" .
            "            s.src = \"//" . $_SERVER['HTTP_HOST'] . "/widget.php?ajax=&w=" . $widget . "&jquery=\"+(\"jQuery\" in window?1:0);\n" .
            "        document.getElementsByTagName(\"head\")[0].appendChild(s);\n" .
            "    })();\n" .
            "</script>"
        ) ?>
    </pre>
    <?php if($widget == 'feedback'): ?>
        <form action="<?= $this->all_configs['prefix'] ?>/widgets/set" method="POST">
            <fieldset>
                <div class="form-group">
                    <label><?= l('Отправлять клиентам смс с кодом'); ?></label>
                    <input type="checkbox" name="send_sms" <?= $sendSms == 'on'? 'checked': '' ?> />
                </div>
                <div class="form-group">
                    <label><?= l('Отправлять клиентам смс с кодом'); ?></label>
                    <input type="url" name="host" value="<?= !empty($host)? $host: '' ?>" />
                </div>
                <button class="btn btn-primary" type="submit"><?= l('Сохранить') ?></button>
            </fieldset>
        </form>
    <?php endif; ?>
<?php endif; ?>
