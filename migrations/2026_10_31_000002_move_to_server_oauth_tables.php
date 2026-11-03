<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $connection = $schema->getConnection();

        if (! $schema->hasTable('server_oauth_clients')) {
            $schema->create('server_oauth_clients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('client_id', 80)->unique();
                $table->string('client_secret', 80)->nullable();
                $table->string('redirect_uri', 2000)->nullable();
                $table->string('grant_types', 80)->nullable();
                $table->string('scope', 1000)->nullable();
                $table->string('user_id', 80)->nullable();
                $table->string('client_name', 500)->nullable();
                $table->string('client_icon', 500)->nullable();
                $table->string('client_desc', 1000)->nullable();
                $table->string('client_home', 500)->nullable();
            });
        }

        if (! $schema->hasTable('server_oauth_scopes')) {
            $schema->create('server_oauth_scopes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('scope', 80)->unique();
                $table->string('resource_path', 500);
                $table->string('method', 20)->default('GET');
                $table->string('visible_fields', 1000)->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('scope_name', 500)->nullable();
                $table->string('scope_icon', 500)->nullable();
                $table->string('scope_desc', 1000)->nullable();
            });
        }

        if (! $schema->hasTable('server_oauth_access_tokens')) {
            $schema->create('server_oauth_access_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->string('access_token', 80)->unique();
                $table->string('client_id', 80);
                $table->string('user_id', 80)->nullable();
                $table->timestamp('expires');
                $table->string('scope', 1000)->nullable();
            });
        }

        if (! $schema->hasTable('server_oauth_authorization_codes')) {
            $schema->create('server_oauth_authorization_codes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('authorization_code', 80)->unique();
                $table->string('client_id', 80);
                $table->string('user_id', 80)->nullable();
                $table->string('redirect_uri', 2000)->nullable();
                $table->timestamp('expires');
                $table->string('scope', 1000)->nullable();
            });
        }

        if (! $schema->hasTable('server_oauth_refresh_tokens')) {
            $schema->create('server_oauth_refresh_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->string('refresh_token', 80)->unique();
                $table->string('client_id', 80);
                $table->string('user_id', 80)->nullable();
                $table->timestamp('expires');
                $table->string('scope', 1000)->nullable();
            });
        }

        if (! $schema->hasTable('server_oauth_records')) {
            $schema->create('server_oauth_records', function (Blueprint $table) {
                $table->increments('id');
                $table->string('client_id', 80);
                $table->string('user_id', 80)->nullable();
                $table->timestamp('authorized_at');
            });
        }

        $value = fn ($row, string $key) => property_exists($row, $key) ? $row->{$key} : null;

        if ($schema->hasTable('oauth_clients') && $schema->hasColumn('oauth_clients', 'client_id') && $connection->table('server_oauth_clients')->count() === 0) {
            foreach ($connection->table('oauth_clients')->get() as $row) {
                $connection->table('server_oauth_clients')->insert([
                    'client_id' => $value($row, 'client_id'),
                    'client_secret' => $value($row, 'client_secret'),
                    'redirect_uri' => $value($row, 'redirect_uri'),
                    'grant_types' => $value($row, 'grant_types'),
                    'scope' => $value($row, 'scope'),
                    'user_id' => $value($row, 'user_id'),
                    'client_name' => $value($row, 'client_name'),
                    'client_icon' => $value($row, 'client_icon'),
                    'client_desc' => $value($row, 'client_desc'),
                    'client_home' => $value($row, 'client_home'),
                ]);
            }
        }

        if ($schema->hasTable('oauth_scopes') && $schema->hasColumn('oauth_scopes', 'scope') && $connection->table('server_oauth_scopes')->count() === 0) {
            foreach ($connection->table('oauth_scopes')->get() as $row) {
                $connection->table('server_oauth_scopes')->insert([
                    'scope' => $value($row, 'scope'),
                    'resource_path' => $value($row, 'resource_path'),
                    'method' => $value($row, 'method') ?? 'GET',
                    'visible_fields' => $value($row, 'visible_fields'),
                    'is_default' => (bool) $value($row, 'is_default'),
                    'scope_name' => $value($row, 'scope_name'),
                    'scope_icon' => $value($row, 'scope_icon'),
                    'scope_desc' => $value($row, 'scope_desc'),
                ]);
            }
        }

        if ($schema->hasTable('oauth_access_tokens') && $schema->hasColumn('oauth_access_tokens', 'access_token') && $connection->table('server_oauth_access_tokens')->count() === 0) {
            foreach ($connection->table('oauth_access_tokens')->get() as $row) {
                $connection->table('server_oauth_access_tokens')->insert([
                    'access_token' => $value($row, 'access_token'),
                    'client_id' => $value($row, 'client_id'),
                    'user_id' => $value($row, 'user_id'),
                    'expires' => $value($row, 'expires'),
                    'scope' => $value($row, 'scope'),
                ]);
            }
        }

        if ($schema->hasTable('oauth_authorization_codes') && $schema->hasColumn('oauth_authorization_codes', 'authorization_code') && $connection->table('server_oauth_authorization_codes')->count() === 0) {
            foreach ($connection->table('oauth_authorization_codes')->get() as $row) {
                $connection->table('server_oauth_authorization_codes')->insert([
                    'authorization_code' => $value($row, 'authorization_code'),
                    'client_id' => $value($row, 'client_id'),
                    'user_id' => $value($row, 'user_id'),
                    'redirect_uri' => $value($row, 'redirect_uri'),
                    'expires' => $value($row, 'expires'),
                    'scope' => $value($row, 'scope'),
                ]);
            }
        }

        if ($schema->hasTable('oauth_refresh_tokens') && $schema->hasColumn('oauth_refresh_tokens', 'refresh_token') && $connection->table('server_oauth_refresh_tokens')->count() === 0) {
            foreach ($connection->table('oauth_refresh_tokens')->get() as $row) {
                $connection->table('server_oauth_refresh_tokens')->insert([
                    'refresh_token' => $value($row, 'refresh_token'),
                    'client_id' => $value($row, 'client_id'),
                    'user_id' => $value($row, 'user_id'),
                    'expires' => $value($row, 'expires'),
                    'scope' => $value($row, 'scope'),
                ]);
            }
        }

        if ($schema->hasTable('oauth_records') && $schema->hasColumn('oauth_records', 'authorized_at') && $connection->table('server_oauth_records')->count() === 0) {
            foreach ($connection->table('oauth_records')->get() as $row) {
                $connection->table('server_oauth_records')->insert([
                    'client_id' => $value($row, 'client_id'),
                    'user_id' => $value($row, 'user_id'),
                    'authorized_at' => $value($row, 'authorized_at'),
                ]);
            }
        }

        if (! $connection->table('server_oauth_scopes')->where('scope', 'user.read')->exists()) {
            $connection->table('server_oauth_scopes')->insert([
                'scope' => 'user.read',
                'resource_path' => '/api/user',
                'method' => 'GET',
                'visible_fields' => null,
                'is_default' => true,
                'scope_name' => 'Read user profile',
                'scope_icon' => 'fas fa-user',
                'scope_desc' => 'Access the profile of {user}',
            ]);
        }
    },
    'down' => function (Builder $schema) {
        foreach (['server_oauth_records', 'server_oauth_refresh_tokens', 'server_oauth_authorization_codes', 'server_oauth_access_tokens', 'server_oauth_scopes', 'server_oauth_clients'] as $table) {
            $schema->dropIfExists($table);
        }
    },
];
