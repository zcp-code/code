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
                        {field: 'status', title: '状态', searchList: {pending:'待确认',confirmed:'已确认',cancelled:'已取消'}, formatter: function(v){
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
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});
