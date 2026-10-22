<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;

/**
 * Espace RH — Tsiky
 * Routes préfixées : /rh/*  | filtre role:RH
 */
class AccueilController extends BaseController
{
    public function index()
    {
        return view('rh/accueil', [
            'titre' => 'Espace RH',
            'role'  => session()->get('role'),
        ]);
    }
}
