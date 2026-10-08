(global["webpackJsonp"] = global["webpackJsonp"] || []).push([["pages/goods/goods"],{

/***/ 112:
/*!****************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/main.js?{"page":"pages%2Fgoods%2Fgoods"} ***!
  \****************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(wx, createPage) {

var _interopRequireDefault = __webpack_require__(/*! @babel/runtime/helpers/interopRequireDefault */ 4);
__webpack_require__(/*! uni-pages */ 26);
var _vue = _interopRequireDefault(__webpack_require__(/*! vue */ 25));
var _goods = _interopRequireDefault(__webpack_require__(/*! ./pages/goods/goods.vue */ 113));
// @ts-ignore
wx.__webpack_require_UNI_MP_PLUGIN__ = __webpack_require__;
createPage(_goods.default);
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/wx.js */ 1)["default"], __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/index.js */ 2)["createPage"]))

/***/ }),

/***/ 113:
/*!*********************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue ***!
  \*********************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./goods.vue?vue&type=template&id=5566b618&scoped=true& */ 114);
/* harmony import */ var _goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./goods.vue?vue&type=script&lang=js& */ 116);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var _goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./goods.vue?vue&type=style&index=0&id=5566b618&scoped=true&lang=css& */ 118);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/runtime/componentNormalizer.js */ 33);

var renderjs





/* normalize component */

var component = Object(_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "5566b618",
  null,
  false,
  _goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["components"],
  renderjs
)

component.options.__file = "pages/goods/goods.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ 114:
/*!****************************************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue?vue&type=template&id=5566b618&scoped=true& ***!
  \****************************************************************************************************************/
/*! exports provided: render, staticRenderFns, recyclableRender, components */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--17-0!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/template.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-uni-app-loader/page-meta.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!./goods.vue?vue&type=template&id=5566b618&scoped=true& */ 115);
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "recyclableRender", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["recyclableRender"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "components", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_template_id_5566b618_scoped_true___WEBPACK_IMPORTED_MODULE_0__["components"]; });



/***/ }),

