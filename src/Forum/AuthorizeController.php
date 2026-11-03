<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Forum;

use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\RedirectResponse;
use OAuth2\Response as OAuthResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TacraftSociality\ServerOAuth\Models\Record;
use TacraftSociality\ServerOAuth\OAuth\RequestFactory;
use TacraftSociality\ServerOAuth\OAuth\ResponseFactory;
use TacraftSociality\ServerOAuth\OAuth\ServerFactory;
use TacraftSociality\ServerOAuth\Permission;
use TacraftSociality\ServerOAuth\Support\JsonApi;

class AuthorizeController implements RequestHandlerInterface
{
    public function __construct(
        protected ServerFactory $servers
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        if (! $actor->hasPermission(Permission::USE_OAUTH)) {
            return JsonApi::error(403, 'You do not have permission to use OAuth');
        }

        $oauthRequest = RequestFactory::fromPsr($request);
        $oauthResponse = new OAuthResponse();
        $server = $this->servers->make();

        if (! $server->validateAuthorizeRequest($oauthRequest, $oauthResponse)) {
            return ResponseFactory::fromOAuth($oauthResponse);
        }

        $body = $request->getParsedBody();
        $body = is_array($body) ? $body : [];

        // The consent form posts everything in the body, so look there first.
        $params = $body + $request->getQueryParams();

        // A third-party site cannot trust an unconfirmed community account, so
        // send the user straight back with an error instead of leaving them on
        // this page with nothing happening. The forum middleware normally
        // catches this before the handler runs; this is the second line of
        // defence for requests that bypass it.
        if (! $actor->is_email_confirmed) {
            $redirectUri = (string) ($params['redirect_uri'] ?? '');

            if ($redirectUri !== '') {
                $errorParams = array_filter([
                    'error' => 'access_denied',
                    'error_description' => (string) resolve('translator')->trans(
                        'tacraft-sociality-server-oauth.forum.authorize.email_unverified'
                    ),
                    'state' => $params['state'] ?? null,
                ], static fn ($value) => $value !== null && $value !== '');

                return new RedirectResponse(
                    $redirectUri.(str_contains($redirectUri, '?') ? '&' : '?').http_build_query($errorParams)
                );
            }
        }

        $authorized = filter_var(Arr::get($body, 'is_authorized', false), FILTER_VALIDATE_BOOLEAN);
        $clientId = (string) (Arr::get($body, 'client_id') ?? $params['client_id'] ?? '');

        if ($authorized) {
            Record::query()->create([
                'client_id' => $clientId,
                'user_id' => (string) $actor->id,
                'authorized_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $server->handleAuthorizeRequest($oauthRequest, $oauthResponse, $authorized, (string) $actor->id);

        return ResponseFactory::fromOAuth($oauthResponse);
    }
}
