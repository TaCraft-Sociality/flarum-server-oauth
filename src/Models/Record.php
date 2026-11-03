<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Models;

use Flarum\Database\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends AbstractModel
{
    protected $table = 'server_oauth_records';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'authorized_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }
}

