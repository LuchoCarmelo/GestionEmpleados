<?php

class GestorNotificaciones
{
    private array $notificadores = [];
    private bool $registroActivado = false;
    
    public function agregarNotificador(InterfazNotificacion $notificador): void
    {
        $this->notificadores[] = $notificador;
        
        if ($this->registroActivado) {
            echo "✅ Notificador agregado al sistema (Total: " . count($this->notificadores) . ")\n";
        }
    }
    
    public function enviarNotificaciones(string $destinatario, string $mensaje): array
{
    $resultados = [];

    if (empty($this->notificadores)) {
        echo "⚠️ No hay notificadores configurados\n";
        return $resultados; // Aquí ya devuelves un array
    }

    echo "📢 Enviando notificaciones a: {$destinatario}\n";
    echo "-------------------------------------------\n";

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