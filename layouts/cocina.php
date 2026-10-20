<?php
session_start();
require_once '../config/conexion.php';

/** @var PDO $pdo */

// Manejo de cambios de estado de las órdenes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiar_estado') {
    $id_orden     = (int)$_POST['id_orden'];
    $nuevo_estado = (int)$_POST['nuevo_estado'];

    $stmt = $pdo->prepare("UPDATE ordenes SET id_estado = :estado WHERE id_orden = :id");
    $stmt->execute(['estado' => $nuevo_estado, 'id' => $id_orden]);

    header('Location: cocina.php');
    exit;
}

// Consultar todas las órdenes registradas junto con su estado
$sql = "
    SELECT o.*, e.nombre AS estado_nombre
    FROM ordenes o
    JOIN estados_orden e ON o.id_estado = e.id_estado
    ORDER BY o.id_orden ASC
";
$todas_ordenes = $pdo->query($sql)->fetchAll();

// Clasificar órdenes en sus respectivas columnas
$en_espera   = [];
$en_proceso  = [];
$listas      = [];
$entregadas  = [];

foreach ($todas_ordenes as $ord) {
    // Si la tabla detalle_orden existe, consultar items
    $stmtD = $pdo->prepare("
        SELECT d.*, p.nombre 
        FROM detalle_orden d 
        JOIN productos p ON d.id_producto = p.id_producto 
        WHERE d.id_orden = :id
    ");
    $stmtD->execute(['id' => $ord['id_orden']]);
    $ord['items'] = $stmtD->fetchAll();

    if ($ord['id_estado'] == 1) {
        $en_espera[] = $ord;
    } elseif ($ord['id_estado'] == 2) {
        $en_proceso[] = $ord;
    } elseif ($ord['id_estado'] == 3) {
        $listas[] = $ord;
    } elseif ($ord['id_estado'] == 4) {
        $entregadas[] = $ord;
    }
}

// Calcular total de órdenes activas (En Espera + En Proceso + Listas)
$total_activas = count($en_espera) + count($en_proceso) + count($listas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrino's Pizza - Monitor de Cocina</title>
    <!-- Auto-refresco en segundo plano cada 10 segundos -->
    <meta http-equiv="refresh" content="10">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --verde-pantalla: #132E22;
            --verde-header: #0F251B;
            --dorado-titulo: #D4AF37;
            --rojo-alerta: #E53E3E;
            --amarillo-proceso: #D69E2E;
            --verde-exito: #2F855A;
            --azul-entrega: #3182CE;
            --gris-entregado: #4A5568;
            --fondo-columna: #1A202C;
            --fondo-card: #2D3748;
            --texto-claro: #EDF2F7;
            --texto-muto: #A0AEC0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--verde-pantalla);
            color: var(--texto-claro);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Encabezado Principal */
        .cocina-header {
            background-color: var(--verde-header);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--dorado-titulo);
        }

        .header-title-box { display: flex; align-items: center; gap: 15px; }
        .brand-name { font-family: 'Cinzel', serif; color: var(--dorado-titulo); font-size: 1.4rem; font-weight: 700; letter-spacing: 1px; }
        .brand-subtitle { color: var(--texto-muto); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; border-left: 2px solid #2d3748; padding-left: 15px; }

        .badge-activas {
            background-color: #9B2C2C;
            color: #ffffff;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* Tablero Kanban (4 Columnas) */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 25px;
            flex-grow: 1;
            overflow-x: auto;
        }

        .kanban-column {
            background-color: var(--fondo-columna);
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            border: 1px solid #2d3748;
        }

        .column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 15px;
            border-bottom: 1px solid #2d3748;
        }

        .col-title {
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 1px;
        }

        .dot-status { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .dot-espera { background-color: var(--rojo-alerta); }
        .dot-proceso { background-color: var(--amarillo-proceso); }
        .dot-listas { background-color: var(--verde-exito); }
        .dot-entregadas { background-color: var(--texto-muto); }

        .col-count {
            background-color: #2d3748;
            color: #ffffff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .column-body {
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex-grow: 1;
            overflow-y: auto;
        }

        /* Tarjetas de Órdenes */
        .order-card {
            background-color: var(--fondo-card);
            border-radius: 10px;
            padding: 15px;
            border: 1px solid transparent;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-espera { border-color: rgba(229, 62, 62, 0.4); }
        .card-proceso { border-color: rgba(214, 158, 46, 0.4); }
        .card-listas { border-color: rgba(47, 133, 90, 0.4); }
        .card-entregadas { border-color: rgba(160, 174, 192, 0.2); opacity: 0.7; }

        .card-header-code {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .code-espera { color: #FC8181; }
        .code-proceso { color: #F6AD55; }
        .code-listas { color: #68D391; }
        .code-entregadas { color: #CBD5E0; }

        .card-time {
            font-size: 0.75rem;
            color: var(--texto-muto);
            margin-bottom: 12px;
        }

        .card-items-box {
            background-color: #1a202c;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .item-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 4px;
        }
        .item-extra {
            font-size: 0.75rem;
            color: var(--dorado-titulo);
            margin-bottom: 6px;
            padding-left: 6px;
        }

        /* Botones de Acción */
        .btn-action {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: transform 0.1s, opacity 0.2s;
        }
        .btn-action:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-iniciar { background-color: #DD6B20; color: #ffffff; }
        .btn-listo { background-color: #38A169; color: #ffffff; }
        .btn-entregado { background-color: #3182CE; color: #ffffff; }
        .btn-finalizado { background-color: #2D3748; color: var(--texto-muto); cursor: default; }

        .empty-col-msg {
            text-align: center;
            color: var(--texto-muto);
            font-size: 0.8rem;
            padding: 30px 10px;
        }
    </style>
</head>
<body>

    <!-- Encabezado corporativo -->
    <header class="cocina-header">
        <div class="header-title-box">
            <span class="brand-name">PADRINO'S PIZZA</span>
            <span class="brand-subtitle">MONITOR DE COCINA</span>
        </div>
        <div>
            <span class="badge-activas"><?php echo $total_activas; ?> Órdenes Activas</span>
        </div>
    </header>

    <!-- Tablero de 4 Columnas -->
    <main class="kanban-board">

        <!-- 1. EN ESPERA -->
        <section class="kanban-column">
            <div class="column-header">
                <span class="col-title" style="color: #FC8181;">
                    <span class="dot-status dot-espera"></span> EN ESPERA
                </span>
                <span class="col-count"><?php echo count($en_espera); ?></span>
            </div>

            <div class="column-body">
                <?php if (empty($en_espera)): ?>
                    <p class="empty-col-msg">No hay órdenes en espera</p>
                <?php else: ?>
                    <?php foreach ($en_espera as $ord): ?>
                        <div class="order-card card-espera">
                            <div>
                                <div class="card-header-code code-espera">#<?php echo htmlspecialchars($ord['codigo']); ?></div>
                                <div class="card-time">Cliente: <?php echo htmlspecialchars($ord['nombre_cliente'] ?? 'Mostrador'); ?></div>

                                <div class="card-items-box">
                                    <?php foreach ($ord['items'] as $it): ?>
                                        <div class="item-text"><?php echo $it['cantidad']; ?>x <?php echo htmlspecialchars($it['nombre']); ?></div>
                                        <?php if (!empty($it['notas'])): ?>
                                            <div class="item-extra">★ <?php echo htmlspecialchars($it['notas']); ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <form method="POST" action="cocina.php">
                                <input type="hidden" name="action" value="cambiar_estado">
                                <input type="hidden" name="id_orden" value="<?php echo $ord['id_orden']; ?>">
                                <input type="hidden" name="nuevo_estado" value="2">
                                <button type="submit" class="btn-action btn-iniciar">Iniciar Preparación ➔</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 2. EN PROCESO -->
        <section class="kanban-column">
            <div class="column-header">
                <span class="col-title" style="color: #F6AD55;">
                    <span class="dot-status dot-proceso"></span> EN PROCESO
                </span>
                <span class="col-count"><?php echo count($en_proceso); ?></span>
            </div>

            <div class="column-body">
                <?php if (empty($en_proceso)): ?>
                    <p class="empty-col-msg">No hay órdenes en proceso</p>
                <?php else: ?>
                    <?php foreach ($en_proceso as $ord): ?>
                        <div class="order-card card-proceso">
                            <div>
                                <div class="card-header-code code-proceso">#<?php echo htmlspecialchars($ord['codigo']); ?></div>
                                <div class="card-time">Cliente: <?php echo htmlspecialchars($ord['nombre_cliente'] ?? 'Mostrador'); ?></div>

                                <div class="card-items-box">
                                    <?php foreach ($ord['items'] as $it): ?>
                                        <div class="item-text"><?php echo $it['cantidad']; ?>x <?php echo htmlspecialchars($it['nombre']); ?></div>
                                        <?php if (!empty($it['notas'])): ?>
                                            <div class="item-extra">★ <?php echo htmlspecialchars($it['notas']); ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <form method="POST" action="cocina.php">
                                <input type="hidden" name="action" value="cambiar_estado">
                                <input type="hidden" name="id_orden" value="<?php echo $ord['id_orden']; ?>">
                                <input type="hidden" name="nuevo_estado" value="3">
                                <button type="submit" class="btn-action btn-listo">Marcar como Lista ➔</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 3. LISTAS -->
        <section class="kanban-column">
            <div class="column-header">
                <span class="col-title" style="color: #68D391;">
                    <span class="dot-status dot-listas"></span> LISTAS
                </span>
                <span class="col-count"><?php echo count($listas); ?></span>
            </div>

            <div class="column-body">
                <?php if (empty($listas)): ?>
                    <p class="empty-col-msg">No hay órdenes listas</p>
                <?php else: ?>
                    <?php foreach ($listas as $ord): ?>
                        <div class="order-card card-listas">
                            <div>
                                <div class="card-header-code code-listas">#<?php echo htmlspecialchars($ord['codigo']); ?></div>
                                <div class="card-time">Empacado • Listo para entrega</div>

                                <div class="card-items-box">
                                    <?php foreach ($ord['items'] as $it): ?>
                                        <div class="item-text"><?php echo $it['cantidad']; ?>x <?php echo htmlspecialchars($it['nombre']); ?></div>
                                        <?php if (!empty($it['notas'])): ?>
                                            <div class="item-extra">★ <?php echo htmlspecialchars($it['notas']); ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <form method="POST" action="cocina.php">
                                <input type="hidden" name="action" value="cambiar_estado">
                                <input type="hidden" name="id_orden" value="<?php echo $ord['id_orden']; ?>">
                                <input type="hidden" name="nuevo_estado" value="4">
                                <button type="submit" class="btn-action btn-entregado">Marcar Entregada ➔</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 4. ENTREGADAS -->
        <section class="kanban-column">
            <div class="column-header">
                <span class="col-title" style="color: #A0AEC0;">
                    <span class="dot-status dot-entregadas"></span> ENTREGADAS
                </span>
                <span class="col-count"><?php echo count($entregadas); ?></span>
            </div>

            <div class="column-body">
                <?php if (empty($entregadas)): ?>
                    <p class="empty-col-msg">Sin entregas recientes</p>
                <?php else: ?>
                    <?php foreach ($entregadas as $ord): ?>
                        <div class="order-card card-entregadas">
                            <div>
                                <div class="card-header-code code-entregadas">#<?php echo htmlspecialchars($ord['codigo']); ?></div>
                                <div class="card-time">Entregado a cliente</div>

                                <div class="card-items-box">
                                    <?php foreach ($ord['items'] as $it): ?>
                                        <div class="item-text"><?php echo $it['cantidad']; ?>x <?php echo htmlspecialchars($it['nombre']); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <button type="button" class="btn-action btn-finalizado" disabled>✓ Finalizada</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

</body>
</html>