import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Modal from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';

const translation = 'tacraft-sociality-server-oauth.admin.tokens';

export default class AddTokenModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.fields = ['access_token', 'client_id', 'user_id', 'expires', 'scope'];

    this.values = this.fields.reduce((values, key) => {
      values[key] = Stream('');
      return values;
    }, {});
  }

  className() {
    return 'AddTokenModal';
  }

  title() {
    return app.translator.trans(translation + '.add_token');
  }

  content() {
    return (
      <div className="Modal-body">
        <form onsubmit={this.onsubmit.bind(this)}>
          {this.fields.map((key) => (
            <div className="Form-group" key={key}>
              <label>{key}</label>
              <input className="FormControl" bidi={this.values[key]} required />
            </div>
          ))}

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

    app.store
      .createRecord('oauth-tokens')
      .save(data)
      .then(() => {
        this.loading = false;
        m.redraw();
        this.hide();
      });
  }
}
