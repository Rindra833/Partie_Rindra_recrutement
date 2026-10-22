<?php

namespace App\Controllers\Departement;

use App\Controllers\BaseController;

/**
 * Espace Département — John
 * Routes préfixées : /departement/*  | filtre role:DEPARTEMENT
 */
class AccueilController extends BaseController
{
    public function index()
    {
        return view('departement/accueil', [
            'titre' => 'Espace Département',
            'role'  => session()->get('role'),
        ]);
    }
}
