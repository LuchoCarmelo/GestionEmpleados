<?php

class ServicioGestionEmpleados
{
    private InterfazRepositorioEmpleado $repositorio;
    private GestorNotificaciones $gestorNotificaciones;
    private FabricaCalculadoraSalario $fabricaCalculadora; // Propiedad para la fábrica de calculadoras

    public function __construct(
        InterfazRepositorioEmpleado $repositorio,
        GestorNotificaciones $gestorNotificaciones
    ) {
        $this->repositorio = $repositorio;
        $this->gestorNotificaciones = $gestorNotificaciones;
        $this->fabricaCalculadora = new FabricaCalculadoraSalario(); // Inicializa la fábrica aquí
    }

    public function agregarEmpleado(InterfazEmpleado $empleado): bool
    {
        $exito = $this->repositorio->guardar($empleado);
        if ($exito) {
            $mensaje = "Nuevo empleado registrado: " . $empleado->obtenerNombre();
            $this->gestorNotificaciones->enviarNotificaciones($empleado->obtenerEmail(), $mensaje);
            echo "✅ " . $mensaje . "\n";
        } else {
            echo "❌ Error al agregar empleado: " . $empleado->obtenerNombre() . "\n";
        }
        return $exito;
    }

    // Método para obtener todos los empleados, solicitado por el error de 'undefined method'
    public function obtenerTodosLosEmpleados(): array
    {
        return $this->repositorio->buscarTodos();
    }

    public function buscarEmpleadoPorId(int $id): ?InterfazEmpleado
    {
        return $this->repositorio->buscarPorId($id);
    }

    public function actualizarEmpleado(InterfazEmpleado $empleado): bool
    {
        $exito = $this->repositorio->actualizar($empleado);
        if ($exito) {
            $mensaje = "Empleado actualizado: " . $empleado->obtenerNombre();
            $this->gestorNotificaciones->enviarNotificaciones($empleado->obtenerEmail(), $mensaje);
            echo "✅ " . $mensaje . "\n";
        } else {
            echo "❌ Error al actualizar empleado: " . $empleado->obtenerNombre() . "\n";
        }
        return $exito;
    }

    public function eliminarEmpleado(int $id): bool
    {
        $empleado = $this->repositorio->buscarPorId($id);
        if (!$empleado) {
            echo "❌ No se encontró el empleado con ID: " . $id . "\n";
            return false;
        }

        $exito = $this->repositorio->eliminar($id);
        if ($exito) {
            $mensaje = "Empleado eliminado: " . $empleado->obtenerNombre();
            // Podrías enviar una notificación al admin o loggear
            echo "✅ " . $mensaje . "\n";
        } else {
            echo "❌ Error al eliminar empleado: " . $empleado->obtenerNombre() . "\n";
        }
        return $exito;
    }

    public function procesarNomina(): void
    {
        echo "\n--- Procesando Nómina ---\n";
        $empleados = $this->repositorio->buscarTodos();
        foreach ($empleados as $empleado) {
            try {
                $calculadora = $this->fabricaCalculadora->crearCalculadora($empleado->obtenerTipo());
                $salario = $calculadora->calcularSalario($empleado);
                // Aquí se corrigió la sintaxis de la variable $salario
                echo "✅ {$empleado->obtenerNombre()} ({$empleado->obtenerTipo()}): Salario Calculado = $" . number_format($salario, 2) . "\n";
                // En una aplicación real, este salario se registraría en algún lugar
            } catch (InvalidArgumentException $e) {
                echo "❌ Error al calcular salario para {$empleado->obtenerNombre()}: " . $e->getMessage() . "\n";
            } catch (Exception $e) {
                echo "❌ Error inesperado para {$empleado->obtenerNombre()}: " . $e->getMessage() . "\n";
            }
        }
        echo "--- Nómina Procesada ---\n";
    }

    public function obtenerResumenSistema(): array
    {
        
        $empleados = $this->repositorio->buscarTodos();
        $totalEmpleados = count($empleados);
        $nominaTotal = 0.0;
        $salarioPromedio = 0.0;
        $salarioMinimo = PHP_FLOAT_MAX;
        $salarioMaximo = PHP_FLOAT_MIN;
        $tiposEmpleadoContador = []; // Para contar cuántos de cada tipo
        $salarioPorTipo = []; // Para sumar salarios por tipo
        $detallePorTipo = [];

        foreach ($empleados as $empleado) {
            $calculadora = $this->fabricaCalculadora->crearCalculadora($empleado->obtenerTipo());
            $salario = $calculadora->calcularSalario($empleado);

            $nominaTotal += $salario;
            $salarioMinimo = min($salarioMinimo, $salario);
            $salarioMaximo = max($salarioMaximo, $salario);

            $tipo = $empleado->obtenerTipo();
            $tiposEmpleadoContador[$tipo] = ($tiposEmpleadoContador[$tipo] ?? 0) + 1;
            $salarioPorTipo[$tipo] = ($salarioPorTipo[$tipo] ?? 0.0) + $salario;
        }

        if ($totalEmpleados > 0) {
            $salarioPromedio = $nominaTotal / $totalEmpleados;
        } else {
            $salarioMinimo = 0.0; // No hay empleados, min es 0
            $salarioMaximo = 0.0; // No hay empleados, max es 0
        }

        foreach ($tiposEmpleadoContador as $tipo => $cantidad) {
            $promedioTipo = $cantidad > 0 ? $salarioPorTipo[$tipo] / $cantidad : 0.0;
            $detallePorTipo[$tipo] = [
                'cantidad' => $cantidad,
                'promedio' => $promedioTipo
            ];
        };


        return [
            'totalEmpleados' => $totalEmpleados,
            'nominaTotal' => $nominaTotal,
            'salarioPromedio' => $salarioPromedio,
            'salarioMinimo' => $salarioMinimo,
            'salarioMaximo' => $salarioMaximo,
            'tiposEmpleadoConteo' => $tiposEmpleadoContador,
            'detallePorTipo' => $detallePorTipo
        ];
    }
}