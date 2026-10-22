<?php

namespace App\Controllers\Direction;

use App\Controllers\BaseController;
use App\Models\OffreModel;

class OffresController extends BaseController
{
   public function index()
{
    return view('direction/offres/index', ['title' => "Offres d'emploi"]);
}

    public function show($id)
    {
        $id   = (int) $id;
        $offre = (new OffreModel())->trouverAvecDemande($id);

        if (! $offre) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Offre $id introuvable");
        }

        $db = \Config\Database::connect();

        $historiques = $db->table('historique_offre_emploi h')
            ->select('h.*, u.email AS auteur')
            ->join('utilisateur u', 'u.id_utilisateur = h.id_utilisateur', 'left')
            ->where('h.id_offre', $id)
            ->orderBy('h.date_action', 'DESC')
            ->get()->getResultArray();

        $validations = $db->table('validation_offre v')
            ->select('v.*, u.email AS validateur')
            ->join('utilisateur u', 'u.id_utilisateur = v.id_validateur', 'left')
            ->where('v.id_offre', $id)
            ->orderBy('v.date_decision', 'DESC')
            ->get()->getResultArray();

        return view('direction/offres/show', [
            'title'       => 'Offre : ' . $offre['titre'],
            'offre'       => $offre,
            'historiques' => $historiques,
            'validations' => $validations,
        ]);
    }

    public function valider($id) { return $this->decider((int)$id, 'VALIDE'); }
    public function refuser($id) { return $this->decider((int)$id, 'REFUSEE'); }

    private function decider(int $id, string $decision)
    {
        // Safety : même si la route autorise FINANCE, seule DIRECTION décide
        if (session()->get('role') !== 'DIRECTION') {
            return redirect()->back()->with('error', 'Seule la Direction peut prendre cette décision.');
        }

        $offre = (new OffreModel())->find($id);
        if (! $offre) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($offre['statut'] !== 'EN_ATTENTE') {
            return redirect()->back()->with('error', "Cette offre n'est pas en attente (statut actuel : {$offre['statut']}).");
        }

        $idUtilisateur = (int) session()->get('id_utilisateur');
        $commentaire   = (string) ($this->request->getPost('commentaire') ?? '');

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $newStatut = ($decision === 'VALIDE') ? 'VALIDE' : 'REFUSEE';

            $db->table('offre_emploi')->where('id_offre', $id)->update(['statut' => $newStatut]);

            $db->table('validation_offre')->insert([
                'id_offre'      => $id,
                'id_validateur' => $idUtilisateur,
                'decision'      => $decision,
                'commentaire'   => $commentaire ?: null,
            ]);

            $db->table('historique_offre_emploi')->insert([
                'id_offre'      => $id,
                'ancien_statut' => 'EN_ATTENTE',
                'nouveau_statut'=> $newStatut,
                'id_utilisateur'=> $idUtilisateur,
                'commentaire'   => $commentaire ?: ('Décision Direction : ' . $decision),
            ]);

            $db->transCommit();
            return redirect()->to('/direction/offres')->with('success', "Offre $newStatut.");
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Direction\OffresController::decider → ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la décision.');
        }
    }
}