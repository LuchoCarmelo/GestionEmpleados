<?php

class CalculadoraSalarioContratista implements InterfazCalculadoraSalario
{
    public function calcularSalario(InterfazEmpleado $empleado): float
    {
        if (!$empleado instanceof EmpleadoContratista) {
            throw new InvalidArgumentException('Se esperaba EmpleadoContratista');
        }
        
        return $empleado->obtenerSalarioBase() * $empleado->obtenerTarifaHora();
    }
}
