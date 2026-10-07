define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            var ids = $("#ids").val();
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'customerlog/index/ids/'+ ids + location.search,
                    add_url: 'customerlog/add',
                    edit_url: 'customerlog/edit',
                    del_url: 'customerlog/del',
                    multi_url: 'customerlog/multi',
                    import_url: 'customerlog/import',
                    table: 'customer_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id')},
                        // {field: 'customer_id', title: __('Customer_id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'agent.name', title: __('Agent'), operate: 'LIKE'},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', align: 'left'},
                        // {field: 'agent_id', title: __('Agent_id')},
                        // {field: 'agent_user_id', title: __('Agent_user_id')},
                        // {field: 'user_id', title: __('User_id')},
                        // {field: 'submit_time', title: __('Submit_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'customer.name', title: __('Customer.name'), operate: 'LIKE'},
                        // {field: 'customer.phone', title: __('Customer.phone'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                 search: false,
                 showExport: true,
                 showColumns: false,
                 //启用普通表单搜索
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
