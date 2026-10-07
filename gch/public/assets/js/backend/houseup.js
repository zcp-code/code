define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'houseup/index' + location.search,
                    add_url: 'houseup/add',
                    edit_url: 'houseup/edit',
                    del_url: 'houseup/del',
                    multi_url: 'houseup/multi',
                    import_url: 'houseup/import',
                    table: 'house_up',
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
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'house_id', title: __('House_id')},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"sale":__('Type sale'),"rent":__('Type rent'),"new":__('Type new')}, formatter: Table.api.formatter.normal},
                        {field: 'cover_image', title: __('Cover_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'unit_price', title: __('Unit_price'), operate:'BETWEEN'},
                        {field: 'monthly_rent', title: __('Monthly_rent'), operate:'BETWEEN'},
                        {field: 'house_type', title: __('House_type'), operate: 'LIKE'},
                        {field: 'area', title: __('Area'), operate:'BETWEEN'},
                        {field: 'inner_area', title: __('Inner_area'), operate:'BETWEEN'},
                        {field: 'house_use', title: __('House_use'), operate: 'LIKE'},
                        {field: 'house_nature', title: __('House_nature'), operate: 'LIKE'},
                        {field: 'floor', title: __('Floor'), operate: 'LIKE'},
                        {field: 'total_floor', title: __('Total_floor'), operate: 'LIKE'},
                        {field: 'co_ownership', title: __('Co_ownership'), operate: 'LIKE'},
                        {field: 'mortgage', title: __('Mortgage'), operate: 'LIKE'},
                        {field: 'commission_type', title: __('Commission_type'), operate: 'LIKE'},
                        {field: 'orientation', title: __('Orientation'), operate: 'LIKE'},
                        {field: 'decoration', title: __('Decoration'), operate: 'LIKE'},
                        {field: 'has_elevator', title: __('Has_elevator'), searchList: {"0":__('Has_elevator 0'),"1":__('Has_elevator 1')}, formatter: Table.api.formatter.normal},
                        {field: 'community_id', title: __('Community_id')},
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'house_code', title: __('House_code'), operate: 'LIKE'},
                        {field: 'house_code_qrcode', title: __('House_code_qrcode'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'verify_status', title: __('Verify_status')},
                        {field: 'verify_time', title: __('Verify_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'agent_user_id', title: __('Agent_user_id')},
                        {field: 'agent_id', title: __('Agent_id')},
                        {field: 'check_status', title: __('Check_status'), searchList: {"0":__('Check_status 0'),"1":__('Check_status 1'),"2":__('Check_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'publish_time', title: __('Publish_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'house.title', title: __('House.title'), operate: 'LIKE'},
                        {field: 'community.name', title: __('Community.name'), operate: 'LIKE'},
                        {field: 'agent.name', title: __('Agent.name'), operate: 'LIKE'},
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
