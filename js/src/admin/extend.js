import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import SettingsPage from './components/SettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin()
    .page(SettingsPage)
    .permission(
      () => ({
        icon: 'fas fa-lock',
        label: app.translator.trans('tacraft-sociality-server-oauth.admin.permission.use_oauth'),
        permission: 'tacraft-sociality-server-oauth.use-oauth',
      }),
      'view',
      95
    ),
];
