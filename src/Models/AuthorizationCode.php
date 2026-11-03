<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Models;

use Flarum\Database\AbstractModel;

class AuthorizationCode extends AbstractModel
{
    protected $table = 'server_oauth_authorization_codes';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
    ];
}

