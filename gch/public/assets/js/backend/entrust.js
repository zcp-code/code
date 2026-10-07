define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'entrust/index' + location.search,
                    add_url: 'entrust/add',
                    edit_url: 'entrust/edit',
                    del_url: 'entrust/del',
                    table: 'entrust',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'entrust_no', title: __('Entrust_no'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: Config.typeList, formatter: Table.api.formatter.label},
                        {field: 'name', title: __('Name'), operate: 'LIKE', align: 'left'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'community_name', title: __('Community'), operate: 'LIKE'},
                        {field: 'area', title: __('Area'), operate: false},
                        {field: 'house_type', title: __('House_type'), operate: false},
                        // {field: 'house_title', title: __('House'), operate: 'LIKE'},
                        // {field: 'agent_name', title: __('Agent'), operate: 'LIKE'},
                        // {field: 'member_nickname', title: __('Member'), operate: 'LIKE'},
                        {field: 'source', title: __('Source'), searchList: {"entrust":__('Source entrust'),"booking":__('Source booking')}, formatter: Table.api.formatter.label},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
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
