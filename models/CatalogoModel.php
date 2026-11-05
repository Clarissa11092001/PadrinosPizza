<?php
require_once __DIR__ . '/../database/conexion.php';

class CatalogoModel {

    public static function getCategoriasActivas() {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY id_categoria ASC")->fetchAll();
    }

    public static function getProductosActivos() {
        $db = Database::getConnection();
        // Consultamos la columna real 'precio'
        return $db->query("
            SELECT p.*, 
                   p.precio AS precio_mostrar,
                   c.nombre AS categoria_nombre 
            FROM productos p 
            JOIN categorias c ON c.id_categoria = p.id_categoria 
            ORDER BY p.id_producto DESC
        ")->fetchAll();
    }

    // EXTRAS DISPONIBLES POR PRODUCTO
    // Regla del negocio: las PIZZAS (categoría 1, o categoría cuyo nombre contiene "pizza") llevan
    // TODOS los ingredientes extra activos de la tabla `extras`. El resto de productos no lleva extras.
    // (OrdenModel::crearOrden valida con esta misma regla.)
    public static function getExtrasPorProducto() {
        try {
            $db = Database::getConnection();
            $todosLosExtras = $db->query("SELECT id_extra, nombre, precio FROM extras WHERE activo = 1 ORDER BY id_extra")->fetchAll();

            $extras = [];
            foreach (self::getProductosActivos() as $prod) {
                $esPizza = intval($prod['id_categoria']) === 1 || stripos($prod['categoria_nombre'], 'pizza') !== false;
                $extras[$prod['id_producto']] = $esPizza ? $todosLosExtras : [];
            }
            return $extras;

        } catch (Exception $e) {
            return [];
        }
    }

    // Guardar nuevo producto (Mantenido intacto para Gerencia)
    public static function guardarProducto($id_categoria, $nombre, $descripcion, $precio, $activo) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO productos (id_categoria, nombre, descripcion, precio, activo) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$id_categoria, $nombre, $descripcion, $precio, $activo]);
    }

    // Actualizar producto existente (Mantenido intacto para Gerencia)
    public static function actualizarProducto($id_producto, $id_categoria, $nombre, $descripcion, $precio, $activo) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE productos 
            SET id_categoria = ?, nombre = ?, descripcion = ?, precio = ?, activo = ? 
            WHERE id_producto = ?
        ");
        return $stmt->execute([$id_categoria, $nombre, $descripcion, $precio, $activo, $id_producto]);
    }
}
?>