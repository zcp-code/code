import Vue from 'vue'
import App from './App.vue'
import auth from '@/utils/auth.js'
import request from '@/utils/request.js'

Vue.config.productionTip = false
Vue.config.devtools = process.env.NODE_ENV !== 'production'
App.mpType = 'app'

// 兼容 vue-cli uniapp 模板默认注入的字段,避免 "keepAliveInclude undefined" 警告
const app = new Vue({
  ...App,
  data() {
    return {
      ...(App.data && App.data()),
      keepAliveInclude: [],
      $Route: { meta: {} }
    }
  }
})

app.$mount()
