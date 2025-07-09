<?php

class NotificacionEmail implements InterfazNotificacion
{
    private string $servidorSmtp;
    private string $puertoSmtp;
    private string $usuarioEmail;
    private string $passwordEmail;
    
    public function __construct(string $servidor = 'localhost', string $puerto = '587', string $usuario = '', string $password = '')
    {
        $this->servidorSmtp = $servidor;
        $this->puertoSmtp = $puerto;
        $this->usuarioEmail = $usuario;
        $this->passwordEmail = $password;
    }
    
    public function enviar(string $destinatario, string $mensaje): bool
    {
        // Aquí iría la lógica real de envío de email
        // Por ejemplo, usando PHPMailer o mail()
        
        $asunto = "Notificación de Nómina - Sistema de Empleados";
        $cabeceras = "From: sistema@empresa.com\r\n";
        $cabeceras .= "Reply-To: sistema@empresa.com\r\n";
        $cabeceras .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $cuerpoEmail = $this->construirCuerpoEmail($mensaje);
        
    
        return true;
    }
    
    private function construirCuerpoEmail(string $mensaje): string
    {
        return "
        <html>
        <body>
            <h2>Sistema de Gestión de Empleados</h2>
            <p>{$mensaje}</p>
            <hr>
            <p><small>Este es un mensaje automático del sistema de nómina.</small></p>
        </body>
        </html>";
    }
}
