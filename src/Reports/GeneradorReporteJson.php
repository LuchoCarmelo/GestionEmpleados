<?php

class GeneradorReporteJson implements InterfazGeneradorReporte
{
    public function generarReporte(array $empleados): string
    {
        $datos = [];
        $fabricaCalculadora = new FabricaCalculadoraSalario();
        
        foreach ($empleados as $empleado) {
            $calculadora = $fabricaCalculadora->obtenerCalculadora($empleado->obtenerTipo());
            $salario = $calculadora->calcularSalario($empleado);
            
            $datos[] = [
                'id' => $empleado->obtenerId(),
                'nombre' => $empleado->obtenerNombre(),
                'email' => $empleado->obtenerEmail(),
                'tipo' => $empleado->obtenerTipo(),
                'salario' => $salario
            ];
        }
        
        return json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}