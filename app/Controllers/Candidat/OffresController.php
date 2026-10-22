<?php

namespace App\Controllers\Candidat;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;
use App\Models\OffreModel;

class OffresController extends BaseController
{
   public function index()
{
    return view('candidat/offres/index', ['title' => 'Offres disponibles']);
}
}