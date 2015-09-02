/**
 * jQuery syncTranslit plugin
 *
 * Copyright (c) 2009 Snitko Roman
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * @author 	Roman Snitko snowcore.net@gmail.com
 * @link http://snowcore.net/
 * @version 0.0.7
 */
;(function($){$.fn.syncTranslit=function(options){var opts=$.extend({},$.fn.syncTranslit.defaults,options);return this.each(function(){$this=$(this);var o=$.meta?$.extend({},opts,$this.data()):opts;var $destination=$('#'+opts.destination);o.destinationObject=$destination;if(!Array.indexOf){Array.prototype.indexOf=function(obj){for(var i=0;i<this.length;i++){if(this[i]==obj){return i}}return-1}}$this.keyup(function(){var str=$(this).val();var result='';for(var i=0;i<str.length;i++){result+=$.fn.syncTranslit.transliterate(str.charAt(i),o)}var regExp=new RegExp('['+o.urlSeparator+']{2,}','g');result=result.replace(regExp,o.urlSeparator);$destination.val(result)})})};$.fn.syncTranslit.transliterate=function(char,opts){var charIsLowerCase=true,trChar;if(char.toLowerCase()!=char){charIsLowerCase=false}char=char.toLowerCase();var index=opts.dictOriginal.indexOf(char);if(index==-1){trChar=char}else{trChar=opts.dictTranslate[index]}if(opts.type=='url'){var code=trChar.charCodeAt(0);if(code>=33&&code<=47&&code!=45||code>=58&&code<=64||code>=91&&code<=96||code>=123&&code<=126||code>=1072||code==171||code==187){return''}if(trChar==' '||trChar=='-'){return opts.urlSeparator}}if(opts.caseStyle=='upper'){return trChar.toUpperCase()}else if(opts.caseStyle=='normal'){if(charIsLowerCase){return trChar.toLowerCase()}else{return trChar.toUpperCase()}}return trChar};$.fn.syncTranslit.defaults={dictOriginal:['а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','і','є','ї','ґ'],dictTranslate:['a','b','v','g','d','e','e','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','sch','','y','','e','ju','ja','i','je','ji','g'],caseStyle:'lower',urlSeparator:'-',type:'url'}})(jQuery);

var mce_init = false;
function init_mce(){
    mce_init = true;
    tinyMCE.init({
        mode : "textareas",
        theme : "advanced",
        language : "ru",
        editor_selector :"mcefull",

        plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
        content_css : prefix+"../extra/mce_main.css",
        file_browser_callback : "tinyBrowser",

        document_base_url : "/manage/",
        relative_urls : false,
        apply_source_formatting : true,
        remove_script_host : true,

        theme_advanced_buttons1 : "save,newdocument,|,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,mybutton,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location : "bottom",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        table_styles: 'Огромная таблица(по ширине)=table_break_words',

        extended_valid_elements : "object[width|height|param|embed],param[name|value],embed[src|type|width|height|allowscriptaccess|allowfullscreen],li[class|rel|id],div[class|rel|id|style],nobr"

        ,setup : function(ed) {
            ed.addButton('mybutton', {
                title : 'Вставить галерею',
                image : prefix+'modules/map/img/dnld.png',
                onclick : function() {
                    ed.focus();
                    ed.selection.setContent('{-page_gallery-}');
                }
            });
        }
    });
}

var ru2en = {
  ru_str : "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя іІїЇ",
  en_str : ['A','B','V','G','D','E','Yo','Zh','Z','I','J','K','L','M','N','O','P','R','S','T',
    'U','F','H','C','CH','SH','SHH',String.fromCharCode(35),'I',String.fromCharCode(39),'Ye','Yu',
    'Ya','a','b','v','g','d','e','jo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
    'h','c','ch','sh','shh',String.fromCharCode(35),'y',String.fromCharCode(39),'ye','yu','ya', "_", "i", "I", "i", "I"],
  translit : function(org_str) {
    var tmp_str = "";
    for(var i = 0, l = org_str.length; i < l; i++) {
      var s = org_str.charAt(i), n = this.ru_str.indexOf(s);
      if(n >= 0) {tmp_str += this.en_str[n];}
      else {tmp_str += s;}
    }
    return tmp_str.toLowerCase();
  }
}


