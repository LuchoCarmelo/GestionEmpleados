<?php

class FabricaCalculadoraSalario
{
    public function crearCalculadora(string $tipoEmpleado): InterfazCalculadoraSalario
    {
        switch ($tipoEmpleado) {
            case 'tiempo_completo':
                return new CalculadoraSalarioTiempoCompleto();
            case 'medio_tiempo':
                return new CalculadoraSalarioMedioTiempo();
            case 'contratista':
                return new CalculadoraSalarioContratista();
            default:
                throw new InvalidArgumentException("Tipo de empleado desconocido: " . $tipoEmpleado);
        }
    }
}