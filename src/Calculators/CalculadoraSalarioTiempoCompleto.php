<?php

class CalculadoraSalarioTiempoCompleto implements InterfazCalculadoraSalario
{
    public function calcularSalario(InterfazEmpleado $empleado): float
    {
        if (!$empleado instanceof EmpleadoTiempoCompleto) {
            throw new InvalidArgumentException("Se esperaba una instancia de EmpleadoTiempoCompleto.");
        }
        return $empleado->obtenerSalarioBase() + $empleado->obtenerBonificacion();
    }
}