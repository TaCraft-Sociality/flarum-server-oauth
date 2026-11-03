import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Modal from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';

const translation = 'tacraft-sociality-server-oauth.admin.clients';

export default class EditClientModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.client = this.attrs.client;
    this.fields = ['client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'client_name', 'client_desc', 'client_icon', 'client_home'];

    this.values = this.fields.reduce((values, key) => {
      values[key] = Stream(this.client[key]() || '');
      return values;
    }, {});
  }

  className() {
    return 'EditClientModal Modal--large';
  }

  title() {
    return app.translator.trans(translation + '.edit_client');
  }

  content() {
    return (
      <div className="Modal-body">
        <form onsubmit={this.onsubmit.bind(this)}>
          <div className="OAuthCenter-Columns">
            <div className="OAuthCenter-Column">
              {this.fields.slice(0, 5).map((key) => (
                <div className="Form-group" key={key}>
                  <label>{app.translator.trans(translation + '.' + key)}</label>
                  <input className="FormControl" bidi={this.values[key]} />
                </div>
              ))}
            </div>
            <div className="OAuthCenter-Column">
              {this.fields.slice(5).map((key) => (
                <div className="Form-group" key={key}>
                  <label>{app.translator.trans(translation + '.' + key)}</label>
                  <input className="FormControl" bidi={this.values[key]} />
                </div>
              ))}
            </div>
          </div>

          <div className="Form-group">
            <Button type="submit" className="Button Button--primary Button--block" loading={this.loading}>
              {app.translator.trans('core.admin.settings.submit_button')}
            </Button>
          </div>
        </form>
      </div>
    );
  }

  onsubmit(event) {
    event.preventDefault();

    this.loading = true;

    const data = this.fields.reduce((values, key) => {
      values[key] = this.values[key]();
      return values;
    }, {});

    this.client.save(data).then(() => {
      this.loading = false;
      m.redraw();
      this.hide();
    });
  }
}
