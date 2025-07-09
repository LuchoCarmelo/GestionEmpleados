<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('America/Lima');

function cargarClase($nombreClase){
    $rutas = [
        'Interfaces/',
        'Models/',
        'Calculators/',
        'Reports/',
        'Notifications/',
        'Repositories/',
        'Services/',
        'Database/',
        'Factories/',
        'Config/'
    ];

    foreach ($rutas as $ruta) {
        $archivo = __DIR__ . '/' . $ruta . $nombreClase . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
}

spl_autoload_register('cargarClase');

$mensaje = ''; // Variable para mensajes al usuario (éxito/error)
$tipoMensaje = ''; // 'resultado' o 'error'

try {
    $conexion_pdo = ConexionBaseDeDatos::getInstance();
    $repositorio = new RepositorioEmpleadoMySQL($conexion_pdo);
    $gestorNotificaciones = new GestorNotificaciones();
    $gestorNotificaciones->agregarNotificador(new NotificacionEmail());
    $gestorNotificaciones->agregarNotificador(new NotificacionSms());
    $servicioEmpleados = new ServicioGestionEmpleados($repositorio, $gestorNotificaciones);
    $fabricaCalculadora = new FabricaCalculadoraSalario();

    // ===========================================
    // LÓGICA DE PROCESAMIENTO DEL FORMULARIO
    // ===========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        if ($accion === 'agregar_empleado') {
            try {
                $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $salarioBase = filter_input(INPUT_POST, 'salario_base', FILTER_VALIDATE_FLOAT);
                $tipoEmpleado = htmlspecialchars(trim($_POST['tipo_empleado'] ?? ''));

                if (!$nombre || !$email || $salarioBase === false || !$tipoEmpleado) {
                    throw new Exception("Datos incompletos o inválidos para el empleado.");
                }

                $nuevoEmpleado = null;
                switch ($tipoEmpleado) {
                    case 'tiempo_completo':
                        $puesto = htmlspecialchars(trim($_POST['puesto'] ?? ''));
                        $bonificacion = filter_input(INPUT_POST, 'bonificacion', FILTER_VALIDATE_FLOAT);
                        if ($bonificacion === false) $bonificacion = 0.0; // Default if not provided
                        $nuevoEmpleado = new EmpleadoTiempoCompleto(null, $nombre, $email, $salarioBase, $puesto, $bonificacion);
                        break;
                    case 'medio_tiempo':
                        $horasSemanales = filter_input(INPUT_POST, 'horas_semanales', FILTER_VALIDATE_INT);
                        if ($horasSemanales === false) throw new Exception("Horas semanales inválidas.");
                        $nuevoEmpleado = new EmpleadoMedioTiempo(null, $nombre, $email, $salarioBase, $horasSemanales);
                        break;
                    case 'contratista':
                        $tarifaHora = filter_input(INPUT_POST, 'tarifa_hora', FILTER_VALIDATE_FLOAT);
                        if ($tarifaHora === false) throw new Exception("Tarifa por hora inválida.");
                        $nuevoEmpleado = new EmpleadoContratista(null, $nombre, $email, $salarioBase, $tarifaHora);
                        break;
                    default:
                        throw new Exception("Tipo de empleado no válido.");
                }

                if ($nuevoEmpleado) {
                    $servicioEmpleados->agregarEmpleado($nuevoEmpleado);
                    $mensaje = "Empleado '{$nombre}' agregado exitosamente a la base de datos.";
                    $tipoMensaje = 'resultado';
                }

            } catch (Exception $e) {
                $mensaje = "Error al agregar empleado: " . htmlspecialchars($e->getMessage());
                $tipoMensaje = 'error';
            }
        }
    }

    // Siempre obtener la lista de empleados y el resumen para mostrarlos
    $todosLosEmpleados = $servicioEmpleados->obtenerTodosLosEmpleados();
    $resumenSistema = $servicioEmpleados->obtenerResumenSistema();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Empleados</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Tu CSS actual */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); text-align: center; }
        .header h1 { color: #2c3e50; font-size: 2.5em; margin-bottom: 10px; font-weight: 700; }
        .header p { color: #7f8c8d; font-size: 1.1em; }
        .card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); }
        .card h2 { color: #2c3e50; margin-bottom: 20px; font-size: 1.8em; border-bottom: 3px solid #3498db; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }
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

        /* --- NUEVO CSS PARA FORMULARIO Y TABLA --- */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            background-color: #f9f9f9;
        }
        .form-group input[type="submit"] {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-group input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        .campos-especificos {
            margin-top: 15px;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 8px;
            background-color: #f0f8ff; /* Light blue background for specific fields */
        }
        .campos-especificos h4 {
            margin-top: 0;
            color: #3498db;
        }

        .empleados-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .empleados-table th, .empleados-table td {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }
        .empleados-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        .empleados-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .empleados-table tr:hover {
            background-color: #eef;
        }
        .empleados-table td.salario-col {
            font-weight: bold;
            color: #27ae60;
        }
        .empleados-table .acciones-col {
            text-align: center;
            white-space: nowrap; /* Evita que los botones se envuelvan */
        }
        .empleados-table .acciones-col .btn {
            padding: 6px 12px;
            font-size: 0.85em;
            border-radius: 5px;
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Sistema de Gestión de Empleados</h1>
            <p>Una aplicación de ejemplo aplicando principios SOLID en PHP.</p>
        </div>

        <?php if ($mensaje): // Muestra mensajes de éxito/error ?>
            <div class="<?= $tipoMensaje ?>">
                <p><?= $mensaje ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>➕ Agregar Nuevo Empleado</h2>
            <form action="index.php" method="POST">
                <input type="hidden" name="accion" value="agregar_empleado">

                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="salario_base">Salario Base:</label>
                    <input type="number" id="salario_base" name="salario_base" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="tipo_empleado">Tipo de Empleado:</label>
                    <select id="tipo_empleado" name="tipo_empleado" onchange="mostrarCamposEspecificos()" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="tiempo_completo">Tiempo Completo</option>
                        <option value="medio_tiempo">Medio Tiempo</option>
                        <option value="contratista">Contratista</option>
                    </select>
                </div>

                <div id="campos_especificos" class="campos-especificos" style="display: none;">
                    </div>

                <div class="form-group">
                    <input type="submit" value="Guardar Empleado">
                </div>
            </form>
        </div>

        <div class="card">
            <h2>📊 Estadísticas del Sistema</h2>
            <div class="estadisticas">
                <div class="stat-card">
                    <h3><?= $resumenSistema['totalEmpleados'] ?? 0 ?></h3> <p>Total Empleados</p>
                </div>
                <div class="stat-card">
                    <h3>$<?= number_format($resumenSistema['nominaTotal'] ?? 0, 2) ?></h3> <p>Nómina Total</p>
                </div>
                <div class="stat-card">
                    <h3>$<?= number_format($resumenSistema['salarioPromedio'] ?? 0, 2) ?></h3> <p>Salario Promedio</p>
                </div>
                <div class="stat-card">
                    <h3><?= count($resumenSistema['tiposEmpleadoConteo'] ?? []) ?></h3> <p>Tipos de Empleado</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>👥 Lista de Empleados Registrados</h2>
            <?php if (empty($todosLosEmpleados)): ?>
                <p>No hay empleados registrados en la base de datos.</p>
            <?php else: ?>
                <table class="empleados-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Salario Calculado</th>
                            <th>Detalles Adicionales</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todosLosEmpleados as $empleado):
                            $calculadora = $fabricaCalculadora->crearCalculadora($empleado->obtenerTipo());
                            $salario = $calculadora->calcularSalario($empleado);
                            $tipoClase = str_replace('_', '-', $empleado->obtenerTipo());
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($empleado->obtenerId()) ?></td>
                                <td><?= htmlspecialchars($empleado->obtenerNombre()) ?></td>
                                <td><?= htmlspecialchars($empleado->obtenerEmail()) ?></td>
                                <td><span class="tipo-badge <?= $tipoClase ?>"><?= ucfirst(str_replace('_', ' ', $empleado->obtenerTipo())) ?></span></td>
                                <td class="salario-col">$<?= number_format($salario, 2) ?></td>
                                <td>
                                    <?php if ($empleado instanceof EmpleadoTiempoCompleto): ?>
                                        Puesto: <?= htmlspecialchars($empleado->obtenerPuesto()) ?><br>
                                        Bonificación: $<?= number_format($empleado->obtenerBonificacion(), 2) ?>
                                    <?php elseif ($empleado instanceof EmpleadoMedioTiempo): ?>
                                        Horas Semanales: <?= htmlspecialchars($empleado->obtenerHorasSemanales()) ?>
                                    <?php elseif ($empleado instanceof EmpleadoContratista): ?>
                                        Tarifa/Hora: $<?= number_format($empleado->obtenerTarifaHora(), 2) ?>
                                    <?php endif; ?>
                                </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>📋 Detalle por Tipo de Empleado</h2>
            <div class="estadisticas">
                <?php foreach ($resumenSistema['detallePorTipo'] ?? [] as $tipo => $datos): ?>
                    <div class="stat-card">
                        <h3><?= $datos['cantidad'] ?? 0 ?></h3>
                        <p><?= ucfirst(str_replace('_', ' ', $tipo)) ?></p>
                        <small>Promedio: $<?= number_format($datos['promedio'] ?? 0, 2) ?></small>
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
        // JS para mostrar campos específicos según el tipo de empleado
        function mostrarCamposEspecificos() {
            const tipoEmpleado = document.getElementById('tipo_empleado').value;
            const camposEspecificosDiv = document.getElementById('campos_especificos');
            camposEspecificosDiv.innerHTML = ''; // Limpiar campos previos
            camposEspecificosDiv.style.display = 'none';

            let htmlCampos = '';
            switch (tipoEmpleado) {
                case 'tiempo_completo':
                    htmlCampos = `
                        <h4>Campos para Empleado de Tiempo Completo</h4>
                        <div class="form-group">
                            <label for="puesto">Puesto:</label>
                            <input type="text" id="puesto" name="puesto" required>
                        </div>
                        <div class="form-group">
                            <label for="bonificacion">Bonificación:</label>
                            <input type="number" id="bonificacion" name="bonificacion" step="0.01" min="0" value="0.00">
                        </div>
                    `;
                    camposEspecificosDiv.style.display = 'block';
                    break;
                case 'medio_tiempo':
                    htmlCampos = `
                        <h4>Campos para Empleado de Medio Tiempo</h4>
                        <div class="form-group">
                            <label for="horas_semanales">Horas Semanales:</label>
                            <input type="number" id="horas_semanales" name="horas_semanales" min="1" max="39" required>
                        </div>
                    `;
                    camposEspecificosDiv.style.display = 'block';
                    break;
                case 'contratista':
                    htmlCampos = `
                        <h4>Campos para Empleado Contratista</h4>
                        <div class="form-group">
                            <label for="tarifa_hora">Tarifa por Hora:</label>
                            <input type="number" id="tarifa_hora" name="tarifa_hora" step="0.01" min="0" required>
                        </div>
                    `;
                    camposEspecificosDiv.style.display = 'block';
                    break;
            }
            camposEspecificosDiv.innerHTML = htmlCampos;
        }

        // Simulación de las acciones (mantenerlas por ahora)
        const todosLosEmpleadosJS = <?= json_encode($todosLosEmpleados ?? []) ?>;
        const estadisticasJS = <?= json_encode($resumenSistema ?? []) ?>;

        function procesarNomina() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Procesando...';
            btn.disabled = true;

            setTimeout(() => {
                document.getElementById('resultado').innerHTML = `
                    <div class="resultado">
                        <h3>✅ Nómina Procesada Exitosamente</h3>
                        <p>Se ha procesado la nómina de ${todosLosEmpleadosJS.length} empleados.</p>
                        <p>Total procesado: $${(estadisticasJS.nominaTotal || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
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

            setTimeout(() => {
                const formatoMayus = formato.toUpperCase();
                document.getElementById('resultado').innerHTML = `
                    <div class="resultado">
                        <h3>📄 Reporte ${formatoMayus} Generado</h3>
                        <p>El reporte en formato ${formatoMayus} ha sido generado exitosamente.</p>
                        <p>Incluye información de ${todosLosEmpleadosJS.length} empleados.</p>
                        <p>Archivo guardado como: reporte_empleados_${new Date().toISOString().split('T')[0]}.${formato}</p>
                    </div>
                `;
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1500);
        }

        function mostrarEstadisticas() {
            let html = '<div class="resultado"><h3>📊 Estadísticas Detalladas</h3>';
            html += `<p><strong>Total empleados:</strong> ${estadisticasJS.totalEmpleados ?? 0}</p>`;
            html += `<p><strong>Nómina total:</strong> $${(estadisticasJS.nominaTotal ?? 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>`;
            html += `<p><strong>Salario promedio:</strong> $${(estadisticasJS.salarioPromedio ?? 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>`;
            html += `<p><strong>Salario mínimo:</strong> $${(estadisticasJS.salarioMinimo ?? 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>`;
            html += `<p><strong>Salario máximo:</strong> $${(estadisticasJS.salarioMaximo ?? 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>`;

            html += '<h4>Por tipo de empleado:</h4><ul>';
            for (const [tipo, datos] of Object.entries(estadisticasJS.detallePorTipo ?? {})) {
                html += `<li><strong>${tipo.replace('_', ' ')}:</strong> ${datos.cantidad ?? 0} empleados, promedio: $${(datos.promedio ?? 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>`;
            }
            html += '</ul></div>';

            document.getElementById('resultado').innerHTML = html;
        }
    </script>
</body>
</html>