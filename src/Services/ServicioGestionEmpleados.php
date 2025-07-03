<?php

class ServicioGestionEmpleados
{
    private InterfazRepositorioEmpleado $repositorio;
    private GestorNotificaciones $gestorNotificaciones;
    private bool $modoDebug = false;
    
    public function __construct(
        InterfazRepositorioEmpleado $repositorio,
        GestorNotificaciones $gestorNotificaciones
    ) {
        $this->repositorio = $repositorio;
        $this->gestorNotificaciones = $gestorNotificaciones;
        
        if ($this->modoDebug) {
            echo "🚀 Sistema de Gestión de Empleados iniciado\n";
            echo "===========================================\n";
        }
    }
    
    /**
     * Agregar un empleado al sistema
     */
    public function agregarEmpleado(InterfazEmpleado $empleado): bool
    {
        try {
            $resultado = $this->repositorio->guardar($empleado);
            
            if ($resultado && $this->modoDebug) {
                echo "👤 Nuevo empleado registrado: " . $empleado->obtenerNombre() . "\n";
            }
            
            return $resultado;
        } catch (Exception $excepcion) {
            echo "❌ Error al agregar empleado: " . $excepcion->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Obtener un empleado por ID
     */
    public function obtenerEmpleado(int $id): ?InterfazEmpleado
    {
        try {
            return $this->repositorio->buscarPorId($id);
        } catch (Exception $excepcion) {
            echo "❌ Error al buscar empleado: " . $excepcion->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Obtener todos los empleados
     */
    public function obtenerTodosLosEmpleados(): array
    {
        try {
            return $this->repositorio->buscarTodos();
        } catch (Exception $excepcion) {
            echo "❌ Error al obtener empleados: " . $excepcion->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Actualizar datos de un empleado
     */
    public function actualizarEmpleado(InterfazEmpleado $empleado): bool
    {
        try {
            $resultado = $this->repositorio->actualizar($empleado);
            
            if ($resultado && $this->modoDebug) {
                echo "✏️ Datos actualizados para: " . $empleado->obtenerNombre() . "\n";
            }
            
            return $resultado;
        } catch (Exception $excepcion) {
            echo "❌ Error al actualizar empleado: " . $excepcion->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Eliminar un empleado
     */
    public function eliminarEmpleado(int $id): bool
    {
        try {
            // Obtener nombre antes de eliminar para el log
            $empleado = $this->repositorio->buscarPorId($id);
            $nombreEmpleado = $empleado ? $empleado->obtenerNombre() : "ID {$id}";
            
            $resultado = $this->repositorio->eliminar($id);
            
            if ($resultado && $this->modoDebug) {
                echo "🗑️ Empleado eliminado del sistema: {$nombreEmpleado}\n";
            }
            
            return $resultado;
        } catch (Exception $excepcion) {
            echo "❌ Error al eliminar empleado: " . $excepcion->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Procesar nómina de todos los empleados
     */
    public function procesarNomina(): array
    {
        echo "\n💰 INICIANDO PROCESAMIENTO DE NÓMINA\n";
        echo "=====================================\n";
        
        $empleados = $this->repositorio->buscarTodos();
        $fabricaCalculadora = new FabricaCalculadoraSalario();
        $resultados = [];
        $totalNomina = 0;
        
        if (empty($empleados)) {
            echo "⚠️ No hay empleados registrados para procesar\n";
            return $resultados;
        }
        
        foreach ($empleados as $empleado) {
            try {
                $calculadora = $fabricaCalculadora->obtenerCalculadora($empleado->obtenerTipo());
                $salario = $calculadora->calcularSalario($empleado);
                $totalNomina += $salario;
                
                $mensaje = "Estimado/a " . $empleado->obtenerNombre() . ", su salario de $" . 
                          number_format($salario, 2) . " ha sido procesado exitosamente.";
                
                $this->gestorNotificaciones->enviarNotificaciones($empleado->obtenerEmail(), $mensaje);
                
                $resultados[] = [
                    'empleado_id' => $empleado->obtenerId(),
                    'empleado_nombre' => $empleado->obtenerNombre(),
                    'empleado_tipo' => $empleado->obtenerTipo(),
                    'salario_calculado' => $salario,
                    'estado' => 'procesado_exitosamente',
                    'fecha_procesamiento' => date('Y-m-d H:i:s')
                ];
                
                echo "✅ {$empleado->obtenerNombre()} - {$salario}\n";
                
            } catch (Exception $excepcion) {
                $resultados[] = [
                    'empleado_id' => $empleado->obtenerId(),
                    'empleado_nombre' => $empleado->obtenerNombre(),
                    'empleado_tipo' => $empleado->obtenerTipo(),
                    'salario_calculado' => 0,
                    'estado' => 'error: ' . $excepcion->getMessage(),
                    'fecha_procesamiento' => date('Y-m-d H:i:s')
                ];
                
                echo "❌ Error procesando: " . $empleado->obtenerNombre() . " - " . $excepcion->getMessage() . "\n";
            }
        }
        
        echo "=====================================\n";
        echo "💰 Total de nómina procesada: $" . number_format($totalNomina, 2) . "\n";
        echo "👥 Empleados procesados: " . count($empleados) . "\n\n";
        
        return $resultados;
    }
    
    /**
     * Calcular salario de un empleado específico
     */
    public function calcularSalarioEmpleado(int $idEmpleado): ?float
    {
        $empleado = $this->repositorio->buscarPorId($idEmpleado);
        
        if (!$empleado) {
            echo "❌ Empleado con ID {$idEmpleado} no encontrado\n";
            return null;
        }
        
        try {
            $fabricaCalculadora = new FabricaCalculadoraSalario();
            $calculadora = $fabricaCalculadora->obtenerCalculadora($empleado->obtenerTipo());
            $salario = $calculadora->calcularSalario($empleado);
            
            if ($this->modoDebug) {
                echo "💵 Salario calculado para " . $empleado->obtenerNombre() . ": $" . number_format($salario, 2) . "\n";
            }
            
            return $salario;
        } catch (Exception $excepcion) {
            echo "❌ Error al calcular salario: " . $excepcion->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Generar reporte en formato específico
     */
    public function generarReporte(string $formato): string
    {
        try {
            echo "📊 Generando reporte en formato: {$formato}\n";
            
            $empleados = $this->repositorio->buscarTodos();
            
            if (empty($empleados)) {
                return "No hay empleados registrados para generar el reporte.";
            }
            
            $fabricaReporte = new FabricaGeneradorReporte();
            $generador = $fabricaReporte->obtenerGenerador($formato);
            
            $contenidoReporte = $generador->generarReporte($empleados);
            
            echo "✅ Reporte generado exitosamente ({$formato})\n";
            echo "📄 Empleados incluidos: " . count($empleados) . "\n";
            
            return $contenidoReporte;
        } catch (Exception $excepcion) {
            $mensajeError = "Error al generar reporte: " . $excepcion->getMessage();
            echo "❌ {$mensajeError}\n";
            return $mensajeError;
        }
    }
    
    /**
     * Guardar reporte en archivo
     */
    public function guardarReporteEnArchivo(string $formato, string $nombreArchivo): bool
    {
        try {
            $contenidoReporte = $this->generarReporte($formato);
            
            $extension = match($formato) {
                'json' => '.json',
                'csv' => '.csv',
                'pdf' => '.html',
                default => '.txt'
            };
            
            $nombreCompletoArchivo = $nombreArchivo . $extension;
            $rutaArchivo = './reportes/' . $nombreCompletoArchivo;
            
            // Crear directorio si no existe
            if (!is_dir('./reportes/')) {
                mkdir('./reportes/', 0755, true);
            }
            
            $resultado = file_put_contents($rutaArchivo, $contenidoReporte) !== false;
            
            if ($resultado) {
                echo "💾 Reporte guardado en: {$rutaArchivo}\n";
            } else {
                echo "❌ Error al guardar reporte en archivo\n";
            }
            
            return $resultado;
        } catch (Exception $excepcion) {
            echo "❌ Error al guardar reporte: " . $excepcion->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Obtener estadísticas del sistema
     */
    public function obtenerEstadisticas(): array
    {
        
        $empleados = $this->repositorio->buscarTodos();
        $fabricaCalculadora = new FabricaCalculadoraSalario();
        
        $estadisticas = [
            'total_empleados' => count($empleados),
            'por_tipo' => [],
            'nomina_total' => 0,
            'salario_promedio' => 0,
            'salario_minimo' => 0,
            'salario_maximo' => 0,
            'fecha_calculo' => date('Y-m-d H:i:s')
        ];
        
        if (empty($empleados)) {
            echo "📊 No hay empleados para calcular estadísticas\n";
            return $estadisticas;
        }
        
        $salarios = [];
        $totalSalario = 0;
        
        foreach ($empleados as $empleado) {
            $tipoEmpleado = $empleado->obtenerTipo();
            
            if (!isset($estadisticas['por_tipo'][$tipoEmpleado])) {
                $estadisticas['por_tipo'][$tipoEmpleado] = [
                    'cantidad' => 0, 
                    'nomina_total' => 0,
                    'salario_promedio' => 0
                ];
            }
            
            $estadisticas['por_tipo'][$tipoEmpleado]['cantidad']++;
            
            try {
                $calculadora = $fabricaCalculadora->obtenerCalculadora($tipoEmpleado);
                $salario = $calculadora->calcularSalario($empleado);
                $estadisticas['por_tipo'][$tipoEmpleado]['nomina_total'] += $salario;
                $salarios[] = $salario;
                $totalSalario += $salario;
            } catch (Exception $excepcion) {
                echo "⚠️ Error calculando salario para " . $empleado->obtenerNombre() . ": " . $excepcion->getMessage() . "\n";
            }
        }
        
        // Calcular promedios por tipo
        foreach ($estadisticas['por_tipo'] as $tipo => &$datos) {
            if ($datos['cantidad'] > 0) {
                $datos['salario_promedio'] = $datos['nomina_total'] / $datos['cantidad'];
            }
        }
        
        // Estadísticas generales
        $estadisticas['nomina_total'] = $totalSalario;
        
        if (!empty($salarios)) {
            $estadisticas['salario_promedio'] = $totalSalario / count($salarios);
            $estadisticas['salario_minimo'] = min($salarios);
            $estadisticas['salario_maximo'] = max($salarios);
        }
        
        return $estadisticas;
    }
    
    /**
     * Buscar empleados por tipo
     */
    public function buscarEmpleadosPorTipo(string $tipo): array
    {
        $todosLosEmpleados = $this->repositorio->buscarTodos();
        $empleadosFiltrados = [];
        
        foreach ($todosLosEmpleados as $empleado) {
            if ($empleado->obtenerTipo() === $tipo) {
                $empleadosFiltrados[] = $empleado;
            }
        }
        
        echo "🔍 Empleados encontrados del tipo '{$tipo}': " . count($empleadosFiltrados) . "\n";
        return $empleadosFiltrados;
    }
    
    /**
     * Validar integridad de datos
     */
    public function validarIntegridadDatos(): array
    {
        echo "🔍 Validando integridad de datos del sistema...\n";
        
        $empleados = $this->repositorio->buscarTodos();
        $errores = [];
        $advertencias = [];
        
        foreach ($empleados as $empleado) {
            $idEmpleado = $empleado->obtenerId();
            $nombreEmpleado = $empleado->obtenerNombre();
            
            // Validar datos básicos
            if (empty($nombreEmpleado)) {
                $errores[] = "Empleado ID {$idEmpleado}: Nombre vacío";
            }
            
            if (empty($empleado->obtenerEmail()) || !filter_var($empleado->obtenerEmail(), FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Empleado ID {$idEmpleado}: Email inválido";
            }
            
            if ($empleado->obtenerSalarioBase() <= 0) {
                $errores[] = "Empleado ID {$idEmpleado}: Salario base inválido";
            }
            
            // Validar cálculo de salario
            try {
                $salario = $this->calcularSalarioEmpleado($idEmpleado);
                if ($salario === null || $salario < 0) {
                    $advertencias[] = "Empleado ID {$idEmpleado}: Salario calculado sospechoso";
                }
            } catch (Exception $excepcion) {
                $errores[] = "Empleado ID {$idEmpleado}: Error en cálculo de salario - " . $excepcion->getMessage();
            }
        }
        
        $resultado = [
            'empleados_validados' => count($empleados),
            'errores' => $errores,
            'advertencias' => $advertencias,
            'estado' => empty($errores) ? 'OK' : 'CON_ERRORES',
            'fecha_validacion' => date('Y-m-d H:i:s')
        ];
        
        // Mostrar resultados
        echo "✅ Empleados validados: " . count($empleados) . "\n";
        echo "❌ Errores encontrados: " . count($errores) . "\n";
        echo "⚠️ Advertencias: " . count($advertencias) . "\n";
        
        if (!empty($errores)) {
            echo "\nERRORES ENCONTRADOS:\n";
            foreach ($errores as $error) {
                echo "❌ {$error}\n";
            }
        }
        
        if (!empty($advertencias)) {
            echo "\nADVERTENCIAS:\n";
            foreach ($advertencias as $advertencia) {
                echo "⚠️ {$advertencia}\n";
            }
        }
        
        return $resultado;
    }
    
    /**
     * Configurar modo debug
     */
    public function configurarModoDebug(bool $activar): void
    {
        $this->modoDebug = $activar;
        echo ($activar ? "🔧 Modo debug activado" : "🔇 Modo debug desactivado") . "\n";
    }
    
    /**
     * Obtener resumen del sistema
     */
    public function obtenerResumenSistema(): array
    {
        $estadisticas = $this->obtenerEstadisticas();
        $validacion = $this->validarIntegridadDatos();
        
        return [
            'version_sistema' => '1.0.0',
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'estadisticas' => $estadisticas,
            'validacion' => $validacion,
            'estado_general' => $validacion['estado'],
            'formatos_reporte_disponibles' => ['json', 'csv', 'pdf']
        ];
    }
}