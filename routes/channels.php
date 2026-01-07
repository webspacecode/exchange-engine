<?php

use Illuminate\Support\Facades\Broadcast;

// Use the guard you are actually using (sanctum / api)
Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Broadcast::channel('user.{id}', function ($user, $id) {
    if (!$user) {
        return false; // reject safely instead of 500
    }
    return (int) $user->id === (int) $id;
});