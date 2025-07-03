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
        // Aquí iría la lógica real de envío de SMS
        // Por ejemplo, usando Twilio API o similar
        
        $mensajeCorto = $this->acortarMensaje($mensaje, 160);
        
        // Simulación de envío
        echo "📱 Enviando SMS a {$destinatario}\n";
        echo "   Mensaje: {$mensajeCorto}\n";
        echo "   API: {$this->apiUrl}\n\n";
        
        // En producción, aquí harías la llamada a la API real
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