<?php

use Illuminate\Support\Facades\Broadcast;

// Ensure the user is authenticated
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});
