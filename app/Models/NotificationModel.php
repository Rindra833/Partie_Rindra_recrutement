<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table      = 'notification';
    protected $primaryKey = 'id_notification';
    protected $returnType = 'array';
    protected $allowedFields = ['id_utilisateur', 'titre', 'message', 'lien', 'lue', 'date_lecture'];
    protected $useTimestamps = false;

    /** Toutes les notifications d'un utilisateur, triées par date desc. */
    public function listePour(int $idUtilisateur, ?bool $luesSeulement = null): array
    {
        $b = $this->db->table('notification')
            ->where('id_utilisateur', $idUtilisateur)
            ->orderBy('date_creation', 'DESC');

        if ($luesSeulement === false) {
            $b->where('lue', false);
        } elseif ($luesSeulement === true) {
            $b->where('lue', true);
        }
        return $b->get()->getResultArray();
    }

    /** Une notification d'un utilisateur donné (contrôle d'accès). */
    public function trouverPour(int $idNotification, int $idUtilisateur): ?array
    {
        return $this->db->table('notification')
            ->where('id_notification', $idNotification)
            ->where('id_utilisateur', $idUtilisateur)
            ->get()->getRowArray();
    }

    public function compterNonLues(int $idUtilisateur): int
    {
        return $this->db->table('notification')
            ->where('id_utilisateur', $idUtilisateur)
            ->where('lue', false)
            ->countAllResults();
    }

    /** Marque une notification comme lue si elle appartient à l'utilisateur. */
    public function marquerLue(int $idNotification, int $idUtilisateur): bool
    {
        $notif = $this->trouverPour($idNotification, $idUtilisateur);
        if (! $notif) {
            return false;
        }
        if ($notif['lue']) {
            return true; // déjà lue
        }
        return $this->db->table('notification')
            ->where('id_notification', $idNotification)
            ->update(['lue' => true, 'date_lecture' => date('Y-m-d H:i:s')]);
    }

    /** Marque toutes les non-lues d'un utilisateur. */
    public function marquerToutLue(int $idUtilisateur): int
    {
        $this->db->table('notification')
            ->where('id_utilisateur', $idUtilisateur)
            ->where('lue', false)
            ->update(['lue' => true, 'date_lecture' => date('Y-m-d H:i:s')]);
        return $this->db->affectedRows();
    }
}