function init_qqfile(){
    if($('#resizeFoto').is(':checked')){
        var resizeFoto = 1;
    }else{
        var resizeFoto = 0;
    }
    
    if($('#resizeFotoNews').is(':checked')){
        var resizeFotoNews = 1;
    }else{
        var resizeFotoNews = 0;
    }
    
    if($('#add_watermark').is(':checked')){
        var add_watermark = 1;
    }else{
        var add_watermark = 0;
    }
    
    if($('#resize_product').is(':checked')){
        var resize_product = 1;
    }else{
        var resize_product = 0;
    }
    
    if($('#resize_gallery').is(':checked')){
        var resize_gallery = 1;
    }else{
        var resize_gallery = 0;
    }
    
    if ($("#file-uploader")[0]) {
        //upload qq
        var page=$("#text_url").val();
        var uploader = new qq.FileUploader({
            // pass the dom node (ex. $(selector)[0] for jQuery users)
            //element: document.getElementById('file-uploader'),
            ////element: $("#file-uploader")[0],
            element: document.getElementById('file-uploader'),
            //path to server-side upload script
            action: prefix+module+'/ajax/?gallery='+$("#sel_gallery").val()+'&resizeFoto='+resizeFoto+'&resizeFotoNews='+resizeFotoNews+'&add_watermark='+add_watermark+'&resize_product='+resize_product+'&resize_gallery='+resize_gallery,
            //                debug: true,
            params: {
                act: 'upload_picture_for_page',
                page: page
            },
            maxConnections: 500,
            allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
            //allowedExtensions: [],
            onComplete: function(id, fileName, responseJSON){
                if (responseJSON.success==true){
                //$("#picture").val(fileName).change();
                //$("#current_picture").attr('src', siteprefix+'images/'+$("#sel_gallery").val()+'/'+fileName);
                }
            }
        });
    }
}

function reload_images(){
    $('.float_content img').each(function(){
        $(this).attr('src', ''+$(this).attr('src')+'?' + (Math.random() * (10000 - 1) + 1)); 
    });
}

function map_saving(show){
    if(show){
        $('#saving').show();
    }else{
        $('#saving').hide();
    }
}

function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name,"",-1);
}

