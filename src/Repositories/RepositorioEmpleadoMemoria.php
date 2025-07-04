<?php

class RepositorioEmpleadoMemoria implements InterfazRepositorioEmpleado
{
    private array $empleados = [];
    private int $siguienteId = 1;
    private bool $modoDebug = false;
    
    public function __construct(bool $debug = false)
    {
        $this->modoDebug = $debug;
        if ($this->modoDebug) {
            echo "🗄️ Repositorio en memoria inicializado\n";
        }
    }
    
    /**
     * Guardar un empleado en memoria
     */
    public function guardar(InterfazEmpleado $empleado): bool
    {
        try {
            $id = $empleado->obtenerId();
            
            // Verificar si ya existe un empleado con este ID
            if (isset($this->empleados[$id])) {
                if ($this->modoDebug) {
                    echo "⚠️ Empleado con ID {$id} ya existe, se sobrescribirá\n";
                }
            }
            
            // Verificar si ya existe un empleado con este email
            foreach ($this->empleados as $empExistente) {
                if ($empExistente->obtenerEmail() === $empleado->obtenerEmail() && 
                    $empExistente->obtenerId() !== $id) {
                    throw new Exception("Ya existe un empleado con el email: " . $empleado->obtenerEmail());
                }
            }
            
            $this->empleados[$id] = $empleado;
            
            // Actualizar siguiente ID si es necesario
            if ($id >= $this->siguienteId) {
                $this->siguienteId = $id + 1;
            }
            
            if ($this->modoDebug) {
                echo "✅ Empleado guardado: " . $empleado->obtenerNombre() . " (ID: {$id})\n";
            }
            
            return true;
            
        } catch (Exception $excepcion) {
            if ($this->modoDebug) {
                echo "❌ Error al guardar empleado: " . $excepcion->getMessage() . "\n";
            }
            return false;
        }
    }
    
    /**
     * Buscar empleado por ID
     */
    public function buscarPorId(int $id): ?InterfazEmpleado
    {
        $empleadoEncontrado = $this->empleados[$id] ?? null;
        
        if ($this->modoDebug) {
            if ($empleadoEncontrado) {
                echo "🔍 Empleado encontrado: " . $empleadoEncontrado->obtenerNombre() . "\n";
            } else {
                echo "❌ No se encontró empleado con ID: {$id}\n";
            }
        }
        
        return $empleadoEncontrado;
    }
    
    /**
     * Buscar todos los empleados
     */
    public function buscarTodos(): array
    {
        $listaEmpleados = array_values($this->empleados);
        
        if ($this->modoDebug) {
            echo "📋 Total de empleados encontrados: " . count($listaEmpleados) . "\n";
        }
        
        return $listaEmpleados;
    }
    
    /**
     * Actualizar un empleado existente
     */
    public function actualizar(InterfazEmpleado $empleado): bool
    {
        $id = $empleado->obtenerId();
        
        if (!isset($this->empleados[$id])) {
            if ($this->modoDebug) {
                echo "❌ No se puede actualizar: empleado con ID {$id} no existe\n";
            }
            return false;
        }
        
        try {
            // Verificar si el nuevo email ya está en uso por otro empleado
            foreach ($this->empleados as $empExistente) {
                if ($empExistente->obtenerEmail() === $empleado->obtenerEmail() && 
                    $empExistente->obtenerId() !== $id) {
                    throw new Exception("El email ya está siendo usado por otro empleado");
                }
            }
            
            $this->empleados[$id] = $empleado;
            
            if ($this->modoDebug) {
                echo "✏️ Empleado actualizado: " . $empleado->obtenerNombre() . "\n";
            }
            
            return true;
            
        } catch (Exception $excepcion) {
            if ($this->modoDebug) {
                echo "❌ Error al actualizar empleado: " . $excepcion->getMessage() . "\n";
            }
            return false;
        }
    }
    
