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

        if ($empleado instanceof EmpleadoTiempoCompleto) {
            $puesto = $empleado->obtenerPuesto();
            // Si tienes bonificación en la DB, añádela aquí
            // $bonificacion = $empleado->obtenerBonificacion();
            // $sql = "INSERT INTO empleados (..., bonificacion) VALUES (..., :bonificacion)"
            // $stmt->bindParam(':bonificacion', $bonificacion);
        } elseif ($empleado instanceof EmpleadoMedioTiempo) {
            $horasSemanales = $empleado->obtenerHorasSemanales(); // O obtenerHorasTrabajadas() si lo renombraste
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
            if ($resultado && $empleado->obtenerId() === null) {
                $empleado->establecerId((int)$this->conexion->lastInsertId());
            }
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al guardar empleado: " . $e->getMessage()); // Loguear en lugar de morir
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

        $fabrica = new FabricaEmpleado();
        return $fabrica->crearDesdeDatos($datos);
    }

    public function buscarTodos(): array
    {
        $stmt = $this->conexion->query("SELECT * FROM empleados");
        $empleados = [];
        $fabrica = new FabricaEmpleado();

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
            // Si tienes bonificación en la DB, añádela aquí
            // $bonificacion = $empleado->obtenerBonificacion();
            // $sql = "UPDATE empleados SET ..., bonificacion = :bonificacion WHERE id = :id"
            // $stmt->bindParam(':bonificacion', $bonificacion);
        } elseif ($empleado instanceof EmpleadoMedioTiempo) {
            $horasSemanales = $empleado->obtenerHorasSemanales(); // O obtenerHorasTrabajadas()
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
            error_log("Error al actualizar empleado: " . $e->getMessage());
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
            error_log("Error al eliminar empleado: " . $e->getMessage());
            return false;
        }
    }
}