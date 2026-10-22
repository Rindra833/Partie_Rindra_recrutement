<?php

namespace App\Libraries;

use App\Models\ContratModel;

class ContratService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Copie les conditions CONFIRMER du poste dans contrat_condition (figement RG-COND-006).
     */
    public function figerConditions(int $idContrat, int $idPoste): int
    {
        // Idempotent : ne re-fige pas si déjà fait
        $exist = $this->db->table('contrat_condition')
            ->where('id_contrat', $idContrat)
            ->countAllResults();
        if ($exist > 0) {
            return $exist;
        }

        $conditions = $this->db->table('condition_contrat')
            ->where('id_poste', $idPoste)
            ->where('statut', 'CONFIRMER')
            ->orderBy('ordre_affichage', 'ASC')
            ->get()->getResultArray();

        $n = 0;
        foreach ($conditions as $c) {
            $this->db->table('contrat_condition')->insert([
                'id_contrat'      => $idContrat,
                'libelle'         => $c['libelle'],
                'type_valeur'     => $c['type_valeur'],
                'valeur_finale'   => $c['valeur_condition'],
                'ordre_affichage' => $c['ordre_affichage'],
            ]);
            $n++;
        }
        return $n;
    }

    /**
     * Génère le PDF du contrat et l'enregistre dans contrat.pdf_donnees.
     * Retourne [nom_fichier, mime, taille_octets].
     */
    public function genererPdf(int $idContrat): array
    {
        $contrat = (new ContratModel())->trouverDetaille($idContrat);
        if (! $contrat) {
            throw new \RuntimeException("Contrat #$idContrat introuvable.");
        }

        $conditions = $this->db->table('contrat_condition')
            ->where('id_contrat', $idContrat)
            ->orderBy('ordre_affichage', 'ASC')
            ->get()->getResultArray();

        $html = view('candidat/contrat/pdf', [
            'contrat'    => $contrat,
            'conditions' => $conditions,
            'employeur'  => 'SIRH SARL — Antananarivo, Madagascar',
        ]);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfBinary = $dompdf->output();

        $nomFichier = 'contrat_' . $contrat['reference'] . '.pdf';

        // Stockage BYTEA via hex
        $this->db->query(
            "UPDATE contrat SET pdf_nom = ?, pdf_type_mime = ?, pdf_donnees = decode(?, 'hex') WHERE id_contrat = ?",
            [$nomFichier, 'application/pdf', bin2hex($pdfBinary), $idContrat]
        );

        return [$nomFichier, 'application/pdf', strlen($pdfBinary)];
    }

    /**
     * Transition de statut avec historique (RG-CONTRAT-008).
     */
    public function changerStatut(int $idContrat, string $nouveau, int $idUtilisateur, ?string $commentaire = null): void
    {
        $contrat = $this->db->table('contrat')->where('id_contrat', $idContrat)->get()->getRowArray();
        if (! $contrat) {
            throw new \RuntimeException("Contrat #$idContrat introuvable.");
        }
        if ($contrat['statut'] === $nouveau) {
            return;
        }

        $this->db->table('contrat')->where('id_contrat', $idContrat)
            ->update(['statut' => $nouveau]);

        $this->db->table('historique_contrat')->insert([
            'id_contrat'     => $idContrat,
            'ancien_statut'  => $contrat['statut'],
            'nouveau_statut' => $nouveau,
            'id_utilisateur' => $idUtilisateur,
            'commentaire'    => $commentaire,
        ]);
    }
}