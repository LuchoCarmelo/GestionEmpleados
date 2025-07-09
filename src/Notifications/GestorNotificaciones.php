<?php

class GestorNotificaciones
{
    private array $notificadores = [];
    private bool $registroActivado = false;
    
    public function agregarNotificador(InterfazNotificacion $notificador): void
    {
        $this->notificadores[] = $notificador;
        
        if ($this->registroActivado) {
        }
    }
    
    public function enviarNotificaciones(string $destinatario, string $mensaje): array
{
    $resultados = [];

    if (empty($this->notificadores)) {
        return $resultados; // Aquí ya devuelves un array
    }


    foreach ($this->notificadores as $indice => $notificador) {
        try {
            $exito = $notificador->enviar($destinatario, $mensaje);
            $resultados[$indice] = [
                'tipo' => get_class($notificador),
                'exito' => $exito,
                'error' => null
            ];
        } catch (Exception $e) {
            $resultados[$indice] = [
                'tipo' => get_class($notificador),
                'exito' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    return $resultados;
    }
}