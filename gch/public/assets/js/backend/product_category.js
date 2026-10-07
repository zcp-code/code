define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'product_category/index',
                    add_url: 'product_category/add',
                    edit_url: 'product_category/edit',
                    del_url: 'product_category/del',
                    table: 'product_category',
                }
            });

            var table = $("#table");
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'sort',
                sortOrder: 'asc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: 'ID'},
                        {field: 'name', title: '分类名', align: 'left'},
                        {field: 'icon', title: '图标', operate: false, formatter: Table.api.formatter.image},
                        {field: 'sort', title: '排序', sortable: true},
                        {field: 'status', title: '状态', searchList: {1:'启用',0:'停用'}, formatter: function(v){
                            return ['<span class="label label-default">停用</span>',
                                    '<span class="label label-success">启用</span>'][v];
                        }},
                        {field: 'createtime', title: '创建时间', formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            Table.api.bindevent(table);

            // TAB 切换
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var status = $(this).data('value');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    if (status !== '' && status !== undefined) params.status = status;
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});
