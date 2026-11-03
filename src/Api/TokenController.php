<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Api;

use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TacraftSociality\ServerOAuth\Models\AccessToken;
use TacraftSociality\ServerOAuth\Models\AuthorizationCode;
use TacraftSociality\ServerOAuth\Models\RefreshToken;
use TacraftSociality\ServerOAuth\Support\JsonApi;
use TacraftSociality\ServerOAuth\Support\Serializer;

class TokenController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return match ((string) $request->getAttribute('routeName')) {
            'oauth.tokens.create' => $this->store($request),
            'oauth.tokens.delete.expired' => $this->deleteExpired($request),
            default => JsonApi::error(404, 'Not found'),
        };
    }

    protected function store(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $attributes = $this->attributes($request);
        $value = trim((string) Arr::get($attributes, 'access_token', ''));
        $clientId = trim((string) Arr::get($attributes, 'client_id', ''));
        $userId = trim((string) Arr::get($attributes, 'user_id', ''));

        if ($value === '' || $clientId === '' || $userId === '') {
            return JsonApi::error(422, 'access_token, client_id and user_id are required');
        }

        $token = AccessToken::query()->where('access_token', $value)->first();

        if ($token !== null) {
            return JsonApi::item('oauth-tokens', $token->id, Serializer::token($token));
        }

        $expires = Arr::get($attributes, 'expires');
        $expires = $expires ? strtotime((string) $expires) : false;

        $token = new AccessToken();
        $token->access_token = $value;
        $token->client_id = $clientId;
        $token->user_id = $userId;
        $token->expires = date('Y-m-d H:i:s', $expires === false ? time() + 86400 : $expires);
        $token->scope = Arr::get($attributes, 'scope');
        $token->save();

        return JsonApi::item('oauth-tokens', $token->id, Serializer::token($token), 201);
    }

    protected function deleteExpired(ServerRequestInterface $request): ResponseInterface
    {
        $this->actor($request)->assertAdmin();

        $now = date('Y-m-d H:i:s');

        AccessToken::query()->where('expires', '<', $now)->delete();
        RefreshToken::query()->where('expires', '<', $now)->delete();
        AuthorizationCode::query()->where('expires', '<', $now)->delete();

        return JsonApi::noContent();
    }
}
