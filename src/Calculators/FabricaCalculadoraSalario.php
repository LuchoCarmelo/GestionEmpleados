<?php

class FabricaCalculadoraSalario
{
    private array $calculadoras = [];
    
    public function __construct()
    {
        $this->calculadoras = [
            'tiempo_completo' => new CalculadoraSalarioTiempoCompleto(),
            'medio_tiempo' => new CalculadoraSalarioMedioTiempo(),
            'contratista' => new CalculadoraSalarioContratista()
        ];
    }
    
    public function obtenerCalculadora(string $tipoEmpleado): InterfazCalculadoraSalario
    {
        if (!isset($this->calculadoras[$tipoEmpleado])) {
            throw new InvalidArgumentException("No se encontró calculadora para el tipo de empleado: {$tipoEmpleado}");
        }
        
        return $this->calculadoras[$tipoEmpleado];
    }
}