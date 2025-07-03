<?php

class FabricaEmpleado
{
    public function crearDesdeDatos(array $datos): InterfazEmpleado
    {
        if (!isset($datos['tipo_empleado'])) {
            throw new InvalidArgumentException("Los datos del empleado no contienen el tipo_empleado.");
        }

        switch ($datos['tipo_empleado']) {
            case 'tiempo_completo':
                return new EmpleadoTiempoCompleto(
                    (int)$datos['id'],
                    $datos['nombre'],
                    $datos['email'],
                    (float)$datos['salario_base'],
                    $datos['puesto'],
                    (float)($datos['bonificacion'] ?? 0.0)
                );
            case 'medio_tiempo':
                return new EmpleadoMedioTiempo(
                    (int)$datos['id'],
                    $datos['nombre'],
                    $datos['email'],
                    (float)$datos['salario_base'],
                    (int)$datos['horas_semanales']
                );
            case 'contratista':
                return new EmpleadoContratista(
                    (int)$datos['id'],
                    $datos['nombre'],
                    $datos['email'],
                    (float)$datos['salario_base'],
                    (float)$datos['tarifa_hora']
                );
            default:
                throw new Exception("Tipo de empleado desconocido: " . $datos['tipo_empleado']);
        }
    }
}