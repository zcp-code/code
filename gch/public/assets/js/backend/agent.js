define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'agent/index' + location.search,
                    add_url: 'agent/add',
                    edit_url: 'agent/edit',
                    del_url: 'agent/del',
                    multi_url: 'agent/multi',
                    table: 'agent',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'avatar', title: __('Avatar'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'name', title: __('Name'), operate: 'LIKE', align: 'left'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'house_count', title: __('House_count'), operate: false, align: 'center'},
                        {field: 'status', title: __('Status'), searchList: {1:__('Status normal'),0:__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'customer_auth', title: __('Customer_auth'), searchList: {1:__('Yes'),0:__('No')}, formatter: Table.api.formatter.label},
                        {field: 'house_auth', title: __('House_auth'), searchList: {1:__('Yes'),0:__('No')}, formatter: Table.api.formatter.label},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                search: false,
                showExport: true,
                showColumns: false,
                searchFormVisible: false,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
