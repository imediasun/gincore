<div class="col-sm-6">
    <div class="input-group">
        <input class="form-control" id="jstree_search" type="text" name="tree_search" placeholder="<?= l('поиск по дереву') ?>">
    <div class="input-group-btn">
        <button type="button" class="btn btn-primary" id="jstree_search_button"><i class="glyphicon glyphicon-search"></i>&nbsp;&nbsp;<?= l('Поиск') ?></button>
    </div>
</div>

</div>
<div class="col-sm-6">
    <?php if ($this->all_configs['oRole']->hasPrivilege('create-filters-categories')): ?>
        <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
           class="btn btn-success pull-right">
            <?= l('Создать категорию') ?>
        </a>
    <?php endif; ?>
</div>


<div class="clearfix m-b-lg"></div>

<div class="col-md-6">

    <div class="hpanel">
        <div class="panel-heading hbuilt showhide cursor-pointer">
            <div class="panel-tools">
                <a class=""><i class="fa fa-chevron-up"></i></a>
            </div>
            <?= l('Дерево категорий') ?>
        </div>
        <div class="panel-body">
            <?php if (!empty($categories)): ?>
                <div id="categories-jstree" style="display: none;">
                    <?= build_array_tree($categories, array($cat_id), 4) ?>
                </div>

            <?php else: ?>
                <p class="text-error"><?= l('Не существует ниодной категории') ?></p>
            <?php endif; ?>
        </div>
    </div>

</div>
<div class="col-md-6" id="show-category">
    <?= $content_html ?>
</div>

<div class="clearfix"></div>

<?php if (!empty($cat_id)): ?>
  <script>
      var jstree_selected_node = <?= $cat_id ?>;
  </script>
<?php endif; ?>

<script type="text/javascript">
    $(function () {
        $('#categories-jstree').jstree({
            core : {
                "check_callback" : true,
                "multiple" : false,
                "animation" : 0
            },
            plugins : [
//                "contextmenu",
                "dnd",
                "search",
                'types'
            ],
            contextmenu : {
                select_node : false,
                items : {
                    0: {
                        label: 'Edit',
                        icon: 'glyphicon glyphicon-edit',
                        action: function (par1, par2) {
//                            location.href = prefix + module + '/create/' + data.node.id;
                        }
                    },
                    1: {
                        label: 'Delete',
                        icon: 'glyphicon glyphicon-remove',
                        action: function (e, data) {

                        }
                    }
                }
            },
            search: {
                show_only_matches: true,
                show_only_matches_children: true
            },
            types : {
                default : {
                    icon : "fa fa-folder"
                }
            }
        }).bind("move_node.jstree", function (e, data) {
            var cur_id = data.node.id;
            var parent_id = data.parent;
            var position = data.position;

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
                            rightSidebar.noty(msg['message'] ,'success');
                            return true;
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(xhr.responseText);
                }
            });


        });
        $('#categories-jstree').show();

        var to = false;
        $('#jstree_search').keyup(function () {
            if(to) { clearTimeout(to); }
            to = setTimeout(function () {
                var v = $('#jstree_search').val();
                $('#categories-jstree').jstree(true).search(v);
            }, 250);
        });
        $('#jstree_search_button').click(function () {
            if(to) { clearTimeout(to); }
            to = setTimeout(function () {
                var v = $('#jstree_search').val();
                $('#categories-jstree').jstree(true).search(v);
            }, 250);
        });

        if (jstree_selected_node) {
            $('#categories-jstree').jstree(true).select_node(jstree_selected_node);
        }

        $('#categories-jstree').bind("select_node.jstree", function(e, data){
            location.href = prefix + module + '/create/' + data.node.id;
        });

        $('#jstree-delete-category').live('click', function () {
            delete_category($(this), prefix + module);
        });


        $(window).on('unload', function() {
            $(window).scrollTop(0);
        });
    });
</script>
