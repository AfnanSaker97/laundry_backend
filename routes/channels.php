<?php
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('delivery-tracking', function ($user) {
    return true; 
});
