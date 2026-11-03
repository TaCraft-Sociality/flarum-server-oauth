<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Forum;

use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use OAuth2\Response as OAuthResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TacraftSociality\ServerOAuth\Models\Record;
use TacraftSociality\ServerOAuth\OAuth\RequestFactory;
use TacraftSociality\ServerOAuth\OAuth\ServerFactory;
use TacraftSociality\ServerOAuth\Permission;

class AuthorizeFetchController implements RequestHandlerInterface
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
            return new JsonResponse([
                'error' => 'access_denied',
                'error_description' => 'You do not have permission to use OAuth',
            ], 403);
        }

        $oauthRequest = RequestFactory::fromPsr($request);
        $oauthResponse = new OAuthResponse();
        $server = $this->servers->make();

        if (! $server->validateAuthorizeRequest($oauthRequest, $oauthResponse)) {
            return new JsonResponse(
                json_decode((string) $oauthResponse->getResponseBody(), true) ?: ['error' => 'invalid_request'],
                $oauthResponse->getStatusCode()
            );
        }

        $body = $request->getParsedBody();
        $body = is_array($body) ? $body : [];
        $authorized = filter_var(Arr::get($body, 'is_authorized', false), FILTER_VALIDATE_BOOLEAN);
        $clientId = (string) (Arr::get($body, 'client_id') ?? $request->getQueryParams()['client_id'] ?? '');

        if ($authorized) {
            Record::query()->create([
                'client_id' => $clientId,
                'user_id' => (string) $actor->id,
                'authorized_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $server->handleAuthorizeRequest($oauthRequest, $oauthResponse, $authorized, (string) $actor->id);

        $location = $oauthResponse->getHttpHeader('Location');

        if ($location !== null && $location !== '') {
            return new JsonResponse(['location' => $location]);
        }

        return new JsonResponse(
            json_decode((string) $oauthResponse->getResponseBody(), true) ?: ['error' => 'invalid_request'],
            $oauthResponse->getStatusCode()
        );
    }
}
