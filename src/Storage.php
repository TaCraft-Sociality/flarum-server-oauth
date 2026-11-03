<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth;

use Flarum\User\User;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\UserCredentialsInterface;
use TacraftSociality\ServerOAuth\Models\AccessToken;
use TacraftSociality\ServerOAuth\Models\AuthorizationCode;
use TacraftSociality\ServerOAuth\Models\Client;
use TacraftSociality\ServerOAuth\Models\RefreshToken;
use TacraftSociality\ServerOAuth\Models\Scope;

class Storage implements
    AccessTokenInterface,
    AuthorizationCodeInterface,
    ClientCredentialsInterface,
    RefreshTokenInterface,
    ScopeInterface,
    UserCredentialsInterface
{
    public function checkClientCredentials($client_id, $client_secret = null): bool
    {
        $client = Client::query()->where('client_id', $client_id)->first();

        return $client !== null && (string) $client->client_secret === (string) $client_secret;
    }

    public function isPublicClient($client_id): bool
    {
        $client = Client::query()->where('client_id', $client_id)->first();

        return $client !== null && empty($client->client_secret);
    }

    public function getClientDetails($client_id): array|false
    {
        $client = Client::query()->where('client_id', $client_id)->first();

        if ($client === null) {
            return false;
        }

        return [
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'redirect_uri' => $client->redirect_uri,
            'grant_types' => $client->grant_types,
            'scope' => $client->scope,
            'user_id' => $client->user_id,
        ];
    }

    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null): bool
    {
        $client = Client::query()->where('client_id', $client_id)->first() ?? new Client();

        $client->client_id = $client_id;
        $client->client_secret = $client_secret;
        $client->redirect_uri = $redirect_uri;
        $client->grant_types = $grant_types;
        $client->scope = $scope;
        $client->user_id = $user_id;

        return (bool) $client->save();
    }

    public function getClientScope($client_id): ?string
    {
        $details = $this->getClientDetails($client_id);

        if ($details === false || empty($details['scope'])) {
            return null;
        }

        return (string) $details['scope'];
    }

    public function checkRestrictedGrantType($client_id, $grant_type): bool
    {
        $details = $this->getClientDetails($client_id);

        if ($details === false || empty($details['grant_types'])) {
            return true;
        }

        return in_array($grant_type, preg_split('/\s+/', trim((string) $details['grant_types'])) ?: [], true);
    }

    public function getAccessToken($access_token): array|false
    {
        $token = AccessToken::query()->where('access_token', $access_token)->first();

        if ($token === null) {
            return false;
        }

        return [
            'access_token' => $token->access_token,
            'client_id' => $token->client_id,
            'user_id' => $token->user_id,
            'expires' => (int) strtotime((string) $token->expires),
            'scope' => $token->scope,
        ];
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null): bool
    {
        $token = AccessToken::query()->where('access_token', $access_token)->first() ?? new AccessToken();

        $token->access_token = $access_token;
        $token->client_id = $client_id;
        $token->user_id = $user_id;
        $token->expires = date('Y-m-d H:i:s', (int) $expires);
        $token->scope = $scope;

        return (bool) $token->save();
    }

    public function unsetAccessToken($access_token): bool
    {
        return (bool) AccessToken::query()->where('access_token', $access_token)->delete();
    }

    public function getAuthorizationCode($code): array|false
    {
        $authorizationCode = AuthorizationCode::query()->where('authorization_code', $code)->first();

        if ($authorizationCode === null) {
            return false;
        }

        return [
            'authorization_code' => $authorizationCode->authorization_code,
            'client_id' => $authorizationCode->client_id,
            'user_id' => $authorizationCode->user_id,
            'redirect_uri' => $authorizationCode->redirect_uri,
            'expires' => (int) strtotime((string) $authorizationCode->expires),
            'scope' => $authorizationCode->scope,
        ];
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null): bool
    {
        $authorizationCode = AuthorizationCode::query()->where('authorization_code', $code)->first() ?? new AuthorizationCode();

        $authorizationCode->authorization_code = $code;
        $authorizationCode->client_id = $client_id;
        $authorizationCode->user_id = $user_id;
        $authorizationCode->redirect_uri = $redirect_uri;
        $authorizationCode->expires = date('Y-m-d H:i:s', (int) $expires);
        $authorizationCode->scope = $scope;

        return (bool) $authorizationCode->save();
    }

    public function expireAuthorizationCode($code): bool
    {
        return (bool) AuthorizationCode::query()->where('authorization_code', $code)->delete();
    }

    public function getRefreshToken($refresh_token): array|false
    {
        $token = RefreshToken::query()->where('refresh_token', $refresh_token)->first();

        if ($token === null) {
            return false;
        }

        return [
            'refresh_token' => $token->refresh_token,
            'client_id' => $token->client_id,
            'user_id' => $token->user_id,
            'expires' => (int) strtotime((string) $token->expires),
            'scope' => $token->scope,
        ];
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null): bool
    {
        $token = RefreshToken::query()->where('refresh_token', $refresh_token)->first() ?? new RefreshToken();

        $token->refresh_token = $refresh_token;
        $token->client_id = $client_id;
        $token->user_id = $user_id;
        $token->expires = date('Y-m-d H:i:s', (int) $expires);
        $token->scope = $scope;

        return (bool) $token->save();
    }

    public function unsetRefreshToken($refresh_token): bool
    {
        return (bool) RefreshToken::query()->where('refresh_token', $refresh_token)->delete();
    }

    public function checkUserCredentials($username, $password): bool
    {
        $user = User::query()
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        return $user !== null && $user->checkPassword((string) $password);
    }

    public function getUserDetails($username): array|false
    {
        $user = User::query()
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user === null) {
            return false;
        }

        return [
            'user_id' => (string) $user->id,
            'scope' => null,
        ];
    }

    public function scopeExists($scope): bool
    {
        $scope = trim((string) $scope);

        if ($scope === '') {
            return true;
        }

        $scopes = preg_split('/\s+/', $scope) ?: [];

        return Scope::query()->whereIn('scope', $scopes)->count() === count($scopes);
    }

    public function getDefaultScope($client_id = null): ?string
    {
        $scopes = Scope::query()->where('is_default', true)->pluck('scope')->all();

        return $scopes === [] ? null : implode(' ', $scopes);
    }
}
