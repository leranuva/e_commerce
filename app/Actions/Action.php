<?php

namespace App\Actions;

/**
 * Clase base abstracta para todas las Action Classes.
 * 
 * Las Action Classes encapsulan la lógica de negocio específica,
 * manteniendo los controladores delgados y el código organizado.
 * 
 * @package App\Actions
 */
abstract class Action
{
    /**
     * Ejecuta la acción.
     * 
     * @param mixed ...$arguments
     * @return mixed
     */
    abstract public function execute(...$arguments);

    /**
     * Método estático para ejecutar la acción directamente.
     * 
     * @param mixed ...$arguments
     * @return mixed
     */
    public static function run(...$arguments)
    {
        return (new static)->execute(...$arguments);
    }
}

