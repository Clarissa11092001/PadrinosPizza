<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/CatalogoModel.php';

// Solo administradores pueden gestionar el menú
if ($_SESSION['id_rol'] != 1) {
    header("Location: PosController.php");
    exit();
}

// Guardar o Actualizar Producto vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = !empty($_POST['id_producto']) ? intval($_POST['id_producto']) : null;
    $nombre = trim($_POST['nombre'] ?? '');
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $precio_base = floatval($_POST['precio_base'] ?? 0);
    $activo = intval($_POST['activo'] ?? 1);
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!empty($nombre) && $id_categoria > 0 && $precio_base > 0) {
        if ($id_producto) {
            CatalogoModel::actualizarProducto($id_producto, $id_categoria, $nombre, $descripcion, $precio_base, $activo);
        } else {
            CatalogoModel::guardarProducto($id_categoria, $nombre, $descripcion, $precio_base, $activo);
        }
    }
    header("Location: CatalogoController.php");
    exit();
}

// Cargar listas para la vista
$categorias = CatalogoModel::getCategoriasActivas();
$productos = CatalogoModel::getProductosActivos();

require_once __DIR__ . '/../views/gerencia/Gestion_Catalogo.php';
?>