define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'wholesaler/index',
                    add_url: 'wholesaler/add',
                    edit_url: 'wholesaler/edit',
                    del_url: 'wholesaler/del',
                    resetpwd_url: 'wholesaler/resetPwd',
                    table: 'wholesaler',
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
                        {field: 'real_name', title: '负责人'},
                        {field: 'shop_name', title: '所属店铺', operate: false},
                        {field: 'mobile', title: '手机号'},
                        {field: 'contact_phone', title: '店铺电话'},
                        {field: 'last_login_time', title: '最后登录', formatter: Table.api.formatter.datetime, operate: false},
                        // 顶部搜索的状态下拉禁用(与 tab 重复,且 wholesaler join shop 后 status 列会 ambiguous) — tab 单独处理
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
                        url: 'wholesaler/resetPwd',
                        data: {ids: ids.join(',')},
                        type: 'POST',
                        dataType: 'json',
                        success: function (ret) {
                            Layer.alert(ret.msg, {icon: ret.code === 1 ? 1 : 2});
                        }
                    });
                });
            });

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
