$(function() {
    $("#sortable").sortable();
    $("#sortable").disableSelection();
    
    
    $('#save_sorting').click(function(){
        var i = 0;
        $('#sortable li').each(function(){
            $(this).children('input').val(i);
            i++;
        });
    });
    
});