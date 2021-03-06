<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Печать</title>

    <script type="text/javascript" src="<?= $this->all_configs['prefix']; ?>js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="<?= $this->all_configs['prefix']; ?>js/bootstrap.js"></script>
    <script type="text/javascript" src="<?= $this->all_configs['prefix']; ?>js/summernote.js"></script>

    <?= isset($_GET['act']) && in_array($_GET['act'], array('label', 'location')) ? '' : '
            <link rel="stylesheet" href="' . $this->all_configs['prefix'] . 'css/summernote.css" />
        ' ?>
    <link rel="stylesheet" href="<?= $this->all_configs['prefix'] ?>css/bootstrap.min.css"/>
    <link rel="stylesheet" href="<?= $this->all_configs['prefix'] ?>css/font-awesome.css">

    <style>
        /* print begin */
        .printer_preview {
            position: absolute;
            right: 20px;
            width: 300px;
            top: 25%;
        }

        .printer_preview p {
            margin-top: 20px;
            line-height: 20px;
            font-size: 16px;
            text-align: center;
        }

        .printer_preview p > i.fa {
            color: indianred;
            font-size: 1.3em;
            margin-right: 10px
        }

        .printer_preview img {
            width: 100%;
        }

        @media print {
            .label-box {
                page-break-before: always;
                page-break-inside: avoid;
            }

            /*.label-box:first-child {
                page-break-before: avoid;
            }*/
        }

        @media print {
            .unprint {
                display: none;
            }
        }

        /* print end */

        body, html {
            font-size: 11px;
            line-height: 12px;
            margin: 0;
            padding: 0;
        }

        li {
            line-height: 14px;
        }

        th, td {
            padding: 2px 4px !important;
        }

        p {
            margin: 0 0 5px;
        }

        /* normalize end */

        /* redactor begin */
        #redactor .template_key, .note-editor .template_value {
            display: none;
        }

        /* redactor end */

        /* label begin */
        .label-box {
            /*height: 2.5cm;*/
            overflow: hidden;
            /*width: 4cm;*/
            display: block;
            margin: 3px 0 1px 2px;
        }
        
        .label-box-title {
            margin-bottom: 3px;
          max-height: 36px;
          overflow: hidden;
        }

        .label-box-code {
            text-align: center;
        }

        .label-box-order {
            text-align: center;
        }

        /* label end */
    </style>

    <script type="text/javascript">
        $(function () {
            $('#lang_change').change(function () {
                window.location = $(this).parent().attr('action') + '?' + $(this).parent().serialize();
            });

            $('#saveRedactor').prop('disabled', true);
            $('#editRedactor').click(function () {
                $(this).prop('disabled', true);
                $('#redactor').hide();
                $('#print_template').show();
                $('#saveRedactor').prop('disabled', false);
                $('#print').prop('disabled', true);
            });

            $('#saveRedactor').click(function () {
                var _this = this,
                    content = tinyMCE.activeEditor.getContent({format : 'raw'});
                $(_this).prop('disabled', true);
                // save content if you need

                $.ajax({
                    type: 'POST',
                    url: window.location.search + '&ajax=editor',
                    data: {html: content},
                    cache: false,
                    success: function (msg) {
                        if (msg) {
                            if (msg['state'] == false && msg['msg']) {
                                alert(msg['msg']);
                            }
                            if (msg['state'] == true) {
                                // destroy editor
                                //$('#redactor').destroy();
                                window.location.reload();
                            }
                        }
                        $(_this).prop('disabled', false);
                        $('#print').prop('disabled', false);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(xhr.responseText, 1);
                    }
                });
            });
            $('#restore').click(function () {
                var _this = this;
                if(confirm('<?= l('Вы уверены? Все внесенные изменения будут сброшены.') ?>')) {
                $(_this).prop('disabled', true);
                // save content if you need
                $.ajax({
                    type: 'POST',
                    url: window.location.search + '&ajax=restore',
                    data: {html: $('#print_template').code()},
                    cache: false,
                    success: function (msg) {
                        if (msg) {
                            if (msg['state'] == false && msg['msg']) {
                                alert(msg['msg']);
                            }
                            if (msg['state'] == true) {
                                window.location.reload();
                            }
                        }
                        $(_this).prop('disabled', false);
                        $('#print').prop('disabled', false);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(xhr.responseText, 1);
                    }
                });
                }
            });

            $('#print').click(function () {
                window.print();
            });
        });

    </script>
    <script type="text/javascript" src="<?= $this->all_configs['prefix']; ?>js/tinymce/tinymce.min.js"></script>
    <script>
        $(document).ready(function () {
            tinymce.init({
                width: '99%',
                height: 400,
                selector: '.tinymce',
                theme: 'modern',
                file_browser_callback: function(field_name, url, type, win) {
                    if(type=='image') $('#my_form input').click();
                },
                plugins: [
                    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars code fullscreen',
                    'insertdatetime nonbreaking save table contextmenu directionality',
                    'template paste textcolor colorpicker textpattern imagetools'
                ],
                toolbar1: 'insertfile undo redo | styleselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
                toolbar2: "bold italic | forecolor backcolor |  fontselect |  fontsizeselect",
                fontsize_formats: "4pt 6pt 8pt 10pt 12pt 14pt 18pt 24pt 36pt"
            });
        });
    </script>

</head>
<body><?= $print_html; ?></body>
</html>
