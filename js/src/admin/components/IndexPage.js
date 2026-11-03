import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Component from 'flarum/common/Component';
import Select from 'flarum/common/components/Select';
import Stream from 'flarum/common/utils/Stream';
import Switch from 'flarum/common/components/Switch';
import extractText from 'flarum/common/utils/extractText';
import saveSettings from 'flarum/admin/utils/saveSettings';

const prefix = 'tacraft-sociality-server-oauth.';
const translation = 'tacraft-sociality-server-oauth.admin';

export default class IndexPage extends Component {
  saving = false;

  flags = {
    allow_delete_records: false,
    allow_implicit: false,
    enforce_state: false,
    require_exact_redirect_uri: false,
    authorization_method_fetch: false,
  };

  oninit(vnode) {
    super.oninit(vnode);

    const settings = app.data.settings;

    this.displayMode = Stream(settings[prefix + 'display_mode'] || 'box');
    this.accessLifetime = Stream(settings[prefix + 'access_lifetime'] || '3600');

    Object.keys(this.flags).forEach((key) => {
      this.flags[key] = settings[prefix + key] === '1';
    });
  }

  view() {
    return (
      <div>
        <form className="Form" onsubmit={this.onsubmit.bind(this)}>
          <div className="Form-group">
            <label>{app.translator.trans(translation + '.settings.display_mode')}</label>
            <Select
              options={{ box: 'Box', card: 'Card', column: 'Column' }}
              value={this.displayMode()}
              onchange={(value) => {
                this.displayMode(value);
                this.saveSetting('display_mode', value);
              }}
            />
          </div>

          <div className="Form-group">
            <label>{app.translator.trans(translation + '.settings.access_lifetime')}</label>
            <input className="FormControl" type="number" min="60" bidi={this.accessLifetime} />
          </div>

          <div className="Form-group">
            {Object.keys(this.flags).map((key) => (
              <div className="Form-group" key={key}>
                <Switch
                  state={this.flags[key]}
                  loading={this.saving}
                  onchange={(value) => {
                    this.flags[key] = value;
                    this.saveSetting(key, value);
                  }}
                >
                  {app.translator.trans(translation + '.settings.' + key)}
                </Switch>
              </div>
            ))}
          </div>

          <div className="Form-group">
            <Button type="submit" className="Button Button--primary" loading={this.saving}>
              {app.translator.trans('core.admin.settings.submit_button')}
            </Button>
          </div>
        </form>

        <hr />

        <Button
          className="Button Button--danger"
          onclick={() => {
            if (!confirm(extractText(app.translator.trans(translation + '.action.delete_all_records_confirm')))) return;

            app.request({ method: 'DELETE', url: app.forum.attribute('apiUrl') + '/oauth-records/all' }).then(() => {
              app.alerts.show({ type: 'success' }, app.translator.trans(translation + '.action.delete_all_records_success'));
            });
          }}
        >
          {app.translator.trans(translation + '.action.delete_all_records_button')}
        </Button>
      </div>
    );
  }

  onsubmit(event) {
    event.preventDefault();

    if (this.saving) return;

    this.saveSetting('access_lifetime', this.accessLifetime() || '3600');
  }

  saveSetting(key, value) {
    if (this.saving) return;

    this.saving = true;

    let stored = value;

    if (typeof value === 'boolean') {
      stored = value ? '1' : '0';
    }

    saveSettings({ [prefix + key]: stored })
      .then(() => app.alerts.show({ type: 'success' }, app.translator.trans('core.admin.settings.saved_message')))
      .finally(() => {
        this.saving = false;
        m.redraw();
      });
  }
}
