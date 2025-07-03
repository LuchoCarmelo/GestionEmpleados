<?php

class EmpleadoContratista extends Empleado
{
    private int $proyectosCompletados;
    private float $tarifaPorProyecto;
    
    public function __construct(int $id, string $nombre, string $email, float $tarifaPorProyecto, int $proyectosCompletados)
    {
        parent::__construct($id, $nombre, $email, $tarifaPorProyecto);
        $this->proyectosCompletados = $proyectosCompletados;
        $this->tarifaPorProyecto = $tarifaPorProyecto;
    }
    
    public function obtenerTipo(): string
    {
        return 'contratista';
    }
    
    public function obtenerProyectosCompletados(): int
    {
        return $this->proyectosCompletados;
    }
    
    public function obtenerTarifaPorProyecto(): float
    {
        return $this->tarifaPorProyecto;
    }
}