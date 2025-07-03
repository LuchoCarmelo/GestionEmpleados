<?php

class CalculadoraSalarioMedioTiempo implements InterfazCalculadoraSalario
{
    public function calcularSalario(InterfazEmpleado $empleado): float
    {
        if (!$empleado instanceof EmpleadoMedioTiempo) {
            throw new InvalidArgumentException('Se esperaba EmpleadoMedioTiempo');
        }
        
        return $empleado->obtenerSalarioBase() * $empleado->obtenerHorasTrabajadas();
    }
}