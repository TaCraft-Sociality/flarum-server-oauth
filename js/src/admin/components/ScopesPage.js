import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Component from 'flarum/common/Component';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import { randomString } from '../../common/utils/randomString';
import EditScopeModal from './EditScopeModal';

const translation = 'tacraft-sociality-server-oauth.admin.scopes';

export default class ScopesPage extends Component {
  scopes = [];
  loading = true;

  oninit(vnode) {
    super.oninit(vnode);

    app.store.find('oauth-scopes').then((scopes) => {
      this.scopes = scopes;
      this.loading = false;
      m.redraw();
    });
  }

  view() {
    if (this.loading) {
      return <LoadingIndicator />;
    }

    return (
      <div className="OAuthCenter-scopesPage">
        <table className="OAuthCenter-table">
          <thead>
            <tr>
              <th>{app.translator.trans(translation + '.scope')}</th>
              <th>{app.translator.trans(translation + '.scope_name')}</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {this.scopes.map((scope) => (
              <tr key={scope.id()}>
                <td>{scope.scope()}</td>
                <td>{scope.scope_name()}</td>
                <td>
                  <Button className="Button Button--icon" icon="fas fa-edit" aria-label="Edit" onclick={() => this.showEditModal(scope)} />
                  <Button
                    className="Button Button--icon"
                    icon="fas fa-times"
                    aria-label="Delete"
                    onclick={() => {
                      scope.delete().then(() => {
                        this.scopes = this.scopes.filter((item) => item !== scope);
                        m.redraw();
                      });
                    }}
                  />
                </td>
              </tr>
            ))}
            <tr>
              <td colspan="3">
                <Button
                  className="Button Button--block"
                  onclick={() => {
                    app.store
                      .createRecord('oauth-scopes')
                      .save({
                        scope: 'scope.' + randomString(8),
                        resource_path: '/api/' + randomString(4),
                        method: 'GET',
                      })
                      .then((scope) => {
                        this.scopes.push(scope);
                        this.showEditModal(scope);
                      });
                  }}
                >
                  {app.translator.trans(translation + '.add_button')}
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    );
  }

  showEditModal(scope) {
    app.modal.show(EditScopeModal, { scope });
  }
}
