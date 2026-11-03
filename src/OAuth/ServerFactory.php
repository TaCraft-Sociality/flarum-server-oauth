<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\OAuth;

use Flarum\Settings\SettingsRepositoryInterface;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server;
use TacraftSociality\ServerOAuth\Settings;
use TacraftSociality\ServerOAuth\Storage;

class ServerFactory
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function make(): Server
    {
        $lifetime = (int) $this->settings->get(Settings::ACCESS_LIFETIME, '3600');

        $storage = new Storage();
        $server = new Server($storage, [
            'allow_implicit' => $this->bool(Settings::ALLOW_IMPLICIT),
            'enforce_state' => $this->bool(Settings::ENFORCE_STATE),
            'require_exact_redirect_uri' => $this->bool(Settings::REQUIRE_EXACT_REDIRECT_URI),
            'access_lifetime' => $lifetime > 0 ? $lifetime : 3600,
        ]);

        $server->addGrantType(new AuthorizationCode($storage));
        $server->addGrantType(new ClientCredentials($storage));
        $server->addGrantType(new UserCredentials($storage));
        $server->addGrantType(new RefreshToken($storage));

        return $server;
    }

    protected function bool(string $key): bool
    {
        return (bool) $this->settings->get($key, '0');
    }
}
