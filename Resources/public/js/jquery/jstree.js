$(function () {
    $('input.genemu_jqueryjstree').each(function (index, $element) {
        $element = $($element);
        var $id = $element.attr('id');
        var $widget = $('#jstree_' + $id + '.genemu_jqueryjstree_widget');
        var $settings = $widget.data('settings') || {};

        $widget.jstree($.extend({
            core: {},
            plugins: ['themes', 'json_data', 'ui', 'hotkeys'],
            json_data: {
                ajax: {
                    url: $settings.list || null,
                    /**
                     * Gets the node about to be open as a paramater (or -1 for initial load).
                     * Whatever you return in the data function will be sent to the server as data
                     * (so for example you can send the node's ID).
                     *
                     * @param node
                     * @return {Object}
                     */
                    data: function (node) {
                        return {
                            id: node.attr ? node.attr('id') : 0
                        }
                    },
                    /**
                     * It will be used to populate the tree - this can be useful if you want to somehow change what
                     * the server returned on the client side before it is displayed in the tree
                     * @param data
                     * @param textStatus
                     * @param jqXHR
                     */
                    success: function (data, textStatus, jqXHR) {
                        var normalizeNodes = function(child) {
                            if (!$.isArray(child)) { // it's not an array, probably child
                                // maybe has children?
                                if (child.children && $.isArray(child.children) && child.children.length > 0) {
                                    child.children = normalizeNodes(child.children);
                                }

                                // state
                                if (!child.state) { // don't overwrite
                                    child.state = 'closed'; // closed by default
                                }
                                // add attr attribute
                                if (!child.attr) {
                                    child.attr = {};
                                }
                                // add an id as html ID if provided
                                if (!child.attr.id && child.id) {
                                    child.attr.id = child.id;
                                }

                                // give them back the same!
                                return child;
                            }
                            // parse child's children
                            $.each(child, function(index, child) {
                                // normalize children as well
                                child = normalizeNodes(child);
                            });
                            return child;
                        };

                        if (data && !data.id && !data.state) { // root node
                            data.state = 'open'; // root always open as exception of rule above
                        }

                        data = normalizeNodes(data);

                        return data;
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
            themes: {
                url: $settings.themes,
                theme: 'default'
            }
        }, $settings.config || {}))
            .bind('loaded.jstree', function (event, data) {
                $.jstree._reference($widget).select_node('#' + $element.val(), true);
            })
            .bind('select_node.jstree', function (event, data) {
                $element.val(data.inst.get_selected().attr('id'));
            });
    });
});
