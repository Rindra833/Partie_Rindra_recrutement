<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table      = 'client';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nom', 
        'prenom', 
        'numero_telephone', 
        'id_prefixe',
        'solde' 
    ];

    public function getByNumero(string $numero)
    {
        return $this->where('numero_telephone', $numero)->first();
    }

    public function existsByNumero(string $numero): bool
    {
        return (bool) $this->where('numero_telephone', $numero)->countAllResults() > 0;
    }

    
}