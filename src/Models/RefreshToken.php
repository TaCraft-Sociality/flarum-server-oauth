<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Models;

use Flarum\Database\AbstractModel;

class RefreshToken extends AbstractModel
{
    protected $table = 'server_oauth_refresh_tokens';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
    ];
}

