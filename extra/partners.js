var partners = (function(){
    
    return {
        init: function(){
            $('.question > .title').on('click', function(){
                var $answer = $(this).siblings('.answer'),
                    $question = $answer.parent();
                $answer.stop(true).slideToggle(200,function(){
                    if($answer.is(':visible')){
                        $question.addClass('active');
                    }else{
                        $question.removeClass('active');
                    }
                    $(window).resize();
                });
            });
        }
    };
    
})();
$(function(){
    partners.init();
});