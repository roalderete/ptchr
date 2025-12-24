
import {createApp} from 'vue';
import components from './components';
import pages from './pages';
import acl, {AclAPI} from './core/plugins/acl/acl';
import toaster, {ToasterAPI} from './core/plugins/toaster/toaster';
import createI18n, {TranslateAPI} from './core/plugins/i18n/translate';
import '@ohrm/oxd/fonts.css';
import '@ohrm/oxd/icons.css';
import '@ohrm/oxd/style.css';
import './core/styles/global.scss';
import './core/plugins/toaster/toaster.scss';
import './core/plugins/loader/loader.scss';

const app = createApp({
  name: 'App',
  components: pages,
});

// Global Register Components
app.use(components);

app.use(toaster, {
  duration: 2500,
  persist: false,
  animation: 'oxd-toast-list',
  position: 'bottom',
});

// @ts-expect-error: appGlobal is not in window object by default
const baseUrl = window.appGlobal.baseUrl;

const {i18n, init} = createI18n({
  baseUrl: baseUrl,
  resourceUrl: '/core/i18n/messages',
});

app.use(acl);
app.use(i18n);

// https://github.com/vuejs/vue-next/pull/982
declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $toast: ToasterAPI;
    $can: AclAPI;
    $t: TranslateAPI;
  }
}

app.config.globalProperties.global = {
  baseUrl,
};

init().then(() => app.mount('#app'));
