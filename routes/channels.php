<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Broadcast::channel('central.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tenant.{tenantId}.user.{id}', function ($user, $tenantId, $id) {
    return tenant('id') === $tenantId && (int) $user->id === (int) $id;
});
