<?php


class EmpleadoTiempoCompleto extends Empleado
{
    private string $puesto;
    private float $bonificacion;

    public function __construct(?int $id, string $nombre, string $email, float $salarioBase, string $puesto, float $bonificacion= 0.0)
    {
        parent::__construct($id, $nombre, $email, $salarioBase);
        $this->puesto = $puesto;
        $this->bonificacion = $bonificacion;
    }

    public function obtenerTipo(): string
    {
        return 'tiempo_completo';
    }

    public function obtenerBonificacion(): float
    {
        return $this->bonificacion;
    }

    public function obtenerPuesto(): string 
    {
        return $this->puesto;
    }

    public function establecerBonificacion(float $bonificacion): void
    {
        $this->bonificacion = $bonificacion;
    }
}