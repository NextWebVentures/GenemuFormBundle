$(function () {
    $('input.genemu_jqueryjstree').each(function (index, $element) {
        $element = $($element);
        var $id = $element.attr('id');
        var $widget = $('#jstree_' + $id + '.genemu_jqueryjstree_widget');
        var $settings = $widget.attr('settings') && $.parseJSON($widget.attr('settings')) || {};

        $widget.jstree($.extend({
            core: {},
            plugins: ['themes', 'json_data', 'ui', 'hotkeys'],
            json_data: {
                ajax: {
                    url: $settings.list || null,
                    data: function (node) {
                        return {
                            _id: node.data ? node.data('_id') : 0
                        }
                    },
                    success: function (data, textStatus, jqXHR) {

                    },
                    error: function (data, textStatus, jqXHR) {
                        if (data.status != 200) {
                            alert(textStatus);
                        }
                    },
                    statusCode: {
                        404: function () {
                            alert('Page not found');
                        },
                        403: function () {
                            alert('Access denied');
                        },
                        500: function () {
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
                url: $settings.themes,
                theme: 'default'
            }
        }, $settings.config || {}))
            .bind('', function (event, data) {
                $.jstree._reference('.genemu_jqueryjstree_widget').select_node('#4f632ffbb3417a5722000ad2', true);
            })
            .bind('select_node.jstree', function (event, data) {
                $element.val(data.inst.get_selected().data('_id'));
            });
    });
});
