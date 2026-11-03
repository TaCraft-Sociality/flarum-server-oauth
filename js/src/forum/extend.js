import Extend from 'flarum/common/extenders';
import UserPageResolver from 'flarum/forum/resolvers/UserPageResolver';
import commonExtend from '../common/extend';
import AuthorizePage from './components/AuthorizePage';
import AuthorizedPage from './components/AuthorizedPage';

export default [
  ...commonExtend,

  new Extend.Routes()
    .add('oauth.authorize', '/oauth/authorize', AuthorizePage)
    .add('user.authorized', '/u/:username/authorized', AuthorizedPage, UserPageResolver),
];
