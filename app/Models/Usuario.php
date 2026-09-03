<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Model
{
    use HasApiTokens;

    protected $table = 'usuarios';
    protected $fillable = ['nombre', 'apellido', 'email', 'telefono', 'password', 'rol_id', 'activo'];
    protected $hidden = ['password'];

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function ingresos()
    {
        return $this->hasMany(Ingreso::class);
    }

    public function ordenesCreadas()
    {
        return $this->hasMany(Orden::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }
}