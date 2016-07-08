var mce_init = false;
function init_mce() {
  mce_init = true;
  tinyMCE.init({
    mode: "textareas",
    theme: "advanced",
    language: "ru",
    editor_selector: "mcefull",

    plugins: "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
    content_css: prefix + "../extra/mce_main.css",
    file_browser_callback: "tinyBrowser",

    document_base_url: "/manage/",
    relative_urls: false,
    apply_source_formatting: true,
    remove_script_host: true,

    theme_advanced_buttons1: "save,newdocument,|,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,mybutton,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
    theme_advanced_toolbar_location: "bottom",
    theme_advanced_toolbar_align: "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,

    table_styles: L['huge-table'] + '(' + L['width'] + ')=table_break_words',

    extended_valid_elements: "object[width|height|param|embed],param[name|value],embed[src|type|width|height|allowscriptaccess|allowfullscreen],li[class|rel|id],div[class|rel|id|style],nobr"

    , setup: function (ed) {
      ed.addButton('mybutton', {
        title: L['paste-gallery'],
        image: prefix + 'modules/map/img/dnld.png',
        onclick: function () {
          ed.focus();
          ed.selection.setContent('{-page_gallery-}');
        }
      });
    }
  });
}
function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}
function createCookie(name, value, days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toGMTString();
  }
  else var expires = "";
  document.cookie = name + "=" + value + expires + "; path=/";
}

function tiny_mce() {
  var checkbox = $('#toggle_mce').iphoneStyle({
    checkedLabel: L['on'],
    uncheckedLabel: L['off'],
    onChange: function (checkbox, state) {
      if (state) {
        createCookie('mce_on', 1, 300);
        if (!mce_init) {
          init_mce();
        } else {
          tinymce.execCommand('mceToggleEditor', false, 'page_content');
        }
      } else {
        createCookie('mce_on', 0, 300);
        tinymce.execCommand('mceToggleEditor', false, 'page_content');
      }
    }
  });

  if (readCookie('mce_on') == 1 || readCookie('mce_on') == null) {
    init_mce();
  }
}

$(function () {
  // поиск по дереву
  var $search_result = $('#search_results');
  $('#tree_search').keyup(function () {
    var query = $.trim($(this).val()).toLowerCase();
    //        console.log(query);
    if (query) {
      var els = [];
      $('a', $('#categories-tree')).each(function () {
        var $this = $(this),
          text = $this.text().toLowerCase(),
          search_pos = text.indexOf(query),
          li = $this.parents('li').first(),
          id = li.attr('id'),
          item_id = li.data('id'),
          goto_el = '<span class="goto_level" data-id="' + id + '">' + L['menu'] + '</span>';
        if (search_pos >= 0) {
          els.push('<li data-id="' + item_id + '" class="dd-item"><span title="' + li.parents('li').attr('title') + '">' + $this[0].outerHTML + '</span>, ' + /*goto_el+*/'</li>');
        }
      });
      $search_result.show().find('ul').html(els.join(''));
      $('.js-delete-category').on('click', function () {
        var $parent = $(this).parents('li.dd-item').first();
        return delete_category($parent);
      });
    } else {
      $search_result.hide().find('ul').html('');
    }
  });
});

$(document).ready(function () {

  $('.dd-item')
    .on('mouseenter', function () {
      $(this).children('.dd-handle').addClass('dd-showing');
    })
    .on('mouseleave', function () {
      $(this).children('.dd-handle').removeClass('dd-showing');
    });

  $('#categories-tree').nestable({
    group: 1,
    maxDepth: 15,
    depthCollapse: 0,
    onChange: function (el) {
      var cur_id = $(el).data('id');
      var parent_id = $(el).parents(this.options.itemNodeName + '.' + this.options.itemClass).data('id');
      var position = $(el).index();

      $.ajax({
        url: prefix + module + '/ajax/?act=update-categories',
        type: 'POST',
        dataType: "json",
        data: '&cur_id=' + cur_id + '&parent_id=' + parent_id + '&position=' + position,
        success: function (msg) {
          if (msg) {
            if (msg['state'] == false && msg['message']) {
              alert(msg['message']);
            }
            if (msg['state'] && msg['state'] == true) {
              return;
            }
          }
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(xhr.responseText);
        }
      });

      return false;
    }
  });
  $('.js-delete-category').on('click', function () {
    var $parent = $(this).parents('li.dd-item').first();
    return delete_category($parent);
  });
});

function delete_category($parent) {

  var id = $parent.data('id');
  if (confirm("Вы действительно хотите удалить категорию?")) {
    $.ajax({
      url: prefix + module + '/ajax/?act=delete-categories',
      type: 'POST',
      dataType: "json",
      data: '&id=' + id,
      success: function (msg) {
        if (msg) {
          if (msg['state'] == false && msg['message']) {
            alert(msg['message']);
          }
          if (msg['state'] && msg['state'] == true) {
            $parent.css('opacity', '0.2');
            $parent.find('.js-delete-category').hide();
            $parent.find('.dd-handle').remove();
            $parent.find('.category-title').append('<span class="deleted">Удалено</span>');
          }
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.responseText);
      }
    });

  }
  return false;
}