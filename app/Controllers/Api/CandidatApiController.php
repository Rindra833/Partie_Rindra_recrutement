<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;
use App\Models\OffreModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CandidatApiController extends BaseController
{
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
    }

    public function dashboard()
    {
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $cm = new CandidatureModel();
        $idCandidat = $cm->idCandidatPourUtilisateur($idUtilisateur);

        if (! $idCandidat) {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Profil candidat introuvable.']);
        }

        $stats = [
            'offres_dispo'           => count((new OffreModel())->listePubliees()),
            'candidatures_envoyees'  => $this->db->table('candidature')
                ->where('id_candidat', $idCandidat)->countAllResults(),
            'candidatures_retenues'  => $this->db->table('candidature')
                ->where('id_candidat', $idCandidat)->where('statut', 'RETENUE')->countAllResults(),
            'notifications_non_lues' => $this->db->table('notification')
                ->where('id_utilisateur', $idUtilisateur)->where('lue', false)->countAllResults(),
        ];

        $offres = (new OffreModel())->listePubliees(5);

        $candidat = $this->db->table('candidat c')
            ->select('c.id_candidat, p.nom, p.prenom')
            ->join('personne p', 'p.id_personne = c.id_personne')
            ->where('p.id_utilisateur', $idUtilisateur)
            ->get()->getRowArray();

        return $this->response->setJSON([
            'stats' => $stats,
            'offres' => array_map(fn($o) => [
                'id_offre'        => (int) $o['id_offre'],
                'titre'           => $o['titre'],
                'poste_libelle'   => $o['poste_libelle']   ?? '—',
                'departement_nom' => $o['departement_nom'] ?? '—',
                'date_limite'     => $o['date_limite']     ?? null,
            ], $offres),
            'candidat' => $candidat ? [
                'nom'    => $candidat['nom'],
                'prenom' => $candidat['prenom'],
            ] : null,
        ]);
    }

    public function notifications()
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $filtre = $this->request->getGet('filtre'); // null | 'non-lues' | 'lues'

    $model = new \App\Models\NotificationModel();
    $luesSeulement = null;
    if ($filtre === 'non-lues') { $luesSeulement = false; }
    if ($filtre === 'lues')     { $luesSeulement = true;  }

    $notifs = $model->listePour($idUtilisateur, $luesSeulement);

    return $this->response->setJSON([
        'notifications' => array_map(fn($n) => [
            'id_notification' => (int) $n['id_notification'],
            'titre'           => $n['titre'],
            'message'         => $n['message'],
            'lien'            => $n['lien'],
            'lue'             => (bool) $n['lue'],
            'date_creation'   => $n['date_creation'],
            'date_lecture'    => $n['date_lecture'],
        ], $notifs),
        'non_lues' => $model->compterNonLues($idUtilisateur),
        'filtre'   => $filtre,
    ]);
}

public function marquerLue($id)
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $ok = (new \App\Models\NotificationModel())->marquerLue((int) $id, $idUtilisateur);

    if (! $ok) {
        return $this->response->setStatusCode(404)->setJSON([
            'ok' => false, 'error' => 'Notification introuvable.',
        ]);
    }
    return $this->response->setJSON(['ok' => true]);
}

public function toutMarquerLu()
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $n = (new \App\Models\NotificationModel())->marquerToutLue($idUtilisateur);

    return $this->response->setJSON(['ok' => true, 'marquees' => $n]);
}

public function offres()
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $cm = new CandidatureModel();
    $idCandidat = $cm->idCandidatPourUtilisateur($idUtilisateur);

    if (! $idCandidat) {
        return $this->response->setStatusCode(403)
            ->setJSON(['error' => 'Profil candidat introuvable.']);
    }

    $offres = (new OffreModel())->listePubliees();
    $dejaPostulees = $cm->offresDejaPostulees($idCandidat);

    return $this->response->setJSON([
        'offres' => array_map(fn($o) => [
            'id_offre'           => (int) $o['id_offre'],
            'titre'              => $o['titre'],
            'description'        => $o['description'],
            'poste_libelle'      => $o['poste_libelle']   ?? '—',
            'departement_nom'    => $o['departement_nom'] ?? '—',
            'date_publication'   => $o['date_publication'] ?? null,
            'date_limite'        => $o['date_limite']     ?? null,
            'salaire'            => $o['salaire_par_personne'] ?? null,
            'deja_postule'       => in_array((int) $o['id_offre'], $dejaPostulees, true),
        ], $offres),
        'stats' => [
            'total'       => count($offres),
            'deja_postule' => count($dejaPostulees),
        ],
    ]);
}

