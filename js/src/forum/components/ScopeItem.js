import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';

export default class ScopeItem extends Component {
  view() {
    const { scope, client } = this.attrs;
    const icon = scope.scope_icon() || 'fas fa-check';
    const description = (scope.scope_desc() || '')
      .replace('{client_name}', client.client_name() || client.client_id())
      .replace('{user}', app.session.user ? app.session.user.displayName() : '');

    return (
      <div className="oauth-scope">
        {icon.indexOf('fa-') > -1 ? <i className={'oauth-scope-object fa-2x ' + icon} /> : <img className="oauth-scope-object" src={icon} alt="" />}

        <div className="oauth-scope-body">
          <h6 className="oauth-scope-heading">{scope.scope_name() || scope.scope()}</h6>
          <small>{description}</small>
        </div>
      </div>
    );
  }
}
