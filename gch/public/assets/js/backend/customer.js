define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'customer/index' + location.search,
                    add_url: 'customer/add',
                    edit_url: 'customer/edit',
                    del_url: 'customer/del',
                    table: 'customer',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE', align: 'left'},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'agent.name', title: __('Agent'), operate: 'LIKE'},
                        // {field: 'user.mobile', title: __('Mobile'), operate: 'LIKE'},
                        // {field: 'user.nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', align: 'left'},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            buttons: [{
                                name: 'log',
                                text: __('历史修改记录'),
                                title: __('历史修改记录'),
                                classname: 'btn btn-xs btn-info btn-log btn-dialog',
                                area: ["90%","90%"],
                                icon: 'fa fa-list',
                                url: 'customerlog/index',
                            }],
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
