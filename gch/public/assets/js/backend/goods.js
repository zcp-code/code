define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/index',
                    edit_url: 'goods/edit',
                    del_url: 'goods/del',
                    status_url: 'goods/status',
                    table: 'goods',
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
                        {field: 'goods_no', title: '货盘编号', width: 160},
                        {field: 'name', title: '品名', align: 'left'},
                        {field: 'shop_name', title: '店铺', operate: false},
                        {field: 'wholesaler_name', title: '批发商', operate: false},
                        {field: 'category_name', title: '分类', operate: false},
                        {field: 'price', title: '单价', formatter: function(v){ return '¥' + parseFloat(v).toFixed(2); }},
                        {field: 'unit', title: '单位', operate: false},
                        {field: 'total_stock', title: '总库存'},
                        {field: 'reserved_quantity', title: '已订'},
                        {field: 'available', title: '可订', operate: false},
                        {field: 'status', title: '状态', searchList: {1:'在售',0:'已下架',2:'售罄'}, formatter: function(v){
                            return ['<span class="label label-success">在售</span>',
                                    '<span class="label label-default">已下架</span>',
                                    '<span class="label label-warning">售罄</span>'][v];
                        }},
                        {field: 'publish_time', title: '发布时间', formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 强制下架（批量）
            $(document).on('click', '.btn-status', function () {
                var ids = Table.api.selectedids(table);
                if (ids.length === 0) {
                    Layer.alert('请选择记录');
                    return;
                }
                layer.confirm('确认强制下架所选货盘？这将取消所有待确认预订', function (i) {
                    layer.close(i);
                    $.ajax({
                        url: 'goods/status',
                        data: {ids: ids.join(',')},
                        type: 'POST',
                        dataType: 'json',
                        success: function (ret) {
                            if (ret.code === 1) {
                                table.bootstrapTable('refresh');
                                Layer.alert(ret.msg, {icon: 1});
                            } else {
                                Layer.alert(ret.msg, {icon: 2});
                            }
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
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});
