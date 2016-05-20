<h2><?= $title ?></h2>
<?php if (!empty($widget)): ?>
    <pre>
        <?= htmlspecialchars(
            '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">' .
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
    <?php if ($widget == 'feedback'): ?>
        <form action="<?= $this->all_configs['prefix'] ?>/widgets/set" method="POST">
            <fieldset>
                <input type="hidden" name="feedback-form" value="1"/>
                <div class="col-sm-6">
                    <table class="table table-no-border">
                        <tr>
                            <td>
                                <label><?= l('Отправлять клиентам смс с кодом'); ?></label>
                            </td>
                            <td>
                                <input type="checkbox" name="send_sms" <?= $sendSms == 'on' ? 'checked' : '' ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?= l('Сайт на котором будет установлен виджет'); ?></label>
                            </td>
                            <td>
                                <input type="url" name="host" value="<?= !empty($host) ? $host : '' ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?= l('Уведомлять о новых отзывах на почту'); ?></label>
                            </td>
                            <td>
                                <input type="url" name="send_email"
                                       value="<?= !empty($sendEmail) ? $sendEmail : '' ?>"/>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-sm-12">
                    <button class="btn btn-primary" type="submit"><?= l('Сохранить') ?></button>
                </div>
            </fieldset>
        </form>
    <?php endif; ?>
<?php endif; ?>
