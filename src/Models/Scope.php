<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Models;

use Flarum\Database\AbstractModel;

class Scope extends AbstractModel
{
    protected $table = 'server_oauth_scopes';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'is_default' => 'boolean',
    ];

    public static function forPath(string $path): iterable
    {
        return static::query()->where('resource_path', 'like', $path.'%')->get();
    }
}

