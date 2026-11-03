<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth;

final class Settings
{
    public const PREFIX = 'tacraft-sociality-server-oauth.';

    public const DISPLAY_MODE = self::PREFIX.'display_mode';
    public const ACCESS_LIFETIME = self::PREFIX.'access_lifetime';
    public const ALLOW_IMPLICIT = self::PREFIX.'allow_implicit';
    public const ENFORCE_STATE = self::PREFIX.'enforce_state';
    public const REQUIRE_EXACT_REDIRECT_URI = self::PREFIX.'require_exact_redirect_uri';
    public const AUTHORIZATION_METHOD_FETCH = self::PREFIX.'authorization_method_fetch';
    public const ALLOW_DELETE_RECORDS = self::PREFIX.'allow_delete_records';
}
