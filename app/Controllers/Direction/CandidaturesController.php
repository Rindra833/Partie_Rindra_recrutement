<?php

namespace App\Controllers\Direction;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;

class CandidaturesController extends BaseController
{
   public function index()
{
    return view('direction/candidatures/index', ['title' => 'Candidatures']);
}

    public function show($id)
    {
        $id = (int) $id;
        $model = new CandidatureModel();
        $candidature = $model->trouverDetaillee($id);

        if (! $candidature) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Candidature $id introuvable");
        }

        return view('direction/candidatures/show', [
            'title'        => 'Candidature — ' . ($candidature['nom'] ?? '') . ' ' . ($candidature['prenom'] ?? ''),
            'candidature'  => $candidature,
            'evaluations'  => $model->evaluationsDe($id),
            'historique'   => $model->historiqueDe($id),
        ]);
    }

    /** Sert un document (cv|lettre|cin) protégé. */
    public function document($id, $type)
    {
        $id = (int) $id;
        $map = [
            'cv'     => ['cv_hex',     'cv_type_mime',     'cv_nom'],
            'lettre' => ['lettre_hex', 'lettre_type_mime', 'lettre_nom'],
            'cin'    => ['cin_hex',    'cin_type_mime',    'cin_nom'],
        ];
        if (! isset($map[$type])) {
            return $this->response->setStatusCode(404)->setBody('Type de document inconnu.');
        }

        $docs = (new CandidatureModel())->documentsEncodees($id);
        if (! $docs) {
            return $this->response->setStatusCode(404)->setBody('Candidature introuvable.');
        }

        [$colHex, $colMime, $colNom] = $map[$type];
        $hex = $docs[$colHex] ?? '';
        if ($hex === '') {
            return $this->response->setStatusCode(404)->setBody('Document absent.');
        }

        $binary = hex2bin($hex);
        $mime   = $docs[$colMime] ?: 'application/octet-stream';
        $nom    = $docs[$colNom]  ?: ($type . '.bin');

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($nom) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($binary);
    }

    /** Décision Direction : RETENUE + historique + notification. */
    public function retenir($id)
    {
        if (session()->get('role') !== 'DIRECTION') {
            return redirect()->back()->with('error', 'Seule la Direction peut retenir une candidature.');
        }

        $id = (int) $id;
        $model = new CandidatureModel();
        $candidature = $model->trouverDetaillee($id);

        if (! $candidature) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $statutActuel = $candidature['statut'];
        if (! in_array($statutActuel, ['PRESELECTIONNEE', 'SELECTIONNEE'], true)) {
            return redirect()->back()->with('error', "Impossible de retenir une candidature en statut $statutActuel.");
        }

        $idUtilisateur       = (int) session()->get('id_utilisateur');
        $idUtilisateurCandid = (int) ($candidature['id_utilisateur_candidat'] ?? 0);

        if ($idUtilisateurCandid <= 0) {
            return redirect()->back()->with('error', 'Impossible de trouver l\'utilisateur du candidat.');
        }

        $commentaire = (string) ($this->request->getPost('commentaire') ?? '');

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1) Statut candidature → RETENUE
            $db->table('candidature')
               ->where('id_candidature', $id)
               ->update(['statut' => 'RETENUE']);

            // 2) Historique candidature
            $db->table('historique_candidature')->insert([
                'id_candidature' => $id,
                'ancien_statut'  => $statutActuel,
                'nouveau_statut' => 'RETENUE',
                'id_utilisateur' => $idUtilisateur,
                'commentaire'    => $commentaire ?: 'Décision Direction : RETENUE',
            ]);

            // 3) Notification pour le candidat
            $titre = 'Candidature retenue';
            $msg   = "Félicitations, votre candidature pour le poste « "
                   . ($candidature['poste_libelle'] ?? '—')
                   . " » a été retenue par la Direction. Consultez les conditions du contrat proposé.";
            $lien  = '/candidat/notifications';

            $db->table('notification')->insert([
                'id_utilisateur' => $idUtilisateurCandid,
                'titre'          => $titre,
                'message'        => $msg,
                'lien'           => $lien,
                'lue'            => false,
            ]);

            $db->transCommit();
            return redirect()->to('/direction/candidatures')
                             ->with('success', 'Candidature retenue. Le candidat a été notifié.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'CandidaturesController::retenir → ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la décision.');
        }
    }
}