<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateUserConnectionDetails
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(UserLoggedIn $event): void
    {
        $user = $event->user;
        $user->derniere_connexion = now();
        ++$user->nombre_connexions;
        $user->save();
    }
}
