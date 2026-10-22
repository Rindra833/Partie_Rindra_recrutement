<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratModel extends Model
{
    protected $table      = 'contrat';
    protected $primaryKey = 'id_contrat';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_candidature', 'reference', 'type_contrat', 'date_debut', 'date_fin',
        'salaire', 'periodicite_salaire', 'lieu_travail', 'missions',
        'horaires', 'conges_absences', 'statut',
        'pdf_nom', 'pdf_type_mime', 'pdf_donnees', 'date_signature',
    ];
    protected $useTimestamps = false;

    public function trouverParCandidature(int $idCandidature): ?array
    {
        return $this->db->table('contrat')
            ->where('id_candidature', $idCandidature)
            ->get()->getRowArray();
    }

    /**
     * Détail complet : contrat + candidature + candidat + offre + poste + département.
     */
    public function trouverDetaille(int $idContrat): ?array
    {
        return $this->db->table('contrat c')
            ->select('c.*, 
                      ca.id_candidature, ca.statut AS candidature_statut,
                      pe.nom, pe.prenom, pe.telephone, pe.adresse, pe.id_utilisateur AS id_utilisateur_candidat,
                      u.email,
                      o.titre AS offre_titre, o.id_offre,
                      d.id_demande, d.id_poste, d.date_besoin, d.salaire_par_personne,
                      po.libelle AS poste_libelle,
                      dep.id_departement, dep.nom AS departement_nom')
            ->join('candidature ca', 'ca.id_candidature = c.id_candidature')
            ->join('candidat cand', 'cand.id_candidat = ca.id_candidat')
            ->join('personne pe', 'pe.id_personne = cand.id_personne')
            ->join('utilisateur u', 'u.id_utilisateur = pe.id_utilisateur')
            ->join('offre_emploi o', 'o.id_offre = ca.id_offre', 'left')
            ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
            ->join('poste po', 'po.id_poste = d.id_poste', 'left')
            ->join('departement dep', 'dep.id_departement = po.id_departement', 'left')
            ->where('c.id_contrat', $idContrat)
            ->get()->getRowArray();
    }

    /** Liste des contrats du candidat (via toutes ses candidatures). */
    public function listePourCandidat(int $idCandidat): array
    {
        return $this->db->table('contrat c')
            ->select('c.id_contrat, c.reference, c.type_contrat, c.statut, c.salaire,
                      c.date_debut, c.date_fin, c.date_signature,
                      ca.id_candidature, ca.statut AS candidature_statut,
                      o.titre AS offre_titre')
            ->join('candidature ca', 'ca.id_candidature = c.id_candidature', 'left')
            ->join('offre_emploi o', 'o.id_offre = ca.id_offre', 'left')
            ->where('ca.id_candidat', $idCandidat)
            ->orderBy('c.id_contrat', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Crée un contrat BROUILLON pour une candidature RETENUE.
     * Idempotent : retourne l'id existant si déjà créé.
     */
    public function creerPourCandidature(int $idCandidature): int
    {
        $existing = $this->trouverParCandidature($idCandidature);
        if ($existing) {
            return (int) $existing['id_contrat'];
        }

        $info = $this->db->table('candidature ca')
            ->select('ca.id_candidature, ca.statut AS candidature_statut,
                      o.id_offre, d.id_poste, d.salaire_par_personne, d.date_besoin,
                      po.libelle AS poste_libelle,
                      dep.nom AS departement_nom')
            ->join('offre_emploi o', 'o.id_offre = ca.id_offre')
            ->join('demande_recrutement d', 'd.id_demande = o.id_demande')
            ->join('poste po', 'po.id_poste = d.id_poste')
            ->join('departement dep', 'dep.id_departement = po.id_departement')
            ->where('ca.id_candidature', $idCandidature)
            ->get()->getRowArray();

        if (! $info) {
            throw new \RuntimeException("Candidature #$idCandidature introuvable.");
        }
        if ($info['candidature_statut'] !== 'RETENUE') {
            throw new \RuntimeException("La candidature doit être RETENUE pour créer un contrat.");
        }

        // Salaire : priorité à la condition "Salaire" CONFIRMER, sinon demande
        $condSalaire = $this->db->table('condition_contrat')
            ->where('id_poste', $info['id_poste'])
            ->where('statut', 'CONFIRMER')
            ->where('type_valeur', 'MONTANT')
            ->orderBy('ordre_affichage', 'ASC')
            ->get()->getRowArray();

        $salaire = $condSalaire
            ? (float) $condSalaire['valeur_condition']
            : (float) $info['salaire_par_personne'];

        $year  = date('Y');
        $count = (int) $this->db->table('contrat')->countAllResults() + 1;
        $reference = sprintf('CT-%s-%04d', $year, $count);

        $this->db->table('contrat')->insert([
            'id_candidature'      => $idCandidature,
            'reference'           => $reference,
            'type_contrat'        => 'CDI',
            'date_debut'          => date('Y-m-d', strtotime('+30 days')),
            'date_fin'            => null,
            'salaire'             => $salaire,
            'periodicite_salaire' => 'MENSUEL',
            'lieu_travail'        => 'Antananarivo',
            'missions'            => 'Missions telles que définies par le département '
                                   . ($info['departement_nom'] ?? '—')
                                   . ' pour le poste de ' . ($info['poste_libelle'] ?? '—') . '.',
            'horaires'            => '35 heures par semaine, du lundi au vendredi, 8h30–16h30.',
            'conges_absences'     => 'Congés payés selon la législation en vigueur '
                                   . '(2,5 jours ouvrables par mois travaillé).',
            'statut'              => 'BROUILLON',
        ]);

        $idContrat = (int) $this->db->insertID();
        log_message('info', "Contrat #$idContrat ($reference) créé pour candidature #$idCandidature.");

        return $idContrat;
    }
}