<?php

namespace App\Controllers\Candidat;

use App\Controllers\BaseController;
use App\Models\CandidatureModel;
use App\Models\OffreModel;

class CandidaturesController extends BaseController
{
    private const MAX_OCTETS = 5 * 1024 * 1024; // 5 Mo

    public function formulaire($idOffre)
    {
        $idOffre = (int) $idOffre;
        $offre   = $this->offreDisponible($idOffre);

        if (! $offre) {
            return redirect()->to('/candidat/offres')
                             ->with('error', 'Offre introuvable ou non publiée.');
        }

        if ($this->aDejaPostule($idOffre)) {
            return redirect()->to('/candidat/offres')
                             ->with('error', 'Vous avez déjà postulé à cette offre.');
        }

        return view('candidat/offres/postuler', [
            'title' => 'Postuler — ' . $offre['titre'],
            'offre' => $offre,
            'errors' => session()->getFlashdata('errors') ?? [],
            'old'    => session()->getFlashdata('old') ?? [],
        ]);
    }

    public function postuler($idOffre)
    {
        $idOffre = (int) $idOffre;
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $cm = new CandidatureModel();

        $offre = $this->offreDisponible($idOffre);
        if (! $offre) {
            return $this->repondreErreur("Offre introuvable ou non publiée.", 404);
        }

        if ($this->aDejaPostule($idOffre)) {
            return $this->repondreErreur("Vous avez déjà postulé à cette offre.", 409);
        }

        $idCandidat = $cm->idCandidatPourUtilisateur($idUtilisateur);
        if (! $idCandidat) {
            return $this->repondreErreur("Profil candidat introuvable.", 403);
        }

        // ----- Validation CI4 (présence + taille) -----
        $rules = [
            'cv'     => "uploaded[cv]|max_size[cv,5120]",
            'lettre' => "uploaded[lettre]|max_size[lettre,5120]",
            'cin'    => "uploaded[cin]|max_size[cin,5120]",
        ];
        $messages = [
            'cv' => [
                'uploaded'  => 'Le CV est obligatoire.',
                'max_size'  => 'Le CV ne doit pas dépasser 5 Mo.',
            ],
            'lettre' => [
                'uploaded'  => 'La lettre de motivation est obligatoire.',
                'max_size'  => 'La lettre ne doit pas dépasser 5 Mo.',
            ],
            'cin' => [
                'uploaded'  => 'La CIN est obligatoire.',
                'max_size'  => 'La CIN ne doit pas dépasser 5 Mo.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->repondreErreur("Validation échouée.", 422, $this->validator->getErrors(),
                                         ['motivation' => (string) $this->request->getPost('motivation')]);
        }

        // ----- Validation MIME réelle (finfo, pas le Content-Type navigateur) -----
        $fichiers = [
            'cv'     => $this->request->getFile('cv'),
            'lettre' => $this->request->getFile('lettre'),
            'cin'    => $this->request->getFile('cin'),
        ];

        $erreurs = [];
        $mimeCv     = $this->mimeReel($fichiers['cv']);
        $mimeLettre = $this->mimeReel($fichiers['lettre']);
        $mimeCin    = $this->mimeReel($fichiers['cin']);

        if ($mimeCv !== 'application/pdf') {
            $erreurs['cv'] = 'Le CV doit être un fichier PDF.';
        }
        if ($mimeLettre !== 'application/pdf') {
            $erreurs['lettre'] = 'La lettre de motivation doit être un fichier PDF.';
        }
        if (! in_array($mimeCin, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
            $erreurs['cin'] = 'La CIN doit être un PDF, un JPEG ou un PNG.';
        }

        if (! empty($erreurs)) {
            return $this->repondreErreur("Type de fichier invalide.", 422, $erreurs,
                                         ['motivation' => (string) $this->request->getPost('motivation')]);
        }

        // ----- Lecture binaire + insertion transactionnelle -----
        $cvBin     = file_get_contents($fichiers['cv']->getTempName());
        $lettreBin = file_get_contents($fichiers['lettre']->getTempName());
        $cinBin    = file_get_contents($fichiers['cin']->getTempName());

        $cvNom     = $fichiers['cv']->getClientName();
        $lettreNom = $fichiers['lettre']->getClientName();
        $cinNom    = $fichiers['cin']->getClientName();

        $motivation = trim((string) $this->request->getPost('motivation'));
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Décodage hex côté SQL — sûr et portable pour BYTEA
            $sql = "INSERT INTO candidature
                    (id_offre, id_candidat,
                     cv_nom, cv_type_mime, cv_donnees,
                     lettre_nom, lettre_type_mime, lettre_donnees,
                     cin_nom, cin_type_mime, cin_donnees,
                     motivation, statut)
                    VALUES (?, ?,
                            ?, ?, decode(?, 'hex'),
                            ?, ?, decode(?, 'hex'),
                            ?, ?, decode(?, 'hex'),
                            ?, 'RECUE')
                    RETURNING id_candidature";

            $row = $db->query($sql, [
                $idOffre, $idCandidat,
                $cvNom, $mimeCv, bin2hex($cvBin),
                $lettreNom, $mimeLettre, bin2hex($lettreBin),
                $cinNom, $mimeCin, bin2hex($cinBin),
                $motivation !== '' ? $motivation : null,
            ])->getRowArray();

            $idCandidature = (int) ($row['id_candidature'] ?? 0);
            if ($idCandidature <= 0) {
                throw new \RuntimeException('ID candidature non retourné.');
            }

            $db->table('historique_candidature')->insert([
                'id_candidature' => $idCandidature,
                'ancien_statut'  => null,
                'nouveau_statut' => 'RECUE',
                'id_utilisateur' => $idUtilisateur,
                'commentaire'    => 'Candidature déposée par le candidat',
            ]);

            $db->transCommit();
            log_message('info', "Candidature #$idCandidature créée par user #$idUtilisateur (offre #$idOffre).");

            if ($this->request->getHeaderLine('Accept') === 'application/json') {
                return $this->response->setJSON([
                    'ok' => true,
                    'id_candidature' => $idCandidature,
                ]);
            }
            return redirect()->to('/candidat/mes-candidatures')
                             ->with('success', 'Candidature envoyée avec succès (statut : RECUE).');

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Candidat\CandidaturesController::postuler → ' . $e->getMessage());

            // Violation contrainte UNIQUE (23505) = double candidature
            $err = $db->error();
            if (($err['code'] ?? '') === '23505') {
                return $this->repondreErreur("Vous avez déjà postulé à cette offre.", 409);
            }
            return $this->repondreErreur("Erreur lors de l'enregistrement. Réessayez.", 500);
        }
    }

