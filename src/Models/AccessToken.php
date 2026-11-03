<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Models;

use Flarum\Database\AbstractModel;

class AccessToken extends AbstractModel
{
    protected $table = 'server_oauth_access_tokens';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
    ];
}

