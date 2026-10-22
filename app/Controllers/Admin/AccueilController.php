<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Espace Admin — technique / Danie
 * Routes préfixées : /admin/*  | filtre role:ADMINISTRATEUR
 */
class AccueilController extends BaseController
{
    public function index()
    {
        return view('admin/accueil', [
            'titre' => 'Administration',
            'role'  => session()->get('role'),
        ]);
    }
}
