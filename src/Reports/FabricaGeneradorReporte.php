<?php

class FabricaGeneradorReporte
{
    private array $generadores = [];
    
    public function __construct()
    {
        $this->generadores = [
            'json' => new GeneradorReporteJson(),
            'csv' => new GeneradorReporteCsv(),
            'pdf' => new GeneradorReportePdf()
        ];
    }
    
    public function obtenerGenerador(string $formato): InterfazGeneradorReporte
    {
        if (!isset($this->generadores[$formato])) {
            throw new InvalidArgumentException("No se encontró generador para el formato: {$formato}");
        }
        
        return $this->generadores[$formato];
    }
    
    public function obtenerFormatosDisponibles(): array
    {
        return array_keys($this->generadores);
    }
}