<?php

class GeneradorReportePdf implements InterfazGeneradorReporte
{
    public function generarReporte(array $empleados): string
    {
        $fabricaCalculadora = new FabricaCalculadoraSalario();
        $pdf = "<html><head><meta charset='UTF-8'></head><body><h1>Reporte de Empleados</h1><table border='1' style='border-collapse: collapse; width: 100%;'>";
        $pdf .= "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Nombre</th><th>Email</th><th>Tipo</th><th>Salario</th></tr>";
        
        foreach ($empleados as $empleado) {
            $calculadora = $fabricaCalculadora->obtenerCalculadora($empleado->obtenerTipo());
            $salario = $calculadora->calcularSalario($empleado);
            
            $pdf .= sprintf(
                "<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>$%.2f</td></tr>",
                $empleado->obtenerId(),
                htmlspecialchars($empleado->obtenerNombre()),
                htmlspecialchars($empleado->obtenerEmail()),
                $empleado->obtenerTipo(),
                $salario
            );
        }
        
        $pdf .= "</table></body></html>";
        return $pdf;
    }
}