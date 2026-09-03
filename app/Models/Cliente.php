<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $fillable = ['nombre', 'doc_nit', 'telefono', 'email', 'direccion'];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }
}
