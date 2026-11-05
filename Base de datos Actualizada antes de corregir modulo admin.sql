-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-11-2026 a las 01:47:07
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `padrinos_pizza`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `activo`) VALUES
(1, 'Pizzas', 1),
(2, 'Bebidas', 1),
(3, 'Postres', 1),
(4, 'Complementos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cierres_caja`
--

DROP TABLE IF EXISTS `cierres_caja`;
CREATE TABLE IF NOT EXISTS `cierres_caja` (
  `id_cierre` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` int UNSIGNED NOT NULL,
  `fecha_apertura` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_esperado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_declarado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('abierta','cerrada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierta',
  PRIMARY KEY (`id_cierre`),
  KEY `fk_cierre_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cierres_caja`
--

INSERT INTO `cierres_caja` (`id_cierre`, `id_usuario`, `fecha_apertura`, `fecha_cierre`, `monto_inicial`, `monto_esperado`, `monto_declarado`, `diferencia`, `observaciones`, `estado`) VALUES
(1, 2, '2026-10-30 01:21:03', '2026-11-03 23:15:14', 50.00, 201.05, 201.05, 0.00, '', 'cerrada'),
(2, 1, '2026-10-30 09:54:46', NULL, 50.00, 0.00, 0.00, 0.00, NULL, 'abierta'),
(3, 2, '2026-11-03 23:15:32', '2026-11-04 01:23:22', 50.00, 50.00, 50.00, 0.00, '', 'cerrada'),
(4, 2, '2026-11-04 01:24:14', '2026-11-04 01:43:26', 50.00, 80.49, 80.00, -0.49, 'los .49 son los que no le cobro al cliente que le ajustaba', 'cerrada'),
(5, 2, '2026-11-04 07:44:54', '2026-11-04 19:30:18', 50.00, 90.23, 90.23, 0.00, '', 'cerrada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

DROP TABLE IF EXISTS `detalle_orden`;
CREATE TABLE IF NOT EXISTS `detalle_orden` (
  `id_detalle` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_orden` int UNSIGNED NOT NULL,
  `id_producto` int UNSIGNED NOT NULL,
  `cantidad` smallint UNSIGNED NOT NULL,
  `precio_unitario` decimal(8,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notas` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `fk_det_orden` (`id_orden`),
  KEY `fk_det_producto` (`id_producto`)
) ;

--
-- Volcado de datos para la tabla `detalle_orden`
--

INSERT INTO `detalle_orden` (`id_detalle`, `id_orden`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`, `notas`) VALUES
(1, 1, 4, 1, 8.95, 8.95, NULL),
(2, 2, 5, 1, 5.00, 5.00, NULL),
(3, 2, 1, 1, 7.50, 7.50, NULL),
(4, 2, 3, 1, 2.00, 2.00, NULL),
(5, 3, 2, 1, 15.99, 15.99, NULL),
(6, 4, 2, 1, 12.99, 12.99, NULL),
(7, 4, 2, 1, 13.49, 13.49, NULL),
(8, 5, 2, 1, 13.74, 13.74, NULL),
(9, 5, 1, 1, 9.00, 9.00, NULL),
(10, 6, 4, 1, 10.45, 10.45, NULL),
(11, 6, 5, 1, 5.00, 5.00, NULL),
(12, 7, 3, 1, 2.00, 2.00, NULL),
(13, 8, 1, 1, 7.50, 7.50, NULL),
(14, 9, 2, 1, 14.99, 14.99, NULL),
(15, 9, 3, 1, 2.00, 2.00, NULL),
(16, 9, 3, 1, 2.00, 2.00, NULL),
(17, 10, 5, 1, 5.00, 5.00, NULL),
(18, 10, 5, 1, 5.00, 5.00, NULL),
(19, 10, 4, 1, 8.45, 8.45, NULL),
(20, 11, 2, 1, 14.24, 14.24, NULL),
(21, 11, 1, 1, 7.50, 7.50, NULL),
(22, 12, 1, 1, 8.75, 8.75, NULL),
(23, 13, 2, 1, 12.99, 12.99, NULL),
(24, 13, 2, 1, 14.24, 14.24, NULL),
(25, 13, 6, 1, 2.50, 2.50, NULL),
(26, 13, 7, 1, 3.00, 3.00, NULL),
(27, 13, 1, 1, 7.50, 7.50, NULL);

--
-- Disparadores `detalle_orden`
--
DROP TRIGGER IF EXISTS `trg_detalle_ad`;
DELIMITER $$
CREATE TRIGGER `trg_detalle_ad` AFTER DELETE ON `detalle_orden` FOR EACH ROW BEGIN
    UPDATE ordenes
    SET subtotal = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_orden WHERE id_orden = OLD.id_orden)
    WHERE id_orden = OLD.id_orden;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_detalle_ai`;
DELIMITER $$
CREATE TRIGGER `trg_detalle_ai` AFTER INSERT ON `detalle_orden` FOR EACH ROW BEGIN
    UPDATE ordenes
    SET subtotal = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_orden WHERE id_orden = NEW.id_orden)
    WHERE id_orden = NEW.id_orden;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_detalle_au`;
DELIMITER $$
CREATE TRIGGER `trg_detalle_au` AFTER UPDATE ON `detalle_orden` FOR EACH ROW BEGIN
    UPDATE ordenes
    SET subtotal = (SELECT COALESCE(SUM(subtotal),0) FROM detalle_orden WHERE id_orden = NEW.id_orden)
    WHERE id_orden = NEW.id_orden;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_detalle_bi`;
DELIMITER $$
CREATE TRIGGER `trg_detalle_bi` BEFORE INSERT ON `detalle_orden` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_detalle_bu`;
DELIMITER $$
CREATE TRIGGER `trg_detalle_bu` BEFORE UPDATE ON `detalle_orden` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_extra`
--

DROP TABLE IF EXISTS `detalle_orden_extra`;
CREATE TABLE IF NOT EXISTS `detalle_orden_extra` (
  `id_detalle` int UNSIGNED NOT NULL,
  `id_extra` smallint UNSIGNED NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id_detalle`,`id_extra`),
  KEY `fk_doe_extra` (`id_extra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_orden_extra`
--

INSERT INTO `detalle_orden_extra` (`id_detalle`, `id_extra`, `precio`) VALUES
(1, 3, 0.50),
(5, 1, 1.00),
(5, 2, 0.75),
(5, 3, 0.50),
(5, 4, 0.75),
(7, 3, 0.50),
(8, 2, 0.75),
(9, 1, 1.00),
(9, 3, 0.50),
(10, 2, 0.75),
(10, 3, 0.50),
(10, 4, 0.75),
(14, 2, 0.75),
(14, 3, 0.50),
(14, 4, 0.75),
(20, 2, 0.75),
(20, 3, 0.50),
(22, 2, 0.75),
(22, 3, 0.50),
(24, 2, 0.75),
(24, 3, 0.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_orden`
--

DROP TABLE IF EXISTS `estados_orden`;
CREATE TABLE IF NOT EXISTS `estados_orden` (
  `id_estado` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_hex` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#A61C1C',
  `orden_flujo` tinyint UNSIGNED NOT NULL,
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados_orden`
--

INSERT INTO `estados_orden` (`id_estado`, `nombre`, `color_hex`, `orden_flujo`) VALUES
(1, 'Pendiente', '#A61C1C', 1),
(2, 'En preparación', '#F4C430', 2),
(3, 'Lista', '#1B4332', 3),
(4, 'Entregada', '#D4AF37', 4),
(5, 'Cancelada', '#6C757D', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extras`
--

DROP TABLE IF EXISTS `extras`;
CREATE TABLE IF NOT EXISTS `extras` (
  `id_extra` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(6,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_extra`),
  UNIQUE KEY `nombre` (`nombre`)
) ;

--
-- Volcado de datos para la tabla `extras`
--

INSERT INTO `extras` (`id_extra`, `nombre`, `precio`, `activo`) VALUES
(1, 'Extra Queso', 1.00, 1),
(2, 'Champiñones', 0.75, 1),
(3, 'Jalapeño', 0.50, 1),
(4, 'Aceitunas', 0.75, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

DROP TABLE IF EXISTS `metodos_pago`;
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id_metodo_pago` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_metodo_pago`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id_metodo_pago`, `nombre`) VALUES
(1, 'Efectivo'),
(3, 'Pago en app'),
(2, 'Tarjeta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

DROP TABLE IF EXISTS `ordenes`;
CREATE TABLE IF NOT EXISTS `ordenes` (
  `id_orden` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_usuario` int UNSIGNED NOT NULL,
  `id_estado` tinyint UNSIGNED NOT NULL,
  `cliente` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hora_entrega` time DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo_anulacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_usuario_anula` int UNSIGNED DEFAULT NULL,
  `fecha_anulacion` datetime DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_orden`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `fk_ord_usuario` (`id_usuario`),
  KEY `fk_ord_anula` (`id_usuario_anula`),
  KEY `idx_ord_fecha` (`fecha_creacion`),
  KEY `idx_ord_estado` (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id_orden`, `codigo`, `id_usuario`, `id_estado`, `cliente`, `hora_entrega`, `subtotal`, `descuento`, `total`, `observaciones`, `fecha_creacion`, `motivo_anulacion`, `id_usuario_anula`, `fecha_anulacion`, `fecha_actualizacion`) VALUES
(1, 'ORD-0001', 2, 4, 'Aldo Isai', '07:30:00', 8.95, 0.00, 8.95, '', '2026-10-30 01:21:36', NULL, NULL, NULL, '2026-10-30 07:42:58'),
(2, 'ORD-0002', 2, 4, 'Samuel', '12:00:00', 14.50, 0.00, 14.50, '', '2026-10-30 01:22:06', NULL, NULL, NULL, '2026-10-30 07:43:07'),
(3, 'ORD-0003', 2, 4, 'Marcos', NULL, 15.99, 0.00, 15.99, '', '2026-10-30 01:26:02', NULL, NULL, NULL, '2026-10-30 08:38:20'),
(4, 'ORD-0004', 2, 4, 'Aldo Isai', '07:00:00', 26.48, 0.00, 26.48, '', '2026-10-30 09:31:21', NULL, NULL, NULL, '2026-10-30 16:25:52'),
(5, 'ORD-0005', 2, 4, 'Diego', NULL, 22.74, 0.00, 22.74, '', '2026-10-30 10:05:23', NULL, NULL, NULL, '2026-10-30 16:27:06'),
(6, 'ORD-0006', 2, 4, 'Aldo Isai', NULL, 15.45, 0.00, 15.45, '', '2026-10-30 10:27:37', NULL, NULL, NULL, '2026-11-04 05:15:00'),
(7, 'ORD-0007', 2, 4, 'Eduardo', NULL, 2.00, 0.00, 2.00, '', '2026-10-30 10:29:20', NULL, NULL, NULL, '2026-11-04 05:14:58'),
(8, 'ORD-0008', 2, 4, 'René', '08:30:00', 7.50, 0.00, 7.50, '', '2026-10-30 10:30:32', NULL, NULL, NULL, '2026-11-04 05:14:54'),
(9, 'ORD-0009', 2, 4, 'Alejandro', NULL, 18.99, 0.00, 18.99, '', '2026-10-30 10:35:09', NULL, NULL, NULL, '2026-11-04 05:14:52'),
(10, 'ORD-0010', 2, 4, 'kenia', NULL, 18.45, 0.00, 18.45, '', '2026-10-30 10:37:35', NULL, NULL, NULL, '2026-11-04 05:14:47'),
(11, 'ORD-0011', 2, 4, 'Aldo Galindo', '20:00:00', 21.74, 0.00, 21.74, '', '2026-11-04 01:26:02', NULL, NULL, NULL, '2026-11-04 07:30:57'),
(12, 'ORD-0012', 2, 4, 'Marcos Josue', '12:00:00', 8.75, 0.00, 8.75, 'quiere que este bien caliente cuando venga a traerla', '2026-11-04 01:28:12', NULL, NULL, NULL, '2026-11-04 07:31:11'),
(13, 'ORD-0013', 2, 4, 'Aldo Isai', '09:00:00', 40.23, 0.00, 40.23, '', '2026-11-04 19:10:59', NULL, NULL, NULL, '2026-11-05 01:30:02');

--
-- Disparadores `ordenes`
--
DROP TRIGGER IF EXISTS `trg_orden_bi`;
DELIMITER $$
CREATE TRIGGER `trg_orden_bi` BEFORE INSERT ON `ordenes` FOR EACH ROW BEGIN
    SET NEW.total = GREATEST(NEW.subtotal - NEW.descuento, 0);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_orden_bu`;
DELIMITER $$
CREATE TRIGGER `trg_orden_bu` BEFORE UPDATE ON `ordenes` FOR EACH ROW BEGIN
    SET NEW.total = GREATEST(NEW.subtotal - NEW.descuento, 0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id_pago` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_orden` int UNSIGNED NOT NULL,
  `id_metodo_pago` tinyint UNSIGNED NOT NULL,
  `id_usuario` int UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago`),
  KEY `fk_pago_orden` (`id_orden`),
  KEY `fk_pago_metodo` (`id_metodo_pago`),
  KEY `fk_pago_usuario` (`id_usuario`)
) ;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_orden`, `id_metodo_pago`, `id_usuario`, `monto`, `fecha_pago`) VALUES
(1, 1, 2, 2, 8.95, '2026-10-30 01:21:36'),
(2, 3, 1, 2, 15.99, '2026-10-30 01:26:02'),
(3, 2, 3, 2, 14.50, '2026-10-30 01:43:07'),
(4, 5, 1, 2, 22.74, '2026-10-30 10:05:23'),
(5, 4, 1, 2, 26.48, '2026-10-30 10:25:52'),
(6, 6, 3, 2, 15.45, '2026-10-30 10:27:37'),
(7, 7, 1, 2, 2.00, '2026-10-30 10:29:20'),
(8, 10, 2, 2, 18.45, '2026-11-03 23:14:47'),
(9, 9, 1, 2, 18.99, '2026-11-03 23:14:52'),
(10, 8, 1, 2, 7.50, '2026-11-03 23:14:54'),
(11, 12, 2, 2, 8.75, '2026-11-04 01:28:12'),
(12, 11, 3, 2, 21.74, '2026-11-04 01:30:57'),
(13, 13, 2, 2, 40.23, '2026-11-04 19:11:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_categoria` smallint UNSIGNED NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio` decimal(8,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `uq_producto_nombre` (`id_categoria`,`nombre`)
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `nombre`, `descripcion`, `precio`, `activo`) VALUES
(1, 1, 'Pizza Pepperoni', 'Salsa de tomate, queso mozzarella y pepperoni artesanal.', 7.50, 1),
(2, 1, 'Pizza Carnívora', 'Pepperoni, jamón, salchicha italiana y tocino crujiente.', 12.99, 1),
(3, 2, 'Soda Coca-Cola 1.25L', 'Gaseosa Coca-Cola de 1.25 litros.', 2.00, 1),
(4, 1, 'pizza de segunda', 'pizza normal pero con algun problema en el proceso de preparacion', 8.45, 0),
(5, 3, 'Tres Leches', 'una porcion del postre 3 leches listo para comer', 5.00, 0),
(6, 4, 'Pan con ajo', 'Pan horneado con mantequilla y ajo.', 2.50, 1),
(7, 4, 'Palitroques', 'Palitos de pan horneados, dorados y crujientes.', 3.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total'),
(2, 'Cajero', 'Registra órdenes y cobra'),
(3, 'Cocina', 'Monitor de cocina');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_rol` tinyint UNSIGNED NOT NULL,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `apellido`, `username`, `email`, `password_hash`, `activo`, `creado_en`) VALUES
(1, 1, 'Admin', 'General', 'admin', NULL, '$2y$12$.v8mO0GLMUpMAeJTo89Pp.nvv605GRUSXEcUviKJO7TFMHHc7ADoG', 1, '2026-10-21 17:47:25'),
(2, 2, 'Caja', 'Uno', 'cajero', NULL, '$2y$12$mmrnR3y6UOpb5sgHMRxVVedy1F1kQOfkMyYBnFKTPc4XEMoVEx1gO', 1, '2026-10-21 17:47:25'),
(3, 3, 'Cocina', 'Uno', 'cocina', NULL, '$2y$12$liZ8cOf8yhlSBsY.yyvh6Oq7Y5sNCIg1MUdLCyTFBxLABjEh00nt6', 1, '2026-10-21 17:47:25');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_anuladas_diarias`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_anuladas_diarias`;
CREATE TABLE IF NOT EXISTS `v_anuladas_diarias` (
`fecha` date
,`num_anuladas` bigint
,`monto_anulado` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ordenes_cocina`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_ordenes_cocina`;
CREATE TABLE IF NOT EXISTS `v_ordenes_cocina` (
`id_orden` int unsigned
,`codigo` varchar(20)
,`estado` varchar(30)
,`orden_flujo` tinyint unsigned
,`fecha_creacion` datetime
,`minutos_transcurridos` bigint
,`id_detalle` int unsigned
,`producto` varchar(80)
,`cantidad` smallint unsigned
,`notas` varchar(150)
,`extras` text
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ordenes_pago`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_ordenes_pago`;
CREATE TABLE IF NOT EXISTS `v_ordenes_pago` (
`id_orden` int unsigned
,`codigo` varchar(20)
,`total` decimal(10,2)
,`pagado` decimal(32,2)
,`pendiente` decimal(33,2)
,`estado_pago` varchar(17)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_productos_mas_vendidos`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_productos_mas_vendidos`;
CREATE TABLE IF NOT EXISTS `v_productos_mas_vendidos` (
`fecha` date
,`id_producto` int unsigned
,`producto` varchar(80)
,`unidades` decimal(27,0)
,`ingresos` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ventas_diarias`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_ventas_diarias`;
CREATE TABLE IF NOT EXISTS `v_ventas_diarias` (
`fecha` date
,`num_ordenes` bigint
,`total_vendido` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ventas_por_categoria`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_ventas_por_categoria`;
CREATE TABLE IF NOT EXISTS `v_ventas_por_categoria` (
`fecha` date
,`id_categoria` smallint unsigned
,`categoria` varchar(50)
,`cantidad_vendida` decimal(27,0)
,`ingreso_subtotal` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_anuladas_diarias`
--
DROP TABLE IF EXISTS `v_anuladas_diarias`;

DROP VIEW IF EXISTS `v_anuladas_diarias`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_anuladas_diarias`  AS SELECT cast(`o`.`fecha_creacion` as date) AS `fecha`, count(0) AS `num_anuladas`, sum(`o`.`total`) AS `monto_anulado` FROM (`ordenes` `o` join `estados_orden` `e` on(((`e`.`id_estado` = `o`.`id_estado`) and (`e`.`nombre` = 'Cancelada')))) GROUP BY cast(`o`.`fecha_creacion` as date) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ordenes_cocina`
--
DROP TABLE IF EXISTS `v_ordenes_cocina`;

DROP VIEW IF EXISTS `v_ordenes_cocina`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ordenes_cocina`  AS SELECT `o`.`id_orden` AS `id_orden`, `o`.`codigo` AS `codigo`, `e`.`nombre` AS `estado`, `e`.`orden_flujo` AS `orden_flujo`, `o`.`fecha_creacion` AS `fecha_creacion`, timestampdiff(MINUTE,`o`.`fecha_creacion`,now()) AS `minutos_transcurridos`, `d`.`id_detalle` AS `id_detalle`, `p`.`nombre` AS `producto`, `d`.`cantidad` AS `cantidad`, `d`.`notas` AS `notas`, (select group_concat(`x`.`nombre` order by `x`.`nombre` ASC separator ', ') from (`detalle_orden_extra` `dx` join `extras` `x` on((`x`.`id_extra` = `dx`.`id_extra`))) where (`dx`.`id_detalle` = `d`.`id_detalle`)) AS `extras` FROM (((`ordenes` `o` join `estados_orden` `e` on((`e`.`id_estado` = `o`.`id_estado`))) join `detalle_orden` `d` on((`d`.`id_orden` = `o`.`id_orden`))) join `productos` `p` on((`p`.`id_producto` = `d`.`id_producto`))) WHERE (`e`.`nombre` in ('Pendiente','En preparación','Lista')) ORDER BY `o`.`fecha_creacion` ASC, `d`.`id_detalle` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ordenes_pago`
--
DROP TABLE IF EXISTS `v_ordenes_pago`;

DROP VIEW IF EXISTS `v_ordenes_pago`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ordenes_pago`  AS SELECT `o`.`id_orden` AS `id_orden`, `o`.`codigo` AS `codigo`, `o`.`total` AS `total`, coalesce(sum(`pg`.`monto`),0) AS `pagado`, greatest((`o`.`total` - coalesce(sum(`pg`.`monto`),0)),0) AS `pendiente`, (case when ((`o`.`total` > 0) and (coalesce(sum(`pg`.`monto`),0) >= `o`.`total`)) then 'Pagada' else 'Pendiente de pago' end) AS `estado_pago` FROM (`ordenes` `o` left join `pagos` `pg` on((`pg`.`id_orden` = `o`.`id_orden`))) GROUP BY `o`.`id_orden`, `o`.`codigo`, `o`.`total` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_productos_mas_vendidos`
--
DROP TABLE IF EXISTS `v_productos_mas_vendidos`;

DROP VIEW IF EXISTS `v_productos_mas_vendidos`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_productos_mas_vendidos`  AS SELECT cast(`o`.`fecha_creacion` as date) AS `fecha`, `p`.`id_producto` AS `id_producto`, `p`.`nombre` AS `producto`, sum(`d`.`cantidad`) AS `unidades`, sum(`d`.`subtotal`) AS `ingresos` FROM (((`detalle_orden` `d` join `ordenes` `o` on((`o`.`id_orden` = `d`.`id_orden`))) join `estados_orden` `e` on(((`e`.`id_estado` = `o`.`id_estado`) and (`e`.`nombre` <> 'Cancelada')))) join `productos` `p` on((`p`.`id_producto` = `d`.`id_producto`))) GROUP BY cast(`o`.`fecha_creacion` as date), `p`.`id_producto`, `p`.`nombre` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ventas_diarias`
--
DROP TABLE IF EXISTS `v_ventas_diarias`;

DROP VIEW IF EXISTS `v_ventas_diarias`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_diarias`  AS SELECT cast(`o`.`fecha_creacion` as date) AS `fecha`, count(0) AS `num_ordenes`, sum(`o`.`total`) AS `total_vendido` FROM (`ordenes` `o` join `estados_orden` `e` on(((`e`.`id_estado` = `o`.`id_estado`) and (`e`.`nombre` <> 'Cancelada')))) GROUP BY cast(`o`.`fecha_creacion` as date) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ventas_por_categoria`
--
DROP TABLE IF EXISTS `v_ventas_por_categoria`;

DROP VIEW IF EXISTS `v_ventas_por_categoria`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_por_categoria`  AS SELECT cast(`o`.`fecha_creacion` as date) AS `fecha`, `c`.`id_categoria` AS `id_categoria`, `c`.`nombre` AS `categoria`, sum(`d`.`cantidad`) AS `cantidad_vendida`, sum(`d`.`subtotal`) AS `ingreso_subtotal` FROM ((((`detalle_orden` `d` join `ordenes` `o` on((`o`.`id_orden` = `d`.`id_orden`))) join `estados_orden` `e` on(((`e`.`id_estado` = `o`.`id_estado`) and (`e`.`nombre` <> 'Cancelada')))) join `productos` `p` on((`p`.`id_producto` = `d`.`id_producto`))) join `categorias` `c` on((`c`.`id_categoria` = `p`.`id_categoria`))) GROUP BY cast(`o`.`fecha_creacion` as date), `c`.`id_categoria`, `c`.`nombre` ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cierres_caja`
--
ALTER TABLE `cierres_caja`
  ADD CONSTRAINT `fk_cierre_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `fk_det_orden` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_det_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `detalle_orden_extra`
--
ALTER TABLE `detalle_orden_extra`
  ADD CONSTRAINT `fk_doe_detalle` FOREIGN KEY (`id_detalle`) REFERENCES `detalle_orden` (`id_detalle`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_doe_extra` FOREIGN KEY (`id_extra`) REFERENCES `extras` (`id_extra`);

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `fk_ord_anula` FOREIGN KEY (`id_usuario_anula`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_ord_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_orden` (`id_estado`),
  ADD CONSTRAINT `fk_ord_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_metodo` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodo_pago`),
  ADD CONSTRAINT `fk_pago_orden` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`),
  ADD CONSTRAINT `fk_pago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
