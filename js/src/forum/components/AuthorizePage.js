import app from 'flarum/forum/app';
import Avatar from 'flarum/common/components/Avatar';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Page from 'flarum/common/components/Page';
import extractText from 'flarum/common/utils/extractText';
import ScopeItem from './ScopeItem';

export default class AuthorizePage extends Page {
  client = null;
  scopes = null;
  clientScopes = [];
  loading = true;
  authorized = false;
  submitLoading = false;

  oninit(vnode) {
    super.oninit(vnode);

    app.setTitle(extractText(app.translator.trans('tacraft-sociality-server-oauth.forum.page.title.authorize')));

    this.params = m.route.param();

    if (!app.session.user) {
      return;
    }

    if (!this.params.client_id || !this.params.response_type || !this.params.redirect_uri) {
      m.route.set('/');
      return;
    }

    Promise.all([app.store.find('oauth-clients', this.params.client_id), app.store.find('oauth-scopes')])
      .then(([client, scopes]) => {
        this.client = client;
        this.scopes = scopes;

        const redirectUris = (client.redirect_uri() || '').split(' ');

        if (app.forum.attribute('serverOAuthRequireExactRedirectUri') && !redirectUris.includes(this.params.redirect_uri)) {
          m.route.set('/');
          return;
        }

        if (!app.forum.attribute('serverOAuthAllowImplicit') && this.params.response_type === 'token') {
          m.route.set('/');
          return;
        }

        if (app.forum.attribute('serverOAuthEnforceState') && !this.params.state) {
          m.route.set('/');
          return;
        }

        const requested = (this.params.scope || client.scope() || '').split(' ');
        const defaults = scopes.filter((scope) => scope.is_default() === 1 || scope.is_default() === true).map((scope) => scope.scope());

        this.clientScopes = [...new Set([...requested, ...defaults])].filter((scope) => scope);

        this.loading = false;
        m.redraw();
      })
      .catch(() => {
        this.loading = false;
        m.route.set('/');
      });
  }

  oncreate(vnode) {
    super.oncreate(vnode);

    if (!app.session.user) {
      setTimeout(() => this.showLogin(), 0);
    }
  }

  showLogin() {
    app.modal.show(() => flarum.reg.asyncModuleImport('flarum/forum/components/LogInModal'));
  }

  view() {
    if (!app.session.user) {
      return (
        <div className="AuthorizePage">
          <div className="container">
            <div className="oauth-area">
              <div className="oauth-main">
                <div className="oauth-header">
                  <h2>{app.forum.attribute('title')}</h2>
                </div>
                <div className="oauth-body">
                  <p>{app.translator.trans('tacraft-sociality-server-oauth.forum.authorize.login_required')}</p>
                  <Button className="Button Button--primary Button--block" onclick={() => this.showLogin()}>
                    {app.translator.trans('core.forum.header.log_in_link')}
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      );
    }

    if (this.loading || !this.client) {
      return <LoadingIndicator />;
    }

    const baseUrl = app.forum.attribute('baseUrl') || '';
    const icon = this.client.client_icon();

    return (
      <div className="AuthorizePage">
        <div className="container">
          <div className="oauth-area">
            <div className={'oauth-main oauth-' + (app.forum.attribute('serverOAuthDisplayMode') || 'box')}>
              <div className="oauth-header">
                <h2>{app.forum.attribute('title')}</h2>
                <p>
                  {app.translator.trans('tacraft-sociality-server-oauth.forum.authorize.access')}{' '}
                  <a href={this.client.client_home() || '#'} target="_blank" rel="noopener noreferrer">
                    {this.client.client_name() || this.client.client_id()}
                  </a>
                </p>
              </div>

              <div className="oauth-body">
                <div className="oauth-user">
                  <Avatar user={app.session.user} className="oauth-avatar" />
                  <div className="oauth-username">
                    <b>{app.session.user.username()}</b>
                    {app.session.user.displayName() !== app.session.user.username() ? <span>{app.session.user.displayName()}</span> : null}
                  </div>
                </div>

                <div className="oauth-info">
                  {app.forum.attribute('faviconUrl') ? <img src={app.forum.attribute('faviconUrl')} alt="" /> : null}
                  <i className="fas fa-exchange-alt fa-2x" />
                  {icon && icon.indexOf('fa-') === -1 ? <img src={icon} alt="" /> : <i className={(icon || 'fas fa-cube') + ' fa-3x'} />}
                  <span>{this.client.client_name() || this.client.client_id()}</span>
                </div>

                <div className="oauth-scope-area">
                  <h3>{app.translator.trans('tacraft-sociality-server-oauth.forum.authorize.require_these_scopes')}</h3>
                  {this.clientScopes.map((name) => {
                    const scope = this.scopes.find((item) => item.scope() === name);

                    return scope ? <ScopeItem scope={scope} client={this.client} /> : null;
                  })}
                </div>

                <form className="oauth-form" method="post" action={baseUrl + '/oauth/authorize'} onsubmit={this.onsubmit.bind(this)}>
                  <input type="hidden" name="client_id" value={this.params.client_id} />
                  <input type="hidden" name="response_type" value={this.params.response_type} />
                  <input type="hidden" name="redirect_uri" value={this.params.redirect_uri} />
                  {this.params.scope ? <input type="hidden" name="scope" value={this.params.scope} /> : null}
                  {this.params.state ? <input type="hidden" name="state" value={this.params.state} /> : null}
                  <input type="hidden" name="is_authorized" value={this.authorized ? '1' : '0'} />

                  <div className="oauth-btn-group">
                    <Button
                      className="Button"
                      type="submit"
                      loading={this.submitLoading}
                      onclick={() => {
                        this.authorized = false;
                      }}
                    >
                      {app.translator.trans('tacraft-sociality-server-oauth.forum.authorize.deny')}
                    </Button>

                    <Button
                      className="Button Button--primary"
                      type="submit"
                      loading={this.submitLoading}
                      onclick={() => {
                        this.authorized = true;
                      }}
                    >
                      {app.translator.trans('tacraft-sociality-server-oauth.forum.authorize.agree')}
                    </Button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  onsubmit(event) {
    if (!app.forum.attribute('serverOAuthAuthorizationMethodFetch')) {
      event.target.elements.is_authorized.value = this.authorized ? '1' : '0';
      return;
    }

    event.preventDefault();

    this.submitLoading = true;

    const body = {
      client_id: this.params.client_id,
      response_type: this.params.response_type,
      redirect_uri: this.params.redirect_uri,
      is_authorized: this.authorized ? '1' : '0',
    };

    if (this.params.scope) body.scope = this.params.scope;
    if (this.params.state) body.state = this.params.state;

    app
      .request({
        method: 'POST',
        url: (app.forum.attribute('baseUrl') || '') + '/oauth/authorize/fetch',
        body,
      })
      .then((response) => {
        window.location.href = response.location;
      })
      .catch(() => {
        this.submitLoading = false;
        m.redraw();
      });
  }
}
