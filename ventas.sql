-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-10-2026 a las 19:22:16
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ventas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja`
--

CREATE TABLE `caja` (
  `caja_id` int(5) NOT NULL,
  `caja_numero` int(5) NOT NULL,
  `caja_nombre` varchar(100) NOT NULL,
  `caja_efectivo` decimal(30,2) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  `caja_efectivo_usd` decimal(20,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `caja`
--

INSERT INTO `caja` (`caja_id`, `caja_numero`, `caja_nombre`, `caja_efectivo`, `estatus`, `caja_efectivo_usd`) VALUES
(1, 1, 'Caja Principal', 366968.18, 1, 718.00),
(2, 2, 'Secundaria', 50.00, 0, 0.00),
(3, 3, 'pruebassssss', 100000.00, 0, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int(7) NOT NULL,
  `categoria_nombre` varchar(50) NOT NULL,
  `categoria_estatus` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `categoria_nombre`, `categoria_estatus`) VALUES
(1, 'dulces', 1),
(2, 'Bebidas Gaseosas', 1),
(3, 'pruebassss', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `cliente_id` int(10) NOT NULL,
  `cliente_tipo_documento` varchar(20) NOT NULL,
  `cliente_numero_documento` varchar(35) NOT NULL,
  `cliente_nombre` varchar(50) NOT NULL,
  `cliente_apellido` varchar(50) NOT NULL,
  `cliente_provincia` varchar(30) NOT NULL,
  `cliente_ciudad` varchar(30) NOT NULL,
  `cliente_direccion` varchar(70) NOT NULL,
  `cliente_telefono` varchar(20) NOT NULL,
  `cliente_email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`cliente_id`, `cliente_tipo_documento`, `cliente_numero_documento`, `cliente_nombre`, `cliente_apellido`, `cliente_provincia`, `cliente_ciudad`, `cliente_direccion`, `cliente_telefono`, `cliente_email`) VALUES
