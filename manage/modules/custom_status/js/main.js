var master = (function($){
    var randomColorCalls = 0, colors = ['008000', '000000', 'ee82ee' ,'8b4512'];

    function getRandomColor() {
        var letters = '0123456789abcdef'.split('');
        var color = '#';
        if (randomColorCalls < colors.length) {
            color += colors[randomColorCalls];
        } else {
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
        }
        randomColorCalls += 1;
        return color;
    }

    function init_colorpickers(){
        $('.colorpicker.colorpicker-element').colorpicker('destroy');
        $('.colorpicker-auto').colorpicker();
    }

    function count_course(val){
        $('.course_value').text(val);
        $('.course_value_return').text((1 / val).toFixed(2).replace('.00',''));
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
                            if($control.hasClass('colorpicker')) {
                              var color = getRandomColor();
                              $control.val(color);
                              $clone.find('.show-color').first().css('background-color', color);
                            }
                            if($control.hasClass('branch-phone')) {
                                $control.val($('input[name=phone]').val());
                            }
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

            var $curr_selects = $('.check-checkboxes');
            $curr_selects.change(function(){
                var $this = $(this),
                    $selected = $this.find(':selected'),
                    name = $this.attr('name');
                $('.'+name).html($selected.data('symbol'));
                var currency_1 = $selected.val(),
                    currency_2 = $curr_selects.filter('[name='+(name == 'orders_currency' ? 'contractors_currency' : 'orders_currency')+']').val();
                if(currency_1 && currency_2){
                   $('#course_input').removeClass('hidden');
                   if(currency_1 == currency_2){
                       count_course(1);
                       $('#course').val(1).attr('disabled', true);
                   }else{
                       $('#course').attr('disabled', false);
                   }
                }else{
                   $('#course_input').addClass('hidden');
                }
            });

            $('input[name=course]').on('keyup input', function(){
                var val = +$(this).val();
                count_course(val);
            });

            $('input[name=phone]').on('keyup', function() {
                var $branchPhone = $('.branch-phone').first();
                $branchPhone.val($(this).val());
            });

        }
    };

})(jQuery);
$(function(){
    master.init();
});