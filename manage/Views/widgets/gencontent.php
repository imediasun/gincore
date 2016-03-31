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
<?php endif; ?>
