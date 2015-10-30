
var multiselect_options = {
    buttonContainer: '<div class="btn-group multiselect-btn-group" />',
    nonSelectedText: 'Выбрать',
    enableFiltering: true,
    inheritClass: true,
    includeSelectAllOption: true,
    enableCaseInsensitiveFiltering: true,
    //dropRight: true,
    selectAllText: 'Отметить все',
    //dataprovider: [{label:1, value: 1}],
    maxHeight: 200,
    maxWidth: 330/*,
    label: function(element) {
        var str = element.text;
        //str.replace("-", "прошел")
        return $(element).html();
    }*/
};

/*function tags_input() {
    $('.tags-input').each(function() {
        var input = $(this);
        var options = {
            itemValue: 'value',
            itemText: 'text',
            trimValue: true,
            allowDuplicates: false,
            typeahead: {
                source: function(query) {
                    return $.post(prefix + "messages.php?act=tags", {table: input.data('table')});
                }
            }
        };
        if (input.data('maxtags')) {
            options.maxTags = input.data('maxtags');
        }

        input.tagsinput(options);

        var values = input.val();
        var texts = input.data('texts');

        if (values && texts) {
            values = values.split(',');
            texts = texts.split(',');
            for (var i = 0; i < values.length; i++) {
                input.tagsinput('add', {value: values[i], text: texts[i]});
            }
        }
    });
}*/

function remove_alarm(_this, id) {
    $(_this).button('loading').html('');

    $.ajax({
        url: prefix + 'messages.php?act=remove-alarm',
        type: 'POST',
        data: 'id=' + id,
        dataType: 'json',
        success: function (result) {
            if (result) {
                if (result['state'] == true) {
                    $(_this).parents('tr').remove();
                }
                if (result['state'] == false && result['msg']) {
                    alert(result['msg']);
                }
            }
            $(_this).button('reset');
        }
    });

    return false;
}

function add_alarm(_this) {

    $(_this).button('loading');

    $.ajax({
        url: prefix + 'messages.php?act=add-alarm',
        type: 'POST',
        data: $('form#add-alarm').serialize(),
        dataType: 'json',
        success: function (result) {

            if (result) {
                if (result['state'] == false && result['msg']) {
                    alert(result['msg']);
                }
                if (result['state'] == true) {
                    click_tab_hash();
                    close_alert_box();
                }
            }
            $(_this).button('reset');
            return false;
        }
    });
}


