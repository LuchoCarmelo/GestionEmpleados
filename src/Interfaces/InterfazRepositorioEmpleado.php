<?php

class RepositorioEmpleadoMySQL implements InterfazRepositorioEmpleado
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function guardar(InterfazEmpleado $empleado): bool
    {
        $sql = "INSERT INTO empleados (nombre, email, salario_base, tipo_empleado, puesto, horas_semanales, tarifa_hora)
                VALUES (:nombre, :email, :salario_base, :tipo_empleado, :puesto, :horas_semanales, :tarifa_hora)";
        $stmt = $this->conexion->prepare($sql);

        $nombre = $empleado->obtenerNombre();
        $email = $empleado->obtenerEmail();
        $salarioBase = $empleado->obtenerSalarioBase();
        $tipo = $empleado->obtenerTipo();

        $puesto = null;
        $horasSemanales = null;
        $tarifaHora = null;

        // Asignar propiedades específicas del tipo de empleado
        if ($empleado instanceof EmpleadoTiempoCompleto) {
            $puesto = $empleado->obtenerPuesto();
        } elseif ($empleado instanceof EmpleadoMedioTiempo) {
            $horasSemanales = $empleado->obtenerHorasSemanales();
        } elseif ($empleado instanceof EmpleadoContratista) {
            $tarifaHora = $empleado->obtenerTarifaHora();
        }

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':salario_base', $salarioBase);
        $stmt->bindParam(':tipo_empleado', $tipo);
        $stmt->bindParam(':puesto', $puesto);
        $stmt->bindParam(':horas_semanales', $horasSemanales);
        $stmt->bindParam(':tarifa_hora', $tarifaHora);

        try {
            $resultado = $stmt->execute();
            // Si el ID se genera automáticamente en la BD, puedes asignarlo al objeto
            if ($resultado && $empleado->obtenerId() === null) {
                $empleado->establecerId((int)$this->conexion->lastInsertId()); // Asume que InterfazEmpleado tiene establecerId()
            }
            return $resultado;
        } catch (PDOException $e) {
            // Logear el error: $e->getMessage();
            // echo "Error al guardar empleado: " . $e->getMessage() . "\n"; // Solo para depuración
            return false;
        }
    }

    public function buscarPorId(int $id): ?InterfazEmpleado
    {
        $stmt = $this->conexion->prepare("SELECT * FROM empleados WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            return null;
        }

        $fabrica = new FabricaEmpleado(); // Asegúrate de que FabricaEmpleado esté cargada
        return $fabrica->crearDesdeDatos($datos);
    }

    public function buscarTodos(): array
    {
        $stmt = $this->conexion->query("SELECT * FROM empleados");
        $empleados = [];
        $fabrica = new FabricaEmpleado(); // Asegúrate de que FabricaEmpleado esté cargada

        while ($datos = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $empleados[] = $fabrica->crearDesdeDatos($datos);
        }
        return $empleados;
    }

    public function actualizar(InterfazEmpleado $empleado): bool
    {
        $sql = "UPDATE empleados SET nombre = :nombre, email = :email, salario_base = :salario_base,
                tipo_empleado = :tipo_empleado, puesto = :puesto, horas_semanales = :horas_semanales,
                tarifa_hora = :tarifa_hora WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);

        $nombre = $empleado->obtenerNombre();
        $email = $empleado->obtenerEmail();
        $salarioBase = $empleado->obtenerSalarioBase();
        $tipo = $empleado->obtenerTipo();
        $id = $empleado->obtenerId();

        $puesto = null;
        $horasSemanales = null;
        $tarifaHora = null;

        if ($empleado instanceof EmpleadoTiempoCompleto) {
            $puesto = $empleado->obtenerPuesto();
        } elseif ($empleado instanceof EmpleadoMedioTiempo) {
            $horasSemanales = $empleado->obtenerHorasSemanales();
        } elseif ($empleado instanceof EmpleadoContratista) {
            $tarifaHora = $empleado->obtenerTarifaHora();
        }

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':salario_base', $salarioBase);
        $stmt->bindParam(':tipo_empleado', $tipo);
        $stmt->bindParam(':puesto', $puesto);
        $stmt->bindParam(':horas_semanales', $horasSemanales);
        $stmt->bindParam(':tarifa_hora', $tarifaHora);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Logear el error: $e->getMessage();
            // echo "Error al actualizar empleado: " . $e->getMessage() . "\n"; // Solo para depuración
            return false;
        }
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM empleados WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Logear el error: $e->getMessage();
            // echo "Error al eliminar empleado: " . $e->getMessage() . "\n"; // Solo para depuración
            return false;
        }
    }
}