import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Checkbox from 'flarum/common/components/Checkbox';
import Modal from 'flarum/common/components/Modal';
import Select from 'flarum/common/components/Select';
import Stream from 'flarum/common/utils/Stream';

const translation = 'tacraft-sociality-server-oauth.admin.scopes';

export default class EditScopeModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.scope = this.attrs.scope;
    this.fields = ['scope', 'resource_path', 'method', 'scope_name', 'scope_icon', 'scope_desc', 'visible_fields'];

    this.values = this.fields.reduce((values, key) => {
      values[key] = Stream(this.scope[key]() || '');
      return values;
    }, {});

    this.isDefault = Stream(this.scope.is_default() === 1 || this.scope.is_default() === true);
  }

  className() {
    return 'EditScopeModal Modal--large';
  }

  title() {
    return app.translator.trans(translation + '.edit_scope');
  }

  content() {
    return (
      <div className="Modal-body">
        <form onsubmit={this.onsubmit.bind(this)}>
          <div className="OAuthCenter-Columns">
            <div className="OAuthCenter-Column">
              {this.renderInput('scope')}
              {this.renderInput('resource_path')}
              {this.renderMethod()}
              {this.renderDefault()}
            </div>
            <div className="OAuthCenter-Column">
              {this.renderInput('scope_name')}
              {this.renderInput('scope_icon')}
              {this.renderInput('scope_desc')}
            </div>
          </div>

          <div className="Form-group">
            <label>{app.translator.trans(translation + '.visible_fields')}</label>
            <textarea className="FormControl" rows="3" placeholder="id,username,email,data.attributes" bidi={this.values.visible_fields} />
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

  renderInput(key) {
    return (
      <div className="Form-group" key={key}>
        <label>{app.translator.trans(translation + '.' + key)}</label>
        <input className="FormControl" bidi={this.values[key]} />
      </div>
    );
  }

  renderMethod() {
    return (
      <div className="Form-group" key="method">
        <label>{app.translator.trans(translation + '.method')}</label>
        <Select
          options={{ GET: 'GET', POST: 'POST', PUT: 'PUT', PATCH: 'PATCH', DELETE: 'DELETE' }}
          value={this.values.method()}
          onchange={this.values.method}
        />
      </div>
    );
  }

  renderDefault() {
    return (
      <div className="Form-group" key="is_default">
        <Checkbox
          state={this.isDefault()}
          onchange={(value) => {
            this.isDefault(value);
            m.redraw();
          }}
        >
          {app.translator.trans(translation + '.is_default')}
        </Checkbox>
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

    data.is_default = this.isDefault() ? 1 : 0;

    this.scope.save(data).then(() => {
      this.loading = false;
      m.redraw();
      this.hide();
    });
  }
}