$(document).ready(function () {


    $(document).on('click', '.fullscreen', function() {
        $('.close-fullscreen-container').remove();
        var el = $(this).data('el') || 'body';
        $(el).fullscreen({toggleClass: 'fullscreen-container'});
        var btn = $('<div />')
            .addClass('close-fullscreen-container')
            .html('<i class="fa fa-close"></i>')
            .click(function() {
                $.fullscreen.exit();
                $('.close-fullscreen-container').remove();
            });
        $('.fullscreen-container').prepend(btn);
    });

    /*$('.tags-input').live('focusin', '.tags-input', function(e) {
        tags_input();
    });*/

    $('.cloneAndClear').live('click', function() {
        var $this = $(this);
        if($this.data('addon')){
            var $el = $this.parent().prev();
        }else if($this.data('clone_siblings')){
            var $el = $this.siblings($this.data('clone_siblings')).eq(0);
        }else{
            var $el = $this.prev();
        }
        var clone = $el.clone();
        if(clone.hasClass('clone_clear_val')){
            input = clone;
        }else{
            var input = clone.find('.clone_clear_val');
        }
        input.val('');
        if (input.hasClass('global-typeahead')) {
            var num = 1 * input.attr('data-select');
            var next = 1 + num;
            clone.find('.typeahead-value-serials' + num).attr('class', 'typeahead-value-serials' + next);
            input.attr('data-select', next);
            input.attr('data-input', 'serials' + next);
        }
        clone.find('.clone_clear_html').html('');
        $this.before(clone);
    });

    /*//form change
    $("input:not([type]), select, input[type=text], textarea, input[type='password'], " +
        "input[type='radio'], input[type='checkbox'], input[type='file']").ElementNavigate({

        message: "Вы сделали изменение и не сохранили. Выйти без сохранения?"
    });*/

    /*$('.copy-btn').live('click', function() {
        var el = $(this);

        el.clipboard({
            path: prefix + 'jquery.clipboard.swf',
            clickAfter: false,
            copy: $('#' + el.data('element') + ' option:selected').text(),
            afterCopy: function() {
                alert('Скопировано!');
                el.show();
            },
            beforeCopy: function() {
                el.hide();
            }
        });
    });*/

    $('.datetimepicker').live('click', function () {
        $(this).datetimepicker({
            locale: 'ru',
            defaultDate: $(this).val(),
            format: $(this).data('format').replace('dd', 'DD').replace('yyyy', 'YYYY').replace('hh', 'HH')
        });
        $(this).data('DateTimePicker').show();
    });

    $('.editable-click').live('click', function (event) {
        event.preventDefault();
        $(this).editable({
            success: function (response, newValue) {
                if (response && response.msg && response.status == 'error') {
                    alert(response.msg);
                } else {
                    $(this).val(newValue);
                    if ($(this).hasClass('editable-html')) {
                        $(this).html(newValue);
                    }
                    if (response && response.element_id && response.element_value) {
                        $('#' + response.element_id).html(response.element_value);
                    }
                }
            }
        });
        //$(this).click();
    });

    $('input.daterangepicker').live('focusin', '.daterangepicker', function(e) {
        $(this).daterangepicker({
            locale: {
                format: 'DD.MM.YYYY'
            },
            showDropdowns: true,
            autoApply: true
        });
    });
    $('input.daterangepicker_single').live('focusin', '.daterangepicker_single', function(e) {
        var $this = $(this),
            format = $this.data('format') ? $this.data('format') : 'DD.MM.YYYY';
        $(this).daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: format
            }
        });
    });

    $('input.show-length, textarea.show-length').live('focusin', '.show-length', function(e) {

        $(this).maxlength({
            alwaysShow: true,
            threshold: 0,
            warningClass: "label label-success",
            limitReachedClass: "label label-important",
            placement: 'top-right'
            //showEvent: 'ready'
        });
    });

    init_multiselect();

    $('.global-typeahead').live('focusin', '.global-typeahead', function(e) {

        //var auto_typeahead = false;
        var input_selector = $(this).data('input');
        var call_function = $(this).data('function');
        
        $('.global-typeahead').typeahead({
            items: 50,
            minLength: 1,
            source: function (query, process) {

                input_selector = $(this.$element).data('input');
                call_function = $(this.$element).data('function');
                var table = $(this.$element).data('table');
                var fix = $('.select-typeahead-' + $(this.$element).data('select')).val();

                return $.ajax({
                    url: prefix + 'messages.php',
                    type: 'POST',
                    data: {act: 'global-typeahead', query: query, table: table, fix: fix,
                        limit: this.options.items, object: arrequest()[2]},
                    dataType: 'json',
                    success: function (result) {

                        //auto_typeahead = false;

                        if (result) {
                            var resultList = result.map(function (item) {
                                var aItem = { original: item, id: item.id, name:
                                    item.title.replace(/\u00A0/g, " ").replace(/[\r\n]+/g, "\n")};

                                return JSON.stringify(aItem);
                            });

                            return process(resultList);
                        }

                        return false;
                    }
                });
            },

            matcher: function (obj) {
                var item = JSON.parse(obj);
                this.query = $.trim(this.query);
                return item.name.toLowerCase();
                //return ~item.name.toLowerCase().indexOf(this.query.toLowerCase())
            },

            /*sorter: function (items) {
                var beginswith = [], caseSensitive = [], caseInsensitive = [], item;
                while (aItem = items.shift()) {
                    var item = JSON.parse(aItem);
                    if (!item.name.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(JSON.stringify(item));
                    else if (~item.name.indexOf(this.query)) caseSensitive.push(JSON.stringify(item));
                    else caseInsensitive.push(JSON.stringify(item));
                }

                return beginswith.concat(caseSensitive, caseInsensitive)
            },*/

            highlighter: function (obj) {
                var item = JSON.parse(obj);
                var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
                return item.name.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                    return '<strong>' + match + '</strong>'
                })
            },

            updater: function (obj) {
                var item = JSON.parse(obj),
                    _this = this;
                $('.typeahead-value-' + input_selector).attr('value', item.id);
                //auto_typeahead = true;
                if(call_function.indexOf(',') > 0){
                    var functions = call_function.split(',');
                    $.each(functions, function(k,v){
                        if (typeof window[v] == 'function') {
                             window[v](_this.$element, item.id, item.original);
                        }
                    });
                }else{
                    if (typeof window[call_function] == 'function') {
                         window[call_function](_this.$element, item.id, item.original);
                    }
                }
                return item.name;
            }
        }).on('focusout', this, function(e) {
            if(!$(this).data('no_clear_if_null')){
                input_selector = $(this).data('input');
                if ($(this).val() == '') {
                    $('.typeahead-value-' + input_selector).val('');
                }
                if (/*auto_typeahead == false && */$('.typeahead-value-' + input_selector).val() == 0 && typeof $(this).data('anyway') === 'undefined') {
                    $(this).val('');
                    //$('.typeahead-value-' + input_selector).val('');
                } else if (typeof $(this).data('anyway') !== 'undefined') {
                    $('.typeahead-value-' + input_selector).val($(this).val());
                }
            }
        });
        $(document).on('mousedown', 'ul.typeahead', function(e) {
          e.preventDefault();
        });
    });

    $(document).on('click', '.typeahead_add_form', function(){
        var $this = $(this),
            $form = $('#'+$this.data('form_id')),
            id = (new Date()).getTime();
        $this.attr('data-id', 'source-'+id);
        $form.attr('data-id', 'form-'+id);
        if(!$form.hasClass('loaded')){
            $this.button('loading');
            $.ajax({
                url: prefix+$this.data('action'),
                dataType: "json",
                data: '&name='+$this.closest('.form-group').find('input.form-control').val(),
                type:'POST',
                success: function(msg) {
                    $form.addClass('loaded').html(msg.html);
                    $form.show();
                    reset_multiselect();
                    $this.button('reset');
                }
            });
            $this.parents('form').find('.submit-from-btn').mousedown(function(){
                $form.removeClass('loaded').empty().hide();
            });
        }else{
            $form.toggle();
        }
    });
    $(document).on('click', '.hide_typeahead_add_form', function(){
        $(this).closest('.typeahead_add_form_box').hide();
    });
    
    $(document).on('focusin', '.typeahead-double', function(e) {
        var $this = $(this),
            id = $this.data('id'),
            this_field = $this.data('field'),
            call_function = $this.data('function');
        $(this).typeahead({
            items: 50,
            minLength: 1,
            source: function (query, process) {
                id = $(this.$element).data('id');
                call_function = $(this.$element).data('function');
                var table = $(this.$element).data('table');
                var fix = $('.select-typeahead-' + $(this.$element).data('select')).val();
                var fields = [];
                $('#' + id).val('');
                $('.typeahead-double[data-id="'+id+'"]').each(function(){
                    fields.push($(this).data('field'));
                });
                return $.ajax({
                    url: prefix + 'messages.php',
                    type: 'POST',
                    data: {act: 'global-typeahead', fields: fields.join(','), 
                           double: true, query: query, table: table, fix: fix,
                           limit: this.options.items, object: arrequest()[2]},
                    dataType: 'json',
                    success: function (result) {
                        if (result) {
                            var resultList = result.map(function (item) {
                                var aItem = { original: item, id: item.id, name:
                                    item.title.replace(/\u00A0/g, " ").replace(/[\r\n]+/g, "\n")};
                                return JSON.stringify(aItem);
                            });
                            return process(resultList);
                        }
                        return false;
                    }
                });
            },
            matcher: function (obj) {
                var item = JSON.parse(obj);
                this.query = $.trim(this.query);
                return item.name.toLowerCase();
            },
            highlighter: function (obj) {
                var item = JSON.parse(obj);
                var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
                return item.name.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                    return '<strong>' + match + '</strong>'
                })
            },
            updater: function (obj) {
                var item = JSON.parse(obj),
                    _this = this;
                $('#' + id).attr('value', item.id);
                if(call_function.indexOf(',') > 0){
                    var functions = call_function.split(',');
                    $.each(functions, function(k,v){
                        if (typeof window[v] == 'function') {
                             window[v](_this.$element, item.id, item.original);
                        }
                    });
                }else{
                    if (typeof window[call_function] == 'function') {
                         window[call_function](_this.$element, item.id, item.original);
                    }
                }
                var return_value = '';
                $('.typeahead-double[data-id="'+id+'"]').each(function(){
                    var field = $(this).data('field');
                    if(field === this_field){
                        return_value = item.original[field];
                    }
                    $(this).val(item.original[field]);
                });
                return return_value;
            }
        }).on('focusout', this, function(e) {
            id = $(this).data('id');
            var $clear_els = $('#' + id);
            var is_clear = true;
            $('.typeahead-double[data-id="'+id+'"]').each(function(){
                var $this = $(this);
                if($.trim($this.val())){
                    is_clear = false;
                }else{
                    $clear_els.add($this);
                }
            });
            if (is_clear) {
                $clear_els.val('');
            }
        });
    });

    $('span#ga').parent().click(function () {
        var b = $('body');
        b.append('<div class="loading-wrapper"></div>');
        var dl = $('<div class="loading-wrapper-text">Загружается Google Analytics<br />Пожалуйста, подождите...</div>');
        b.append(dl);
        var left = (b.width() - dl.width()) / 2;
        var top = 300;
        dl.css({
            'left': left + 'px',
            'top': top + 'px'
        });
    });

    function init_hash() {
        if (window.location.hash) {
            var hashs = window.location.hash;
        } else {
            var hashs = $('a.click_tab.default').attr('href');
        }

        if (hashs) {
            var hash = hashs.split('-');
            $('ul.nav.nav-tabs > li > a[href="' + hash[0] + '"]').click();
        }
    }

    init_hash();

    /*$('ul.nav.nav-pills > li > a.unhash').live('click', function (event) {
        event.preventDefault();
        $(this).tab('show');
    });*/


    $('#infoblock>span').click(function(){
      $('#infoblock').toggleClass('activate');
    });

    function arrequest_for_editable() {
        var url = window.location.toString();
        var arr = url.split(prefix.toString())[1];

        arr = arr.split('#')[0];
        var url_get = arr.split('?')[1];
        arr = arr.split('?')[0];
        var arrequest = arr.split('/');
        arrequest['get'] = url_get;

        return arrequest;
    }

    $(".infoblock").editable({
        type: 'textarea',
        url: prefix + 'messages.php',
        title: 'Обновите информацию',
        pk: {act: "infobox", do: "set", hash: module+(arrequest_for_editable()[1] || '')}, //
        emptytext:'Нет данных, нажмите чтобы добавить.'
    });
    
    $('.header-link.hide-menu').click(function(){
        $.cookie('hide_menu', $('body').hasClass('hide-sidebar') ? 1 : 0);
    });
    
    $(document).on('change', '#contractor_type_select', function(){
        var $this = $(this).find(':selected');
        $('.multiselect[data-type="categories_1"]').multiselect('deselectAll', false)
                                                   .multiselect('select', $this.data('categories_1'));
        $('.multiselect[data-type="categories_2"]').multiselect('deselectAll', false)
                                                   .multiselect('select', $this.data('categories_2'));
    });
});

