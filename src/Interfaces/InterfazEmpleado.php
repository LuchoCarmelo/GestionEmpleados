<?php


interface InterfazEmpleado
{
    public function obtenerId(): ?int;
    public function establecerId(int $id): void;
    public function obtenerNombre(): string;
    public function obtenerEmail(): string;
    public function obtenerTipo(): string;
    public function obtenerSalarioBase(): float;
}