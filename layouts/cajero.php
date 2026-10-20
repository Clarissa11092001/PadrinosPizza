<?php
session_start();
require_once '../config/conexion.php';

/** @var PDO $pdo */

// Procesar el envío de la orden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&$_POST['action'] === 'crear_orden') {
    $id_usuario     =$_SESSION['id_usuario'] ?? 1;
    $nombre_cliente = trim($_POST['nombre_cliente'] ?? 'Cliente Mostrador');
    $hora_entrega   =$_POST['hora_entrega'] ?? date('H:i', strtotime('+20 minutes'));
    $estado_pago    =$_POST['estado_pago'] ?? 'Sin pagar';
    $cart_json      =$_POST['cart_data'] ?? '[]';
    $cart           = json_decode($cart_json, true);

    if (!empty($cart)) {
        // Generar código único correlativo para la orden
        $codigo = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

        // Crear la orden principal (Estado inicial 1 = Pendiente)
        $stmtOrd =$pdo->prepare("
            INSERT INTO ordenes (codigo, id_usuario, nombre_cliente, hora_entrega, estado_pago, id_estado, origen, observaciones) 
            VALUES (:codigo, :usuario, :cliente, :hora, :pago, 1, 'Mostrador', 'Pedido registrado desde POS Cajero')
        ");
        $stmtOrd->execute([
            'codigo'  => $codigo,
            'usuario' => $id_usuario,
            'cliente' => $nombre_cliente,
            'hora'    => $hora_entrega,
            'pago'    => $estado_pago
        ]);
        $id_orden =$pdo->lastInsertId();

        // Insertar cada producto del carrito en detalle_orden
        $stmtDet =$pdo->prepare("
            INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, notas) 
            VALUES (:id_orden, :id_producto, :cantidad, :precio, :notas)
        ");

        foreach ($cart as $item) {$stmtDet->execute([
                'id_orden'    => $id_orden,
                'id_producto' => $item['id_producto'],
                'cantidad'    => $item['cantidad'],
                'precio'      => $item['precio_unitario'],
                'notas'       => $item['extras_texto']
            ]);
        }

        header('Location: cajero.php?msg=success');
        exit;
    }
}

