-- =====================================================================
--  MIGRACIÓN 001 · Padrino's Pizza
--  1) Quita lo de entregas a domicilio de la tabla `ordenes`
--  2) Quita la tabla `producto_extra` (las pizzas llevan siempre los mismos extras)
--  3) Agrega la categoría "Complementos" con Pan con ajo y Palitroques
--
--  ANTES DE EJECUTAR
--    a) Exporta la base (phpMyAdmin > Exportar) como respaldo.
--    b) Copia PRIMERO el código nuevo del proyecto. El código nuevo funciona con la base
--       vieja y con la nueva; el código viejo NO funciona con la base nueva.
--
--  CÓMO EJECUTAR
--    phpMyAdmin > selecciona la base padrinos_pizza > pestaña SQL > pega TODO > Continuar.
--    Es seguro volver a ejecutarla: lo que ya se hizo se omite.
--
--  DESPUÉS: vuelve a exportar la base y reemplaza "base de datos padrinos_pizza.txt" en el repo.
-- =====================================================================


-- ---------------------------------------------------------------------
-- A) Vistas que usaban la columna `origen` (se recrean sin ella)
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_ordenes_cocina;
CREATE VIEW v_ordenes_cocina AS
SELECT o.id_orden AS id_orden,
       o.codigo AS codigo,
       e.nombre AS estado,
       e.orden_flujo AS orden_flujo,
       o.fecha_creacion AS fecha_creacion,
       TIMESTAMPDIFF(MINUTE, o.fecha_creacion, NOW()) AS minutos_transcurridos,
       d.id_detalle AS id_detalle,
       p.nombre AS producto,
       d.cantidad AS cantidad,
       d.notas AS notas,
       (SELECT GROUP_CONCAT(x.nombre ORDER BY x.nombre ASC SEPARATOR ', ')
          FROM detalle_orden_extra dx
          JOIN extras x ON x.id_extra = dx.id_extra
         WHERE dx.id_detalle = d.id_detalle) AS extras
FROM ordenes o
JOIN estados_orden e ON e.id_estado = o.id_estado
JOIN detalle_orden d ON d.id_orden = o.id_orden
JOIN productos p ON p.id_producto = d.id_producto
WHERE e.nombre IN ('Pendiente', 'En preparación', 'Lista')
ORDER BY o.fecha_creacion ASC, d.id_detalle ASC;

DROP VIEW IF EXISTS v_ventas_diarias;
CREATE VIEW v_ventas_diarias AS
SELECT CAST(o.fecha_creacion AS DATE) AS fecha,
       COUNT(0) AS num_ordenes,
       SUM(o.total) AS total_vendido
FROM ordenes o
JOIN estados_orden e ON e.id_estado = o.id_estado AND e.nombre <> 'Cancelada'
GROUP BY CAST(o.fecha_creacion AS DATE);


-- ---------------------------------------------------------------------
-- B) Columnas de domicilio / canales externos en `ordenes`
--      id_cliente      -> registro de clientes (no se usa: el POS guarda el nombre como texto)
--      origen          -> canal de la orden (Mostrador / delivery / app); todo es Mostrador
--      codigo_externo  -> código de apps de delivery
--
--    Este bloque NO supone nombres: busca la llave foránea real y revisa si cada columna
--    existe antes de borrarla, así se puede ejecutar aunque ya se haya hecho una parte.
-- ---------------------------------------------------------------------

-- B1) Quitar la llave foránea de id_cliente, se llame como se llame
SET @fk := (SELECT CONSTRAINT_NAME
              FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'ordenes'
               AND COLUMN_NAME = 'id_cliente'
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1);
SET @sql := IF(@fk IS NULL,
               'SELECT ''No hay llave foranea de id_cliente que quitar'' AS aviso',
               CONCAT('ALTER TABLE ordenes DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- B2) Quitar la columna id_cliente (si existe)
SET @sql := IF(EXISTS (SELECT 1 FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ordenes' AND COLUMN_NAME = 'id_cliente'),
               'ALTER TABLE ordenes DROP COLUMN id_cliente',
               'SELECT ''id_cliente ya no existe'' AS aviso');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- B3) Quitar la columna origen (si existe)
SET @sql := IF(EXISTS (SELECT 1 FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ordenes' AND COLUMN_NAME = 'origen'),
               'ALTER TABLE ordenes DROP COLUMN origen',
               'SELECT ''origen ya no existe'' AS aviso');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- B4) Quitar la columna codigo_externo (si existe)
SET @sql := IF(EXISTS (SELECT 1 FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ordenes' AND COLUMN_NAME = 'codigo_externo'),
               'ALTER TABLE ordenes DROP COLUMN codigo_externo',
               'SELECT ''codigo_externo ya no existe'' AS aviso');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tabla de clientes (ya nadie la referencia). Si quieres conservarla para un futuro módulo
-- de clientes frecuentes, borra SOLO la siguiente línea antes de ejecutar.
DROP TABLE IF EXISTS clientes;


-- ---------------------------------------------------------------------
-- C) producto_extra: sobraba. Los extras de las pizzas son siempre los de la tabla `extras`.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS producto_extra;


-- ---------------------------------------------------------------------
-- D) Categoría nueva "Complementos" + sus dos productos
--    OJO: los precios son PROVISIONALES. Ajústalos desde Gerencia > Catálogo.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO categorias (nombre, activo) VALUES ('Complementos', 1);

INSERT IGNORE INTO productos (id_categoria, nombre, descripcion, precio, activo)
SELECT c.id_categoria, 'Pan con ajo', 'Pan horneado con mantequilla y ajo.', 2.50, 1
FROM categorias c WHERE c.nombre = 'Complementos';

INSERT IGNORE INTO productos (id_categoria, nombre, descripcion, precio, activo)
SELECT c.id_categoria, 'Palitroques', 'Palitos de pan horneados, dorados y crujientes.', 3.00, 1
FROM categorias c WHERE c.nombre = 'Complementos';