public function mesCandidatures()
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $cm = new CandidatureModel();
    $idCandidat = $cm->idCandidatPourUtilisateur($idUtilisateur);

    if (! $idCandidat) {
        return $this->response->setStatusCode(403)
            ->setJSON(['error' => 'Profil candidat introuvable.']);
    }

    $liste = $cm->listeParCandidat($idCandidat);

    $comptes = $this->db->table('candidature')
        ->select('statut, count(*) AS n')
        ->where('id_candidat', $idCandidat)
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
            'offre_titre'     => $c['offre_titre']   ?? '—',
            'offre_statut'    => $c['offre_statut']  ?? null,
            'date_candidature'=> $c['date_candidature'],
            'statut'          => $c['statut'],
            'peut_requete'    => in_array($c['statut'], ['RETENUE', 'SELECTIONNEE'], true),
        ], $liste),
        'comptes_par_statut' => $comptesParStatut,
    ]);
}

public function requetes()
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $idCandidat = (new CandidatureModel())->idCandidatPourUtilisateur($idUtilisateur);

    if (! $idCandidat) {
        return $this->response->setStatusCode(403)
            ->setJSON(['error' => 'Profil candidat introuvable.']);
    }

    $candidatures = $this->db->table('candidature c')
        ->select('c.id_candidature, c.statut, c.date_candidature, o.titre AS offre_titre,
                  (SELECT count(*) FROM requete_condition_contrat rq WHERE rq.id_candidature = c.id_candidature) AS nb_requetes,
                  (SELECT count(*) FROM requete_condition_contrat rq WHERE rq.id_candidature = c.id_candidature
                    AND rq.statut IN (\'SOUMISE\',\'ACCEPTEE_RH\',\'ACCEPTEE_DIRECTION\')) AS nb_requetes_actives')
        ->join('offre_emploi o', 'o.id_offre = c.id_offre', 'left')
        ->where('c.id_candidat', $idCandidat)
        ->whereIn('c.statut', ['RETENUE', 'SELECTIONNEE'])
        ->orderBy('c.date_candidature', 'DESC')
        ->get()->getResultArray();

    return $this->response->setJSON([
        'candidatures' => array_map(fn($c) => [
            'id_candidature'      => (int) $c['id_candidature'],
            'offre_titre'         => $c['offre_titre'] ?? '—',
            'statut'              => $c['statut'],
            'date_candidature'    => $c['date_candidature'],
            'nb_requetes'         => (int) $c['nb_requetes'],
            'nb_requetes_actives' => (int) $c['nb_requetes_actives'],
        ], $candidatures),
        'total' => count($candidatures),
    ]);
}

public function contrats()
{
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $idCandidat = (new CandidatureModel())->idCandidatPourUtilisateur($idUtilisateur);

    if (! $idCandidat) {
        return $this->response->setStatusCode(403)
            ->setJSON(['error' => 'Profil candidat introuvable.']);
    }

    $liste = (new \App\Models\ContratModel())->listePourCandidat($idCandidat);

    return $this->response->setJSON([
        'contrats' => array_map(fn($c) => [
            'id_contrat'    => (int) $c['id_contrat'],
            'reference'     => $c['reference'],
            'offre_titre'   => $c['offre_titre']   ?? '—',
            'type_contrat'  => $c['type_contrat'],
            'salaire'       => (float) $c['salaire'],
            'statut'        => $c['statut'],
            'date_debut'    => $c['date_debut'],
            'date_fin'      => $c['date_fin'],
            'date_signature'=> $c['date_signature'],
        ], $liste),
        'total' => count($liste),
    ]);
}

}