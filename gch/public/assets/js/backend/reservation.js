define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'reservation/index',
                    edit_url: 'reservation/edit',
                    del_url: 'reservation/del',
                    table: 'reservation',
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
                        {field: 'reservation_no', title: '预订编号', width: 160},
                        {field: 'goods_name', title: '货品', align: 'left'},
                        {field: 'shop_name', title: '店铺', operate: false},
                        {field: 'buyer_name', title: '采购商', operate: false},
                        {field: 'wholesaler_name', title: '批发商', operate: false},
                        {field: 'price', title: '单价', formatter: function(v){ return '¥' + parseFloat(v).toFixed(2); }},
                        {field: 'quantity', title: '数量'},
                        // 顶部搜索的状态下拉禁用(与 tab 重复,且 join 多表后 status 列会 ambiguous) — tab 单独处理
                        {field: 'status', title: '状态', operate: false, formatter: function(v){
                            return ['<span class="label label-warning">待确认</span>',
                                    '<span class="label label-success">已确认</span>',
                                    '<span class="label label-default">已取消</span>'][
                                        v == 'pending' ? 0 : (v == 'confirmed' ? 1 : 2)
                                    ];
                        }},
                        {field: 'createtime', title: '下单时间', formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'confirm_time', title: '确认时间', formatter: Table.api.formatter.datetime, operate: false},
                        {field: 'cancel_time', title: '取消时间', formatter: Table.api.formatter.datetime, operate: false},
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
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});
