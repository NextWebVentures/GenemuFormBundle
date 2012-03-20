$(function () {
    $('input.genemu_jqueryjstree').each(function($element) {
        console.info($element);
        $element = $($element);
        var $id = $element.attr('id');
        var $widget = $('#jstree_' + $id + '.genemu_jqueryjstree_widget');
        var $config = $widget.attr('settings') || {};
        $widget.jstree($.extend({
            core: {},
            plugins: ['themes', 'json_data', 'ui', 'hotkeys'],
            json_data: {
                ajax: {
                    url: $config.url,
                    data: function (node) {
                        return {
                            _id: node.data ? node.data('_id') : 0
                        }
                    },
                    success: function(data, textStatus, jqXHR) {

                    },
                    error: function(data, textStatus, jqXHR) {
                        if (data.status != 200) {
                            alert(textStatus);
                        }
                    },
                    statusCode: {
                        404: function() {
                            alert('Page not found');
                        },
                        403: function() {
                            alert('Access denied');
                        },
                        500: function() {
                            alert('The server encountered an error');
                        }
                    }
                }
            },
            ui: {
                select_limit: 1
            },
            //_themes: "/abczdrowie/bundles/abcadminadmin/css/jquery/jstree",
            themes: {
                url: "/abczdrowie/bundles/abcadminadmin/css/jquery/jstree/themes/default/style.css",
                theme: 'default'
            }
        }, $config))
        .bind('select_node.jstree', function (event, data) {
            console.info(data.inst.get_selected().data('_id'));
        });
    });
});
