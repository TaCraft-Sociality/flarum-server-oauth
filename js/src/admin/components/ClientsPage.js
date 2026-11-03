import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Component from 'flarum/common/Component';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import { randomString } from '../../common/utils/randomString';
import EditClientModal from './EditClientModal';

const translation = 'tacraft-sociality-server-oauth.admin.clients';

export default class ClientsPage extends Component {
  clients = [];
  loading = true;

  oninit(vnode) {
    super.oninit(vnode);

    app.store.find('oauth-clients').then((clients) => {
      this.clients = clients;
      this.loading = false;
      m.redraw();
    });
  }

  view() {
    if (this.loading) {
      return <LoadingIndicator />;
    }

    return (
      <div className="OAuthCenter-clientsPage">
        <table className="OAuthCenter-table">
          <thead>
            <tr>
              <th>{app.translator.trans(translation + '.client_id')}</th>
              <th>{app.translator.trans(translation + '.client_name')}</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {this.clients.map((client) => (
              <tr key={client.id()}>
                <td>{client.client_id()}</td>
                <td>{client.client_name()}</td>
                <td>
                  <Button className="Button Button--icon" icon="fas fa-edit" aria-label="Edit" onclick={() => this.showEditModal(client)} />
                  <Button
                    className="Button Button--icon"
                    icon="fas fa-times"
                    aria-label="Delete"
                    onclick={() => {
                      client.delete().then(() => {
                        this.clients = this.clients.filter((item) => item !== client);
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
                      .createRecord('oauth-clients')
                      .save({
                        client_id: randomString(32),
                        client_secret: randomString(32),
                      })
                      .then((client) => {
                        this.clients.push(client);
                        this.showEditModal(client);
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

  showEditModal(client) {
    app.modal.show(EditClientModal, { client });
  }
}
