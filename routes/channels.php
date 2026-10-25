<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('feed', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
