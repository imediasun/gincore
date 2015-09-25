var master = (function($){
    
    function init_colorpickers(){
        $('.colorpicker.colorpicker-element').colorpicker('destroy');
        $('.colorpicker').colorpicker();
    }
    
    return {
        init: function(){
            init_colorpickers();
            
            $('.clone-elements').click(function(e){
                e.preventDefault();
                var $this = $(this),
                    $elements = $this.siblings($this.data('clone')),
                    $clone = $elements.eq(0).clone();
                $clone.find('.number').text($elements.length + 1 + '.');
                $clone.attr('data-id', $elements.length);
                $clone.find('input,select').each(function(){
                    var $control = $(this),
                        attrName = $control.attr('name');
                    $control.attr('name', attrName.replace('[0]', '['+$elements.length+']'));
                    switch($control[0].nodeName.toLowerCase()){
                        case 'input':
                            $control.val('').removeClass('colorpicker-element');
                        break;
                        case 'select':
                            $control.find('option:disabled').attr('selected', true);
                        break;
                    }
                });
                $this.before($clone);
                init_colorpickers();
            });
            
            $(document).on('keyup input', '.fill-select', function(){
                var $this = $(this),
                    id = $this.parents('[data-id]').attr('data-id'),
                    name = $.trim($this.val()),
                    select = $this.data('select');
                $(select).each(function(){
                    var $select = $(this),
                        $option = $select.find('option[value='+id+']');
                    if(!$option.length && name){
                        $select.append('<option value="'+id+'">'+name+'</option>');
                    }else{
                        if(name){
                            $option.text(name);
                        }else if($select.val() == id){
                            $select.find('option:disabled').attr('selected', true);
                            $option.remove();
                        }
                    }
                });
            });
            
            $('.toggle-currency-course').change(function(){
                var $this = $(this);
                $this.parents('.checkbox-with-course').find('.currencies-courses')
                                                      .toggleClass('hidden', !$this.is(':checked'));
            });
            
            var $check = $('.check-checkboxes'),
                $checkboxwithcourse = $('.checkbox-with-course'),
                $checkboxes = $checkboxwithcourse.find(':checkbox');
            $check.change(function(){
                $checkboxes.attr({
                    checked: false,
                    disabled: false
                }).change();
                $checkboxwithcourse.find('.currencies-courses').val('').attr('disabled', false);
                $check.each(function(){
                    var $this = $(this),
                        val = $this.val(),
                        $checkbox = $checkboxes.filter('[value='+val+']');
                    $checkbox.attr({
                        checked: true,
                        disabled: true
                    }).change();
                    if($this.data('main')){
                        $checkbox.parents('.checkbox-with-course').find('.currencies-courses')
                                    .val('1').attr('disabled', true);
                    }
                });
            });
        }
    };
    
})(jQuery);
$(function(){
    master.init();
});