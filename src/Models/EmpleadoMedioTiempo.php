<?php

class EmpleadoMedioTiempo extends Empleado
{
    private int $horasSemanales;

    public function __construct(?int $id, string $nombre, string $email, float $salarioBase, int $horasSemanales)
    {
        parent::__construct($id, $nombre, $email, $salarioBase);
        $this->horasSemanales = $horasSemanales;
    }

    public function obtenerTipo(): string
    {
        return 'medio_tiempo';
    }

    public function obtenerHorasSemanales(): int // Este es el método que se estaba quejando Intelephense
    {
        return $this->horasSemanales;
    }
}