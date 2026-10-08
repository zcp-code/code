(global["webpackJsonp"] = global["webpackJsonp"] || []).push([["pages/wholesaler/publish"],{

/***/ 168:
/*!***********************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/main.js?{"page":"pages%2Fwholesaler%2Fpublish"} ***!
  \***********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(wx, createPage) {

var _interopRequireDefault = __webpack_require__(/*! @babel/runtime/helpers/interopRequireDefault */ 4);
__webpack_require__(/*! uni-pages */ 26);
var _vue = _interopRequireDefault(__webpack_require__(/*! vue */ 25));
var _publish = _interopRequireDefault(__webpack_require__(/*! ./pages/wholesaler/publish.vue */ 169));
// @ts-ignore
wx.__webpack_require_UNI_MP_PLUGIN__ = __webpack_require__;
createPage(_publish.default);
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/wx.js */ 1)["default"], __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/index.js */ 2)["createPage"]))

/***/ }),

/***/ 169:
/*!****************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue ***!
  \****************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./publish.vue?vue&type=template&id=3a745426&scoped=true& */ 170);
/* harmony import */ var _publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./publish.vue?vue&type=script&lang=js& */ 172);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[key]; }) }(__WEBPACK_IMPORT_KEY__));
/* harmony import */ var _publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./publish.vue?vue&type=style&index=0&id=3a745426&scoped=true&lang=css& */ 174);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/runtime/componentNormalizer.js */ 33);

var renderjs





/* normalize component */

var component = Object(_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "3a745426",
  null,
  false,
  _publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["components"],
  renderjs
)

component.options.__file = "pages/wholesaler/publish.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ 170:
/*!***********************************************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue?vue&type=template&id=3a745426&scoped=true& ***!
  \***********************************************************************************************************************/
/*! exports provided: render, staticRenderFns, recyclableRender, components */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--17-0!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/template.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-uni-app-loader/page-meta.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!./publish.vue?vue&type=template&id=3a745426&scoped=true& */ 171);
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "recyclableRender", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["recyclableRender"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "components", function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_17_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_template_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_uni_app_loader_page_meta_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_template_id_3a745426_scoped_true___WEBPACK_IMPORTED_MODULE_0__["components"]; });



/***/ }),