$(function(){
    
    $("#pagename").keyup(function(){
        var trans=ru2en.translit($(this).val());
        $("#pageurl").val(trans);
        
    })
    
    $("#pagename").syncTranslit({destination: "pageurl"});
    
    $(".make_watermark").live('click', function(){
        var f = $(this).attr('rel');
        $.ajax({
            url: prefix+module+'/ajax/',
            data: 'act=make_watermark&gallery='+$("#sel_gallery").val()+'&picture='+f,
            success: function(){
                //                        $('#bt_page_photo_choose').click();
                //                        setTimeout("reload_images();", 300);
                reload_images();
            }
        });	
    });
    
    $(".crop").live('click', function(){
        var f = $(this).attr('rel');
        $("#float .float_content").html('Загрузка...').load(prefix+module+'/'+'ajax/?act=crop&gallery='+$("#sel_gallery").val()+'&picture='+f);
    });

    $('.sortTable li:odd').addClass('sortTableOdd');
    $('.sortTable').sortable({
        'stop': function(){
            $('.sortTable li').removeClass('sortTableOdd');
            $('.sortTable li:odd').addClass('sortTableOdd');
        } 
    });

    $('#saveSortOrder').click(function(){
        var i = 1;
        $('.sortTable li').each(function(){
            $(this).children('table').find('input').val(i);
            i++;
        });
    });
    
    $( "#datetimepick" ).datetimepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat : "yy-mm-dd",
        timeFormat : "hh:mm:ss",
        showSecond : true,
        stepHour   : 1,
        stepMinute : 1,
        stepSecond : 1
    });
    
    $('#sel_gallery').change(function(){
        if($(this).val()==''){
            $('#gallery_options').hide();
            $('#new_gal').show();
        }else{
            $('#gallery_options').show();
            $('#new_gal').hide();
        }
        $('#no_gal').removeAttr('selected');
        
        init_qqfile();
        
    });
    
    $('#new_gal :button').click(function(){
        var gal = $('#new_gal :text').val();
        $.ajax({
            url: prefix+module+'/ajax/',
            data: 'act=new_gallery&file='+gal,
            success: function(data){
                if(data!='fail'){
                    $('#no_gal').val(data).text(data);
                    alert('Галлерея создана!');
                    $('#gallery_options').show();
                    $('#new_gal').hide();
                    init_qqfile();
                }else{
                    alert('Ошибка.Возможно папка с таким именем уже существует или не предоставлены права на запись.');
                }
            }
        });
    });
    
    
    init_qqfile();
    
    $("#sel_gallery, #resizeFoto, #resizeFotoNews, #add_watermark, #resize_product, #resize_gallery").change(function(){
        init_qqfile();
    })
    
    $(".bt_page_photo_choose").click(function(){
        $("#float").fadeIn(300).attr('data-file', $(this).data('file')); 
        $("#float .float_content").html('Загрузка...').load(prefix+module+'/'+'ajax/?act=choose_picture_for_page&filtr=small&gallery='+$("#sel_gallery").val());
    });
    
    $("#filtr_thumb").live('click', function(){
        $("#float .float_content").html('Загрузка...').load(prefix+module+'/'+'ajax/?act=choose_picture_for_page&filtr=small&gallery='+$("#sel_gallery").val());
    });
    
    $("#filtr_not_selected").live('click', function(){
        $("#float .float_content").html('Загрузка...').load(prefix+module+'/'+'ajax/?act=choose_picture_for_page&filtr=not_selected&gallery='+$("#sel_gallery").val());
    });
    
    $("#filtr_all").live('click', function(){
        $("#float .float_content").html('Загрузка...').load(prefix+module+'/'+'ajax/?act=choose_picture_for_page&gallery='+$("#sel_gallery").val());
    });


    $(".float_content .fmfoto2 img").live('click', function(){
        var picture = $('#float').attr('data-file');
        $(".Ppicture[name='"+picture+"']").val($(this).attr('alt')).change();
        $("#current_"+picture).attr('src', siteprefix+'images/'+$("#sel_gallery").val()+'/'+$(this).attr('alt'));
        $("#float").fadeOut(300);
    })

    $(".del_rename").live('click', function(){
        var answer = confirm("Удалить?")
        if (answer){
            var f=$(this).attr('rel');
            $("#float .float_content").html('Удаление '+f).load(prefix+module+'/'+'ajax/?act=choose_picture_for_page&del='+f+'&gallery='+$("#sel_gallery").val());
            return true;
        }
    })

    $('#bt_page_photo_clear').click(function(){
        $(".Ppicture[name='picture']").val('');
        $('#current_picture').attr('src', prefix+'modules/map/img/no_picture.jpg');
    });

    $('#bt_page_photo_clear2').click(function(){
        $(".Ppicture[name='picture2']").val('');
        $('#current_picture2').attr('src', prefix+'modules/map/img/no_picture.jpg');
    });

    //float box
    $(".float_close").live('click', function (){
        $(this).parent(".float").fadeOut(300);
    })
    
    function nl2br( str ) {	
        return str.replace(/([^>])\n/g, '$1<br/>');
    }


    $(".saveTitle").live('click', function(){
        var parent = $(this).parent('span.imgTitle'),
        gallery = parent.attr('data-gallery'),
        picture = parent.attr('data-file'),
        title_ru = nl2br(parent.children('textarea[name=title_ru]').val()),
        title_en = nl2br(parent.children('textarea[name=title_en]').val()),
        data = 'act=savetitle&gallery='+gallery+'&picture='+picture+'&title_ru='+title_ru+'&title_en='+title_en;

        $.ajax({
            url: prefix+module+'/ajax/',
            data: data,
            success: function(){
                alert('Подпись картинки успешно сохранена!');	
            }
        });							   
    });
        
        
    $('.change_title').live('click', function(){
        var $this = $(this);
        $this.siblings('textarea').hide();
        $this.siblings('textarea[name=title_'+$this.attr('data-lng')+']').show();
        $this.siblings('.change_title').removeClass('change_title_active');
        $this.addClass('change_title_active');
    });
    
    
    $('.nav-tabs a').click(function (e) {
        var hash = $(this).attr('href');
        window.location.hash = hash;
        var map = $('#form_map');
        if(map.length){
            var action = map.attr('action').split('#');
            map.attr('action', action[0]+hash);
            
        }
    });
    
    if(window.location.hash){
        $('.nav-tabs a[href="'+window.location.hash+'"]').click();
    }
    
    
    
    
    /* ---------- MENU ------------- */
    
    var theme = 'base',
        $tree = $('#jqxTree');
    // Create jqxTree
    $tree.jqxTree({ 
        height: 'auto', 
        width: '100%', 
        theme: theme,
        animationShowDuration: 0,
        animationHideDuration: 0,
        dragStart: function (item) {
//                    console.log(item);
        },
        dragEnd: function (item, dropItem, args, dropPosition, tree) {
           map_saving(true);
           setTimeout(function(){
                
               // сохранаяем сортировку
               var order = '';
               $('.map_menu_level').each(function(){
                   var $ul = $(this),
                       $lis = $ul.children('li');
                   $lis.each(function(){
                       var $this = $(this),
                           id = $this.attr('id').replace('menu-', ''),
                           index = $lis.index($this);
                       order += '&order['+id+']='+index;
                   });
               });

                // сохранаяем родителя
                var selectedItem = $('#jqxTree').jqxTree('selectedItem'),
                    parent_id = selectedItem.parentId ? selectedItem.parentId.replace('menu-', '') : 0,
                    page_id = item.id.replace('menu-', '');
                
                $.ajax({
                    url: prefix+module+'/ajax/?act=save_page_parent',
                    type: 'POST',
                    data: 'page_id='+page_id+'&new_parent='+parent_id+order,
                    success: function(data){
                        map_saving(false);
                    }
                });
           }, 10);
           
        }
    });
    
    // Expand All
    $('#ExpandAll').click(function () {
//        $('.map_menu_level').show();
//        $('.jqx-tree-item-arrow-collapse-base').removeClass('jqx-tree-item-arrow-collapse').addClass('jqx-tree-item-arrow-expand');
        $tree.jqxTree('expandAll');
    });
    // Collapse All
    $('#CollapseAll').click(function () {
        $tree.jqxTree('collapseAll');
    });
    
