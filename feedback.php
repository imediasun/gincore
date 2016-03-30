<html>
<head>
    <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <script>
        (function () {
            var s = document.createElement("script");
            s.type = "text/javascript";
            s.async = true;
            s.src = "//localhost:8080/widget.php?ajax=&w=status&jquery="+("jQuery" in window?1:0);
            if(document.getElementsByClassName('gcw-fixed').length > 0) {
                document.getElementsByClassName('gcw-fixed')[0].appendChild(s);
            } else {
                var fixed = document.createElement('div');
                fixed.className = 'gcw-fixed';
                fixed.appendChild(s);
                document.getElementsByTagName("head")[0].appendChild(fixed);
            }
        })();
    </script>
    <script>
        (function () {
            var s = document.createElement("script");
            s.type = "text/javascript";
            s.async = true;
            s.src = "//localhost:8080/widget.php?w=feedback&jquery="+(typeof jQuery != 'undefined'?1:0);
            document.getElementsByTagName("head")[0].appendChild(s);
        })();
    </script>
    <style>
        .gcw_title {
            background-color: white !important;
        }
    </style>
</head>
<body>

</body>
</html>