<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidatureModel extends Model
{
    protected $table      = 'candidature';
    protected $primaryKey = 'id_candidature';
    protected $returnType = 'array';
    protected $allowedFields = ['statut'];
    protected $useTimestamps = false;

    /** Liste enrichie avec candidat + offre. */
    public function listeEnrichie(?string $statut = null): array
    {
        $b = $this->db->table('candidature c')
            ->select('c.id_candidature, c.statut, c.date_candidature,
                      c.cv_nom, c.lettre_nom, c.cin_nom,
                      p.nom, p.prenom, p.telephone,
                      o.titre AS offre_titre, o.statut AS offre_statut')
            ->join('candidat ca', 'ca.id_candidat = c.id_candidat', 'left')
            ->join('personne p', 'p.id_personne = ca.id_personne', 'left')
            ->join('offre_emploi o', 'o.id_offre = c.id_offre', 'left')
            ->orderBy('c.date_candidature', 'DESC');

        if ($statut) {
            $b->where('c.statut', $statut);
        }
        return $b->get()->getResultArray();
    }

    /** Détail complet d'une candidature. */
    public function trouverDetaillee(int $id): ?array
    {
        return $this->db->table('candidature c')
            ->select('c.*, p.nom, p.prenom, p.telephone, p.adresse, p.date_naissance,
                      u.id_utilisateur AS id_utilisateur_candidat, u.email AS email_candidat,
                      ca.niveau_etude, ca.experience,
                      o.id_offre, o.titre AS offre_titre, o.statut AS offre_statut,
                      d.id_demande, d.id_poste,
                      po.libelle AS poste_libelle, dep.nom AS departement_nom')
            ->join('candidat ca', 'ca.id_candidat = c.id_candidat', 'left')
            ->join('personne p', 'p.id_personne = ca.id_personne', 'left')
            ->join('utilisateur u', 'u.id_utilisateur = p.id_utilisateur', 'left')
            ->join('offre_emploi o', 'o.id_offre = c.id_offre', 'left')
            ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
            ->join('poste po', 'po.id_poste = d.id_poste', 'left')
            ->join('departement dep', 'dep.id_departement = po.id_departement', 'left')
            ->where('c.id_candidature', $id)
            ->get()->getRowArray();
    }

    /** Évaluations RH de tous les types. */
    public function evaluationsDe(int $idCandidature): array
    {
        return $this->db->table('evaluation_candidat ev')
            ->select('ev.*, u.email AS evaluateur_email,
                      e.date_entretien, e.lieu AS entretien_lieu,
                      t.intitule AS test_intitule')
            ->join('utilisateur u', 'u.id_utilisateur = ev.id_evaluateur', 'left')
            ->join('entretien e', 'e.id_entretien = ev.id_entretien', 'left')
            ->join('test_technique t', 't.id_test = ev.id_test', 'left')
            ->where('ev.id_candidature', $idCandidature)
            ->orderBy('ev.date_evaluation', 'DESC')
            ->get()->getResultArray();
    }

    /** Historique des changements de statut. */
    public function historiqueDe(int $idCandidature): array
    {
        return $this->db->table('historique_candidature h')
            ->select('h.*, u.email AS auteur')
            ->join('utilisateur u', 'u.id_utilisateur = h.id_utilisateur', 'left')
            ->where('h.id_candidature', $idCandidature)
            ->orderBy('h.date_action', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Retourne les 3 documents encodés en hex.
     * On encode côté SQL car le driver pgsql renvoie BYTEA en format échappé.
     */
    public function documentsEncodees(int $idCandidature): ?array
    {
        return $this->db->query(
            "SELECT
                encode(cv_donnees,     'hex') AS cv_hex,     cv_type_mime,     cv_nom,
                encode(lettre_donnees, 'hex') AS lettre_hex, lettre_type_mime, lettre_nom,
                encode(cin_donnees,    'hex') AS cin_hex,    cin_type_mime,    cin_nom
             FROM candidature WHERE id_candidature = ?",
            [$idCandidature]
        )->getRowArray();
    }

    /** Toutes les offres auxquelles ce candidat a déjà postulé. */
public function offresDejaPostulees(int $idCandidat): array
{
    $rows = $this->db->table('candidature')
        ->select('id_offre')
        ->where('id_candidat', $idCandidat)
        ->get()->getResultArray();
    return array_map('intval', array_column($rows, 'id_offre'));
}

/** Retourne le id_candidat pour un id_utilisateur, ou null. */
public function idCandidatPourUtilisateur(int $idUtilisateur): ?int
{
    $row = $this->db->table('candidat c')
        ->select('c.id_candidat')
        ->join('personne p', 'p.id_personne = c.id_personne')
        ->where('p.id_utilisateur', $idUtilisateur)
        ->get()->getRowArray();
    return $row ? (int) $row['id_candidat'] : null;
}

/** Liste des candidatures d'un candidat (pour la page Mes candidatures). */
public function listeParCandidat(int $idCandidat): array
{
    return $this->db->table('candidature c')
        ->select('c.id_candidature, c.statut, c.date_candidature,
                  o.titre AS offre_titre, o.statut AS offre_statut, o.date_limite')
        ->join('offre_emploi o', 'o.id_offre = c.id_offre', 'left')
        ->where('c.id_candidat', $idCandidat)
        ->orderBy('c.date_candidature', 'DESC')
        ->get()->getResultArray();
}

}