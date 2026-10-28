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

    // OBTENER EXTRAS AUTOMÁTICOS PARA PIZZAS
    public static function getExtrasPorProducto() {
        try {
            $db = Database::getConnection();

            // 1. Obtener la lista completa de todos los ingredientes extras activos en la BD
            $stmtTodosExtras = $db->query("SELECT id_extra, nombre, precio FROM extras WHERE activo = 1");
            $todosLosExtras = $stmtTodosExtras->fetchAll();

            // 2. Obtener productos para saber su categoría
            $productos = self::getProductosActivos();
            $extras = [];

            // 3. Consultar la tabla relacional producto_extra para productos con extras asignados individualmente
            $stmtEspecificos = $db->query("
                SELECT pe.id_producto, e.id_extra, e.nombre, e.precio 
                FROM producto_extra pe 
                JOIN extras e ON e.id_extra = pe.id_extra 
                WHERE e.activo = 1
            ");
            $extrasEspecificos = [];
            while ($row = $stmtEspecificos->fetch()) {
                $extrasEspecificos[$row['id_producto']][] = $row;
            }

            // 4. Asignación inteligente por producto
            foreach ($productos as $prod) {
                $idProd = $prod['id_producto'];
                $idCat  = intval($prod['id_categoria']);

                // Si es Pizza (id_categoria == 1), le asignamos AUTOMÁTICAMENTE los 4 extras generales
                if ($idCat === 1 || stripos($prod['categoria_nombre'], 'pizza') !== false) {
                    $extras[$idProd] = $todosLosExtras;
                } else {
                    // Si no es pizza, mantiene los extras específicos asignados en producto_extra (si tiene)
                    $extras[$idProd] = $extrasEspecificos[$idProd] ?? [];
                }
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