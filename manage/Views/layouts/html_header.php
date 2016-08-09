<?php
$autoload = $webRoot . '/../gincore/vendor/autoload.php';

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    $error = true;
}

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <title>{-txt-page_title}</title>
    <script type="text/javascript">var manage_lang = '{-html-manage_lang}';</script>

    <?php if (DEBUG || isset($error)): ?>
    <link type="text/css" rel="stylesheet" href="<?= $assetsDir ?>bootstrap/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="<?= $assetsDir ?>css/bootstrap-responsive.min.css">
    <link type="text/css" rel="stylesheet" href="<?= $assetsDir ?>css/bootstrap3-editable.css">
    <link type="text/css" rel="stylesheet" href="<?= $assetsDir ?>css/main.css?4">
    <link type="text/css" rel="stylesheet" href="<?= $assetsDir ?>css/daterangepicker-bs2.css?1">
    <link type="text/css" rel="stylesheet" href="<?= $assetsDir ?>css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" href="<?= $assetsDir ?>css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/loadingbar.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/jquery.Jcrop.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/bootstrap-lightbox.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/bootstrap-multiselect.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/bootstrap-switch.min.css"/>
    <link rel="stylesheet" href="<?= $assetsDir ?>css/jquery-ui.css"/>
        <!-- new admin -->
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/admin/animate.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/admin/helper.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/admin/metisMenu.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/admin/pe-icon-7-stroke.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>css/admin/style.css?6"/>

        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery-ui-1.9.0.custom.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>bootstrap/js/bootstrap.min.js?1"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.noty.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/parsley.js?1"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/noty.default.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.ui.datepicker-ru.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.ui.datepicker-en.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-maxlength.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-button.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.FormNavigate.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-multiselect.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap3-editable.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-maxlength.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootbox.js?1"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.clipboard.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.Jcrop.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/webcam.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-lightbox.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.plugin.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.countdown.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/moment-with-locales.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-datetimepicker.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/moment.js?1"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/daterangepicker.js?1"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.fullscreen-0.4.1.min.js?2"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.loadingbar.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.ba-bbq.js"></script>
        <script type="text/javascript" src="<?= $assetsDir ?>js/jquery.maskedinput.min.js"></script>
        <script src="<?= $assetsDir ?>js/admin/jquery.slimscroll.min.js"></script>
        <script src="<?= $assetsDir ?>js/admin/jquery.flot.js"></script>
        <script src="<?= $assetsDir ?>js/admin/jquery.flot.resize.js"></script>
        <script src="<?= $assetsDir ?>js/admin/jquery.flot.pie.js"></script>
        <script src="<?= $assetsDir ?>js/admin/curvedLines.js"></script>
        <script src="<?= $assetsDir ?>js/admin/index.js"></script>
        <script src="<?= $assetsDir ?>js/admin/metisMenu.min.js"></script>
        <script src="<?= $assetsDir ?>js/admin/icheck.min.js"></script>
        <script src="<?= $assetsDir ?>js/admin/jquery.peity.min.js"></script>
        <script src="<?= $assetsDir ?>js/admin/index(1).js"></script>
        <script src="<?= $assetsDir ?>js/admin/homer.js?1"></script>
        <script src="<?= $assetsDir ?>js/admin/charts.js"></script>
    <?php else: ?>
    <?php
    $js = new AssetCollection(array(
        new FileAsset("{$webRoot}/js/jquery-1.10.2.min.js"),
        new FileAsset("{$webRoot}/js/jquery-migrate-1.2.1.min.js"),
        new FileAsset("{$webRoot}/js/jquery-ui-1.9.0.custom.min.js"),
        new FileAsset("{$webRoot}/bootstrap/js/bootstrap.min.js"),
        new FileAsset("{$webRoot}/js/jquery.noty.js"),
        new FileAsset("{$webRoot}/js/parsley.js"),
        new FileAsset("{$webRoot}/js/noty.default.js"),
        new FileAsset("{$webRoot}/js/jquery.ui.datepicker-ru.js"),
        new FileAsset("{$webRoot}/js/jquery.ui.datepicker-en.js"),
        new FileAsset("{$webRoot}/js/bootstrap-maxlength.min.js"),
        new FileAsset("{$webRoot}/js/bootstrap-button.js"),
        new FileAsset("{$webRoot}/js/jquery.FormNavigate.js"),
        new FileAsset("{$webRoot}/js/bootstrap3-editable.min.js"),
        new FileAsset("{$webRoot}/js/bootstrap-maxlength.js"),
        new FileAsset("{$webRoot}/js/bootbox.js"),
        new FileAsset("{$webRoot}/js/jquery.clipboard.js"),
        new FileAsset("{$webRoot}/js/jquery.cookie.js"),
        new FileAsset("{$webRoot}/js/jquery.Jcrop.min.js"),
        new FileAsset("{$webRoot}/js/webcam.js"),
        new FileAsset("{$webRoot}/js/bootstrap-lightbox.min.js"),
        new FileAsset("{$webRoot}/js/jquery.plugin.js"),
        new FileAsset("{$webRoot}/js/jquery.countdown.js"),
        new FileAsset("{$webRoot}/js/moment-with-locales.min.js"),
        new FileAsset("{$webRoot}/js/bootstrap-datetimepicker.min.js"),
        new FileAsset("{$webRoot}/js/bootstrap-switch.min.js"),
        new FileAsset("{$webRoot}/js/moment.js"),
        new FileAsset("{$webRoot}/js/daterangepicker.js"),
        new FileAsset("{$webRoot}/js/jquery.fullscreen-0.4.1.min.js"),
        new FileAsset("{$webRoot}/js/jquery.loadingbar.js"),
        new FileAsset("{$webRoot}/js/jquery.ba-bbq.js"),
        new FileAsset("{$webRoot}/js/admin/jquery.slimscroll.min.js"),
        new FileAsset("{$webRoot}/js/admin/jquery.flot.js"),
        new FileAsset("{$webRoot}/js/admin/jquery.flot.resize.js"),
        new FileAsset("{$webRoot}/js/admin/jquery.flot.pie.js"),
        new FileAsset("{$webRoot}/js/admin/curvedLines.js"),
        new FileAsset("{$webRoot}/js/admin/index.js"),
        new FileAsset("{$webRoot}/js/admin/metisMenu.min.js"),
        new FileAsset("{$webRoot}/js/admin/icheck.min.js"),
        new FileAsset("{$webRoot}/js/admin/jquery.peity.min.js"),
        new FileAsset("{$webRoot}/js/admin/index(1).js"),
        new FileAsset("{$webRoot}/js/admin/homer.js"),
        new FileAsset("{$webRoot}/js/admin/charts.js"),
        new FileAsset("{$webRoot}/js/bootstrap-multiselect.js"),
    ));

    $css = new AssetCollection(array(
        new FileAsset("{$webRoot}/bootstrap/css/bootstrap.min.css"),
        new FileAsset("{$webRoot}/css/bootstrap-responsive.min.css"),
        new FileAsset("{$webRoot}/css/bootstrap3-editable.css"),
        new FileAsset("{$webRoot}/css/main.css"),
        new FileAsset("{$webRoot}/css/daterangepicker-bs2.css"),
        new FileAsset("{$webRoot}/css/bootstrap-datetimepicker.min.css"),
        new FileAsset("{$webRoot}/css/font-awesome.css"),
        new FileAsset("{$webRoot}/css/loadingbar.css"),
        new FileAsset("{$webRoot}/css/jquery.Jcrop.min.css"),
        new FileAsset("{$webRoot}/css/bootstrap-lightbox.min.css"),
        new FileAsset("{$webRoot}/css/bootstrap-multiselect.css"),
        new FileAsset("{$webRoot}/css/bootstrap-switch.min.css"),
        new FileAsset("{$webRoot}/css/jquery-ui.css"),
        new FileAsset("{$webRoot}/css/admin/animate.css"),
        new FileAsset("{$webRoot}/css/admin/helper.css"),
        new FileAsset("{$webRoot}/css/admin/metisMenu.css"),
        new FileAsset("{$webRoot}/css/admin/pe-icon-7-stroke.css"),
        new FileAsset("{$webRoot}/css/admin/style.css"),

    ));
    $js->setTargetPath('scripts.js');
    $css->setTargetPath('styles.css');
    $am = new AssetManager();
    $am->set('combined_js', $js);
    $am->set('combined_css', $css);
    $writer = new AssetWriter($webRoot . '/assets');
    try {
        $writer->writeManagerAssets($am);
    } catch (Exception $e) {
        print_r($e->getMessage());
    }
    ?>
        <script src="<?= $assetsDir ?>assets/scripts.js?10"></script>
    <link rel="stylesheet" type="text/css" href="<?= $assetsDir ?>assets/styles.css?10"/>
    <?php endif; ?>

    <script type="text/javascript">
        var prefix = '<?= $assetsDir ?>';
        var siteprefix = '{-txt-siteprefix}';
        var module = '{-txt-module}';
        var cur_course = 1;
        var formdata_original = true;
        var L = {-txt-manage_translates_js} ;
    </script>

    {-css-module}

    <script type="text/javascript" src="<?= $assetsDir ?>js/main.js?29"></script>
</head>
