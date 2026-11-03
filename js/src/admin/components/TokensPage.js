import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Component from 'flarum/common/Component';
import AddTokenModal from './AddTokenModal';

const translation = 'tacraft-sociality-server-oauth.admin.tokens';

export default class TokensPage extends Component {
  view() {
    return (
      <div>
        <Button className="Button" onclick={() => app.modal.show(AddTokenModal, {})}>
          {app.translator.trans(translation + '.add_token')}
        </Button>

        <Button
          className="Button"
          onclick={() => {
            app.request({ method: 'DELETE', url: app.forum.attribute('apiUrl') + '/oauth-tokens/expired' }).then(() => {
              app.alerts.show({ type: 'success' }, app.translator.trans('core.admin.settings.saved_message'));
            });
          }}
        >
          {app.translator.trans(translation + '.delete_token')}
        </Button>
      </div>
    );
  }
}
