<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $fillable = ['usuario_id', 'tipo', 'titulo', 'mensaje', 'ingreso_id', 'leida_at'];
    protected $casts = ['leida_at' => 'datetime'];

    // Roles que se enteran de lo que pasa con los vehículos
    public const ROLES_GESTION = ['Contabilidad', 'Jefe de taller', 'Administrador'];

    public const ESTADOS = [
        'en_espera_diagnostico' => 'En espera de diagnóstico',
        'en_reparacion' => 'En reparación',
        'pendiente_cotizacion' => 'Pendiente de cotización',
        'pendiente_informe' => 'Pendiente de informe',
        'pendiente_prefactura' => 'Pendiente de prefactura',
        'orden_enviada' => 'Orden enviada',
        'salio_taller' => 'Salió del taller',
    ];

    public static function etiquetaEstado(?string $clave): string
    {
        return self::ESTADOS[$clave] ?? (string) $clave;
    }

    // Usuario que está haciendo la acción (web por sesión, app por token)
    public static function actorId(): ?int
    {
        $id = session('usuario_id');

        if (!$id) {
            $usuario = request()->user();
            $id = $usuario ? $usuario->id : null;
        }

        return $id ? (int) $id : null;
    }

    public static function nombreActor(?int $id): string
    {
        if (!$id) {
            return 'Alguien';
        }

        $u = Usuario::find($id);

        return $u ? trim($u->nombre . ' ' . ($u->apellido ?? '')) : 'Alguien';
    }

    public static function aUsuario(int $usuarioId, string $tipo, string $titulo, ?string $mensaje = null, ?int $ingresoId = null): void
    {
        static::create([
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'titulo' => mb_substr($titulo, 0, 150),
            'mensaje' => $mensaje !== null ? mb_substr($mensaje, 0, 300) : null,
            'ingreso_id' => $ingresoId,
        ]);
    }

    public static function aRoles(array $roles, string $tipo, string $titulo, ?string $mensaje = null, ?int $ingresoId = null, ?int $excluirUsuarioId = null): void
    {
        $ids = Usuario::where('activo', true)
            ->whereHas('rol', fn ($q) => $q->whereIn('nombre', $roles))
            ->when($excluirUsuarioId, fn ($q) => $q->where('id', '!=', $excluirUsuarioId))
            ->pluck('id');

        foreach ($ids as $id) {
            static::aUsuario((int) $id, $tipo, $titulo, $mensaje, $ingresoId);
        }
    }
}
