$(function(){
    var $links = $('.links');
    $links.children('a').click(function(){
        $links.html('<img src="img/loading.gif" alt="instalation...">');
    });
});