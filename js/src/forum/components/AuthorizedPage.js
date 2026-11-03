import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import UserPage from 'flarum/forum/components/UserPage';
import extractText from 'flarum/common/utils/extractText';

export default class AuthorizedPage extends UserPage {
  records = [];
  loading = true;
  nomore = false;
  page = 0;

  oninit(vnode) {
    super.oninit(vnode);

    this.loadUser(m.route.param('username'));
    this.loadRecords();
  }

  loadRecords() {
    app.store.find('oauth-records', { page: this.page }).then((records) => {
      this.records = this.records.concat(records);
      this.nomore = records.length < 10;
      this.loading = false;
      m.redraw();
    });
  }

  loadMore() {
    if (this.loading || this.nomore) return;

    this.loading = true;
    this.page += 1;
    this.loadRecords();
  }

  content() {
    const allowDelete = app.forum.attribute('serverOAuthAllowDeleteRecords');

    if (!this.loading && this.records.length === 0) {
      return (
        <div className="AuthorizedPage">
          <p>{app.translator.trans('tacraft-sociality-server-oauth.forum.authorized.no_records')}</p>
        </div>
      );
    }

    return (
      <div className="AuthorizedPage">
        {allowDelete ? (
          <Button
            className="Button Button--danger"
            onclick={() => {
              if (!confirm(extractText(app.translator.trans('tacraft-sociality-server-oauth.forum.authorized.delete_all_confirm')))) return;

              app.request({ method: 'DELETE', url: app.forum.attribute('apiUrl') + '/oauth-records/user' }).then(() => {
                this.records = [];
                this.page = 0;
                this.nomore = true;
                m.redraw();
              });
            }}
          >
            {app.translator.trans('tacraft-sociality-server-oauth.forum.authorized.delete_all_button')}
          </Button>
        ) : null}

        <ul className="AuthorizedRecords">
          {this.records.map((record) => {
            const client = record.attribute('client') || {};

            return (
              <li className="AuthorizedRecord" key={record.id()}>
                <div className="AuthorizedRecord-content">
                  <div className="AuthorizedRecord-left">
                    {client.client_icon && client.client_icon.indexOf('fa-') > -1 ? (
                      <i className={client.client_icon + ' AuthorizedRecord-icon'} />
                    ) : client.client_icon ? (
                      <img className="AuthorizedRecord-icon" src={client.client_icon} alt="" />
                    ) : null}
                    <div className="AuthorizedRecord-info">
                      <h3>
                        <a href={client.client_home || '#'} target="_blank" rel="noopener noreferrer">
                          {client.client_name || client.client_id}
                        </a>
                      </h3>
                      <p>{client.client_desc}</p>
                    </div>
                  </div>

                  <div className="AuthorizedRecord-right">
                    <time>{record.authorized_at() ? record.authorized_at().toLocaleString() : ''}</time>

                    {allowDelete ? (
                      <Button
                        className="Button Button--danger Button--small"
                        onclick={() => {
                          record.delete().then(() => {
                            this.records = this.records.filter((item) => item !== record);
                            m.redraw();
                          });
                        }}
                      >
                        {app.translator.trans('tacraft-sociality-server-oauth.forum.authorized.delete_button')}
                      </Button>
                    ) : null}
                  </div>
                </div>
                <hr />
              </li>
            );
          })}
        </ul>

        {this.loading ? <LoadingIndicator display="block" /> : null}

        {!this.loading && !this.nomore ? (
          <div style="text-align:center;padding:20px">
            <Button className="Button Button--primary" onclick={() => this.loadMore()}>
              {app.translator.trans('tacraft-sociality-server-oauth.forum.authorized.load_more')}
            </Button>
          </div>
        ) : null}

        {this.nomore && this.records.length > 0 ? (
          <p className="helpText">{app.translator.trans('tacraft-sociality-server-oauth.forum.authorized.no_more_records')}</p>
        ) : null}
      </div>
    );
  }
}
