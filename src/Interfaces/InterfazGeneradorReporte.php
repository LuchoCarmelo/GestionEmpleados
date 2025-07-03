<?php

interface InterfazGeneradorReporte
{
    public function generarReporte(array $empleados): string;
}