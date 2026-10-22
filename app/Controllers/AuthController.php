<?php

namespace App\Controllers;

/**
 * Authentification — socle minimal.
 * Danie finalisera : hash bcrypt, création utilisateur/personne/candidat,
 * messages d'erreur sans fuite d'info, sessions sécurisées.
 *
 * Session attendue après connexion réussie :
 *   id_utilisateur, email, role, nom, prenom (optionnel)
 */
class AuthController extends BaseController
{
    public function connexion()
    {
        // Si déjà connecté → rediriger vers l'espace du rôle
        if (session()->get('id_utilisateur') && session()->get('role')) {
            helper('role');
            return redirect()->to(role_home());
        }

        return view('auth/connexion');
    }

    public function checkConnexion()
    {
        // TODO Danie : valider email + mot_de_passe_hash (jamais en clair)
        // Exemple de structure attendue après auth réussie :
        //
        // session()->set([
        //     'id_utilisateur' => $user->id_utilisateur,
        //     'email'          => $user->email,
        //     'role'           => $user->role, // DEPARTEMENT|RH|DIRECTION|CANDIDAT|ADMINISTRATEUR|FINANCE
        //     'nom'            => $personne->nom,
        //     'prenom'         => $personne->prenom,
        // ]);
        //
        // return redirect()->to(role_home($user->role));

        return redirect()->to('/connexion')->with('error', 'Authentification non implémentée (Danie).');
    }

    public function inscription()
    {
        return view('auth/inscription');
    }

    public function registerCandidat()
    {
        // TODO Danie : transaction utilisateur + personne + candidat
        // RG-UTIL-001 email unique, RG-UTIL-002 hash, RG-UTIL-003
        return redirect()->to('/connexion')->with('error', 'Inscription non implémentée (Danie).');
    }

    public function deconnexion()
    {
        session()->destroy();
        return redirect()->to('/connexion')->with('success', 'Vous êtes déconnecté.');
    }
}
