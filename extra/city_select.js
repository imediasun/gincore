function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else
        var expires = "";
    document.cookie = name + "=" + value + expires + "; path="+prefix;
}
$(function(){
    $(document).on('click', '.city_select li:not(.active)', function(){
        window.location = $(this).data('city');
    });
    
    $('#first_city_select_another').click(function(){
        $('#first_city_select_confirm').addClass('hidden');
        $('#first_city_select_change').removeClass('hidden');
    });
    
    $('.current_city_confirm').click(function(){
        var $selected_item = $('input[name=city_confirm_select]:checked');
        if($selected_item.hasClass('current_city')){
            createCookie('user_city', $selected_item.val(), 2);
        }else{
            window.location = $selected_item.data('site');
        }
        $('#first_city_select').hide();
    });
});