function contractor_create(_this, callback) {
    var form_data = $('.bootbox form.form_contractor');
    $(_this).button('loading');

    $.ajax({
        url: prefix + 'accountings/ajax/?act=contractor-create',
        dataType: "json",
        data: form_data.serialize(),
        type: 'POST',
        success: function (data) {
            if (data) {
                if (data['state'] == true){
                    $(_this).closest('.modal').modal('hide');
                    if(typeof callback == 'function'){
                        callback(data);
                    }else{
                        click_tab_hash();
                    }
                }
                if (data['state'] == false && data['message'])
                    alert(data['message'])
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function quick_create_supplier_callback(data){
    console.log(data);
    $('select[name="warehouse-supplier"]').append('<option selected value="'+data.id+'">'+data.name+'</option>');
}

function select_typeahead_device(data, $form){
    if(data.state && data.id){
        var id = $form.parent().attr('data-id'),
            $f = $('[data-id="source-'+id.replace('form-','')+'"]').closest('.input-group');
        $f.find(':hidden').val(data.id);
        $f.find('input.form-control').val(data.name);
        $form.closest('.typeahead_add_form_box').hide().empty().removeClass('loaded');
    }
}

function load_infoblock(hash) {
    hash=hash.replace('#', '');
    $('#infoblock').data("hash", hash);

    //alert($('#infoblock').data("hash"));

    $.ajax({
        type: "POST",
        url: prefix + "messages.php",
        data: {act: "infobox", do: "get", hash: hash}
    }).done(function(json) {
        $('div.infoblock').html(json['text']);
        $('div#infoblock .title').html(json['title']);
        $(".infoblock").editable('setValue', json['text']);
        $(".infoblock").editable('option', 'pk', {act: "infobox", do: "set", hash: $('#infoblock').data("hash")});
    });
}

var popover_target;
var popover_timer;

$(document).on('mouseleave', '.popover-info', function() {
    var _this = this;

    function popover_hide() {

        clearTimeout(popover_timer);

        popover_timer = setTimeout(function() {
            if (!$(".popover:hover").length) {
                $(_this).popover("hide");
                popover_target = null;
                clearTimeout(popover_timer);
            } else {
                popover_hide();
            }
        }, 200);
    };

    popover_hide();
});

$(document).on('mouseenter', '.popover-info', function() {
    clearTimeout(popover_timer);
    var _this = this;
    var placement = $(this).attr('data-placement') ? $(this).attr('data-placement') : 'left';
    var trigger = $(this).attr('data-trigger') ? $(this).attr('data-trigger') : 'mouseenter';

    if (popover_target !== _this) {
        $(popover_target).popover("hide");
        popover_target = _this;

        $(_this).popover({
            html: true,
            trigger: trigger,
            placement: placement,
            boxLRMargin: -10
        }).popover('show');
    }
});
/**
 * add symbol counter before DOM object,
 * counts symbols in object & output result
 *
 * @param DOM-object
 *
 */
/*
 function add_symbol_counter(obj){
 var objName = obj.attr('name');
 obj.keyup( function (){
 $('.count_signs_'+objName).text('Символов: '+this.value.length);
 });

 if($('.count_signs_'+objName).length <= 0){
 obj.prepend('<div class="count_signs_' + objName + '"></div><br>');
 $('.count_signs_' + objName).insertBefore(obj);
 obj.keyup();
 }
 }

 $(document).ready(function(){
 $('.add_symbol_counter').each(function(){
 add_symbol_counter($(this));
 });

 });

 */

/**
 * mimo84-bootstrap-maxlength plugin usage
 * http://mimo84.github.io/bootstrap-maxlength/
 *
 * adds counter to fields
 *
 * to display counter set data-symbol_counter
 * attribute
 *
 * (uses size="" or maxlength="" attributes)
 */
var set_symbol_counters = function () {

    var max = '';
    var counter_data = 'symbol_counter';
    // for each counted fiels set size attr
    $('[data-' + counter_data + ']').each(function () {
        max = $(this).data(counter_data);
        if (max < 1) max = 10000;
        $(this).attr('size', max);
    });

    $('[data-' + counter_data + ']').maxlength({
        alwaysShow: true,
        placement: 'top',
        preText: 'Символов '
    });
};

function endcountdown()
{
    var countdown = $(this);
    var order_id = countdown.data('o_id');
    var alarm_id = countdown.data('alarm_id');
    var text = countdown.data('text');

    $('#btn-timer-' + order_id + '.text-info').removeClass('text-info');
    countdown.countdown('destroy');

    if (order_id == 0) {
        sound('alarm.mp3', 2);
        $("html, body").animate({scrollTop: 0}, "quick");
        $('#wrapper>.content').prepend('<div class="alert alert-danger">' +
            '<button type="button" class="close close_alarm" data-alarm_id="'+alarm_id+'" data-dismiss="alert">×</button>' + text + '</div>');
    }
}

function closecountdown() {
    $('.alarm-timer.is-countdown').countdown('destroy');
    $('.btn-timer.text-info').removeClass('text-info');
}

function startcountdown(msg) {
    var countdowns = $('.alarm-timer');

    if (countdowns.length > 0 && typeof msg == 'object') {
        $.each(msg, function(index, value) {

            var countdown = $('#alarm-timer-' + index);
            var date = value.date;

            if (date > 0 && countdown.length > 0) {

                countdown.attr('data-text', value.text);
                countdown.attr('data-alarm_id', value.id);
                $('#btn-timer-' + index + ':not(.text-info)').addClass('text-info');

                countdown.countdown({
                    compact: true,
                    onExpiry: endcountdown,
                    until: new Date(date * 1000)
                });
            }
        });
    }
}

var global_check_mess;
function check_mess(last_time_query) {

    $(document).ready(function () {

        $.ajax({
            url: prefix + 'messages.php?last_seconds=' + last_time_query + location.search.replace('?', '&'),
            type: 'POST',
            data: 'act=global-ajax',
            success: function (msg) {

                if (msg) {

                    startcountdown(msg['alarms'] ? msg['alarms'] : null);

                    if (msg['counts']) {
                        $('.tab_count').addClass('hide');
                        for (key in msg['counts']) {
                            //if (msg['counts'][key] > 0) {
                                $('span.' + key).removeClass('hide').html(msg['counts'][key]);
                            //}
                        }
                    }

                    if (msg['error'])
                        alert(msg['error']);

                    //if (msg['messages'] && msg['messages']['new_count'] > 0) {
                    if (msg['new_comments'] && msg['new_comments'] > 0) {
                        var n = noty({
                            text: 'Новое сообщение',
                            timeout: 3000,
                            type: 'alert',
                            dismissQueue: true,
                            layout: 'topRight',
                            theme: 'defaultTheme'
                        });

                        /*$('.messages-block').prepend(msg['messages']['html']);
                        $('#new-count-mess').html(msg['messages']['i_count']);
                        $('#count-mess').html(msg['messages']['count']);*/

                        sound('mess.mp3');
                    }

                    //if (module == 'orders' && msg['new_orders'] > 0) click_tab_hash();
                }

                clearTimeout(global_check_mess);
                global_check_mess = setTimeout(check_mess, 15000, Math.round(new Date().getTime() / 1000));
            }
        });
    });
}

$(document).ready(function () {

    $(document).on('click', '.close_alarm', function(){
        $.ajax({
            url: prefix + 'messages.php',
            type: 'POST',
            data: 'act=close-alarm&id=' + $(this).data('alarm_id')
        });
    });

    $('.btn-timer').live('mouseover', function () {
        var order_id = $(this).data('o_id');
        $(this).attr('title', $('#alarm-timer-' + order_id + ' span').html());
    });

    //reset_tagsinput();
    $(set_symbol_counters);

    clearTimeout(global_check_mess);
    global_check_mess = setTimeout(check_mess, 100, Math.round(new Date().getTime() / 1000));

    $('#show-more-massages').click(function () {
        $.ajax({
            url: prefix + 'messages.php',
            type: 'POST',
            data: 'act=get-messages&skip=' + $('.messages-block > div').length,
            success: function (msg) {
                if (msg) {
                    if (msg['html'])
                        $('.messages-block').append(msg['html']);
                    if (msg['count'] == 0)
                        $('#show-more-massages').remove();
                }
            }
        });
    });
    
    introJs().start();
    
});

function parseDate(value) {
    // parse date 24.12.2009
    var tmp = value.split(".");
    return { day: tmp[0], month: tmp[1], year: tmp[2] };
}

function read_mess(_this, mess_id) {
    $.ajax({
        url: prefix + 'messages.php?act=read-message',
        type: 'POST',
        data: 'mess_id=' + mess_id,
        success: function (msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['state'] == true) {
                    $(_this).addClass('muted');
                    $(_this).attr('onClick', '');
                }
                $('.new-count-mess').html(msg['qty']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function remove_message(_this, mess_id, type) {
    //var all = mess_id == 'all' ? true : false;

    $.ajax({
        url: prefix + 'messages.php?act=remove-message',
        type: 'POST',
        data: 'mess_id=' + mess_id + '&type=' + type,
        success: function (msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['state'] == true) {
                    if (mess_id == 'all') {
                        $('#accordion-messages').html('Сообщений нет');
                    } else {
                        $(_this).parents('.panel').remove();
                    }
                }
                $('.new-count-mess').html(msg['qty']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function getURLParameter(name) {
    return decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search) || [, null])[1]
    );
}

function isNumberKey(e, _this) {
    //alert(e.keyCode)
    // Allow: backspace, delete, tab, escape, enter and
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
        // Allow: Ctrl+A
        (e.keyCode == 65 && e.ctrlKey === true) ||
        // Allow: Ctrl+C
        (e.keyCode == 67 && e.ctrlKey === true) ||
        // Allow: Ctrl+X
        (e.keyCode == 88 && e.ctrlKey === true) ||
        // Allow: Ctrl+V
        (e.keyCode == 86 && e.ctrlKey === true) ||
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
        // let it happen, don't do anything
        return;
    }

    if (e.keyCode == 190 && _this) {
        return;
    }

    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
}

/*function isNumberKey(e, _this)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode

    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46 && charCode != 44
            && charCode != 37 && charCode != 38 && charCode != 39 && charCode != 40 && charCode != 8)
        return false;

    if (charCode == 44) {
        if (_this) {
            var str = $(_this).val();
            //evt.preventDefault();

            //if (theEvent.preventDefault) theEvent.preventDefault();
            if (theEvent.preventDefault) theEvent.preventDefault();

            if ( str.indexOf(".") === -1) {
                $(_this).val(str + '.');
            }
        }

        return false;
    }

    if (charCode == 46) {
        if (_this) {
            var str = $(_this).val();
            if ( str.indexOf(".") === -1) {
                return true;
            }
        }

        return false;
    }

    return true;
}*/

function arrequest() {
    var url = window.location.toString();
    var arr = url.split(prefix.toString())[1];

    arr = arr.split('#')[0];
    var url_get = arr.split('?')[1];
    arr = arr.split('?')[0];
    var arrequest = arr.split('/');
    arrequest['get'] = url_get;

    return arrequest;
}

function hash_link() {

    $('.hash_link').click(function () {
        window.location.href = $(this).attr('href');
        window.location.reload();
    });

    window.scrollTo(0, 0);
}



function click_tab(_this, e, hashs) {

    //$.fn.ElementNavigate(_this);

    closecountdown();

    if ($(_this).parent().hasClass('disabled') || formdata_original == false) {
        if ($(_this).parent().hasClass('disabled')) {
            e.stopImmediatePropagation();
            //e.preventDefault(e);
        }
        return;
    }
    $('a.click_tab').parent('li:not(.disabled)').addClass('disabled');

    var html = _this.innerHTML;
    $($(_this).attr('href')).html('');
    $(_this).button('loading').html(html);

    var hash = $(_this).attr('href');
    var all_hash = window.location.hash;

    //if (!$(_this).hasClass('unhash'))
        window.location.hash = hash;

    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=tab-load&' + arrequest()['get'],
        type: 'POST',
        data: '&tab=' + $(_this).data('open_tab') + '&hashs=' + all_hash,
        beforeSend: function() {
            if ($("#loadingbar").length === 0) {
                $("body").append("<div id='loadingbar'></div>")
                $("#loadingbar").addClass("waiting").append($("<dt/><dd/>"));
                $("#loadingbar").width((50 + Math.random() * 30) + "%");
            }
        }
    }).done(function(msg) {
        if (msg) {
            if (typeof(msg) == 'string') {
                window.location.hash = '';
                window.location.reload();
                return;
            }
            if (msg['message']) {
                alert(msg['message']);
            }
            if (msg['state'] == true && msg['html']) {
                $($(_this).attr('href')).html(msg['html']);

                $(_this).closest('ul').find('li.active').removeClass('active').children('.active').removeClass('active');
                $(_this).parent('li').addClass('active');
                $(_this).addClass('active');
                $('div.pill-content > div.active').removeClass('active');
                $('div.pill-content > div' + $(_this).attr('href')).addClass('active');
            }
        }
        $(_this).button('reset');
        $('a.click_tab').parent('li.disabled').removeClass('disabled');

        if (msg) {
            if (msg['menu']) {
                var str = window.location.hash.split('-');
//                console.log(str[0] + '-menu');
                $(str[0] + '-menu').html(msg['menu']);
            }
            if (msg['functions'] && msg['functions'].length > 0) {
                for (i in msg['functions']) {
                    eval(msg['functions'][i]);
                }
            }
        }
        $("#loadingbar").width("101%").delay(200).fadeOut(400, function() {
            $(this).remove();
        });

        hash_link();

        load_infoblock(hash);
        clearTimeout(global_check_mess);
        global_check_mess = setTimeout(check_mess, 100, Math.round(new Date().getTime() / 1000));

        $('.focusin').focus();
    });
}

function click_tab_hash(hash) {
    var href = hash ? hash : window.location.hash;

    if (href) {
        $('a.click_tab[href="' + href + '"]').click();
    } else {
        window.location.reload()
    }
}

function is_enter(_this, e, id, func) {
    if (e.keyCode == 13) {
        window[func](_this, id);
    }
}

function init_multiselect(){ 
    $('.multiselect').each(function(){
        var $this = $(this),
            opts = multiselect_options;
        if(typeof $this.attr('data-numberDisplayed') !== 'undefined'){
            opts.numberDisplayed = $this.attr('data-numberDisplayed');
        }
        $this.multiselect(opts);
    });
}

function reset_multiselect() {
    $('.multiselect').multiselect('destroy');

    init_multiselect();
//    $('.multiselect').multiselect(multiselect);//.trigger('reset');
    //$('.multiselect').multiselect('setOptions', multiselect)//.multiselect('rebuild');
    //$('.multiselect').multiselect('refresh');
    //$('.multiselect').multiselect('rebuild');
}

function close_alert_box()
{
    $('.bootbox .modal-footer').find('button[data-bb-handler="ok"]').click();
}

function alert_box(_this, content, ajax_act, data, callback, url, e) {
    if(e){
        e.stopPropagation();
    }
    if ($(_this).hasClass('disabled') || $(_this).prop('disabled'))
        return false;

    if ($(_this).is('input') || $(_this).is('button'))
        $(_this).button('loading');
    else
        $(_this).addClass('disabled');

    bootbox.addLocale('ru', {
        OK : 'Отмена',
        CANCEL : 'Отмена',
        CONFIRM : 'Подтвердить'
    });
    bootbox.setDefaults({
        /**
         * @optional String
         * @default: en
         * which locale settings to use to translate the three
         * standard button labels: OK, CONFIRM, CANCEL
         */
        locale: "ru",

        /**
         * @optional Boolean
         * @default: true
         * whether the dialog should be shown immediately
         */
        show: true,

        /**
         * @optional Boolean
         * @default: true
         * whether the dialog should be have a backdrop or not
         */
        backdrop: true,

        /**
         * @optional Boolean
         * @default: true
         * show a close button
         */
        closeButton: true,

        /**
         * @optional Boolean
         * @default: true
         * animate the dialog in and out (not supported in < IE 10)
         */
        animate: true,

        /**
         * @optional String
         * @default: null
         * an additional class to apply to the dialog wrapper
         */
        className: "my-modal"
    });

    if (content) {close_alert_box()
        $('.bootbox.bootbox-alert').remove();
        $('.modal-backdrop').remove();

        var btns;
        if (typeof content !== 'string') {
            btns = content['btns'];
            content = content['content'];
        }
        bootbox.alert(content/*, function() {
            $(_this).button('reset');
            return false;
        }*/);
        if (btns) {
            $('.bootbox-alert .modal-footer').prepend(btns);
        }
    } else if (ajax_act) {

        if (typeof data !== 'object')
            data = {};

        if (typeof $(_this).data('o_id') !== 'undefined')
            data['object_id'] = $(_this).data('o_id');

        $.ajax({
            url: prefix + (url ? url : module + '/ajax/' + arrequest()[2]) + '?act=' + ajax_act,
            type: 'POST',
            data: data,
            //contentType: 'application/json',
            //dataType: 'json',
            success: function (msg) {
                $('.bootbox.bootbox-alert').remove();
                $('.modal-backdrop').remove();

                if (msg) {
                    if (msg['state'] == true && msg['content']) {
                        bootbox.alert(msg['content']);
                        if (msg['btns']) {
                            $('.bootbox-alert .modal-footer').prepend(msg['btns']);
                        }
//                        if (typeof callback == 'function') callback(msg);
                    }
                    if (msg['functions'] && msg['functions'].length > 0) {
                        for (i in msg['functions']) {
                            eval(msg['functions'][i]);
                        }
                    }
                }
                if (msg['width']) {
                    $('.bootbox.modal').addClass('bootbox-big');
//                    $('.modal-body').css({maxHeight: '500px'});
//                    $('.bootbox.modal').css({
//                        left: '20px',
//                        marginLeft: '0',
//                        marginRight: '0',
//                        maxWidth: '100%',
//                        right: '20px',
//                        width: 'auto',
//                        top: '45%'
//                    });
                }
                $(_this).button('reset');
                return false;
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
                $(_this).button('reset');
            }
        });

        return false;
    }
}

function remove_by_id(_this, element_id) {
    $('#' + element_id).remove();
}

function sound(track, time_sec) {
    var audioElement = document.createElement('audio');
    audioElement.setAttribute('src', prefix + track);
    audioElement.setAttribute('autoplay', 'autoplay');
    jQuery.get();
    audioElement.addEventListener("load", function() {
        audioElement.play();
    }, true);
    if(time_sec){
        setTimeout(function(){
            audioElement.pause();
        }, time_sec * 1000);
    }
}

function display_serial_product(_this, item_id)
{
    $(_this).parent().find('small').html('');

    $.ajax({
        url: prefix + 'messages.php?act=get-product-title',
        type: 'POST',
        dataType: "json",

        data: '&item_id=' + item_id,
        success: function(msg) {
            if (msg) {
                /*if (msg['state'] == false) {
                 if (msg['message']) {
                 alert(msg['message']);
                 }
                 }*/
                if (msg['msg']) {
                    $(_this).parent().find('.product-title').html(msg['msg']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function move_order(_this, rand) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + 'messages.php?act=move-order',
        type: 'POST',
        dataType: "json",

        data: $('#moving-item-form-' + rand).serialize(),
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false) {
                    if (msg['message']) {
                        alert(msg['message']);
                    }
                    if (msg['location']) {
                        window.location.href = msg['location'];
                    }
                }
                if (msg['state'] == true || msg['reload'] == true) {
                    click_tab_hash();
                    close_alert_box();
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function send_get_form(_this) {
    $(_this).button('loading');
    window.location.search = $(_this).parents('form').find(':input[value!=""]').serialize();
    $(_this).button('reset');
}

function change_warehouse(_this) {
    var suffix = typeof $(_this).data('multi') !== 'undefined' ? $(_this).data('multi') : '';
    var select = $(_this).parents('form').find('select.select-location' + suffix);
    $(select).html('');

    $.ajax({
        url: prefix + 'messages.php?act=get_locations',
        type: 'POST',
        dataType: "json",
        data: 'wh_id=' + $(_this).val(),
        success: function(msg) {
            if (msg) {
                if (msg['html']) {
                    $(select).html(msg['html']);
                    reset_multiselect();
                }
                if (msg['msg']) {
                    alert(msg['msg']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function icons_marked(_this, object_id, type) {

    var icons_class = 'star-marked-unactive';
    var icons_class_active = 'star-marked-active';

    if ($(_this).hasClass('star-marked-active') == true) {
        icons_class = 'star-marked-active';
        icons_class_active = 'star-marked-unactive';
    }

    if (object_id && type) {
        $.ajax({
            url: prefix + 'messages.php',
            type: 'POST',
            dataType: "json",

            data: '&object_id=' + object_id + '&type=' + type + '&act=marked-object',
            success: function(msg) {
                if (msg['error']) {
                    alert(msg['message']);
                } else {
                    if ($(_this).hasClass('star-remove-icons') == true && $(_this).hasClass('star-marked-active'))
                        $(_this).parents('.remove-marked-object').remove();

                    $(_this).removeClass(icons_class);
                    $(_this).addClass(icons_class_active);
                    $('#count-marked-' + type).html(msg['count-marked']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

        return false;
    }
}

function set_cookie(_this, key, value, reload) {
    $.removeCookie(key, { path: prefix});
    $.cookie(key, value, {
        expires : 30, //expires in 10 days
        path    : prefix
    });
    if (reload == 1) {
        window.location.href = $.param.querystring(window.location.href, 'p=1');
        window.location.reload();
    }
}
/*
function reset_tagsinput() {
    $('input.input-tags').tagsinput({
        itemValue: 'id',
        itemText: 'title',
        typeahead: {
            onTagExists: function(item, $tag) {
                $tag.hide.fadeIn();
            },
            //remove : function () {},
            source: function (query, process, element) {
                var table = $(element).data('table');

                return $.ajax({
                    url: prefix + 'messages.php',
                    type: 'POST',
                    data: {act: 'global-tagsinput', query: query, table: table, object: arrequest()[2]},
                    dataType: 'json',
                    success: function (result) {
                        return JSON.stringify(result);
                    }
                });
            }
        }
    });

    $('input.input-tags').each(function() {
        var _this = this;
        var value = $(_this).val();
        var arrayValue = value.split('&');

        for (var i = 0; i < arrayValue.length; i++) {
            if (arrayValue[i].length > 0) {
                var key_value = arrayValue[i].split('=');

                if (key_value[0] && key_value[0] > 0 && key_value[1]) {
                    $('input.input-tags').tagsinput('add', {id: key_value[0] , title: decodeURIComponent(key_value[1])});
                }
            }
        }
    });
}*/


$(function(){
    function set_events(){
        $('.ajax_form').each(function(){
            var $this = $(this),
                submit_on_blur = $this.data('submit_on_blur');
            if(submit_on_blur){
                var inputs = submit_on_blur.split(',');
                $.each(inputs, function(k, v){
                    $this.find('[name="'+v+'"]').blur(function(e){
                        var has_values = false;
                        $.each(inputs, function(k, v){
                            var $val_el = $this.find('[name="'+v+'"]');
                            if($val_el.hasClass('global-typeahead')){
                                $val_el = $this.find('.typeahead-value-'+$val_el.attr('data-input'));
                            }
                            var val = $.trim($val_el.val());
                            if(val != '' && val != 0){
                                has_values = true;
                            }
                        });
                        if(has_values){
                            $this.submit();
                        }
                    });
                });
            }
        });
    }
    function ajax_form_event(_this, e){
        e.preventDefault();
        var $this = $(_this),
            $form = $this.hasClass('ajax_form') ? $this : $this.closest('.ajax_form');
        $form.find(':submit').attr('disabled', true);
        if($form.hasClass('emulate_form')){
            var data = $form.clone().wrap('<form></form>').parent().serialize();
        }else{
            var data = $form.serialize();
        }
        $.ajax({
            url: $form.attr('action') || $form.attr('data-action'),
            type: $form.attr('method') || $form.attr('data-method'),
            data: data,
            dataType: 'json',
            success: function (data) {
                $form.find(':submit').attr('disabled', false);
                if(!data.state){
                    alert(data.msg);
                }else{
                    if(data.redirect){
                        window.location = data.redirect;
                    }
                    var update_val = $form.data('on_success_set_value_for');
                    if(update_val){
                        var $input = $form.find('[name="'+update_val+'"]');
                        if(!$input.val()){
                            $input.val(data[update_val]);
                        }
                    }
                    if(data.after){
                        $form.after(data.after);
                        set_events();
                    }
                    if($form.data('callback')){
                        window[$form.data('callback')](data, $form);
                    }
                }
            }
        });
        return false;
    }
    set_events();
    $('.ajax_form').live('submit', function(e){
        return ajax_form_event(this, e);
    });
    $('.ajax_form.emulate_form :submit').live('click', function(e){
        return ajax_form_event(this, e);
    });
    
    $(document).on('click', '.toggle-hidden', function(){
        var $this = $(this),
            $context = $this.closest('.tab-pane'),
            $toggle = $('#'+$this.attr('data-toggle'), $context);
        if(!$toggle.length){
            $toggle = $('#'+$this.attr('data-toggle'), $context.find('.pill-pane.active'));
        }
        if($toggle.hasClass('hidden')){
            $toggle.removeClass('hidden');
            $this.addClass('active');
        }else{
            $this.removeClass('active');
            $toggle.addClass('hidden');
        }
    });
    
    $('.module_submenu_click_tab_event').click(function(e){
        var $menu = $('a[href="'+$(this).data('href')+'"]');
        if($menu.length){
            e.preventDefault();
            $menu.click();
        }
    });
});