/***/ 171:
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--17-0!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/template.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-uni-app-loader/page-meta.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue?vue&type=template&id=3a745426&scoped=true& ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
}
var recyclableRender = false
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ 172:
/*!*****************************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/babel-loader/lib!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--13-1!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/script.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!./publish.vue?vue&type=script&lang=js& */ 173);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_babel_loader_lib_index_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_13_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_script_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ 173:
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--13-1!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/script.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(uni, wx) {

var _interopRequireDefault = __webpack_require__(/*! @babel/runtime/helpers/interopRequireDefault */ 4);
var _typeof = __webpack_require__(/*! @babel/runtime/helpers/typeof */ 13);
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _regenerator = _interopRequireDefault(__webpack_require__(/*! @babel/runtime/regenerator */ 51));
var _asyncToGenerator2 = _interopRequireDefault(__webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ 53));
var _request = _interopRequireWildcard(__webpack_require__(/*! @/utils/request.js */ 35));
function _getRequireWildcardCache(nodeInterop) { if (typeof WeakMap !== "function") return null; var cacheBabelInterop = new WeakMap(); var cacheNodeInterop = new WeakMap(); return (_getRequireWildcardCache = function _getRequireWildcardCache(nodeInterop) { return nodeInterop ? cacheNodeInterop : cacheBabelInterop; })(nodeInterop); }
function _interopRequireWildcard(obj, nodeInterop) { if (!nodeInterop && obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(nodeInterop); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (key !== "default" && Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
// 单位选项（picker 单选）
var UNITS = ['斤', '公斤', '箱', '袋', '盒', '件', '个', '只', '把', '捆'];
var _default = {
  data: function data() {
    return {
      categories: [],
      categoryIndex: -1,
      units: UNITS,
      unitIndex: -1,
      form: {
        category_id: 0,
        name: '',
        price: '',
        unit: '',
        total_stock: ''
      },
      images: [],
      submitting: false
    };
  },
  onLoad: function onLoad() {
    this.loadCategories();
  },
  methods: {
    loadCategories: function loadCategories() {
      var _this = this;
      return (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee() {
        return _regenerator.default.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.prev = 0;
                _context.next = 3;
                return _request.default.get(_request.api.wholesalerCategories, {}, {
                  hideError: true
                });
              case 3:
                _this.categories = _context.sent;
                _context.next = 9;
                break;
              case 6:
                _context.prev = 6;
                _context.t0 = _context["catch"](0);
                uni.showToast({
                  title: _context.t0.message || "操作失败",
                  icon: "none"
                });
              case 9:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, null, [[0, 6]]);
      }))();
    },
    onCategoryChange: function onCategoryChange(e) {
      this.categoryIndex = e.detail.value;
      this.form.category_id = this.categories[this.categoryIndex].id;
    },
    onUnitChange: function onUnitChange(e) {
      this.unitIndex = e.detail.value;
      this.form.unit = UNITS[this.unitIndex];
    },
    chooseImage: function chooseImage() {
      var _this2 = this;
      uni.chooseImage({
        count: 9 - this.images.length,
        sizeType: ['compressed'],
        // 压缩上传,减少体积
        success: function () {
          var _success = (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee2(_ref) {
            var tempFilePaths, _iterator, _step, _loop;
            return _regenerator.default.wrap(function _callee2$(_context3) {
              while (1) {
                switch (_context3.prev = _context3.next) {
                  case 0:
                    tempFilePaths = _ref.tempFilePaths;
                    _iterator = _createForOfIteratorHelper(tempFilePaths);
                    _context3.prev = 2;
                    _loop = /*#__PURE__*/_regenerator.default.mark(function _loop() {
                      var p, up, body, msg;
                      return _regenerator.default.wrap(function _loop$(_context2) {
                        while (1) {
                          switch (_context2.prev = _context2.next) {
                            case 0:
                              p = _step.value;
                              _context2.prev = 1;
                              _context2.next = 4;
                              return new Promise(function (resolve, reject) {
                                wx.uploadFile({
                                  url: getApp().globalData.apiBase + _request.api.commonUpload,
                                  filePath: p,
                                  name: 'file',
                                  header: {
                                    'Content-Type': 'multipart/form-data'
                                  },
                                  success: resolve,
                                  fail: reject
                                });
                              });
                            case 4:
                              up = _context2.sent;
                              try {
                                body = JSON.parse(up.data);
                              } catch (e) {
                                body = up.data;
                              }
                              if (body && body.code === 200 && body.data && body.data.url) {
                                _this2.images.push(body.data.url);
                                uni.showToast({
                                  title: '上传成功',
                                  icon: 'success'
                                });
                              } else {
                                uni.showToast({
                                  title: body && body.msg || '上传失败',
                                  icon: 'none'
                                });
                              }
                              _context2.next = 14;
                              break;
                            case 9:
                              _context2.prev = 9;
                              _context2.t0 = _context2["catch"](1);
                              msg = _context2.t0 && (_context2.t0.errMsg || _context2.t0.message) || '上传失败';
                              uni.showToast({
                                title: msg,
                                icon: 'none',
                                duration: 3000
                              });
                              console.error('[upload] failed:', _context2.t0);
                            case 14:
                            case "end":
                              return _context2.stop();
                          }
                        }
                      }, _loop, null, [[1, 9]]);
                    });
                    _iterator.s();
                  case 5:
                    if ((_step = _iterator.n()).done) {
                      _context3.next = 9;
                      break;
                    }
                    return _context3.delegateYield(_loop(), "t0", 7);
                  case 7:
                    _context3.next = 5;
                    break;
                  case 9:
                    _context3.next = 14;
                    break;
                  case 11:
                    _context3.prev = 11;
                    _context3.t1 = _context3["catch"](2);
                    _iterator.e(_context3.t1);
                  case 14:
                    _context3.prev = 14;
                    _iterator.f();
                    return _context3.finish(14);
                  case 17:
                  case "end":
                    return _context3.stop();
                }
              }
            }, _callee2, null, [[2, 11, 14, 17]]);
          }));
          function success(_x) {
            return _success.apply(this, arguments);
          }
          return success;
        }()
      });
    },
    removeImage: function removeImage(i) {
      this.images.splice(i, 1);
    },
    submit: function submit() {
      var _this3 = this;
      return (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee3() {
        var f, relativeImages;
        return _regenerator.default.wrap(function _callee3$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                if (!_this3.submitting) {
                  _context4.next = 2;
                  break;
                }
                return _context4.abrupt("return");
              case 2:
                f = _this3.form;
                if (!(!f.category_id || !f.name || !f.price || !f.unit || !f.total_stock)) {
                  _context4.next = 5;
                  break;
                }
                return _context4.abrupt("return", uni.showToast({
                  title: '请填写必填项',
                  icon: 'none'
                }));
              case 5:
                if (!(Number(f.total_stock) < 1)) {
                  _context4.next = 7;
                  break;
                }
                return _context4.abrupt("return", uni.showToast({
                  title: '库存必须 ≥ 1',
                  icon: 'none'
                }));
              case 7:
                _this3.submitting = true;
                _context4.prev = 8;
                // 后端 Common::upload 现在返完整 URL,Wholesaler::create 只取 path 字段
                relativeImages = _this3.images.map(function (u) {
                  var m = u.match(/\/uploads\/.+/);
                  return m ? m[0] : u;
                });
                _context4.next = 12;
                return _request.default.post(_request.api.wholesalerGoodsCreate, {
                  category_id: f.category_id,
                  name: f.name,
                  price: Number(f.price),
                  unit: f.unit,
                  total_stock: Number(f.total_stock),
                  images: relativeImages
                }, {
                  hideError: true
                });
              case 12:
                uni.showModal({
                  title: '发布成功',
                  content: '货盘已上架',
                  showCancel: false,
                  success: function success() {
                    return uni.navigateBack();
                  }
                });
                _context4.next = 18;
                break;
              case 15:
                _context4.prev = 15;
                _context4.t0 = _context4["catch"](8);
                uni.showToast({
                  title: _context4.t0.message || "操作失败",
                  icon: "none"
                });
              case 18:
                _this3.submitting = false;
              case 19:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee3, null, [[8, 15]]);
      }))();
    }
  }
};
exports.default = _default;
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/index.js */ 2)["default"], __webpack_require__(/*! ./node_modules/@dcloudio/uni-mp-weixin/dist/wx.js */ 1)["default"]))