/***/ 115:
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--17-0!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/template.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-uni-app-loader/page-meta.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue?vue&type=template&id=5566b618&scoped=true& ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns, recyclableRender, components */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "recyclableRender", function() { return recyclableRender; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "components", function() { return components; });
var components
var render = function () {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  var g0 = _vm.goods ? _vm.images.length : null
  var g1 = _vm.goods ? Number(_vm.goods.price).toFixed(2) : null
  var g2 = _vm.goods && _vm.goods ? Number(_vm.goods.price).toFixed(2) : null
  var g3 = _vm.goods && _vm.goods ? _vm.goods.available.toLocaleString() : null
  _vm.$mp.data = Object.assign(
    {},
    {
      $root: {
        g0: g0,
        g1: g1,
        g2: g2,
        g3: g3,
      },
    }
  )
}
var recyclableRender = false
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ 116:
/*!**********************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/babel-loader/lib!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--13-1!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/script.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!./goods.vue?vue&type=script&lang=js& */ 117);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ 117:
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--13-1!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/script.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(uni) {

var _interopRequireDefault = __webpack_require__(/*! @babel/runtime/helpers/interopRequireDefault */ 4);
var _typeof = __webpack_require__(/*! @babel/runtime/helpers/typeof */ 13);
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _regenerator = _interopRequireDefault(__webpack_require__(/*! @babel/runtime/regenerator */ 51));
var _asyncToGenerator2 = _interopRequireDefault(__webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ 53));
var _request = _interopRequireWildcard(__webpack_require__(/*! @/utils/request.js */ 35));
var _user = _interopRequireDefault(__webpack_require__(/*! @/store/user.js */ 30));
function _getRequireWildcardCache(nodeInterop) { if (typeof WeakMap !== "function") return null; var cacheBabelInterop = new WeakMap(); var cacheNodeInterop = new WeakMap(); return (_getRequireWildcardCache = function _getRequireWildcardCache(nodeInterop) { return nodeInterop ? cacheNodeInterop : cacheBabelInterop; })(nodeInterop); }
function _interopRequireWildcard(obj, nodeInterop) { if (!nodeInterop && obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(nodeInterop); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (key !== "default" && Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
var _default = {
  data: function data() {
    return {
      id: 0,
      goods: null,
      images: [],
      favorited: false,
      showSheet: false,
      bookQty: 500,
      submitting: false
    };
  },
  computed: {
    isBuyer: function isBuyer() {
      return _user.default.isBuyer;
    }
  },
  onLoad: function onLoad(q) {
    this.id = q.id;
    this.load();
  },
  methods: {
    load: function load() {
      var _this = this;
      return (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee() {
        var goods;
        return _regenerator.default.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.prev = 0;
                _context.next = 3;
                return _request.default.get(_request.api.goodsDetail(_this.id), {}, {
                  hideError: true
                });
              case 3:
                goods = _context.sent;
                _this.goods = goods;
                _this.images = goods.images || [];
                // 默认数量:库存 5% 取整,但不少于 1,不超过可订库存
                _this.bookQty = Math.max(1, Math.min(500, Math.floor(goods.available / 20) || 500));
                _context.next = 11;
                break;
              case 9:
                _context.prev = 9;
                _context.t0 = _context["catch"](0);
              case 11:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, null, [[0, 9]]);
      }))();
    },
    openBookSheet: function openBookSheet() {
      if (!this.goods) return;
      // 必须真 token + buyer 角色
      if (!_user.default.canOrder) {
        return uni.showModal({
          title: '请先登录',
          content: '下单需使用账号密码登录采购商账号',
          confirmText: '去登录',
          success: function success(_ref) {
            var confirm = _ref.confirm;
            if (confirm) uni.navigateTo({
              url: '/pages/login/account?role=buyer'
            });
          }
        });
      }
      this.showSheet = true;
    },
    closeBookSheet: function closeBookSheet() {
      this.showSheet = false;
    },
    submitBook: function submitBook() {
      var _this2 = this;
      return (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee2() {
        var qty, order;
        return _regenerator.default.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                if (_this2.goods) {
                  _context2.next = 2;
                  break;
                }
                return _context2.abrupt("return");
              case 2:
                qty = parseInt(_this2.bookQty, 10); // 校验(规格 8.4):整数 ≥ 1 且 ≤ 可订库存
                if (!(!qty || qty < 1)) {
                  _context2.next = 5;
                  break;
                }
                return _context2.abrupt("return", uni.showToast({
                  title: '请填写正确的预订数量',
                  icon: 'none'
                }));
              case 5:
                if (!(qty > _this2.goods.available)) {
                  _context2.next = 7;
                  break;
                }
                return _context2.abrupt("return", uni.showToast({
                  title: '预订数量超过库存',
                  icon: 'none'
                }));
              case 7:
                _this2.submitting = true;
                _context2.prev = 8;
                _context2.next = 11;
                return _request.default.post(_request.api.reservationCreate, {
                  goods_id: _this2.goods.id,
                  quantity: qty
                }, {
                  hideError: true
                });
              case 11:
                order = _context2.sent;
                uni.showModal({
                  title: '预订成功!',
                  content: "\u7F16\u53F7 ".concat(order.reservation_no, "\n\u7B49\u5F85\u6279\u53D1\u5546\u786E\u8BA4"),
                  showCancel: false,
                  success: function success() {
                    _this2.showSheet = false;
                    uni.switchTab({
                      url: '/pages/orders/orders'
                    });
                  }
                });
                _context2.next = 17;
                break;
              case 15:
                _context2.prev = 15;
                _context2.t0 = _context2["catch"](8);
              case 17:
                _this2.submitting = false;
              case 18:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2, null, [[8, 15]]);
      }))();
    },
    toggleFav: function toggleFav() {
      var _this3 = this;
      return (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee3() {
        return _regenerator.default.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                if (_user.default.token) {
                  _context3.next = 2;
                  break;
                }
                return _context3.abrupt("return", uni.navigateTo({
                  url: '/pages/login/account?role=buyer'
                }));
              case 2:
                _context3.prev = 2;
                _context3.next = 5;
                return _request.default.post(_request.api.favoriteGoods, {
                  goods_id: _this3.id
                }, {
                  hideError: true
                });
              case 5:
                _this3.favorited = !_this3.favorited;
                uni.showToast({
                  title: _this3.favorited ? '已收藏' : '已取消收藏',
                  icon: 'none'
                });
                _context3.next = 11;
                break;
              case 9:
                _context3.prev = 9;
                _context3.t0 = _context3["catch"](2);
              case 11:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3, null, [[2, 9]]);
      }))();
    },
    callWholesaler: function callWholesaler() {
      var _this4 = this;
      return (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee4() {
        var data;
        return _regenerator.default.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                _context4.prev = 0;
                _context4.next = 3;
                return _request.default.post(_request.api.callDial, {
                  shop_id: _this4.goods.shop_id
                }, {
                  hideError: true
                });
              case 3:
                data = _context4.sent;
                uni.makePhoneCall({
                  phoneNumber: data.phone
                });
                _context4.next = 9;
                break;
              case 7:
                _context4.prev = 7;
                _context4.t0 = _context4["catch"](0);
              case 9:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4, null, [[0, 7]]);
      }))();
    }
  }
};
exports.default = _default;
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/index.js */ 2)["default"]))

/***/ }),

/***/ 118:
/*!******************************************************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue?vue&type=style&index=0&id=5566b618&scoped=true&lang=css& ***!
  \******************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/mini-css-extract-plugin/dist/loader.js??ref--6-oneOf-1-0!./node_modules/css-loader/dist/cjs.js??ref--6-oneOf-1-1!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--6-oneOf-1-2!./node_modules/postcss-loader/src??ref--6-oneOf-1-3!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!./goods.vue?vue&type=style&index=0&id=5566b618&scoped=true&lang=css& */ 119);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_goods_vue_vue_type_style_index_0_id_5566b618_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ 119:
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??ref--6-oneOf-1-0!./node_modules/css-loader/dist/cjs.js??ref--6-oneOf-1-1!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--6-oneOf-1-2!./node_modules/postcss-loader/src??ref--6-oneOf-1-3!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!F:/2026/GCH/code/gch/uniapp-miniprogram/pages/goods/goods.vue?vue&type=style&index=0&id=5566b618&scoped=true&lang=css& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin
    if(false) { var cssReload; }
  

/***/ })

},[[112,"common/runtime","common/vendor"]]]);
//# sourceMappingURL=../../../.sourcemap/mp-weixin/pages/goods/goods.js.map