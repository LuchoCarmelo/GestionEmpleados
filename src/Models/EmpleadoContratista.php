<?php

class EmpleadoContratista extends Empleado
{
    private float $tarifaHora;

    public function __construct(?int $id, string $nombre, string $email, float $salarioBase, float $tarifaHora)
    {
        parent::__construct($id, $nombre, $email, $salarioBase);
        $this->tarifaHora = $tarifaHora;
    }

    public function obtenerTipo(): string
    {
        return 'contratista';
    }

    public function obtenerTarifaHora(): float 
    {
        return $this->tarifaHora;
    }
}