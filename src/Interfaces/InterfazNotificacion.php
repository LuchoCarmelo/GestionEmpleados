<?php

interface InterfazNotificacion
{
    public function enviar(string $destinatario, string $mensaje): bool;
}