<?php

namespace App\Models;

use CodeIgniter\Model;

class OffreModel extends Model
{
    protected $table         = 'offre_emploi';
    protected $primaryKey    = 'id_offre';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_demande', 'titre', 'description', 'date_publication',
        'date_limite', 'statut', 'cree_par',
    ];
    protected $useTimestamps = false;

    /** Liste enrichie avec demande + poste + département. */
    public function listeAvecDemande(?string $statut = null): array
    {
        $builder = $this->db->table('offre_emploi o')
            ->select('o.*, d.date_besoin, d.effectif_demande, d.salaire_par_personne,
                      p.libelle AS poste_libelle, dep.nom AS departement_nom')
            ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
            ->join('poste p', 'p.id_poste = d.id_poste', 'left')
            ->join('departement dep', 'dep.id_departement = p.id_departement', 'left')
            ->orderBy('o.date_creation', 'DESC');

        if ($statut) {
            $builder->where('o.statut', $statut);
        }
        return $builder->get()->getResultArray();
    }

    /** Une offre avec ses jointures (pour la page détail). */
    public function trouverAvecDemande(int $id): ?array
    {
        return $this->db->table('offre_emploi o')
            ->select('o.*, d.date_besoin, d.effectif_demande, d.salaire_par_personne, d.motif,
                      p.libelle AS poste_libelle, dep.nom AS departement_nom')
            ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
            ->join('poste p', 'p.id_poste = d.id_poste', 'left')
            ->join('departement dep', 'dep.id_departement = p.id_departement', 'left')
            ->where('o.id_offre', $id)
            ->get()->getRowArray();
    }

    /** Offres PUBLIEE et non fermées (date_limite nulle ou future). */
public function listePubliees(?int $limit = null): array
{
    $b = $this->db->table('offre_emploi o')
        ->select('o.*, d.date_besoin, d.effectif_demande, d.salaire_par_personne, d.motif,
                  p.libelle AS poste_libelle, dep.nom AS departement_nom')
        ->join('demande_recrutement d', 'd.id_demande = o.id_demande', 'left')
        ->join('poste p', 'p.id_poste = d.id_poste', 'left')
        ->join('departement dep', 'dep.id_departement = p.id_departement', 'left')
        ->where('o.statut', 'PUBLIEE')
        ->groupStart()
            ->where('o.date_limite IS NULL', null, false)
            ->orWhere('o.date_limite >=', date('Y-m-d'))
        ->groupEnd()
        ->orderBy('o.date_publication', 'DESC');

    if ($limit !== null) {
        $b->limit($limit);
    }
    return $b->get()->getResultArray();
}
}