<?php

declare(strict_types=1);

use Flarum\Extend;
use Flarum\Http\Middleware\CheckCsrfToken;
use TacraftSociality\ServerOAuth\Api\ClientController;
use TacraftSociality\ServerOAuth\Api\RecordController;
use TacraftSociality\ServerOAuth\Api\ScopeController;
use TacraftSociality\ServerOAuth\Api\TokenController as ApiTokenController;
use TacraftSociality\ServerOAuth\Api\UserController;
use TacraftSociality\ServerOAuth\Forum\AuthorizeController;
use TacraftSociality\ServerOAuth\Forum\AuthorizeFetchController;
use TacraftSociality\ServerOAuth\Forum\TokenController;
use TacraftSociality\ServerOAuth\Middleware\OAuthScope;
use TacraftSociality\ServerOAuth\Middleware\ScopeFields;

$prefix = 'tacraft-sociality-server-oauth.';

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less')
        ->route('/oauth/authorize', 'oauth.authorize'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Routes('forum'))
        ->post('/oauth/authorize', 'oauth.authorize.post', AuthorizeController::class)
        ->post('/oauth/authorize/fetch', 'oauth.authorize.fetch', AuthorizeFetchController::class)
        ->post('/oauth/token', 'oauth.token', TokenController::class),

    (new Extend\Routes('api'))
        ->get('/oauth-clients', 'oauth.clients.list', ClientController::class)
        ->post('/oauth-clients', 'oauth.clients.create', ClientController::class)
        ->get('/oauth-clients/{client_id}', 'oauth.clients.show', ClientController::class)
        ->patch('/oauth-clients/{id}', 'oauth.clients.update', ClientController::class)
        ->delete('/oauth-clients/{id}', 'oauth.clients.delete', ClientController::class)
        ->get('/oauth-scopes', 'oauth.scopes.list', ScopeController::class)
        ->post('/oauth-scopes', 'oauth.scopes.create', ScopeController::class)
        ->patch('/oauth-scopes/{id}', 'oauth.scopes.update', ScopeController::class)
        ->delete('/oauth-scopes/{id}', 'oauth.scopes.delete', ScopeController::class)
        ->post('/oauth-tokens', 'oauth.tokens.create', ApiTokenController::class)
        ->delete('/oauth-tokens/expired', 'oauth.tokens.delete.expired', ApiTokenController::class)
        ->get('/oauth-records', 'oauth.records.list', RecordController::class)
        ->delete('/oauth-records/all', 'oauth.records.delete.all', RecordController::class)
        ->delete('/oauth-records/user', 'oauth.records.delete.user', RecordController::class)
        ->delete('/oauth-records/{id}', 'oauth.records.delete', RecordController::class)
        ->get('/user', 'oauth.user', UserController::class),

    (new Extend\Settings())
        ->default($prefix.'display_mode', 'box')
        ->default($prefix.'access_lifetime', '3600')
        ->default($prefix.'allow_implicit', '0')
        ->default($prefix.'enforce_state', '0')
        ->default($prefix.'require_exact_redirect_uri', '0')
        ->default($prefix.'authorization_method_fetch', '0')
        ->default($prefix.'allow_delete_records', '0')
        ->serializeToForum('serverOAuthDisplayMode', $prefix.'display_mode')
        ->serializeToForum('serverOAuthAllowImplicit', $prefix.'allow_implicit', 'boolval')
        ->serializeToForum('serverOAuthEnforceState', $prefix.'enforce_state', 'boolval')
        ->serializeToForum('serverOAuthRequireExactRedirectUri', $prefix.'require_exact_redirect_uri', 'boolval')
        ->serializeToForum('serverOAuthAuthorizationMethodFetch', $prefix.'authorization_method_fetch', 'boolval')
        ->serializeToForum('serverOAuthAllowDeleteRecords', $prefix.'allow_delete_records', 'boolval'),

    (new Extend\Middleware('api'))
        ->insertBefore(CheckCsrfToken::class, OAuthScope::class)
        ->add(ScopeFields::class),

    (new Extend\Middleware('forum'))
        ->add(\TacraftSociality\ServerOAuth\Middleware\VerifiedEmailGate::class),

    (new Extend\Csrf())
        ->exemptRoute('oauth.authorize.post')
        ->exemptRoute('oauth.authorize.fetch')
        ->exemptRoute('oauth.token'),
];
