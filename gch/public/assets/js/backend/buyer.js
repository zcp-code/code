define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'buyer/index',
                    add_url: 'buyer/add',
                    edit_url: 'buyer/edit',
                    del_url: 'buyer/del',
                    resetpwd_url: 'buyer/resetPwd',
                    status_url: 'buyer/status',
                    table: 'buyer',
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
                        {field: 'account', title: '账号'},
                        {field: 'real_name', title: '姓名'},
                        {field: 'mobile', title: '手机号'},
                        {field: 'last_login_time', title: '最后登录', formatter: Table.api.formatter.datetime, operate: false},
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

            // 重置密码（批量）
            $(document).on('click', '.btn-resetpwd', function () {
                var ids = Table.api.selectedids(table);
                if (ids.length === 0) {
                    Layer.alert('请选择记录');
                    return;
                }
                layer.confirm('确认重置所选账号密码？', function (i) {
                    layer.close(i);
                    $.ajax({
                        url: 'buyer/resetPwd',
                        data: {ids: ids.join(',')},
                        type: 'POST',
                        dataType: 'json',
                        success: function (ret) {
                            Layer.alert(ret.msg, {icon: ret.code === 1 ? 1 : 2});
                        }
                    });
                });
            });

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
