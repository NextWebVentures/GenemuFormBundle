$(function () {
    $('input.genemu_jqueryjstree').each(function (index, $element) {
        $element = $($element);
        var $id = $element.attr('id');
        var $widget = $('#jstree_' + $id + '.genemu_jqueryjstree_widget');
        var $settings = $widget.data('settings') || {};

        $.expr[':'].jstree_id = function(a, i, m) {
            return ($(a).parent().data('id') || '').toLowerCase().indexOf(m[3].toLowerCase()) >= 0;
        };

        $widget
            // jstree loaded
            .bind('loaded.jstree', function (event, data) {
                if ($element.val()) {
                    data.inst.search($element.val());
                }
                data.inst.select_node('#' + $id + '_' + ($element.val() ? $element.val() : 0), true);
            })
            // change input field as select changes
            .bind('select_node.jstree', function (event, data) {
                $element.val(data.inst.get_selected().data('id'));
            })
            .bind('search.jstree', function (event, data) {
                // @TODO co jest w data i event, mogę sprawdzić czy faktycznie coś znalazł?
                // @TODO wywal select_node do odrębnej metody
                data.inst.clear_search();
                data.inst.select_node('#' + $id + '_' + ($element.val() ? $element.val() : 0), true);
            })
            // initial load
            .bind('init.jstree', function (event, data) {

            })
            .jstree($.extend({
                core: {},
                plugins: ['themes', 'json_data', 'ui', 'hotkeys', 'search'],
                ui: {
                    select_limit: 1
                },
                themes: {
                    url: $settings.themes,
                    theme: 'default'
                },
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
                            if (node == -1) { // init load
                                // add a param (ie. val) with current value of $element to tell server to return
                                // complete tree path for current value
                            }
                            return {
                                id: node.data ? node.data('id') : 0
                            };
                        },
                        /**
                         * It will be used to populate the tree - this can be useful if you want to somehow change what
                         * the server returned on the client side before it is displayed in the tree
                         *
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
                                    // add an id as html ID if provided
                                    if (child.id) {
                                        if ((!child.attr || !child.attr.id)) {
                                            (child.attr || (child.attr = {})).id = $id + '_' + child.id;
                                        }

                                        (child.metadata || (child.metadata = {})).id = child.id;
                                    }

                                    // give them back the same!
                                    return child;
                                }
                                // parse child's children
                                $.each(child, function(index, children) {
                                    // normalize children as well
                                    children = normalizeNodes(children);
                                });
                                return child;
                            };

                            if (data && !data.id && !data.state) { // root node
                                data.state = 'open'; // root always open as exception of rule above
                                (data.attr || (data.attr = {})).id = $id + '_' + 0;
                                (data.metadata || (data.metadata = {})).id = 0;
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
                search: {
                    case_insensitive: true,
                    search_method: 'jstree_id',
                    ajax: {
                        url: $settings.search,
                        data: function (value) {
                            return {
                                search: value
                            };
                        },
                        success: function (data, textStatus, jqXHR) {
                            if (data && $.isArray(data)) {
                                $.each(data, function(i, node) {
                                    data[i] = '#' + $id + '_' + node;
                                });
                            }
                            return data;
                        }
                    }
                }
            }, $settings.config || {}));
    });
});
