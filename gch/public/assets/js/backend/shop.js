define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'shop/index',
                    add_url: 'shop/add',
                    edit_url: 'shop/edit',
                    del_url: 'shop/del',
                    table: 'shop',
                }
            });

            var table = $("#table");
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: 'ID'},
                        {field: 'name', title: '店铺名', align: 'left'},
                        {field: 'logo', title: 'Logo', operate: false, formatter: Table.api.formatter.image},
                        {field: 'position', title: '位置', operate: false},
                        {field: 'contact_phone', title: '联系电话'},
                        {field: 'business_hours', title: '营业时间', operate: false},
                        {field: 'goods_count', title: '货盘数', operate: false},
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
