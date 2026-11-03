<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
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

        $connection = $schema->getConnection();

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