//    var checkbox = $('.RememberMe :checkbox').iphoneStyle({ 
//        checkedLabel: 'Включено', 
//        uncheckedLabel: 'Скрыто',
//        onChange: function(checkbox, state){
//            console.log(state);
//        }
//    });
    
    var contextMenu = $("#jqxMenu").jqxMenu({ 
                            width: '186px', 
                            theme: theme,
                            height: '', 
                            autoOpenPopup: false, 
                            mode: 'popup',
                            animationShowDuration: 0,
                            animationHideDuration: 0
                        });

    $('#jqxMenu').bind('shown', function () { 
        var selectedItem = $tree.jqxTree('selectedItem'),
            el = $('#'+selectedItem.id+'>div>a');
        $('#page_state').attr('checked', !el.hasClass('disabledpage'));
    }); 
    
    $('#page_state').change(function(){
        var checked = $(this).is(':checked'),
            selectedItem = $tree.jqxTree('selectedItem'),
            el = $('#'+selectedItem.id+'>div>a');
        if(checked){
            el.removeClass('disabledpage');
        }else{
            el.addClass('disabledpage');
        }
        map_saving(true);
        $.ajax({
            url: prefix+module+'/ajax/?act=update_map_state',
            type: 'POST',
            data: 'page_id='+selectedItem.id.replace('menu-', '')+'&state='+(checked ? 1 : 0),
            success: function(data){
                map_saving(false);
            }
        });
    });
    
    var clickedItem = null;
    // open the context menu when the user presses the mouse right button.

    $("li", $tree).live('mousedown', function (event) {
        var target = $(event.target).parents('li:first')[0];

        var rightClick = isRightClick(event);
        if (rightClick && target != null) {
            $tree.jqxTree('selectItem', target);
            var scrollTop = $(window).scrollTop();
            var scrollLeft = $(window).scrollLeft();
            contextMenu.jqxMenu('open', parseInt(event.clientX) + 5 + scrollLeft, parseInt(event.clientY) + 5 + scrollTop);
            return false;
        }
    });

    $('#add_item').click(function(){
        var selectedItem = $tree.jqxTree('selectedItem');
        window.location = prefix+'map/add/'+selectedItem.id.replace('menu-', '');
    });

    $('#copy_item').click(function(){
        var selectedItem = $tree.jqxTree('selectedItem');
        window.location = prefix+'map/add/copy/'+selectedItem.id.replace('menu-', '');
    });

    // disable the default browser's context menu.
    $(document).bind('contextmenu', function (e) {
        if ($(e.target).parents('.jqx-tree').length > 0) {
            return false;
        }
        return true;
    });

    function isRightClick(event) {
        var rightclick;
        if (!event) var event = window.event;
        if (event.which) rightclick = (event.which == 3);
        else if (event.button) rightclick = (event.button == 2);
        return rightclick;
    }
    
    
    
    
    var checkbox = $('#toggle_mce').iphoneStyle({ 
        checkedLabel: 'Вкл.', 
        uncheckedLabel: 'Выкл.',
        onChange: function(checkbox, state){
            if(state){
                createCookie('mce_on', 1, 300);
                if(!mce_init){
                    init_mce();
                }else{
                    tinymce.execCommand('mceToggleEditor', false, 'page_content');
                }
            }else{
                createCookie('mce_on', 0, 300);
                tinymce.execCommand('mceToggleEditor', false, 'page_content');
            }
        }
    });
    
    if(readCookie('mce_on') == 1 || readCookie('mce_on') == null){
        init_mce();
    }
    
    // поиск по дереву
    var $search_result = $('#search_results');
    $('#tree_search').keyup(function(){
        var query = $.trim($(this).val()).toLowerCase();
//        console.log(query);
        if(query){
            var els = [];
            $('a', $tree).each(function(){
                var $this = $(this),
                    text = $this.text().toLowerCase(),
                    search_pos = text.indexOf(query),
                    li = $this.parents('li'),
                    id = li.attr('id'),
                    goto_el = '<span class="goto_level" data-id="'+id+'">меню</span>';
                if(search_pos >= 0){
                    els.push('<li><span title="'+li.parents('li').attr('title')+'">'+$this[0].outerHTML+'</span>, '+goto_el+'</li>');
                }
            });
            $search_result.show().html(els.join(''));
//            console.log(els);
        }else{
            $search_result.html('').hide();
        }
    });
    
    $('.goto_level').live('click', function(){
        $('#CollapseAll').click();
        var id = $(this).data('id'),
            el = $('#'+id),
            root = el.parents('.menu_root_li');
        el.children('div').click();
        $tree.jqxTree('expandItem', document.getElementById(id));
        window.scrollTo(1, root.offset().top - 50);        
    });
    
    var selectedItem = $tree.jqxTree('selectedItem');
    if(selectedItem){
        $tree.jqxTree('expandItem', document.getElementById(selectedItem.id));
        $tree.jqxTree('expandItem', document.getElementById(selectedItem.parentId));
    }
    
    // product 2 product
    
    $('.chp :checkbox').click(function(){
        if($(this).is(':checked')){
            $(this).siblings('.prio2product').show();
        }else{
            $(this).siblings('.prio2product').hide();
        }
    });
    
    
    //form change
    $(".changeme").FormNavigate({
        message: "Выйти без сохранения?",
        aOutConfirm: "button:not(#pr1-btn), button:not(#pr2-btn)"
    });
    
    //prices
    $(".editable").editable({
      type:  'text',
      pk:    0,
      name:  'username',
      url:   prefix + 'map/ajax?act=price_edit_row',  
      title: 'Enter text' 
    });
    
    $("#pr2-btn").click(function(){
      if ($("#pr2-name").val() != '') {
        $.ajax({
            url: prefix+module+'/ajax?act=price_add_row',
            type: 'POST',
            data: 'name='+$("#pr2-name").val()
                 +'&pricecopymark='+$("#pr2-pricecopymark").val()
                 +'&pricecopy='+$("#pr2-pricecopy").val()
                 +'&pricemark='+$("#pr2-pricemark").val()
                 +'&price='+$("#pr2-price").val()
                 +'&timerequired='+$("#pr2-timerequired").val()
                 +'&prio='+$("#pr2-prio").val()
                 +'&map='+$("#pr2-btn").data('map'),
            success: function(data){
              if (data > 0){
                $('#tbl-price2').find('tr:last').before('<tr><td>'+data+'</td><td>'+$("#pr2-name").val()+'</td><td>'+$("#pr2-pricecopymark").val()+'</td><td>'+$("#pr2-pricecopy").val()+'</td><td>'+$("#pr2-pricemark").val()+'</td><td>'+$("#pr2-price").val()+'</td><td>'+$("#pr2-timerequired").val()+'</td><td>'+$("#pr2-prio").val()+'</td><td></td></tr>');
                $("#pr2-name, #pr2-pricecopymark, #pr2-pricecopy, #pr2-pricemark, #pr2-price, #pr2-timerequired, #pr2-prio").val("");
              }  else {
                alert('Строка не добавлена: '+data);
              }
            }
          });
        //location.reload();
      } else {
        $("#pr2-name").focus();
        alert('Введите название!');
      }
    });
    $("#pr1-btn").click(function(){
      if ($("#pr1-name").val() != '') {
        $.ajax({
            url: prefix+module+'/ajax?act=price_add_row',
            type: 'POST',
            data: 'name='+$("#pr1-name").val()
                 +'&pricemark='+$("#pr1-pricemark").val()
                 +'&price='+$("#pr1-price").val()
                 +'&timerequired='+$("#pr1-timerequired").val()
                 +'&prio='+$("#pr1-prio").val()
                 +'&map='+$("#pr1-btn").data('map'),
            success: function(data){
              if (data > 0){
                $('#tbl-price1').find('tr:last').before('<tr><td>'+data+'</td><td>'+$("#pr1-name").val()+'</td><td>'+$("#pr1-pricemark").val()+'</td><td>'+$("#pr1-price").val()+'</td><td>'+$("#pr1-timerequired").val()+'</td><td>'+$("#pr1-prio").val()+'</td><td></td></tr>');
                $("#pr1-name, #pr1-pricemark, #pr1-price, #pr1-timerequired, #pr1-prio").val("");
              }  else {
                alert('Строка не добавлена: '+data);
              }
            }
          });
        //location.reload();
      } else {
        $("#pr1-name").focus();
        alert('Введите название!');
      }
    });
    
    $('.copy_pricing_table').click(function(){
        var $this = $(this),
            table = $this.data('type'),
            current_id = $this.data('current_id'),
            id = $this.siblings('input[name=pricing_copy]').val();
        $this.attr('disabled', true);
        $.ajax({
            url: prefix+module+'/ajax?act=price_copy_table',
            type: 'POST',
            data: 'table='+table+'&id='+id+'&current_id='+current_id,
            success: function(data){
                if(data.state){
                    window.location.reload(true);
                }else{
                    alert(data.msg);
                }
            }
          });
    });
    
    
});

