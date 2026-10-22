<?php

namespace App\Controllers\Candidat;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;
use App\Models\OffreModel;

class AccueilController extends BaseController
{
   public function index()
{
    return view('candidat/home', ['title' => 'Espace Candidat']);
}
}