   public function mesCandidatures()
{
    return view('candidat/mes_candidatures', ['title' => 'Mes candidatures']);
}

    // ---------- helpers ----------

   private function offreDisponible(int $idOffre): ?array
{
    $db = \Config\Database::connect();   // ← connexion locale

    return $db->table('offre_emploi o')
        ->select('o.*, p.libelle AS poste_libelle, dep.nom AS departement_nom,
                  d.salaire_par_personne, d.date_besoin')
        ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
        ->join('poste p', 'p.id_poste = d.id_poste', 'left')
        ->join('departement dep', 'dep.id_departement = p.id_departement', 'left')
        ->where('o.id_offre', $idOffre)
        ->where('o.statut', 'PUBLIEE')
        ->groupStart()
            ->where('o.date_limite IS NULL', null, false)
            ->orWhere('o.date_limite >=', date('Y-m-d'))
        ->groupEnd()
        ->get()->getRowArray();
}

private function aDejaPostule(int $idOffre): bool
{
    $db = \Config\Database::connect();   // ← connexion locale

    $idUtilisateur = (int) session()->get('id_utilisateur');
    $idCandidat = (new CandidatureModel())->idCandidatPourUtilisateur($idUtilisateur);
    if (! $idCandidat) {
        return false;
    }
    return $db->table('candidature')
        ->where('id_offre', $idOffre)
        ->where('id_candidat', $idCandidat)
        ->countAllResults() > 0;
}

    private function mimeReel($file): string
    {
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return '';
        }
        try {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            return (string) $finfo->file($file->getTempName());
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Réponse unifiée JSON ou HTML+redirect pour les erreurs.
     */
    private function repondreErreur(string $msg, int $code = 422, array $erreurs = [], array $old = [])
    {
        if ($this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->response->setStatusCode($code)->setJSON([
                'ok' => false,
                'message' => $msg,
                'errors'  => $erreurs,
            ]);
        }
        return redirect()->back()->withInput()
                         ->with('error', $msg)
                         ->with('errors', $erreurs);
    }
}