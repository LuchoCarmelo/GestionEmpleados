<?php

class EmpleadoMedioTiempo extends Empleado
{
    private int $horasTrabajadas;
    
    public function __construct(int $id, string $nombre, string $email, float $tarifaPorHora, int $horasTrabajadas)
    {
        parent::__construct($id, $nombre, $email, $tarifaPorHora);
        $this->horasTrabajadas = $horasTrabajadas;
    }
    
    public function obtenerTipo(): string
    {
        return 'medio_tiempo';
    }
    
    public function obtenerHorasTrabajadas(): int
    {
        return $this->horasTrabajadas;
    }
}