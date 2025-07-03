<?php

// Configurar errores y codificación
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('America/Lima');

// Función simple de autoload
function cargarClase($nombreClase) {
    // Rutas donde se buscarán las clases
    $rutas = [
        'Interfaces/',
        'Models/',
        'Calculators/',
        'Reports/',
        'Notifications/',
        'Repositories/',
        'Services/',
        // 'src/Database/', // Descomentar si tienes clases de base de datos
        // 'src/Config/'    // Descomentar si tienes clases de configuración
    ];
    
    // Recorremos cada ruta para buscar el archivo de la clase
    foreach ($rutas as $ruta) {
        $archivo = __DIR__.'/'. $ruta . $nombreClase . '.php';
        // Si el archivo existe, lo incluimos y salimos de la función
        if (file_exists($archivo)) {
            require_once $archivo;
            return; 
        }
    }
    //throw new Exception("La clase $nombreClase no pudo ser cargada automáticamente. Archivo no encontrado en las rutas especificadas.");
}

// Registrar la función 'cargarClase' como autoloader
spl_autoload_register('cargarClase');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Empleados</title>
    <style>
        /* Tu CSS se mantiene intacto */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); text-align: center; }
        .header h1 { color: #2c3e50; font-size: 2.5em; margin-bottom: 10px; font-weight: 700; }
        .header p { color: #7f8c8d; font-size: 1.1em; }
        .card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); }
        .card h2 { color: #2c3e50; margin-bottom: 20px; font-size: 1.8em; border-bottom: 3px solid #3498db; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .empleados-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .empleado-card { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px; padding: 20px; border-left: 5px solid #3498db; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .empleado-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); }
        .empleado-card.tiempo-completo { border-left-color: #27ae60; }
        .empleado-card.medio-tiempo { border-left-color: #f39c12; }
        .empleado-card.contratista { border-left-color: #e74c3c; }
        .empleado-card h3 { color: #2c3e50; margin-bottom: 10px; font-size: 1.3em; }
        .empleado-info { font-size: 0.9em; color: #7f8c8d; margin: 5px 0; }
        .salario { font-size: 1.2em; font-weight: bold; color: #27ae60; margin-top: 10px; }
        .tipo-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; margin-top: 5px; }
        .tipo-badge.tiempo-completo { background-color: #27ae60; color: white; }
        .tipo-badge.medio-tiempo { background-color: #f39c12; color: white; }
        .tipo-badge.contratista { background-color: #e74c3c; color: white; }
        .estadisticas { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; transition: transform 0.3s ease; }
        .stat-card:hover { transform: scale(1.05); }
        .stat-card h3 { font-size: 2em; margin-bottom: 5px; }
        .stat-card p { font-size: 0.9em; opacity: 0.9; }
        .acciones { display: flex; gap: 15px; flex-wrap: wrap; justify-content: center; margin-top: 20px; }
        .btn { padding: 12px 25px; border: none; border-radius: 25px; font-size: 1em; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; }
        .btn-success { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; }
        .btn-warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2); }
        .loading { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(255, 255, 255, 0.3); border-radius: 50%; border-top-color: white; animation: spin 1s ease-in-out infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .resultado { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: rgba(255, 255, 255, 0.8); margin-top: 40px; }
        @media (max-width: 768px) {
            .header h1 { font-size: 2em; }
            .empleados-grid { grid-template-columns: 1fr; }
            .acciones { flex-direction: column; align-items: center; }
            .btn { width: 100%; max-width: 300px; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Gestión de Empleados</h1>
        </div>

        <?php
        try {
            // ===============================================
            // CONFIGURAR EL SISTEMA
            // ===============================================
            
            // Crear repositorio (usando memoria para simplicidad)
            // La clase RepositorioEmpleadoMemoria será cargada por el autoloader
            $repositorio = new RepositorioEmpleadoMemoria();
            
            // Configurar notificaciones
            // Las clases GestorNotificaciones, NotificacionEmail, NotificacionSms
            // serán cargadas por el autoloader
            $gestorNotificaciones = new GestorNotificaciones();
            $gestorNotificaciones->agregarNotificador(new NotificacionEmail());
            $gestorNotificaciones->agregarNotificador(new NotificacionSms());
            
            // Crear servicio principal
            // La clase ServicioGestionEmpleados será cargada por el autoloader
            // Si RepositorioEmpleadoMemoria implementa InterfazRepositorioEmpleado,
            // este error se resolverá.
            $servicioEmpleados = new ServicioGestionEmpleados($repositorio, $gestorNotificaciones);
            
            // ===============================================
            // CREAR EMPLEADOS DE DEMOSTRACIÓN
            // ===============================================
            
            $empleados = [
                new EmpleadoTiempoCompleto(1, "María García López", "maria@empresa.com", 5000, 1000),
                new EmpleadoMedioTiempo(2, "Carlos Rodríguez", "carlos@empresa.com", 30, 100),
                new EmpleadoContratista(3, "Ana Martínez", "ana@freelance.com", 1500, 3),
                new EmpleadoTiempoCompleto(4, "Luis Hernández", "luis@empresa.com", 4500, 800),
                new EmpleadoMedioTiempo(5, "Sofia Vásquez", "sofia@empresa.com", 25, 120),
                new EmpleadoContratista(6, "Pedro Morales", "pedro@freelance.com", 2000, 2)
            ];
            
            // Agregar empleados al sistema
            foreach ($empleados as $empleado) {
                $servicioEmpleados->agregarEmpleado($empleado);
            }
            
            // ===============================================
            // OBTENER DATOS PARA MOSTRAR
            // ===============================================
            
            $todosLosEmpleados = $servicioEmpleados->obtenerTodosLosEmpleados();
            $estadisticas = $servicioEmpleados->obtenerEstadisticas();
            // La clase FabricaCalculadoraSalario será cargada por el autoloader
            $fabricaCalculadora = new FabricaCalculadoraSalario();
            
            ?>

            <div class="card">
                <h2>📊 Estadísticas del Sistema</h2>
                <div class="estadisticas">
                    <div class="stat-card">
                        <h3><?= $estadisticas['total_empleados'] ?></h3>
                        <p>Total Empleados</p>
                    </div>
                    <div class="stat-card">
                        <h3>$<?= number_format($estadisticas['nomina_total'], 2) ?></h3>
                        <p>Nómina Total</p>
                    </div>
                    <div class="stat-card">
                        <h3>$<?= number_format($estadisticas['salario_promedio'], 2) ?></h3>
                        <p>Salario Promedio</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= count($estadisticas['por_tipo']) ?></h3>
                        <p>Tipos de Empleado</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>👥 Lista de Empleados</h2>
                <div class="empleados-grid">
                    <?php foreach ($todosLosEmpleados as $empleado): 
                        $calculadora = $fabricaCalculadora->obtenerCalculadora($empleado->obtenerTipo());
                        $salario = $calculadora->calcularSalario($empleado);
                        $tipoClase = str_replace('_', '-', $empleado->obtenerTipo());
                    ?>
                        <div class="empleado-card <?= $tipoClase ?>">
                            <h3><?= htmlspecialchars($empleado->obtenerNombre()) ?></h3>
                            <div class="empleado-info">
                                📧 <?= htmlspecialchars($empleado->obtenerEmail()) ?>
                            </div>
                            <div class="empleado-info">
                                🆔 ID: <?= $empleado->obtenerId() ?>
                            </div>
                            <div class="salario">
                                💰 $<?= number_format($salario, 2) ?>
                            </div>
                            <span class="tipo-badge <?= $tipoClase ?>">
                                <?= ucfirst(str_replace('_', ' ', $empleado->obtenerTipo())) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h2>📋 Detalle por Tipo de Empleado</h2>
                <div class="estadisticas">
                    <?php foreach ($estadisticas['por_tipo'] as $tipo => $datos): ?>
                        <div class="stat-card">
                            <h3><?= $datos['cantidad'] ?></h3>
                            <p><?= ucfirst(str_replace('_', ' ', $tipo)) ?></p>
                            <small>Promedio: $<?= number_format($datos['salario_promedio'], 2) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h2>⚡ Acciones del Sistema</h2>
                <div class="acciones">
                    <button class="btn btn-primary" onclick="procesarNomina()">
                        💰 Procesar Nómina
                    </button>
                    <button class="btn btn-success" onclick="generarReporte('json')">
                        📄 Generar Reporte JSON
                    </button>
                    <button class="btn btn-warning" onclick="generarReporte('csv')">
                        📊 Generar Reporte CSV
                    </button>
                    <button class="btn btn-primary" onclick="mostrarEstadisticas()">
                        📈 Ver Estadísticas Detalladas
                    </button>
                </div>
                
                <div id="resultado"></div>
            </div>

            <?php
        } catch (Exception $e) {
            echo '<div class="card error">';
            echo '<h2>❌ Error del Sistema</h2>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Archivo:</strong> ' . $e->getFile() . '</p>';
            echo '<p><strong>Línea:</strong> ' . $e->getLine() . '</p>';
            echo '</div>';
        }
        ?>
    </div>

    <script>
        function procesarNomina() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Procesando...';
            btn.disabled = true;
            
            // Simular procesamiento
            setTimeout(() => {
                document.getElementById('resultado').innerHTML = `
                    <div class="resultado">
                        <h3>✅ Nómina Procesada Exitosamente</h3>
                        <p>Se ha procesado la nómina de <?= count($todosLosEmpleados) ?> empleados.</p>
                        <p>Total procesado: $<?= number_format($estadisticas['nomina_total'], 2) ?></p>
                        <p>Notificaciones enviadas por email y SMS a todos los empleados.</p>
                    </div>
                `;
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
        
        function generarReporte(formato) {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Generando...';
            btn.disabled = true;
            
            // Simular generación de reporte
            setTimeout(() => {
                const formatoMayus = formato.toUpperCase();
                document.getElementById('resultado').innerHTML = `
                    <div class="resultado">
                        <h3>📄 Reporte ${formatoMayus} Generado</h3>
                        <p>El reporte en formato ${formatoMayus} ha sido generado exitosamente.</p>
                        <p>Incluye información de <?= count($todosLosEmpleados) ?> empleados.</p>
                        <p>Archivo guardado como: reporte_empleados_${new Date().toISOString().split('T')[0]}.${formato}</p>
                    </div>
                `;
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1500);
        }
        
        function mostrarEstadisticas() {
            const estadisticas = <?= json_encode($estadisticas) ?>;
            let html = '<div class="resultado"><h3>📊 Estadísticas Detalladas</h3>';
            html += `<p><strong>Total empleados:</strong> ${estadisticas.total_empleados}</p>`;
            html += `<p><strong>Nómina total:</strong> $${estadisticas.nomina_total.toLocaleString()}</p>`;
            html += `<p><strong>Salario promedio:</strong> $${estadisticas.salario_promedio.toLocaleString()}</p>`;
            html += `<p><strong>Salario mínimo:</strong> $${estadisticas.salario_minimo.toLocaleString()}</p>`;
            html += `<p><strong>Salario máximo:</strong> $${estadisticas.salario_maximo.toLocaleString()}</p>`;
            
            html += '<h4>Por tipo de empleado:</h4><ul>';
            for (const [tipo, datos] of Object.entries(estadisticas.por_tipo)) {
                html += `<li><strong>${tipo.replace('_', ' ')}:</strong> ${datos.cantidad} empleados, promedio: $${datos.salario_promedio.toLocaleString()}</li>`;
            }
            html += '</ul></div>';
            
            document.getElementById('resultado').innerHTML = html;
        }
    </script>
</body>
</html>