    /**
     * Eliminar un empleado
     */
    public function eliminar(int $id): bool
    {
        if (!isset($this->empleados[$id])) {
            if ($this->modoDebug) {
                echo "❌ No se puede eliminar: empleado con ID {$id} no existe\n";
            }
            return false;
        }
        
        $nombreEmpleado = $this->empleados[$id]->obtenerNombre();
        unset($this->empleados[$id]);
        
        if ($this->modoDebug) {
            echo "🗑️ Empleado eliminado: {$nombreEmpleado} (ID: {$id})\n";
        }
        
        return true;
    }
    
    /**
     * Buscar empleados por tipo
     */
    public function buscarPorTipo(string $tipo): array
    {
        $empleadosFiltrados = [];
        
        foreach ($this->empleados as $empleado) {
            if ($empleado->obtenerTipo() === $tipo) {
                $empleadosFiltrados[] = $empleado;
            }
        }
        
        if ($this->modoDebug) {
            echo "🔍 Empleados del tipo '{$tipo}' encontrados: " . count($empleadosFiltrados) . "\n";
        }
        
        return $empleadosFiltrados;
    }
    
    /**
     * Buscar empleado por email
     */
    public function buscarPorEmail(string $email): ?InterfazEmpleado
    {
        foreach ($this->empleados as $empleado) {
            if ($empleado->obtenerEmail() === $email) {
                if ($this->modoDebug) {
                    echo "🔍 Empleado encontrado por email: " . $empleado->obtenerNombre() . "\n";
                }
                return $empleado;
            }
        }
        
        if ($this->modoDebug) {
            echo "❌ No se encontró empleado con email: {$email}\n";
        }
        
        return null;
    }
    
    /**
     * Buscar empleados por nombre (búsqueda parcial)
     */
    public function buscarPorNombre(string $nombre): array
    {
        $empleadosEncontrados = [];
        $nombreBusqueda = strtolower($nombre);
        
        foreach ($this->empleados as $empleado) {
            if (str_contains(strtolower($empleado->obtenerNombre()), $nombreBusqueda)) {
                $empleadosEncontrados[] = $empleado;
            }
        }
        
        if ($this->modoDebug) {
            echo "🔍 Empleados encontrados con nombre '{$nombre}': " . count($empleadosEncontrados) . "\n";
        }
        
        return $empleadosEncontrados;
    }
    
    /**
     * Contar total de empleados
     */
    public function contarEmpleados(): int
    {
        return count($this->empleados);
    }
    
    /**
     * Contar empleados por tipo
     */
    public function contarPorTipo(): array
    {
        $conteos = [];
        
        foreach ($this->empleados as $empleado) {
            $tipo = $empleado->obtenerTipo();
            $conteos[$tipo] = ($conteos[$tipo] ?? 0) + 1;
        }
        
        return $conteos;
    }
    
    /**
     * Obtener empleados con salario base mayor a un monto
     */
    public function buscarPorSalarioMinimo(float $salarioMinimo): array
    {
        $empleadosFiltrados = [];
        
        foreach ($this->empleados as $empleado) {
            if ($empleado->obtenerSalarioBase() >= $salarioMinimo) {
                $empleadosFiltrados[] = $empleado;
            }
        }
        
        if ($this->modoDebug) {
            echo "🔍 Empleados con salario >= $" . number_format($salarioMinimo, 2) . ": " . count($empleadosFiltrados) . "\n";
        }
        
        return $empleadosFiltrados;
    }
    
    /**
     * Obtener empleados ordenados por salario base
     */
    public function obtenerOrdenadosPorSalario(bool $descendente = true): array
    {
        $empleados = $this->buscarTodos();
        
        usort($empleados, function($a, $b) use ($descendente) {
            $comparacion = $a->obtenerSalarioBase() <=> $b->obtenerSalarioBase();
            return $descendente ? -$comparacion : $comparacion;
        });
        
        return $empleados;
    }
    