/***/ }),

/***/ 174:
/*!*************************************************************************************************************************************!*\
  !*** F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue?vue&type=style&index=0&id=3a745426&scoped=true&lang=css& ***!
  \*************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/mini-css-extract-plugin/dist/loader.js??ref--6-oneOf-1-0!./node_modules/css-loader/dist/cjs.js??ref--6-oneOf-1-1!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--6-oneOf-1-2!./node_modules/postcss-loader/src??ref--6-oneOf-1-3!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!./publish.vue?vue&type=style&index=0&id=3a745426&scoped=true&lang=css& */ 175);
/* harmony import */ var _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_css_loader_dist_cjs_js_ref_6_oneOf_1_1_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_loaders_stylePostLoader_js_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_webpack_preprocess_loader_index_js_ref_6_oneOf_1_2_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_3_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_vue_cli_plugin_uni_packages_vue_loader_lib_index_js_vue_loader_options_D_LenovoQMDownload_SoftMgr_HBuilder_X_plugins_uniapp_cli_node_modules_dcloudio_webpack_uni_mp_loader_lib_style_js_publish_vue_vue_type_style_index_0_id_3a745426_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ 175:
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??ref--6-oneOf-1-0!./node_modules/css-loader/dist/cjs.js??ref--6-oneOf-1-1!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/webpack-preprocess-loader??ref--6-oneOf-1-2!./node_modules/postcss-loader/src??ref--6-oneOf-1-3!./node_modules/@dcloudio/vue-cli-plugin-uni/packages/vue-loader/lib??vue-loader-options!./node_modules/@dcloudio/webpack-uni-mp-loader/lib/style.js!F:/2026/GCH/code/gch/uniapp-miniprogram/pages/wholesaler/publish.vue?vue&type=style&index=0&id=3a745426&scoped=true&lang=css& ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin
    if(false) { var cssReload; }
  

/***/ })

},[[168,"common/runtime","common/vendor"]]]);
//# sourceMappingURL=../../../.sourcemap/mp-weixin/pages/wholesaler/publish.js.map