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
                        // 顶部搜索的状态下拉禁用(与 tab 重复) — tab 单独处理
                        {field: 'status', title: '状态', operate: false, formatter: function(v){
                            return ['<span class="label label-default">停用</span>',
                                    '<span class="label label-success">启用</span>'][v];
                        }},
                        {field: 'createtime', title: '创建时间', formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            Table.api.bindevent(table);

            // TAB 切换(状态筛选) — 用 click 直接绑,绕过 shown.bs.tab 兼容问题
            $(document).on('click', 'a[data-toggle="tab"]', function (e) {
                e.preventDefault();
                var $a = $(this);
                var status = $a.data('value');
                // 等 Bootstrap 切完 tab 再触发 refresh
                setTimeout(function() {
                    var options = table.bootstrapTable('getOptions');
                    options.pageNumber = 1;
                    options.queryParams = function (params) {
                        if (status !== '' && status !== undefined && status !== null) {
                            params.status = status;
                        } else {
                            delete params.status;
                        }
                        return params;
                    };
                    table.bootstrapTable('refresh', { queryParams: options.queryParams, pageNumber: 1 });
                }, 100);
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