// Consultar Categorías y Productos Activos desde la BD
$categorias =$pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY id_categoria ASC")->fetchAll();
$productos  =$pdo->query("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM productos p 
    JOIN categorias c ON p.id_categoria = c.id_categoria 
    WHERE p.activo = 1 
    ORDER BY p.id_categoria ASC, p.nombre ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrino's Pizza - POS / Cajero</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --verde-oscuro: #1B4332;
            --dorado: #D4AF37;
            --rojo-pepperoni: #A61C1C;
            --amarillo-queso: #F4C430;
            --blanco-crema: #FFF8E7;
            --texto-oscuro: #2D3748;
            --gris-fondo: #F4F6F8;
            --gris-borde: #E2E8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 100vw;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            background-color: var(--gris-fondo);
            color: var(--texto-oscuro);
            overflow: hidden;
        }

        .pos-wrapper {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .pos-header {
            background-color: var(--verde-oscuro);
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--dorado);
            flex-shrink: 0;
        }

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .logo-title { font-family: 'Cinzel', serif; color: var(--dorado); font-size: 1.3rem; font-weight: 700; }
        .badge-caja { background-color: var(--amarillo-queso); color: var(--verde-oscuro); font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; }

        .btn-salir {
            background-color: var(--rojo-pepperoni);
            color: #ffffff;
            border: none;
            padding: 6px 18px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .pos-body {
            display: grid;
            grid-template-columns: 1fr 420px;
            height: calc(100vh - 60px);
            width: 100%;
        }

        .catalog-section {
            padding: 25px;
            background-color: #fafbfc;
            border-right: 1px solid var(--gris-borde);
            overflow-y: auto;
        }

        .categories-tabs { display: flex; gap: 12px; margin-bottom: 25px; }
        .tab-btn {
            padding: 8px 20px;
            background-color: #ffffff;
            border: 1px solid var(--gris-borde);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--texto-oscuro);
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background-color: var(--verde-oscuro);
            color: #ffffff;
            border-color: var(--verde-oscuro);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .product-card {
            background-color: #ffffff;
            border: 1px solid var(--gris-borde);
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
        .product-name { font-size: 1rem; font-weight: 700; color: var(--texto-oscuro); }
        .product-price { font-size: 1.1rem; font-weight: 700; color: var(--verde-oscuro); }
        .product-desc { font-size: 0.78rem; color: #718096; margin-bottom: 15px; min-height: 36px; }

        .extras-box {
            background-color: #f8fafc;
            border: 1px solid var(--gris-borde);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 15px;
        }
        .extras-title { font-size: 0.7rem; font-weight: 700; color: #4a5568; margin-bottom: 8px; text-transform: uppercase; }
        .extras-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .extra-label { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: #4a5568; cursor: pointer; }

        .btn-add {
            width: 100%;
            background-color: var(--verde-oscuro);
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-add:hover { background-color: #123024; }

        /* Columna Derecha: Ticket de Orden */
        .order-section {
            background-color: #ffffff;
            padding: 20px 25px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            overflow-y: auto;
        }

        .order-top-info {
            border-bottom: 2px solid var(--gris-borde);
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .order-title { font-family: 'Cinzel', serif; font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; }

        .client-inputs-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .field-group { display: flex; flex-direction: column; gap: 4px; }
        .field-group label { font-size: 0.75rem; font-weight: 600; color: #4a5568; }
        .field-group input, .field-group select {
            padding: 8px 10px;
            border: 1px solid var(--gris-borde);
            border-radius: 6px;
            font-size: 0.85rem;
            font-family: inherit;
        }

        .order-items-list {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 430px);
            margin-bottom: 15px;
        }

        .order-item {
            padding: 10px 0;
            border-bottom: 1px dashed var(--gris-borde);
        }

        .item-row { display: flex; justify-content: space-between; font-size: 0.88rem; font-weight: 600; }
        .item-note { font-size: 0.75rem; color: var(--rojo-pepperoni); margin-top: 2px; }
        .btn-remove-item { color: var(--rojo-pepperoni); cursor: pointer; font-size: 0.8rem; font-weight: 700; margin-left: 8px; }

        .order-totals { border-top: 2px solid var(--gris-borde); padding-top: 12px; margin-bottom: 15px; }
        .subtotal-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #718096; margin-bottom: 6px; }
        .total-row { display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; }

        .btn-procesar {
            width: 100%;
            background-color: var(--rojo-pepperoni);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-procesar:hover { background-color: #881616; }

        .alert-success-msg {
            background-color: #C6F6D5;
            color: #22543D;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="pos-wrapper">
        <header class="pos-header">
            <div class="header-brand">
                <h1 class="logo-title">PADRINO'S PIZZA</h1>
                <span class="badge-caja">Caja #1 - Mostrador</span>
            </div>
            <a href="login.php" class="btn-salir">Salir</a>
        </header>

        <div class="pos-body">
            <!-- CATÁLOGO DINÁMICO DESDE BD -->
            <section class="catalog-section">
                <div class="categories-tabs">
                    <button class="tab-btn active" onclick="filtrarCategoria('todas', this)">Todas</button>
                    <?php foreach ($categorias as$cat): ?>
                        <button class="tab-btn" onclick="filtrarCategoria(<?php echo $cat['id_categoria']; ?>, this)">
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="products-grid">
                    <?php foreach ($productos as$p): ?>
                        <div class="product-card product-item" data-categoria="<?php echo $p['id_categoria']; ?>">
                            <div>
                                <div class="product-header">
                                    <span class="product-name"><?php echo htmlspecialchars($p['nombre']); ?></span>
                                    <span class="product-price">$<?php echo number_format($p['precio'], 2); ?></span>
                                </div>
                                <p class="product-desc"><?php echo htmlspecialchars($p['descripcion']); ?></p>

                                <?php if ($p['id_categoria'] == 1): ?>
                                    <div class="extras-box">
                                        <div class="extras-title">Ingredientes / Extras:</div>
                                        <div class="extras-grid">
                                            <label class="extra-label">
                                                <input type="checkbox" class="extra-cb" value="1.00" data-name="Extra Queso"> Extra Queso (+$1.00)
                                            </label>
                                            <label class="extra-label">
                                                <input type="checkbox" class="extra-cb" value="0.75" data-name="Champiñones"> Champiñones (+$0.75)
                                            </label>
                                            <label class="extra-label">
                                                <input type="checkbox" class="extra-cb" value="0.50" data-name="Jalapeño"> Jalapeño (+$0.50)
                                            </label>
                                            <label class="extra-label">
                                                <input type="checkbox" class="extra-cb" value="0.75" data-name="Aceitunas"> Aceitunas (+$0.75)
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="button" class="btn-add" onclick="agregarAlCarrito(<?php echo $p['id_producto']; ?>, '<?php echo addslashes($p['nombre']); ?>', <?php echo$p['precio']; ?>, this)">
                                + Agregar a la Orden
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- TICKET DE LA ORDEN ACTUAL -->
            <form class="order-section" method="POST" action="cajero.php" onsubmit="return validarOrden();">
                <input type="hidden" name="action" value="crear_orden">
                <input type="hidden" name="cart_data" id="cart_data_input">

                <div>
                    <div class="order-top-info">
                        <h2 class="order-title">ORDEN ACTUAL</h2>
                        <?php if (isset($_GET['msg']) &&$_GET['msg'] === 'success'): ?>
                            <div class="alert-success-msg">✔ ¡Orden enviada a Cocina!</div>
                        <?php endif; ?>

                        <!-- DATOS EXIGIDOS POR EL DOCUMENTO -->
                        <div class="client-inputs-grid">
                            <div class="field-group">
                                <label>Nombre del Cliente:</label>
                                <input type="text" name="nombre_cliente" placeholder="Ej. Juan Pérez" required>
                            </div>
                            <div class="field-group">
                                <label>Hora de Entrega:</label>
                                <input type="time" name="hora_entrega" value="<?php echo date('H:i', strtotime('+20 minutes')); ?>" required>
                            </div>
                        </div>

                        <div class="field-group">
                            <label>Estado de Pago:</label>
                            <select name="estado_pago">
                                <option value="Pagado">Pagado</option>
                                <option value="Sin pagar" selected>Sin pagar</option>
                            </select>
                        </div>
                    </div>

                    <div class="order-items-list" id="ticket_items">
                        <p style="text-align: center; color: #a0aec0; font-size: 0.85rem; margin-top: 30px;" id="empty_cart_msg">
                            No hay productos en la orden.
                        </p>
                    </div>
                </div>

                <div>
                    <div class="order-totals">
                        <div class="subtotal-row">
                            <span>Subtotal:</span>
                            <span id="subtotal_txt">$0.00</span>
                        </div>
                        <div class="total-row">
                            <span>Total a Pagar:</span>
                            <span id="total_txt">$0.00</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-procesar">Enviar Orden a Cocina 🍕</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let carrito = [];

        function filtrarCategoria(catId, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.product-item').forEach(card => {
                if (catId === 'todas' || card.getAttribute('data-categoria') == catId) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function agregarAlCarrito(idProducto, nombre, precioBase, btnElement) {
            let card = btnElement.closest('.product-card');
            let extrasCbs = card.querySelectorAll('.extra-cb:checked');
            let extraMonto = 0;
            let extrasNombres = [];

            extrasCbs.forEach(cb => {
                extraMonto += parseFloat(cb.value);
                extrasNombres.push(cb.getAttribute('data-name'));
            });

            let precioUnitarioFinal = precioBase + extraMonto;
            let extrasTexto = extrasNombres.length > 0 ? "Extra: " + extrasNombres.join(', ') : "";

            let itemExistente = carrito.find(i => i.id_producto === idProducto && i.extras_texto === extrasTexto);

            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                carrito.push({
                    id_producto: idProducto,
                    nombre: nombre,
                    cantidad: 1,
                    precio_unitario: precioUnitarioFinal,
                    extras_texto: extrasTexto
                });
            }

            card.querySelectorAll('.extra-cb').forEach(cb => cb.checked = false);
            renderizarTicket();
        }

        function eliminarItem(index) {
            carrito.splice(index, 1);
            renderizarTicket();
        }

        function renderizarTicket() {
            let container = document.getElementById('ticket_items');
            container.innerHTML = '';

            if (carrito.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #a0aec0; font-size: 0.85rem; margin-top: 30px;">No hay productos en la orden.</p>';
                document.getElementById('subtotal_txt').innerText = '$0.00';
                document.getElementById('total_txt').innerText = '$0.00';
                return;
            }

            let totalGeneral = 0;

            carrito.forEach((item, index) => {
                let subtotalItem = item.cantidad * item.precio_unitario;
                totalGeneral += subtotalItem;

                let div = document.createElement('div');
                div.className = 'order-item';
                div.innerHTML = `
                    <div class="item-row">
                        <span>${item.cantidad}x ${item.nombre} <span class="btn-remove-item" onclick="eliminarItem(${index})">(✕)</span></span>
                        <span>$${subtotalItem.toFixed(2)}</span>
                    </div>
                    ${item.extras_texto ? `<div class="item-note">${item.extras_texto}</div>` : ''}
                `;
                container.appendChild(div);
            });

            document.getElementById('subtotal_txt').innerText = '$' + totalGeneral.toFixed(2);
            document.getElementById('total_txt').innerText = '$' + totalGeneral.toFixed(2);
        }

        function validarOrden() {
            if (carrito.length === 0) {
                alert('Por favor agrega al menos un producto a la orden.');
                return false;
            }
            document.getElementById('cart_data_input').value = JSON.stringify(carrito);
            return true;
        }
    </script>
</body>
</html>