(1, 'Otro', 'N/A', 'Publico', 'General', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A'),
(3, 'Cedula', '6676677676', 'paola', 'perez', 'carabobo', 'yagua', 'yagua', '00000000000000000000', 'paola@gmail.com'),
(4, 'Cedula', '3445566676', 'juan', 'fgrg', 'hfuyhuhf', 'gggyygu', 'gygfytyg', '7875567886', 'yyufe@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `empresa_id` int(11) NOT NULL,
  `empresa_nombre` varchar(90) NOT NULL,
  `empresa_telefono` varchar(20) NOT NULL,
  `empresa_email` varchar(50) NOT NULL,
  `empresa_direccion` varchar(100) NOT NULL,
  `iva` int(3) NOT NULL DEFAULT 16,
  `tasa_dolar` decimal(20,4) NOT NULL DEFAULT 1.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`empresa_id`, `empresa_nombre`, `empresa_telefono`, `empresa_email`, `empresa_direccion`, `iva`, `tasa_dolar`) VALUES
(1, 'Comercializadora Valles de Galilea', '2345445456', 'vallesdegalilea@gmail.com', 'yagua', 10, 875.6505);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `producto_id` int(20) NOT NULL,
  `producto_codigo` varchar(77) NOT NULL,
  `producto_nombre` varchar(100) NOT NULL,
  `producto_stock_total` int(25) NOT NULL,
  `producto_tipo_unidad` varchar(20) NOT NULL,
  `producto_precio_compra` decimal(30,2) NOT NULL,
  `producto_precio_venta` decimal(30,2) NOT NULL,
  `producto_marca` varchar(35) NOT NULL,
  `producto_estado` varchar(20) NOT NULL,
  `producto_foto` varchar(500) NOT NULL,
  `categoria_id` int(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`producto_id`, `producto_codigo`, `producto_nombre`, `producto_stock_total`, `producto_tipo_unidad`, `producto_precio_compra`, `producto_precio_venta`, `producto_marca`, `producto_estado`, `producto_foto`, `categoria_id`) VALUES
(1, '34567876543', 'tic toc chocolate', 87, 'Paquete', 30.00, 50.00, '', 'Habilitado', '34567876543_11.jpg', 1),
(7, '89989788777', 'galleta', 9, 'Paquete', 80.00, 150.00, 'holl', 'Habilitado', '89989788777_98.jpg', 1),
(8, '764256', 'coca', 14, 'Lata', 10.00, 30.00, 'coca', 'Habilitado', '764256_27.jpg', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usuario_id` int(7) NOT NULL,
  `usuario_nombre` varchar(50) NOT NULL,
  `usuario_apellido` varchar(50) NOT NULL,
  `usuario_email` varchar(50) NOT NULL,
  `usuario_usuario` varchar(30) NOT NULL,
  `usuario_clave` varchar(535) NOT NULL,
  `usuario_foto` varchar(200) NOT NULL,
  `caja_id` int(5) NOT NULL,
  `admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usuario_id`, `usuario_nombre`, `usuario_apellido`, `usuario_email`, `usuario_usuario`, `usuario_clave`, `usuario_foto`, `caja_id`, `admin`) VALUES
(1, 'Administrador', 'Principal', '', 'Administrador', '$2y$10$kWXhNcBbGVj0KpsN8dVDneeBAbi.4mz8WkTUc8r9dYG6q6HHi5Z9i', 'Administrador_88.png', 1, 1),
(3, 'juan', 'swdfrtghyujyhtgre', 'rtyuhygtfret@gmail.com', 'juan', '$2y$10$kAiaybtDyFdKuACoeXkEO.olV1qiwZ78RoyNpvqVqMW5xBUs93Ud2', '', 1, 0),
(4, 'ana', 'alvarez', 'anita@gmail.com', 'anita', '$2y$10$bGI3r6KFu0FgeRXnsMa2uemQlwKsSzN8rePTFiuPjVAAhoD/zZLqq', '', 1, 0),
(5, 'paola', 'ygygyuyt', 'pao@gmail.com', 'paola', '$2y$10$dBoM9ZhYl3p3/d3pmS1AIeNiHI68U./uwIrJKzRKaSF4hZQIJrY4G', '', 1, 0),
(6, 'paola', 'deghffuhef', 'ygyuede@gmail.com', 'paolaaaaa', '$2y$10$yEdMNpgiD72T8h1U6XUqIejEVwKshjNx0JXXf2h5KTSkxfSegGM56', '', 1, 0),
(7, 'lise', 'tytyyygfgf', 'lise@gmail.com', 'liseth', '$2y$10$4m8RlWxCFSOGd7NSRI8UIe8GGaKwWBgW6KavgR3GwmtRm0XMbz77q', '', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `venta_id` int(30) NOT NULL,
  `venta_codigo` varchar(200) NOT NULL,
  `venta_fecha` date NOT NULL,
  `venta_hora` varchar(17) NOT NULL,
  `venta_subtotal` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_iva` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_total` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_pagado` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_cambio` decimal(20,2) NOT NULL DEFAULT 0.00,
  `usuario_id` int(7) NOT NULL,
  `cliente_id` int(10) NOT NULL,
  `caja_id` int(5) NOT NULL,
  `venta_forma_pago` varchar(3) NOT NULL DEFAULT 'Bs',
  `anulado` tinyint(1) NOT NULL DEFAULT 0,
  `venta_tasa_dolar` decimal(20,4) NOT NULL DEFAULT 1.0000,
  `venta_total_usd` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_total_bs` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_pagado_usd` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_pagado_bs` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_cambio_usd` decimal(20,2) NOT NULL DEFAULT 0.00,
  `venta_cambio_bs` decimal(20,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`venta_id`, `venta_codigo`, `venta_fecha`, `venta_hora`, `venta_subtotal`, `venta_iva`, `venta_total`, `venta_pagado`, `venta_cambio`, `usuario_id`, `cliente_id`, `caja_id`, `venta_forma_pago`, `anulado`, `venta_tasa_dolar`, `venta_total_usd`, `venta_total_bs`, `venta_pagado_usd`, `venta_pagado_bs`, `venta_cambio_usd`, `venta_cambio_bs`) VALUES
(1, 'G4J3B2K2N0-1', '2026-10-09', '10:53 am', 210.00, 21.00, 231.00, 234.26, 0.00, 1, 1, 1, 'Mix', 0, 875.6505, 231.00, 202275.27, 200.00, 30000.00, 0.00, 2854.83),
(2, 'N2V0H6A5T3-2', '2026-10-09', '11:15 am', 180.00, 18.00, 198.00, 200.00, 2.00, 1, 1, 1, 'USD', 0, 875.6505, 198.00, 173378.80, 200.00, 0.00, 2.00, 0.00),
(3, 'J8I1P5J1S6-3', '2026-10-09', '11:16 am', 300.00, 30.00, 330.00, 342.60, 0.00, 1, 1, 1, 'Bs', 0, 875.6505, 330.00, 288964.67, 0.00, 300000.00, 0.00, 11035.34),
(4, 'Z8L7D0Q7Q7-4', '2026-10-09', '11:16 am', 30.00, 3.00, 33.00, 33.00, 0.00, 1, 1, 1, 'Bs', 0, 875.6505, 33.00, 28896.47, 0.00, 28896.47, 0.00, 0.00),
(5, 'C0A3Z7H1E6-5', '2026-10-09', '11:18 am', 210.00, 21.00, 231.00, 231.42, 0.00, 1, 1, 1, 'Mix', 0, 875.6505, 231.00, 202275.27, 220.00, 10000.00, 0.00, 367.84),
(6, 'O4G5U8V8S5-6', '2026-10-09', '01:17 pm', 100.00, 10.00, 110.00, 110.28, 0.00, 1, 1, 1, 'Mix', 0, 875.6505, 110.00, 96321.56, 100.00, 9000.00, 0.00, 243.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `venta_detalle_id` int(100) NOT NULL,
  `venta_detalle_cantidad` int(10) NOT NULL,
  `venta_detalle_precio_compra` decimal(30,2) NOT NULL,
  `venta_detalle_precio_venta` decimal(30,2) NOT NULL,
  `venta_detalle_total` decimal(30,2) NOT NULL,
  `venta_detalle_descripcion` varchar(200) NOT NULL,
  `venta_codigo` varchar(200) NOT NULL,
  `producto_id` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `venta_detalle`
--

INSERT INTO `venta_detalle` (`venta_detalle_id`, `venta_detalle_cantidad`, `venta_detalle_precio_compra`, `venta_detalle_precio_venta`, `venta_detalle_total`, `venta_detalle_descripcion`, `venta_codigo`, `producto_id`) VALUES
(1, 1, 80.00, 150.00, 150.00, 'galleta', 'G4J3B2K2N0-1', 7),
(2, 2, 10.00, 30.00, 60.00, 'coca', 'G4J3B2K2N0-1', 8),
(3, 1, 80.00, 150.00, 150.00, 'galleta', 'N2V0H6A5T3-2', 7),
(4, 1, 10.00, 30.00, 30.00, 'coca', 'N2V0H6A5T3-2', 8),
(5, 2, 80.00, 150.00, 300.00, 'galleta', 'J8I1P5J1S6-3', 7),
(6, 1, 10.00, 30.00, 30.00, 'coca', 'Z8L7D0Q7Q7-4', 8),
(7, 2, 10.00, 30.00, 60.00, 'coca', 'C0A3Z7H1E6-5', 8),
(8, 1, 80.00, 150.00, 150.00, 'galleta', 'C0A3Z7H1E6-5', 7),
(9, 2, 30.00, 50.00, 100.00, 'tic toc chocolate', 'O4G5U8V8S5-6', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`caja_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoria_id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`cliente_id`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`empresa_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`producto_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`venta_id`),
  ADD UNIQUE KEY `venta_codigo` (`venta_codigo`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indices de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`venta_detalle_id`),
  ADD KEY `venta_id` (`venta_codigo`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `caja`
--
ALTER TABLE `caja`
  MODIFY `caja_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `cliente_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `empresa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `venta_id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `venta_detalle_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`);

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`),
  ADD CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`cliente_id`),
  ADD CONSTRAINT `venta_ibfk_3` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`caja_id`);

--
-- Filtros para la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `venta_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`),
  ADD CONSTRAINT `venta_detalle_ibfk_3` FOREIGN KEY (`venta_codigo`) REFERENCES `venta` (`venta_codigo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
