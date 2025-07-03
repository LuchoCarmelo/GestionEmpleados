<?php

class CalculadoraSalarioMedioTiempo implements InterfazCalculadoraSalario
{
    public function calcularSalario(InterfazEmpleado $empleado): float
    {
        if (!$empleado instanceof EmpleadoMedioTiempo) {
            throw new InvalidArgumentException("Se esperaba una instancia de EmpleadoMedioTiempo.");
        }
        // Ajusta esta lógica según cómo calcules el salario de medio tiempo.
        // Si el salario base YA es el salario total por el medio tiempo, entonces:
        return $empleado->obtenerSalarioBase();
        // Si el salario se calcula por horas, y el salarioBase es solo una base:
        // return $empleado->obtenerSalarioBase() + ($empleado->obtenerHorasTrabajadas() * $tarifaPorHora);
        // Donde $tarifaPorHora es un valor que debes obtener o definir.
    }
}