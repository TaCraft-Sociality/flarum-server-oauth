import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import ClientsPage from './ClientsPage';
import IndexPage from './IndexPage';
import ScopesPage from './ScopesPage';
import TokensPage from './TokensPage';

const extensionId = 'tacraft-sociality-server-oauth';
const translationPrefix = 'tacraft-sociality-server-oauth.admin.page.';

export default class SettingsPage extends ExtensionPage {
  pages = {
    index: IndexPage,
    clients: ClientsPage,
    scopes: ScopesPage,
    tokens: TokensPage,
  };

  icons = {
    index: 'fas fa-home',
    clients: 'fas fa-network-wired',
    scopes: 'fas fa-user-lock',
    tokens: 'fas fa-key',
  };

  content() {
    const current = m.route.param().page || 'index';

    return (
      <div className="ExtensionPage-settings OAuthCenter">
        <div className="oauth-menu">
          <div className="container">
            {Object.keys(this.pages).map((key) => (
              <Button
                className={`Button ${current === key ? 'item-selected' : ''}`}
                icon={this.icons[key]}
                onclick={() => m.route.set(app.route('extension', { id: extensionId, page: key }))}
              >
                {app.translator.trans(translationPrefix + key)}
              </Button>
            ))}
          </div>
        </div>

        <div className="container OAuthCenterPage-container">{this.pageContent(current)}</div>
      </div>
    );
  }

  pageContent(page) {
    const PageComponent = this.pages[page];

    return PageComponent ? <PageComponent /> : null;
  }
}
