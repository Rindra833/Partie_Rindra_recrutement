<?php

namespace App\Controllers\Candidat;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;
use App\Models\RequeteConditionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RequetesController extends BaseController
{
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
    }

    /** Liste des candidatures RETENUE avec le résumé des requêtes. */
   public function index()
{
    return view('candidat/requetes/index', ['title' => 'Mes requêtes sur les conditions']);
}
    /** Détail d'une candidature : toutes les requêtes + statuts. */
    public function show($idCandidature)
    {
        $idCandidature = (int) $idCandidature;
        $candidature   = $this->candidatureDuCandidat($idCandidature);

        if (! $candidature) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rm = new RequeteConditionModel();

        return view('candidat/requetes/show', [
            'title'       => 'Requêtes — ' . $candidature['offre_titre'],
            'candidature' => $candidature,
            'conditions'  => $rm->conditionsDuPoste($idCandidature),
            'requetes'    => $rm->requetesDe($idCandidature),
            'peut_creer'  => in_array($candidature['statut'], ['RETENUE', 'SELECTIONNEE'], true),
        ]);
    }

    /** Formulaire de création — un champ par condition modifiable. */
    public function formulaire($idCandidature)
    {
        $idCandidature = (int) $idCandidature;
        $candidature   = $this->candidatureDuCandidat($idCandidature);

        if (! $candidature) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (! in_array($candidature['statut'], ['RETENUE', 'SELECTIONNEE'], true)) {
            return redirect()->to('/candidat/requetes')
                             ->with('error', 'Vous devez être retenu pour proposer des modifications.');
        }

        $conditions = (new RequeteConditionModel())->conditionsModifiables($idCandidature);

        if (empty($conditions)) {
            return redirect()->to('/candidat/requetes/' . $idCandidature)
                             ->with('error', 'Aucune condition ne peut être modifiée pour le moment.');
        }

        return view('candidat/requetes/formulaire', [
            'title'       => 'Proposer des modifications',
            'candidature' => $candidature,
            'conditions'  => $conditions,
            'errors'      => session()->getFlashdata('errors') ?? [],
            'old'         => session()->getFlashdata('old') ?? [],
        ]);
    }

    /** Soumet toutes les propositions non vides en une transaction. */
    public function soumettre($idCandidature)
    {
        $idCandidature = (int) $idCandidature;
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $candidature   = $this->candidatureDuCandidat($idCandidature);

        if (! $candidature) {
            return $this->repondreErreur('Candidature introuvable.', 404);
        }
        if (! in_array($candidature['statut'], ['RETENUE', 'SELECTIONNEE'], true)) {
            return $this->repondreErreur('Vous devez être retenu pour proposer des modifications.', 403);
        }

        $rm = new RequeteConditionModel();
        $modifiables = $rm->conditionsModifiables($idCandidature);
        $indexParId  = [];
        foreach ($modifiables as $c) {
            $indexParId[(int) $c['id_condition']] = $c;
        }

        $propositions = $this->request->getPost('proposition') ?? [];  // [id_condition => valeur]
        $motifs       = $this->request->getPost('motif')       ?? [];  // [id_condition => motif]

        $aCreer = [];
        foreach ($propositions as $idCond => $valeur) {
            $idCond = (int) $idCond;
            if (! isset($indexParId[$idCond])) {
                continue; // condition non modifiable ou inconnue
            }
            $valeur = trim((string) $valeur);
            if ($valeur === '') {
                continue; // champ vide = pas de requête (RG-REQ-003)
            }
            $aCreer[] = [
                'id_condition'       => $idCond,
                'valeur_proposee'    => $valeur,
                'commentaire_candidat' => trim((string) ($motifs[$idCond] ?? '')),
            ];
        }

        if (empty($aCreer)) {
            return $this->repondreErreur('Aucune proposition remplie. Remplissez au moins un champ.', 422,
                                         [], ['old' => $propositions]);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $created = [];
            foreach ($aCreer as $row) {
                $db->table('requete_condition_contrat')->insert([
                    'id_candidature'       => $idCandidature,
                    'id_condition'         => $row['id_condition'],
                    'valeur_proposee'      => $row['valeur_proposee'],
                    'commentaire_candidat' => $row['commentaire_candidat'] ?: null,
                    'statut'               => 'SOUMISE',
                ]);
                $newId = (int) $db->insertID();
                $created[] = $newId;

                $db->table('historique_requete_condition')->insert([
                    'id_requete'     => $newId,
                    'ancien_statut'  => null,
                    'nouveau_statut' => 'SOUMISE',
                    'id_utilisateur' => $idUtilisateur,
                    'commentaire'    => 'Proposition soumise par le candidat',
                ]);
            }

            $db->transCommit();

            $msg = count($created) === 1
                ? 'Votre proposition a été transmise au RH.'
                : count($created) . ' propositions transmises au RH.';

            if ($this->request->getHeaderLine('Accept') === 'application/json') {
                return $this->response->setJSON([
                    'ok'      => true,
                    'created' => $created,
                    'count'   => count($created),
                ]);
            }

            return redirect()->to('/candidat/requetes/' . $idCandidature)
                             ->with('success', $msg);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'RequetesController::soumettre → ' . $e->getMessage());
            return $this->repondreErreur('Erreur lors de la soumission. Réessayez.', 500);
        }
    }

    // ---------- helpers ----------

    /** Candidature appartenant au candidat connecté, avec les infos d'offre. */
    private function candidatureDuCandidat(int $idCandidature): ?array
    {
        $idUtilisateur = (int) session()->get('id_utilisateur');

        return $this->db->table('candidature c')
            ->select('c.*, o.titre AS offre_titre, o.statut AS offre_statut,
                      p.libelle AS poste_libelle, dep.nom AS departement_nom')
            ->join('candidat ca', 'ca.id_candidat = c.id_candidat', 'left')
            ->join('personne pe', 'pe.id_personne = ca.id_personne', 'left')
            ->join('offre_emploi o', 'o.id_offre = c.id_offre', 'left')
            ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
            ->join('poste p', 'p.id_poste = d.id_poste', 'left')
            ->join('departement dep', 'dep.id_departement = p.id_departement', 'left')
            ->where('c.id_candidature', $idCandidature)
            ->where('pe.id_utilisateur', $idUtilisateur)
            ->get()->getRowArray();
    }

    private function repondreErreur(string $msg, int $code = 422, array $erreurs = [], array $old = [])
    {
        if ($this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->response->setStatusCode($code)->setJSON([
                'ok' => false, 'message' => $msg, 'errors' => $erreurs,
            ]);
        }
        $redirect = redirect()->back()->withInput()->with('error', $msg);
        if (! empty($erreurs)) {
            $redirect->with('errors', $erreurs);
        }
        return $redirect;
    }
}