<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Middleware;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TacraftSociality\ServerOAuth\Models\Scope;
use TacraftSociality\ServerOAuth\OAuth\RequestFactory;
use TacraftSociality\ServerOAuth\OAuth\ServerFactory;

class OAuthScope implements MiddlewareInterface
{
    public function __construct(
        protected ServerFactory $servers
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        $query = $request->getQueryParams();
        $hasToken = str_starts_with(strtolower($header), 'bearer ') || isset($query['access_token']);

        if (! $hasToken) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();

        try {
            $scopes = Scope::forPath($path);

            if (count($scopes) === 0) {
                $scopes = Scope::forPath('/api'.$path);
            }
        } catch (\Illuminate\Database\QueryException) {
            return $handler->handle($request);
        }

        if (count($scopes) === 0) {
            return $handler->handle($request);
        }

        $server = $this->servers->make();
        $oauthRequest = RequestFactory::fromPsr($request);

        if (! $server->verifyResourceRequest($oauthRequest)) {
            $response = $server->getResponse();

            return new JsonResponse(
                json_decode((string) $response?->getResponseBody(), true) ?: [
                    'error' => 'invalid_token',
                    'error_description' => 'The access token is invalid or expired',
                ],
                401
            );
        }

        $token = $server->getAccessTokenData($oauthRequest);
        $granted = preg_split('/\s+/', trim((string) ($token['scope'] ?? ''))) ?: [];
        $matched = [];

        foreach ($scopes as $scope) {
            if (strcasecmp($request->getMethod(), (string) $scope->method) !== 0) {
                continue;
            }

            if (! in_array((string) $scope->scope, $granted, true)) {
                continue;
            }

            $matched[] = $scope;
        }

        if ($matched === []) {
            return new JsonResponse([
                'error' => 'insufficient_scope',
                'error_description' => 'The access token does not grant access to this resource',
            ], 403);
        }

        $user = User::query()->find($token['user_id'] ?? null);

        if ($user === null) {
            return new JsonResponse([
                'error' => 'invalid_token',
                'error_description' => 'The access token owner no longer exists',
            ], 401);
        }

        $request = RequestUtil::withActor($request, $user)
            ->withAttribute('oauth.scopes', $matched)
            ->withAttribute('bypassCsrfToken', true)
            ->withoutAttribute('session');

        return $handler->handle($request);
    }
}
