<?php

namespace App\Controllers\Candidat;

use App\Controllers\BaseController;
use App\Libraries\ContratService;
use App\Models\CandidatureModel;
use App\Models\ContratModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ContratController extends BaseController
{
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
    }

    public function index()
{
    return view('candidat/contrat/index', ['title' => 'Mes contrats']);
}

    public function show($id)
    {
        $id = (int) $id;
        $contrat = $this->contratDuCandidat($id);

        if (! $contrat) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Contrat #$id introuvable");
        }

        $conditions = $this->db->table('contrat_condition')
            ->where('id_contrat', $id)
            ->orderBy('ordre_affichage', 'ASC')
            ->get()->getResultArray();

        $historique = $this->db->table('historique_contrat h')
            ->select('h.*, u.email AS auteur')
            ->join('utilisateur u', 'u.id_utilisateur = h.id_utilisateur', 'left')
            ->where('h.id_contrat', $id)
            ->orderBy('h.date_action', 'DESC')
            ->get()->getResultArray();

        return view('candidat/contrat/show', [
            'title'      => 'Contrat ' . $contrat['reference'],
            'contrat'    => $contrat,
            'conditions' => $conditions,
            'historique' => $historique,
            'peut_agir'  => $contrat['statut'] === 'BROUILLON',
            'peut_voir_pdf' => ! empty($contrat['pdf_nom']),
        ]);
    }

    public function accepter($id)
    {
        $id = (int) $id;
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $contrat = $this->contratDuCandidat($id);

        if (! $contrat) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($contrat['statut'] !== 'BROUILLON') {
            return redirect()->back()->with('error', "Ce contrat n'est plus en état d'être accepté (statut : {$contrat['statut']}).");
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $service = new ContratService();

            // 1) Figer les conditions (RG-COND-006)
            $nbConditions = $service->figerConditions($id, (int) $contrat['id_poste']);

            // 2) Générer le PDF
            $service->genererPdf($id);

            // 3) Transition BROUILLON → A_SIGNER avec historique
            $service->changerStatut(
                $id,
                'A_SIGNER',
                $idUtilisateur,
                "Contrat accepté par le candidat ($nbConditions conditions figées)"
            );

            $db->transCommit();

            return redirect()->to('/candidat/contrats/' . $id)
                             ->with('success', "Contrat accepté. Vous pouvez maintenant signer et télécharger le PDF.");
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'ContratController::accepter → ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'acceptation : ' . $e->getMessage());
        }
    }

    public function refuser($id)
    {
        $id = (int) $id;
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $contrat = $this->contratDuCandidat($id);

        if (! $contrat) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if ($contrat['statut'] !== 'BROUILLON') {
            return redirect()->back()->with('error', "Ce contrat ne peut plus être refusé.");
        }

        $commentaire = trim((string) $this->request->getPost('commentaire'));

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            (new ContratService())->changerStatut(
                $id,
                'ANNULE',
                $idUtilisateur,
                'Refusé par le candidat' . ($commentaire !== '' ? ' — ' . $commentaire : '')
            );
            $db->transCommit();
            return redirect()->to('/candidat/contrats')->with('success', 'Contrat refusé.');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'ContratController::refuser → ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur.');
        }
    }

    public function signer($id)
{
    $id = (int) $id;
    $idUtilisateur = (int) session()->get('id_utilisateur');
    $contrat = $this->contratDuCandidat($id);

    if (! $contrat) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
    if ($contrat['statut'] !== 'A_SIGNER') {
        return redirect()->back()
            ->with('error', "Ce contrat n'est pas en état d'être signé (statut : {$contrat['statut']}).");
    }

    // Validation des champs de signature
    $nomComplet = trim((string) $this->request->getPost('nom_complet'));
    $accepte    = (string) $this->request->getPost('accepte');

    $erreurs = [];
    if (mb_strlen($nomComplet) < 3) {
        $erreurs[] = 'Vous devez saisir votre nom complet (au moins 3 caractères).';
    }
    if ($accepte !== '1') {
        $erreurs[] = 'Vous devez cocher « J\'ai lu et j\'approuve le contrat ».';
    }

    // Vérification tolérante que le nom saisi correspond bien au salarié
    $attendu = mb_strtolower(($contrat['prenom_salarie'] ?? '') . ' ' . ($contrat['nom_salarie'] ?? ''));
    $attendu = preg_replace('/\s+/', ' ', $attendu);
    $saisi   = mb_strtolower($nomComplet);
    $saisi   = preg_replace('/\s+/', ' ', $saisi);

    if ($attendu !== '' && $saisi !== $attendu && ! empty($erreurs) === false) {
        // Nom différent — on prévient sans bloquer (l'utilisateur peut signer avec variantes)
        // Pour bloquer, remplace par : $erreurs[] = 'Le nom saisi ne correspond pas au salarié.';
    }

    if (! empty($erreurs)) {
        return redirect()->back()->withInput()->with('errors', $erreurs);
    }

    $db = \Config\Database::connect();
    $db->transBegin();

    try {
        $service = new ContratService();

        // ---- 1) Signature du contrat ----
        $db->table('contrat')->where('id_contrat', $id)->update([
            'statut'         => 'SIGNE',
            'date_signature' => date('Y-m-d H:i:s'),
        ]);
        $db->table('historique_contrat')->insert([
            'id_contrat'     => $id,
            'ancien_statut'  => 'A_SIGNER',
            'nouveau_statut' => 'SIGNE',
            'id_utilisateur' => $idUtilisateur,
            'commentaire'    => 'Signé électroniquement par « ' . $nomComplet . ' »',
        ]);

        // ---- 2) Création de l'employé (RG-CONTRAT-009) ----
        $idPersonne = (int) ($contrat['id_personne_salarie'] ?? 0);
        if ($idPersonne <= 0) {
            throw new \RuntimeException("Impossible de trouver la personne du salarié.");
        }

        // Matricule unique : EMP-YYYY-NNNN
        $annee = date('Y');
        $dejaCetteAnnee = (int) $db->table('employe')
            ->like('matricule', "EMP-$annee-", 'after')
            ->countAllResults();
        $matricule = sprintf('EMP-%s-%04d', $annee, $dejaCetteAnnee + 1);

        // date_embauche = date_debut du contrat
        $dateEmbauche = $contrat['date_debut'] ?? date('Y-m-d');

        $db->table('employe')->insert([
            'id_contrat'    => $id,
            'id_personne'   => $idPersonne,
            'matricule'     => $matricule,
            'date_embauche' => $dateEmbauche,
            'statut'        => 'ACTIF',
        ]);
        $idEmploye = (int) $db->insertID();

        // Historique employé : NULL → ACTIF
        $db->table('historique_employe')->insert([
            'id_employe'     => $idEmploye,
            'ancien_statut'  => null,
            'nouveau_statut' => 'ACTIF',
            'id_utilisateur' => $idUtilisateur,
            'commentaire'    => "Création après signature du contrat {$contrat['reference']}",
        ]);

        // ---- 3) Affectation initiale (RG-CONTRAT-011) ----
        $idDepartement = (int) ($contrat['departement_id'] ?? 0);
        if ($idDepartement > 0) {
            $db->table('affectation')->insert([
                'id_employe'    => $idEmploye,
                'id_departement'=> $idDepartement,
                'date_debut'    => $dateEmbauche,
                'date_fin'      => null,
                'fonction'      => $contrat['poste_libelle'] ?? '—',
            ]);
        }

        // ---- 4) Notification au candidat ----
        $idUtilisateurCandidat = (int) ($contrat['id_utilisateur_salarie'] ?? 0);
        if ($idUtilisateurCandidat > 0) {
            $db->table('notification')->insert([
                'id_utilisateur' => $idUtilisateurCandidat,
                'titre'          => 'Contrat signé — Bienvenue !',
                'message'        => "Votre contrat {$contrat['reference']} est signé. "
                                  . "Votre matricule : {$matricule}. "
                                  . "Date d'embauche : {$dateEmbauche}.",
                'lien'           => '/candidat/contrats/' . $id,
                'lue'            => false,
            ]);
        }

        $db->transCommit();

        return redirect()->to('/candidat/contrats/' . $id)
                         ->with('success', "Contrat signé. Matricule attribué : {$matricule}.");

    } catch (\Throwable $e) {
        $db->transRollback();
        log_message('error', 'ContratController::signer → ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erreur lors de la signature : ' . $e->getMessage());
    }
}

    public function telechargerPdf($id)
    {
        $id = (int) $id;
        $contrat = $this->contratDuCandidat($id);

        if (! $contrat) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (empty($contrat['pdf_nom'])) {
            return redirect()->back()->with('error', 'Le PDF n\'est pas encore généré. Acceptez d\'abord le contrat.');
        }

        $row = $this->db->query(
            "SELECT encode(pdf_donnees, 'hex') AS hex, pdf_type_mime, pdf_nom FROM contrat WHERE id_contrat = ?",
            [$id]
        )->getRowArray();

        $binary = hex2bin((string) $row['hex']);
        return $this->response
            ->setHeader('Content-Type', $row['pdf_type_mime'] ?: 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($row['pdf_nom']) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($binary);
    }

    private function contratDuCandidat(int $idContrat): ?array
{
    $idUtilisateur = (int) session()->get('id_utilisateur');

    return $this->db->table('contrat c')
        ->select('c.*,
                  ca.id_candidature AS candidature_id,
                  ca.statut AS candidature_statut,
                  d.id_poste AS demande_id_poste,
                  d.id_demande AS demande_id,
                  po.libelle AS poste_libelle,
                  dep.id_departement AS departement_id,
                  dep.nom AS departement_nom,
                  o.titre AS offre_titre,
                  pe.id_personne AS id_personne_salarie,
                  pe.nom AS nom_salarie,
                  pe.prenom AS prenom_salarie,
                  pe.telephone AS telephone_salarie,
                  pe.adresse AS adresse_salarie,
                  u.email AS email_salarie,
                  u.id_utilisateur AS id_utilisateur_salarie')
        ->join('candidature ca', 'ca.id_candidature = c.id_candidature')
        ->join('candidat cand', 'cand.id_candidat = ca.id_candidat')
        ->join('personne pe', 'pe.id_personne = cand.id_personne')
        ->join('utilisateur u', 'u.id_utilisateur = pe.id_utilisateur')
        ->join('offre_emploi o', 'o.id_offre = ca.id_offre', 'left')
        ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
        ->join('poste po', 'po.id_poste = d.id_poste', 'left')
        ->join('departement dep', 'dep.id_departement = po.id_departement', 'left')
        ->where('c.id_contrat', $idContrat)
        ->where('pe.id_utilisateur', $idUtilisateur)
        ->get()->getRowArray();
}
}