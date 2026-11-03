import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';
import UserPage from 'flarum/forum/components/UserPage';

app.initializers.add('tacraft-sociality/flarum-server-oauth', () => {
  extend(UserPage.prototype, 'navItems', function (items) {
    if (!this.user || !app.session.user) return;

    if (app.session.user.id() !== this.user.id()) return;

    items.add(
      'oauth-authorized',
      <LinkButton href={app.route('user.authorized', { username: this.user.slug() })} icon="fas fa-lock">
        {app.translator.trans('tacraft-sociality-server-oauth.forum.page.label.authorized')}
      </LinkButton>,
      -105
    );
  });
});

export { default as extend } from './extend';
