define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'house/index' + location.search,
                    add_url: 'house/add',
                    edit_url: 'house/edit',
                    del_url: 'house/del',
                    multi_url: 'house/multi',
                    import_url: 'house/import',
                    table: 'house',
                }
            });

            var table = $("#table");
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-add").data("area", ["90%", "90%"]);
                $(".btn-edit").data("area", ["90%", "90%"]);
                $(".btn-editone").data("area", ['90%','90%']);
                $(".btn-check").data("area", ['90%','90%']);
            });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'status desc,is_recommend desc,weigh',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'community.name', title: __('Community_name'), operate: 'LIKE'},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'agent.name', title: __('Agent_name'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"sale":__('Type sale'),"rent":__('Type rent'),"new":__('Type new')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Controller.api.formatter.status},
                        {field: 'is_recommend', title: __('Is_recommend'), searchList: {"1":__('Is_recommend 1'),"0":__('Is_recommend 0')}, formatter: Controller.api.formatter.is_recommend},
                        {field: 'cover_image', title: __('Cover_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'verify_status', title: __('Verify_status'), searchList: {"0":__('Verify_status 0'),"1":__('Verify_status 1'),"2":__('Verify_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'check_status', title: __('Check_status'), searchList: Config.checkStatusList, formatter: Table.api.formatter.label},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            buttons: [{
                                name: 'check',
                                text: __('审核'),
                                title: __('审核'),
                                classname: 'btn btn-xs btn-info btn-check btn-dialog',
                                icon: 'fa fa-check',
                                url: 'house/check',
                                visible: function (row) {
                                    return row.check_status == 0 || row.check_status == 9;
                                }
                            }],
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }

                        // field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate
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
        check: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {//渲染的方法
                status: function (value, row) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="house/toggle" data-params="status" data-id="' + row.id + '"><i class="fa fa-toggle-on fa-2x' + (row.status == 0 ? ' fa-flip-horizontal text-gray' : '') +'"></i></a>';
                },
                is_recommend: function (value, row) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="house/toggle" data-params="is_recommend" data-id="' + row.id + '"><i class="fa fa-toggle-on fa-2x' + (row.is_recommend == 0 ? ' fa-flip-horizontal text-gray' : '') +'"></i></a>';
                },
            },
        }
    };
    return Controller;
});
