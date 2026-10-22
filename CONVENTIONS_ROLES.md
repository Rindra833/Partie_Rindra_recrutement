# Conventions rôles & filtres — SIRH

## Session après connexion

```php
session()->set([
    'id_utilisateur' => (int) $user->id_utilisateur,
    'email'          => $user->email,
    'role'           => $user->role, // exact SQL: DEPARTEMENT|RH|DIRECTION|CANDIDAT|ADMINISTRATEUR|FINANCE
    'nom'            => $personne->nom ?? null,
    'prenom'         => $personne->prenom ?? null,
]);
```

## Filtres

| Alias  | Classe              | Usage                                      |
|--------|---------------------|--------------------------------------------|
| `auth` | AuthFilter          | Connecté, n'importe quel rôle              |
| `role` | RoleFilter          | Connecté + rôle(s) autorisé(s)             |

Exemples routes :

```php
$routes->group('rh', ['filter' => 'role:RH'], function ($routes) { ... });
$routes->group('direction', ['filter' => 'role:DIRECTION,FINANCE'], ...);
$routes->get('profil', 'ProfilController::index', ['filter' => 'auth']);
```

## Préfixes d'URL

| Rôle            | Préfixe        | Responsable     |
|-----------------|----------------|-----------------|
| DEPARTEMENT     | `/departement` | John            |
| RH              | `/rh`          | Tsiky           |
| DIRECTION/FINANCE | `/direction` | D / Rindra      |
| CANDIDAT        | `/candidat`    | D / Rindra      |
| ADMINISTRATEUR  | `/admin`       | Danie           |

## Helpers

- `current_role()`, `current_user_id()`, `has_role('RH')`, `role_home()`
- Auto-chargé via `Config\Autoload::$helpers = ['role']`

## Important

- Toujours contrôler le rôle **côté serveur** (filtre ou contrôleur), pas seulement masquer les boutons.
- Les valeurs de `role` en session doivent correspondre **exactement** au CHECK SQL (majuscules).
- Ne pas réutiliser l'ancien code mobile-money (ClientModel, role `client` / `operateur`).
