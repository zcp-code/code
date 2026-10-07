define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'slide/slidecat/index' + location.search,
                    add_url: 'slide/slidecat/add',
                    edit_url: 'slide/slidecat/edit',
                    del_url: 'slide/slidecat/del',
                    multi_url: 'slide/slidecat/multi',
                    import_url: 'slide/slidecat/import',
                    table: 'slide_cat',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'cid',
                sortName: 'cid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'cid', title: __('Cid')},
                        {field: 'cat_name', title: __('Cat_name'), operate: 'LIKE'},
                        {field: 'cat_idname', title: __('Cat_idname'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Table.api.formatter.status},
                        {field: 'cat_width', title: __('Cat_width')},
                        {field: 'cat_height', title: __('Cat_height')},
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