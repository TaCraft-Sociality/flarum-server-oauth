<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Support;

use TacraftSociality\ServerOAuth\Models\AccessToken;
use TacraftSociality\ServerOAuth\Models\Client;
use TacraftSociality\ServerOAuth\Models\Record;
use TacraftSociality\ServerOAuth\Models\Scope;

final class Serializer
{
    public static function client(Client $client, bool $withSecret = true): array
    {
        $attributes = [
            'client_id' => $client->client_id,
            'redirect_uri' => $client->redirect_uri,
            'grant_types' => $client->grant_types,
            'scope' => $client->scope,
            'client_name' => $client->client_name,
            'client_icon' => $client->client_icon,
            'client_desc' => $client->client_desc,
            'client_home' => $client->client_home,
        ];

        if ($withSecret) {
            $attributes['client_secret'] = $client->client_secret;
            $attributes['user_id'] = $client->user_id;
        }

        return $attributes;
    }

    public static function scope(Scope $scope, bool $full = true): array
    {
        $attributes = [
            'scope' => $scope->scope,
            'is_default' => $scope->is_default ? 1 : 0,
            'scope_name' => $scope->scope_name,
            'scope_icon' => $scope->scope_icon,
            'scope_desc' => $scope->scope_desc,
        ];

        if ($full) {
            $attributes['resource_path'] = $scope->resource_path;
            $attributes['method'] = $scope->method;
            $attributes['visible_fields'] = $scope->visible_fields;
        }

        return $attributes;
    }

    public static function record(Record $record): array
    {
        return [
            'client' => $record->client === null ? null : self::client($record->client, false),
            'user_id' => $record->user_id,
            'authorized_at' => Date::format($record->authorized_at),
        ];
    }

    public static function token(AccessToken $token): array
    {
        return [
            'access_token' => $token->access_token,
            'client_id' => $token->client_id,
            'user_id' => $token->user_id,
            'expires' => Date::format($token->expires),
            'scope' => $token->scope,
        ];
    }
}
