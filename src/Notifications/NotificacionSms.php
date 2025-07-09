<?php


class NotificacionSms implements InterfazNotificacion
{
    private string $apiKey;
    private string $apiUrl;
    
    public function __construct(string $clave = '', string $url = 'https://api.twilio.com')
    {
        $this->apiKey = $clave;
        $this->apiUrl = $url;
    }
    
    public function enviar(string $destinatario, string $mensaje): bool
    {
    
        $mensajeCorto = $this->acortarMensaje($mensaje, 160);
        return true;
    }
    
    private function acortarMensaje(string $mensaje, int $limite): string
    {
        if (strlen($mensaje) <= $limite) {
            return $mensaje;
        }
        
        return substr($mensaje, 0, $limite - 3) . '...';
    }
}