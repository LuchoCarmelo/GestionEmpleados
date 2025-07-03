<?php

class EmpleadoTiempoCompleto extends Empleado
{
    private float $bonificacion;
    
    public function __construct(int $id, string $nombre, string $email, float $salarioBase, float $bonificacion = 0)
    {
        parent::__construct($id, $nombre, $email, $salarioBase);
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
}