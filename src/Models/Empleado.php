<?php

abstract class Empleado implements InterfazEmpleado
{
    protected ?int $id;
    protected string $nombre;
    protected string $email;
    protected float $salarioBase;

    public function __construct(?int $id, string $nombre, string $email, float $salarioBase)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->salarioBase = $salarioBase;
    }

    public function obtenerId(): ?int
    {
        return $this->id;
    }

    public function establecerId(int $id): void
    {
        $this->id = $id;
    }

    public function obtenerNombre(): string
    {
        return $this->nombre;
    }

    public function obtenerEmail(): string
    {
        return $this->email;
    }

    public function obtenerSalarioBase(): float
    {
        return $this->salarioBase;
    }

    abstract public function obtenerTipo(): string;
}