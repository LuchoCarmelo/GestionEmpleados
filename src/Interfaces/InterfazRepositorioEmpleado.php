<?php

interface InterfazRepositorioEmpleado
{
    public function guardar(InterfazEmpleado $empleado): bool;
    public function buscarPorId(int $id): ?InterfazEmpleado;
    public function buscarTodos(): array;
    public function actualizar(InterfazEmpleado $empleado): bool;
    public function eliminar(int $id): bool;
}