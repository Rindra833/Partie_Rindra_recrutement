<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\OffreModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DirectionApiController extends BaseController
{
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
    }

    public function dashboard()
    {
        if (session()->get('role') !== 'DIRECTION') {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Réservé à la Direction.']);
        }

        $stats = [
            'offres_en_attente'     => $this->db->table('offre_emploi')->where('statut', 'EN_ATTENTE')->countAllResults(),
            'offres_publiees'       => $this->db->table('offre_emploi')->where('statut', 'PUBLIEE')->countAllResults(),
            'candidatures_recues'   => $this->db->table('candidature')->where('statut', 'RECUE')->countAllResults(),
            'candidatures_retenues' => $this->db->table('candidature')->where('statut', 'RETENUE')->countAllResults(),
        ];

        $offres = (new OffreModel())->listeAvecDemande();

        return $this->response->setJSON([
            'stats'    => $stats,
            'offres'   => array_map(fn($o) => [
                'id_offre'        => (int) $o['id_offre'],
                'titre'           => $o['titre'],
                'poste_libelle'   => $o['poste_libelle'] ?? '—',
                'departement_nom' => $o['departement_nom'] ?? '—',
                'statut'          => $o['statut'],
            ], $offres),
            'user'     => [
                'email' => session()->get('email'),
                'role'  => session()->get('role'),
            ],
        ]);
    }

    public function candidatures()
{
    if (session()->get('role') !== 'DIRECTION') {
        return $this->response->setStatusCode(403)
            ->setJSON(['error' => 'Réservé à la Direction.']);
    }

    $statut = $this->request->getGet('statut');
    $model  = new \App\Models\CandidatureModel();
    $liste  = $model->listeEnrichie($statut);

    // Compter par statut (pour les badges des filtres)
    $comptes = $this->db->table('candidature')
        ->select('statut, count(*) AS n')
        ->groupBy('statut')
        ->get()->getResultArray();
    $comptesParStatut = [];
    foreach ($comptes as $c) {
        $comptesParStatut[$c['statut']] = (int) $c['n'];
    }
    $comptesParStatut['TOUTES'] = array_sum($comptesParStatut);

    return $this->response->setJSON([
        'candidatures' => array_map(fn($c) => [
            'id_candidature'  => (int) $c['id_candidature'],
            'nom'             => $c['nom'] ?? '',
            'prenom'          => $c['prenom'] ?? '',
            'telephone'       => $c['telephone'] ?? '',
            'offre_titre'     => $c['offre_titre'] ?? '—',
            'statut'          => $c['statut'],
            'date_candidature'=> $c['date_candidature'],
        ], $liste),
        'statut_actif'       => $statut,
        'comptes_par_statut' => $comptesParStatut,
    ]);
}

public function offres()
{
    if (session()->get('role') !== 'DIRECTION') {
        return $this->response->setStatusCode(403)
            ->setJSON(['error' => 'Réservé à la Direction.']);
    }

    $statut = $this->request->getGet('statut');
    $liste  = (new \App\Models\OffreModel())->listeAvecDemande($statut);

    // Comptes par statut (pour les badges des filtres)
    $comptes = $this->db->table('offre_emploi')
        ->select('statut, count(*) AS n')
        ->groupBy('statut')
        ->get()->getResultArray();
    $comptesParStatut = [];
    foreach ($comptes as $c) {
        $comptesParStatut[$c['statut']] = (int) $c['n'];
    }
    $comptesParStatut['TOUTES'] = array_sum($comptesParStatut);

    return $this->response->setJSON([
        'offres' => array_map(fn($o) => [
            'id_offre'         => (int) $o['id_offre'],
            'titre'            => $o['titre'],
            'poste_libelle'    => $o['poste_libelle']    ?? '—',
            'departement_nom'  => $o['departement_nom']  ?? '—',
            'effectif_demande' => (int) ($o['effectif_demande'] ?? 0),
            'statut'           => $o['statut'],
            'date_publication' => $o['date_publication'] ?? null,
            'date_limite'      => $o['date_limite']      ?? null,
        ], $liste),
        'statut_actif'       => $statut,
        'comptes_par_statut' => $comptesParStatut,
    ]);
}

}