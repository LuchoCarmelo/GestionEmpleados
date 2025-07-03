<?php

interface InterfazCalculadoraSalario
{
    public function calcularSalario(InterfazEmpleado $empleado): float;
}