<?php

namespace App\Models;

use CodeIgniter\Model;

class RequeteConditionModel extends Model
{
    protected $table      = 'requete_condition_contrat';
    protected $primaryKey = 'id_requete';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_candidature', 'id_condition', 'valeur_proposee',
        'commentaire_candidat', 'statut',
    ];
    protected $useTimestamps = false;

    /**
     * Conditions CONFIRMER du poste associé à une candidature.
     * Exclut les conditions qui ont déjà une requête active (SOUMISE ou ACCEPTEE_*).
     */
    public function conditionsModifiables(int $idCandidature): array
    {
        return $this->db->query(
            "SELECT c.id_condition, c.libelle, c.type_valeur, c.valeur_condition, c.ordre_affichage
             FROM condition_contrat c
             JOIN poste p ON p.id_poste = c.id_poste
             JOIN demande_recrutement dr ON dr.id_poste = p.id_poste
             JOIN offre_emploi o ON o.id_demande = dr.id_demande
             JOIN candidature ca ON ca.id_offre = o.id_offre
             WHERE ca.id_candidature = ?
               AND c.statut = 'CONFIRMER'
               AND NOT EXISTS (
                   SELECT 1 FROM requete_condition_contrat rq
                   WHERE rq.id_candidature = ca.id_candidature
                     AND rq.id_condition = c.id_condition
                     AND rq.statut IN ('SOUMISE', 'ACCEPTEE_RH', 'ACCEPTEE_DIRECTION')
               )
             ORDER BY c.ordre_affichage",
            [$idCandidature]
        )->getResultArray();
    }

    /** Toutes les conditions confirmées du poste (pour affichage même si déjà requêtées). */
    public function conditionsDuPoste(int $idCandidature): array
    {
        return $this->db->query(
            "SELECT c.id_condition, c.libelle, c.type_valeur, c.valeur_condition, c.ordre_affichage
             FROM condition_contrat c
             JOIN poste p ON p.id_poste = c.id_poste
             JOIN demande_recrutement dr ON dr.id_poste = p.id_poste
             JOIN offre_emploi o ON o.id_demande = dr.id_demande
             JOIN candidature ca ON ca.id_offre = o.id_offre
             WHERE ca.id_candidature = ?
               AND c.statut = 'CONFIRMER'
             ORDER BY c.ordre_affichage",
            [$idCandidature]
        )->getResultArray();
    }

    /** Requêtes existantes pour une candidature, avec le libellé de la condition. */
    public function requetesDe(int $idCandidature): array
    {
        return $this->db->table('requete_condition_contrat rq')
            ->select('rq.*, c.libelle, c.type_valeur, c.valeur_condition')
            ->join('condition_contrat c', 'c.id_condition = rq.id_condition', 'left')
            ->where('rq.id_candidature', $idCandidature)
            ->orderBy('rq.date_creation', 'DESC')
            ->get()->getResultArray();
    }

    /** Historique d'une requête. */
    public function historiqueDe(int $idRequete): array
    {
        return $this->db->table('historique_requete_condition h')
            ->select('h.*, u.email AS auteur')
            ->join('utilisateur u', 'u.id_utilisateur = h.id_utilisateur', 'left')
            ->where('h.id_requete', $idRequete)
            ->orderBy('h.date_action', 'DESC')
            ->get()->getResultArray();
    }
}