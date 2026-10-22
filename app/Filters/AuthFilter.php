<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtre "utilisateur connecté" uniquement (sans contrôle de rôle).
 * Utile pour les pages accessibles à tous les rôles authentifiés
 * (ex. notifications, profil).
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('id_utilisateur') || ! $session->get('role')) {
            return redirect()
                ->to('/connexion')
                ->with('error', 'Veuillez vous connecter.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // rien
    }
}
