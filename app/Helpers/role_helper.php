<?php

/**
 * Constantes et helpers de rôles — alignés sur le CHECK SQL de utilisateur.role
 * et sur regles_de_gestion.md / TODO_recrutement.md.
 */

if (! defined('ROLE_DEPARTEMENT')) {
    define('ROLE_DEPARTEMENT', 'DEPARTEMENT');
    define('ROLE_RH', 'RH');
    define('ROLE_DIRECTION', 'DIRECTION');
    define('ROLE_CANDIDAT', 'CANDIDAT');
    define('ROLE_ADMINISTRATEUR', 'ADMINISTRATEUR');
    define('ROLE_FINANCE', 'FINANCE');
}

if (! function_exists('current_role')) {
    function current_role(): ?string
    {
        $role = session()->get('role');
        return $role !== null ? strtoupper((string) $role) : null;
    }
}

if (! function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $id = session()->get('id_utilisateur');
        return $id !== null ? (int) $id : null;
    }
}

if (! function_exists('has_role')) {
    /**
     * @param string|array<string> $roles
     */
    function has_role(string|array $roles): bool
    {
        $current = current_role();
        if ($current === null) {
            return false;
        }
        $list = array_map('strtoupper', (array) $roles);
        return in_array($current, $list, true);
    }
}

if (! function_exists('role_home')) {
    function role_home(?string $role = null): string
    {
        $role = strtoupper($role ?? (string) current_role());
        return match ($role) {
            ROLE_DEPARTEMENT    => '/departement',
            ROLE_RH             => '/rh',
            ROLE_DIRECTION      => '/direction',
            ROLE_CANDIDAT       => '/candidat',
            ROLE_ADMINISTRATEUR => '/admin',
            ROLE_FINANCE        => '/direction',
            default             => '/connexion',
        };
    }
}
