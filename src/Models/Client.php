<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Models;

use Flarum\Database\AbstractModel;

class Client extends AbstractModel
{
    protected $table = 'server_oauth_clients';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
    ];

    public function records()
    {
        return $this->hasMany(Record::class, 'client_id', 'client_id');
    }
}

