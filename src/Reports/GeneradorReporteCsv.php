<?php

class GeneradorReporteCsv implements InterfazGeneradorReporte
{
    public function generarReporte(array $empleados): string
    {
        $fabricaCalculadora = new FabricaCalculadoraSalario();
        $csv = "ID,Nombre,Email,Tipo,Salario\n";
        
        foreach ($empleados as $empleado) {
            $calculadora = $fabricaCalculadora->obtenerCalculadora($empleado->obtenerTipo());
            $salario = $calculadora->calcularSalario($empleado);
            
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f\n",
                $empleado->obtenerId(),
                $empleado->obtenerNombre(),
                $empleado->obtenerEmail(),
                $empleado->obtenerTipo(),
                $salario
            );
        }
        
        return $csv;
    }
}
