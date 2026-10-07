define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        // {field: 'group.name', title: __('Group')},
                        // {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        // {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'is_agent', title: __('Is_agent'), formatter: Controller.api.formatter.is_agent, operate: false},
                        // {field: 'level', title: __('Level'), operate: 'BETWEEN', sortable: true},
                        // {field: 'gender', title: __('Gender'), visible: false, searchList: {1: __('Male'), 0: __('Female')}},
                        // {field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true},
                        // {field: 'successions', title: __('Successions'), visible: false, operate: 'BETWEEN', sortable: true},
                        // {field: 'maxsuccessions', title: __('Maxsuccessions'), visible: false, operate: 'BETWEEN', sortable: true},
                        {field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        // {field: 'loginip', title: __('Loginip'), formatter: Table.api.formatter.search},
                        {field: 'jointime', title: __('Jointime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        // {field: 'joinip', title: __('Joinip'), formatter: Table.api.formatter.search},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('Hidden')}},
                        {
                            field: 'operate', 
                            title: __('Operate'), 
                            table: table,
                            // buttons: [
                            //     {
                            //         name: 'toggle-agent-1',
                            //         text: __('设置/取消经纪人'),
                            //         // title: __('Detail'),
                            //         classname: 'btn btn-xs btn-info btn-ajax btn-toggle-agent-1',
                            //         icon: 'fa fa-eye',
                            //         url: 'user/user/toggleAgent',
                            //     }
                            // ],
                            events: Table.api.events.operate, 
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 设置/取消经纪人按钮事件
            $(document).on("click", ".btn-toggle-agent", function () {
                var ids = Table.api.selectedids(table);
                if (ids.length === 0) {
                    Layer.msg(__('Please choose at least one record'));
                    return false;
                }
                var that = this;
                Fast.api.ajax({
                    url: $(that).data("url"),
                    data: {ids: ids.join(",")}
                }, function (data, ret) {
                    Layer.close(index);
                    table.bootstrapTable('refresh');
                });
                return false;
            });
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
            },
            formatter: {//渲染的方法
                is_agent: function (value, row) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="user/user/toggleAgent" data-params="is_agent" data-id="' + row.id + '"><i class="fa fa-toggle-on fa-2x' + (row.is_agent == 0 ? ' fa-flip-horizontal text-gray' : '') +'"></i></a>';
                }
            },
        }
    };
    return Controller;
});