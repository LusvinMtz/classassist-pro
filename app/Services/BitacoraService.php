<?php

namespace App\Services;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Model;

class BitacoraService
{
    /**
     * Registra una entrada genérica en la bitácora.
     */
    public static function registrar(
        string  $accion,
        string  $modulo,
        string  $descripcion,
        ?int    $entidadId       = null,
        ?array  $datosAnteriores = null,
        ?array  $datosNuevos     = null,
        string  $nivel           = 'info',
    ): void {
        try {
            Bitacora::create([
                'usuario_id'       => auth()->id(),
                'accion'           => $accion,
                'modulo'           => $modulo,
                'entidad_id'       => $entidadId,
                'descripcion'      => $descripcion,
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos'     => $datosNuevos,
                'ip'               => request()->ip(),
                'user_agent'       => request()->userAgent(),
                'nivel'            => $nivel,
            ]);
        } catch (\Throwable) {
            // La bitácora nunca debe romper el flujo principal
        }
    }

    /**
     * Registra la creación de un modelo Eloquent.
     */
    public static function registrarCreacion(Model $model, string $modulo, string $descripcion): void
    {
        self::registrar(
            accion:      'crear',
            modulo:      $modulo,
            descripcion: $descripcion,
            entidadId:   $model->getKey(),
            datosNuevos: self::modelToArray($model),
        );
    }

    /**
     * Registra la edición de un modelo Eloquent.
     */
    public static function registrarEdicion(Model $model, string $modulo, string $descripcion): void
    {
        $cambios   = $model->getChanges();
        $originales = array_intersect_key($model->getOriginal(), $cambios);

        // Omitir campos sensibles
        unset($cambios['password'], $originales['password'], $cambios['remember_token'], $originales['remember_token']);

        self::registrar(
            accion:          'editar',
            modulo:          $modulo,
            descripcion:     $descripcion,
            entidadId:       $model->getKey(),
            datosAnteriores: $originales ?: null,
            datosNuevos:     $cambios    ?: null,
        );
    }

    /**
     * Registra la eliminación (soft delete) de un modelo Eloquent.
     */
    public static function registrarEliminacion(Model $model, string $modulo, string $descripcion): void
    {
        self::registrar(
            accion:          'eliminar',
            modulo:          $modulo,
            descripcion:     $descripcion,
            entidadId:       $model->getKey(),
            datosAnteriores: self::modelToArray($model),
            nivel:           'advertencia',
        );
    }

    /**
     * Registra la restauración de un modelo con soft delete.
     */
    public static function registrarRestauracion(Model $model, string $modulo, string $descripcion): void
    {
        self::registrar(
            accion:      'restaurar',
            modulo:      $modulo,
            descripcion: $descripcion,
            entidadId:   $model->getKey(),
            datosNuevos: self::modelToArray($model),
        );
    }

    /**
     * Registra un error de sistema.
     */
    public static function registrarError(string $modulo, string $descripcion, ?\Throwable $e = null): void
    {
        $datosError = null;
        if ($e) {
            $datosError = [
                'excepcion' => get_class($e),
                'mensaje'   => $e->getMessage(),
                'archivo'   => $e->getFile(),
                'linea'     => $e->getLine(),
            ];
        }

        self::registrar(
            accion:      'error',
            modulo:      $modulo,
            descripcion: $descripcion,
            datosNuevos: $datosError,
            nivel:       'error',
        );
    }

    private static function modelToArray(Model $model): array
    {
        $data = $model->toArray();
        unset($data['password'], $data['remember_token']);
        return $data;
    }
}
