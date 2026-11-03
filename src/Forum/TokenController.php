<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Forum;

use Flarum\User\User;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use OAuth2\Response as OAuthResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TacraftSociality\ServerOAuth\OAuth\RequestFactory;
use TacraftSociality\ServerOAuth\OAuth\ResponseFactory;
use TacraftSociality\ServerOAuth\OAuth\ServerFactory;
use TacraftSociality\ServerOAuth\Permission;

class TokenController implements RequestHandlerInterface
{
    public function __construct(
        protected ServerFactory $servers
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $body = is_array($body) ? $body : [];

        if (Arr::get($body, 'grant_type') === 'password') {
            $username = (string) Arr::get($body, 'username', '');
            $user = User::query()->where('username', $username)->orWhere('email', $username)->first();

            if ($user !== null && ! $user->hasPermission(Permission::USE_OAUTH)) {
                return new JsonResponse([
                    'error' => 'access_denied',
                    'error_description' => 'You do not have permission to use OAuth',
                ], 403);
            }
        }

        $server = $this->servers->make();
        $oauthResponse = $server->handleTokenRequest(RequestFactory::fromPsr($request), new OAuthResponse());

        return ResponseFactory::fromOAuth($oauthResponse);
    }
}