    /**
     * Obtener empleados ordenados por nombre
     */
    public function obtenerOrdenadosPorNombre(bool $ascendente = true): array
    {
        $empleados = $this->buscarTodos();
        
        usort($empleados, function($a, $b) use ($ascendente) {
            $comparacion = strcasecmp($a->obtenerNombre(), $b->obtenerNombre());
            return $ascendente ? $comparacion : -$comparacion;
        });
        
        return $empleados;
    }
    
    /**
     * Verificar si existe un empleado con el ID dado
     */
    public function existe(int $id): bool
    {
        return isset($this->empleados[$id]);
    }
    
    /**
     * Verificar si existe un empleado con el email dado
     */
    public function existeEmail(string $email): bool
    {
        foreach ($this->empleados as $empleado) {
            if ($empleado->obtenerEmail() === $email) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Obtener el próximo ID disponible
     */
    public function obtenerSiguienteId(): int
    {
        return $this->siguienteId;
    }
    
    /**
     * Limpiar todos los empleados (usar con cuidado)
     */
    public function limpiarTodos(): void
    {
        $totalEliminados = count($this->empleados);
        $this->empleados = [];
        $this->siguienteId = 1;
        
        if ($this->modoDebug) {
            echo "🧹 Se eliminaron {$totalEliminados} empleados del repositorio\n";
        }
    }
    
    /**
     * Obtener estadísticas del repositorio
     */
    public function obtenerEstadisticas(): array
    {
        $empleados = $this->buscarTodos();
        
        if (empty($empleados)) {
            return [
                'total_empleados' => 0,
                'por_tipo' => [],
                'salario_promedio' => 0,
                'salario_minimo' => 0,
                'salario_maximo' => 0
            ];
        }
        
        $salarios = array_map(fn($emp) => $emp->obtenerSalarioBase(), $empleados);
        
        return [
            'total_empleados' => count($empleados),
            'por_tipo' => $this->contarPorTipo(),
            'salario_promedio' => array_sum($salarios) / count($salarios),
            'salario_minimo' => min($salarios),
            'salario_maximo' => max($salarios),
            'emails_unicos' => count(array_unique(array_map(fn($emp) => $emp->obtenerEmail(), $empleados))),
            'ids_utilizados' => array_keys($this->empleados),
            'siguiente_id_disponible' => $this->siguienteId
        ];
    }
    
    /**
     * Exportar todos los empleados como array
     */
    public function exportarDatos(): array
    {
        $datos = [];
        
        foreach ($this->empleados as $empleado) {
            $datosEmpleado = [
                'id' => $empleado->obtenerId(),
                'nombre' => $empleado->obtenerNombre(),
                'email' => $empleado->obtenerEmail(),
                'tipo' => $empleado->obtenerTipo(),
                'salario_base' => $empleado->obtenerSalarioBase()
            ];
            
            // Agregar datos específicos según el tipo
            if ($empleado instanceof EmpleadoTiempoCompleto) {
                $datosEmpleado['bonificacion'] = $empleado->obtenerBonificacion();
            } elseif ($empleado instanceof EmpleadoMedioTiempo) {
                $datosEmpleado['horas_trabajadas'] = $empleado->obtenerHorasSemanales();
            } elseif ($empleado instanceof EmpleadoContratista) {
                $datosEmpleado['proyectos_completados'] = $empleado->obtenerSalarioBase();
                $datosEmpleado['tarifa_por_proyecto'] = $empleado->obtenerTarifaHora();
            }
            
            $datos[] = $datosEmpleado;
        }
        
        return $datos;
    }
    
    /**
     * Importar empleados desde array de datos
     */
    public function importarDatos(array $datosEmpleados): int
    {
        $importados = 0;
        
        foreach ($datosEmpleados as $datos) {
            try {
                $empleado = $this->crearEmpleadoDesdeDatos($datos);
                if ($this->guardar($empleado)) {
                    $importados++;
                }
            } catch (Exception $excepcion) {
                if ($this->modoDebug) {
                    echo "⚠️ Error importando empleado: " . $excepcion->getMessage() . "\n";
                }
            }
        }
        
        if ($this->modoDebug) {
            echo "📥 Empleados importados: {$importados} de " . count($datosEmpleados) . "\n";
        }
        
        return $importados;
    }
    
    /**
     * Crear instancia de empleado desde array de datos
     */
    private function crearEmpleadoDesdeDatos(array $datos): InterfazEmpleado
    {
        if (!isset($datos['id'], $datos['nombre'], $datos['email'], $datos['tipo'], $datos['salario_base'])) {
            throw new InvalidArgumentException("Datos de empleado incompletos");
        }
        
        switch ($datos['tipo']) {
            case 'tiempo_completo':
                return new EmpleadoTiempoCompleto(
                    $datos['id'],
                    $datos['nombre'],
                    $datos['email'],
                    $datos['salario_base'],
                    $datos['bonificacion'] ?? 0
                );
                
            case 'medio_tiempo':
                return new EmpleadoMedioTiempo(
                    $datos['id'],
                    $datos['nombre'],
                    $datos['email'],
                    $datos['salario_base'],
                    $datos['horas_trabajadas'] ?? 0
                );
                
            case 'contratista':
                return new EmpleadoContratista(
                    $datos['id'],
                    $datos['nombre'],
                    $datos['email'],
                    $datos['tarifa_por_proyecto'] ?? $datos['salario_base'],
                    $datos['proyectos_completados'] ?? 0
                );
                
            default:
                throw new InvalidArgumentException("Tipo de empleado desconocido: " . $datos['tipo']);
        }
    }
    
    /**
     * Configurar modo debug
     */
    public function configurarModoDebug(bool $activar): void
    {
        $this->modoDebug = $activar;
        if ($activar) {
            echo "🔧 Modo debug del repositorio activado\n";
        } else {
            echo "🔇 Modo debug del repositorio desactivado\n";
        }
    }
    
    /**
     * Validar integridad de los datos
     */
    public function validarIntegridad(): array
    {
        $errores = [];
        $advertencias = [];
        $emails = [];
        $ids = [];
        
        foreach ($this->empleados as $empleado) {
            $id = $empleado->obtenerId();
            $email = $empleado->obtenerEmail();
            
            // Verificar IDs duplicados
            if (in_array($id, $ids)) {
                $errores[] = "ID duplicado encontrado: {$id}";
            } else {
                $ids[] = $id;
            }
            
            // Verificar emails duplicados
            if (in_array($email, $emails)) {
                $errores[] = "Email duplicado encontrado: {$email}";
            } else {
                $emails[] = $email;
            }
            
            // Verificar datos básicos
            if (empty($empleado->obtenerNombre())) {
                $errores[] = "Empleado con ID {$id} tiene nombre vacío";
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Empleado con ID {$id} tiene email inválido: {$email}";
            }
            
            if ($empleado->obtenerSalarioBase() <= 0) {
                $errores[] = "Empleado con ID {$id} tiene salario base inválido: " . $empleado->obtenerSalarioBase();
            }
            
            // Verificaciones específicas por tipo
            if ($empleado instanceof EmpleadoMedioTiempo && $empleado->obtenerHorasSemanales() <= 0) {
                $advertencias[] = "Empleado de medio tiempo {$id} tiene 0 horas trabajadas";
            } elseif ($empleado instanceof EmpleadoContratista && $empleado->obtenerSalarioBase() <= 0) {
                $advertencias[] = "Empleado contratista {$id} tiene 0 proyectos completados";
            }
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'advertencias' => $advertencias,
            'total_empleados' => count($this->empleados),
            'emails_unicos' => count($emails),
            'ids_unicos' => count($ids)
        ];
    }
}