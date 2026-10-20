<?php
session_start();
require_once '../config/conexion.php';

// Obtener ventas del día
$ventas_dia = $pdo->query("SELECT COALESCE(SUM(total_vendido), 0) as total, COALESCE(SUM(num_ordenes), 0) as ordenes FROM v_ventas_diarias WHERE fecha = CURDATE()")->fetch();

// Obtener producto estrella
$estrella = $pdo->query("SELECT * FROM v_productos_mas_vendidos LIMIT 1")->fetch();

// Obtener lista de órdenes recientes
$ordenes_recientes = $pdo->query("
    SELECT o.id_orden, o.codigo, o.total, e.nombre as estado 
    FROM ordenes o 
    JOIN estados_orden e ON o.id_estado = e.id_estado 
    ORDER BY o.fecha_creacion DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrino's Pizza - Panel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --verde-oscuro: #1B4332; --dorado: #D4AF37; --bg-admin: #0D1F17;
            --card-admin: #142E22; --rojo-pepperoni: #A61C1C;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-admin); color: #fff; min-height: 100vh; padding: 20px; }
        .admin-header { background: var(--verde-oscuro); padding: 15px 25px; border-radius: 8px; border: 1px solid var(--dorado); display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .metric-card { background: var(--card-admin); padding: 20px; border-radius: 8px; border: 1px solid #1E4332; }
        .metric-value { font-size: 1.5rem; font-weight: 700; color: var(--dorado); margin-top: 5px; }
        .table-section { background: var(--card-admin); padding: 20px; border-radius: 8px; border: 1px solid #1E4332; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #1E4332; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1 style="font-family:'Cinzel'; color:var(--dorado);">PADRINO'S PIZZA — MÓDULO DE GERENCIA</h1>
        <a href="login.php" style="color:#fff; text-decoration:none; background:var(--rojo-pepperoni); padding:6px 15px; border-radius:6px;">Cerrar Sesión</a>
    </div>

    <!-- TARJETAS DE MÉTRICAS -->
    <div class="metrics-grid">
        <div class="metric-card">
            <h4>VENTAS TOTALES (DÍA)</h4>
            <div class="metric-value">$<?php echo number_format($ventas_dia['total'], 2); ?></div>
        </div>
        <div class="metric-card">
            <h4>ÓRDENES ATENDIDAS</h4>
            <div class="metric-value"><?php echo $ventas_dia['ordenes']; ?></div>
        </div>
        <div class="metric-card">
            <h4>PRODUCTO ESTRELLA</h4>
            <div class="metric-value"><?php echo $estrella['producto'] ?? 'N/A'; ?></div>
        </div>
    </div>

    <!-- TABLA DE AUDITORÍA -->
    <div class="table-section">
        <h3>Auditoría de Pedidos Recientes</h3>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Monto Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordenes_recientes as $ord): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($ord['codigo']); ?></td>
                        <td>$<?php echo number_format($ord['total'], 2); ?></td>
                        <td><?php echo htmlspecialchars($ord['estado']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>