<?php

namespace App\Controllers\Direction;

use App\Controllers\BaseController;
use App\Models\OffreModel;

class AccueilController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $stats = [
            'offres_en_attente'     => $db->table('offre_emploi')->where('statut', 'EN_ATTENTE')->countAllResults(),
            'offres_publiees'       => $db->table('offre_emploi')->where('statut', 'PUBLIEE')->countAllResults(),
            'candidatures_recues'   => $db->table('candidature')->where('statut', 'RECUE')->countAllResults(),
            'candidatures_retenues' => $db->table('candidature')->where('statut', 'RETENUE')->countAllResults(),
        ];

        $offres = (new OffreModel())->listeAvecDemande();

        return view('direction/home', [
            'title'  => 'Tableau de bord Direction',
            'stats'  => $stats,
            'offres' => $offres,
        ]);
    }
}