window.onload = function(){
    
    
    $('#middle').css({
        opacity: 1
    });
    
    $('#map_loading').hide();
}

$(document).ready(function(){
    // count string
    add_symbol_counter($('textarea[name=metadescription]'));
    add_symbol_counter($('textarea[name=fullname]'));
});

/**
 * add symbol counter before DOM object,
 * counts symbols in object & output result
 * 
 * @param DOM-object
 * 
 */
function add_symbol_counter(obj){
    var objName = obj.attr('name');
    obj.keyup( function (){
        $('.count_signs_'+objName).text('Символів: '+this.value.length);
    });
    
    if($('.count_signs_'+objName).length <= 0){
        obj.prepend('<div class="count_signs_' + objName + '"></div><br>');
        $('.count_signs_' + objName).insertBefore(obj);
        obj.keyup();
    }
}

/* jcrop init */

function init_jcrop($aspect, $minW, $minH){
    var $x = 0,
        $y = 0,
        $height = 0,
        $width = 0;
            
    $(".crop_image").Jcrop({
        minSize: [$minW, $minH],
        aspectRatio: $aspect,
        onSelect: changeCoords,
        onChange: changeCoords
    });
    
    function changeCoords (c){
        $x = c.x,
        $y = c.y,
        $height = c.h,
        $width = c.w;
    }
    
    $(".do_crop").live('click', function(){
        var f = $(this).attr('rel');
        $("#float .float_content").html('Загрузка...').load(prefix+module+'/'+
            'ajax/?act=crop&crop=1&gallery='+$("#sel_gallery").val()+'&picture='+f+
            '&x='+$x+'&y='+$y+'&height='+$height+'&width='+$width
        );
    });
    
};