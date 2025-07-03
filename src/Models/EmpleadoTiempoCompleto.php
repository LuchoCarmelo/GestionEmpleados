<?php


class EmpleadoTiempoCompleto extends Empleado
{
    private string $puesto;

    public function __construct(?int $id, string $nombre, string $email, float $salarioBase, string $puesto)
    {
        parent::__construct($id, $nombre, $email, $salarioBase);
        $this->puesto = $puesto;
    }

    public function obtenerTipo(): string
    {
        return 'tiempo_completo';
    }

    public function obtenerPuesto(): string 
    {
        return $this->puesto;
    }
}