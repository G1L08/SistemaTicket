-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-09-2026 a las 19:39:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ticket_system_dar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivoadjunto`
--

CREATE TABLE `archivoadjunto` (
  `Id_archivo` int(11) NOT NULL,
  `Id_ticket` int(11) NOT NULL,
  `Folio` varchar(50) NOT NULL,
  `Nombre_original` varchar(255) NOT NULL,
  `Nombre_guardado` varchar(255) NOT NULL,
  `Ruta` varchar(512) NOT NULL,
  `Tipo` varchar(100) DEFAULT NULL,
  `Tamanio_bytes` int(11) DEFAULT NULL,
  `Fecha_carga` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivoadjunto`
--

INSERT INTO `archivoadjunto` (`Id_archivo`, `Id_ticket`, `Folio`, `Nombre_original`, `Nombre_guardado`, `Ruta`, `Tipo`, `Tamanio_bytes`, `Fecha_carga`) VALUES
(1, 2, 'TKT-2026-34447', 'Interfaces (1).pdf', '6a9b1f585c3f7.pdf', 'uploads/TKT-2026-34447/6a9b1f585c3f7.pdf', 'application/pdf', 3841989, '2026-09-04 13:43:20'),
(2, 3, 'TKT-2026-51526', 'Interfaces (1).pdf', '6a9b1fb831780.pdf', 'uploads/TKT-2026-51526/6a9b1fb831780.pdf', 'application/pdf', 3841989, '2026-09-04 13:44:56'),
(3, 8, 'TKT-2026-91756', 'Interfaces (1).pdf', '6a9b205b7656b.pdf', 'uploads/TKT-2026-91756/6a9b205b7656b.pdf', 'application/pdf', 3841989, '2026-09-04 13:47:39'),
(4, 10, 'TKT-2026-81109', 'Interfaces (1).pdf', '6a9b23c0517c3.pdf', 'uploads/TKT-2026-81109/6a9b23c0517c3.pdf', 'application/pdf', 3841989, '2026-09-04 14:02:08'),
(5, 14, 'TKT-2026-50981', 'Diseño sin título (1).pdf', '6a9b4889b0a50.pdf', 'uploads/TKT-2026-50981/6a9b4889b0a50.pdf', 'application/pdf', 1480442, '2026-09-04 16:39:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `Id_bitacora` int(11) NOT NULL,
  `Id_usuario` int(11) DEFAULT NULL,
  `Modulo` varchar(100) DEFAULT NULL,
  `Accion` varchar(100) DEFAULT NULL,
  `Id_ticket` int(11) DEFAULT NULL,
  `Resultado` varchar(20) DEFAULT NULL,
  `Ip` varchar(45) DEFAULT NULL,
  `User_agent` varchar(255) DEFAULT NULL,
  `Detalle_tecnico` text DEFAULT NULL,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `Id_categoria` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Atencion_horas` int(11) DEFAULT NULL,
  `Resolucion_horas` int(11) DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`Id_categoria`, `Nombre`, `Descripcion`, `Atencion_horas`, `Resolucion_horas`, `Activo`) VALUES
(1, 'Software', 'Problemas con aplicaciones y sistemas', 4, 24, 1),
(2, 'Hardware', 'Problemas físicos de equipos', 8, 48, 1),
(3, 'Redes', 'Problemas de conectividad y red', 4, 12, 1),
(4, 'Sistema', 'Problemas del sistema operativo', 6, 24, 1),
(5, 'Base de Datos', 'Problemas con bases de datos', 4, 24, 1),
(6, 'Seguridad', 'Problemas de seguridad y accesos', 4, 24, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentario`
--

CREATE TABLE `comentario` (
  `Id_comentario` int(11) NOT NULL,
  `Id_ticket` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Contenido` text NOT NULL,
  `Es_interno` tinyint(1) DEFAULT 0,
  `Fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentario`
--

INSERT INTO `comentario` (`Id_comentario`, `Id_ticket`, `Id_usuario`, `Contenido`, `Es_interno`, `Fecha_registro`) VALUES
(1, 13, 3, 'hOLAAA', 0, '2026-09-04 16:16:21'),
(2, 13, 2, 'Prueba de noti', 0, '2026-09-04 16:17:16'),
(3, 13, 3, 'Prueba 2', 0, '2026-09-04 16:20:01'),
(4, 14, 3, 'Prueba de comentario add', 0, '2026-09-04 16:38:54'),
(5, 14, 2, 'Comentario 1 tec', 0, '2026-09-04 16:44:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestasatisfaccion`
--

CREATE TABLE `encuestasatisfaccion` (
  `Id_encuesta` int(11) NOT NULL,
  `Id_ticket` int(11) NOT NULL,
  `Calificacion_respeto` int(11) DEFAULT NULL,
  `Calificacion_tiempo` int(11) DEFAULT NULL,
  `Calificacion_conocimiento` int(11) DEFAULT NULL,
  `Informado` tinyint(1) DEFAULT NULL,
  `Calificacion_archivos` int(11) DEFAULT NULL,
  `Calificacion_facilidad` int(11) DEFAULT NULL,
  `Contacto_externo` tinyint(1) DEFAULT NULL,
  `Interacciones` varchar(20) DEFAULT NULL,
  `Comentarios` text DEFAULT NULL,
  `Fecha_envio` datetime DEFAULT current_timestamp(),
  `Fecha_respuesta` datetime DEFAULT NULL,
  `Fecha_cierre` datetime DEFAULT NULL,
  `Respondida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `Id_estado` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`Id_estado`, `Nombre`, `Descripcion`) VALUES
(1, 'Abierto', 'Ticket recién creado'),
(2, 'En proceso', 'Ticket en atención'),
(3, 'En espera', 'Esperando información del usuario'),
(4, 'Cerrado', 'Ticket resuelto'),
(5, 'Expirado', 'Ticket vencido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialticket`
--

CREATE TABLE `historialticket` (
  `Id_historial` int(11) NOT NULL,
  `Id_ticket` int(11) NOT NULL,
  `Estado_anterior` varchar(50) DEFAULT NULL,
  `Estado_nuevo` varchar(50) DEFAULT NULL,
  `Id_usuario` int(11) DEFAULT NULL,
  `Fecha_cambio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historialticket`
--

INSERT INTO `historialticket` (`Id_historial`, `Id_ticket`, `Estado_anterior`, `Estado_nuevo`, `Id_usuario`, `Fecha_cambio`) VALUES
(1, 2, 'Nuevo', 'Abierto', 2, '2026-09-04 13:43:20'),
(2, 3, 'Nuevo', 'Abierto', 2, '2026-09-04 13:44:56'),
(3, 4, 'Nuevo', 'Abierto', 2, '2026-09-04 13:46:00'),
(4, 5, 'Nuevo', 'Abierto', 2, '2026-09-04 13:46:01'),
(5, 6, 'Nuevo', 'Abierto', 2, '2026-09-04 13:46:01'),
(6, 7, 'Nuevo', 'Abierto', 2, '2026-09-04 13:46:01'),
(7, 8, 'Nuevo', 'Abierto', 2, '2026-09-04 13:47:39'),
(8, 9, 'Nuevo', 'Abierto', 2, '2026-09-04 14:00:03'),
(9, 10, 'Nuevo', 'Abierto', 2, '2026-09-04 14:02:08'),
(10, 11, 'Nuevo', 'Abierto', 2, '2026-09-04 14:25:48'),
(11, 12, 'Nuevo', 'Abierto', 2, '2026-09-04 14:26:24'),
(12, 13, 'Nuevo', 'Abierto', 3, '2026-09-04 16:06:14'),
(13, 13, 'Abierto', 'En proceso', 2, '2026-09-04 16:08:20'),
(14, 14, 'Nuevo', 'Abierto', 3, '2026-09-04 16:36:29'),
(15, 14, 'Abierto', 'En proceso', 2, '2026-09-04 16:43:18'),
(16, 14, 'En proceso', 'Extendido +11h', 2, '2026-09-04 16:44:01'),
(17, 2, 'Abierto', 'Prioridad confirmada: Alta', 1, '2026-09-07 14:02:42'),
(18, 8, 'Abierto', 'Prioridad confirmada: Baja', 1, '2026-09-07 14:03:06'),
(19, 3, 'Abierto', 'En proceso', 1, '2026-09-07 14:08:26'),
(20, 3, 'En proceso', 'Prioridad confirmada: Baja', 1, '2026-09-07 14:08:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `Id_notificacion` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_ticket` int(11) DEFAULT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Mensaje` text NOT NULL,
  `Leida` tinyint(1) DEFAULT 0,
  `Fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`Id_notificacion`, `Id_usuario`, `Id_ticket`, `Tipo`, `Mensaje`, `Leida`, `Fecha_creacion`) VALUES
(1, 2, 13, 'comentario', 'Nuevo comentario en el ticket TKT-2026-06180: Prueba 2...', 0, '2026-09-04 16:20:01'),
(2, 1, 14, 'ticket_nuevo', 'Nuevo ticket: TKT-2026-50981 - Prueba 200', 1, '2026-09-04 16:36:29'),
(3, 2, 14, 'ticket_nuevo', 'Nuevo ticket: TKT-2026-50981 - Prueba 200', 0, '2026-09-04 16:36:29'),
(4, 3, 14, 'comentario', 'Nuevo comentario en el ticket TKT-2026-50981: Comentario 1 tec...', 0, '2026-09-04 16:44:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridad`
--

CREATE TABLE `prioridad` (
  `Id_prioridad` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Horas_resolucion` int(11) DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prioridad`
--

INSERT INTO `prioridad` (`Id_prioridad`, `Nombre`, `Descripcion`, `Horas_resolucion`, `Activo`) VALUES
(1, 'Baja', 'Problema menor - sin impacto urgente', 48, 1),
(2, 'Media', 'Problema con impacto moderado', 24, 1),
(3, 'Alta', 'Problema urgente - afecta operaciones', 8, 1),
(4, 'Crítica', 'Problema crítico - sistema caído', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `Id_rol` int(11) NOT NULL,
  `Nombre_rol` varchar(50) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`Id_rol`, `Nombre_rol`, `Descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Tecnico', 'Puede gestionar tickets'),
(3, 'Usuario', 'Puede crear y ver sus tickets');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket`
--

CREATE TABLE `ticket` (
  `Id_ticket` int(11) NOT NULL,
  `Folio` varchar(50) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Descripcion` text NOT NULL,
  `Id_usuario` int(11) DEFAULT NULL,
  `Id_estado` int(11) DEFAULT 1,
  `Id_prioridad` int(11) DEFAULT NULL,
  `Id_categoria` int(11) DEFAULT NULL,
  `Id_tecnico_asignado` int(11) DEFAULT NULL,
  `Fecha_creacion` datetime DEFAULT current_timestamp(),
  `Fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Fecha_vencimiento` datetime DEFAULT NULL,
  `Descripcion_solucion` text DEFAULT NULL,
  `Prioridad_confirmada` varchar(50) DEFAULT NULL,
  `Id_encuesta` int(11) DEFAULT NULL,
  `Tiempo_extendido` int(11) DEFAULT NULL,
  `Fecha_extension` datetime DEFAULT NULL,
  `Reabierto` tinyint(1) DEFAULT 0,
  `Fecha_reabierto` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ticket`
--

INSERT INTO `ticket` (`Id_ticket`, `Folio`, `Titulo`, `Descripcion`, `Id_usuario`, `Id_estado`, `Id_prioridad`, `Id_categoria`, `Id_tecnico_asignado`, `Fecha_creacion`, `Fecha_actualizacion`, `Fecha_vencimiento`, `Descripcion_solucion`, `Prioridad_confirmada`, `Id_encuesta`, `Tiempo_extendido`, `Fecha_extension`, `Reabierto`, `Fecha_reabierto`) VALUES
(2, 'TKT-2026-34447', 'Prueba', 'Sistema afectado: plataforma garza\r\n\r\nasd', 2, 1, 4, 5, NULL, '2026-09-04 13:43:20', '2026-09-07 14:02:41', '2026-09-06 21:43:20', NULL, 'Alta', NULL, NULL, NULL, 0, NULL),
(3, 'TKT-2026-51526', 'Prueba', 'Sistema afectado: plataforma garza\r\n\r\nasd', 2, 2, 4, 5, 1, '2026-09-04 13:44:56', '2026-09-07 14:08:27', '2026-09-06 21:44:56', NULL, 'Baja', NULL, NULL, NULL, 0, NULL),
(4, 'TKT-2026-72832', 'Prueba', 'Descripcdsffdsfión de prueba', 2, 1, 1, 1, NULL, '2026-09-04 13:46:00', '2026-09-04 13:46:00', '2026-09-06 21:46:00', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(5, 'TKT-2026-33250', 'Prueba', 'Descripcdsffdsfión de prueba', 2, 1, 1, 1, NULL, '2026-09-04 13:46:01', '2026-09-04 13:46:01', '2026-09-06 21:46:01', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(6, 'TKT-2026-32019', 'Prueba', 'Descripcdsffdsfión de prueba', 2, 1, 1, 1, NULL, '2026-09-04 13:46:01', '2026-09-04 13:46:01', '2026-09-06 21:46:01', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(7, 'TKT-2026-76658', 'Prueba', 'Descripcdsffdsfión de prueba', 2, 1, 1, 1, NULL, '2026-09-04 13:46:01', '2026-09-04 13:46:01', '2026-09-06 21:46:01', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(8, 'TKT-2026-91756', 'Prueba', 'Sistema afectado: plataforma garza\r\n\r\nqwe', 2, 1, 4, 5, NULL, '2026-09-04 13:47:39', '2026-09-07 14:03:06', '2026-09-06 21:47:39', NULL, 'Baja', NULL, NULL, NULL, 0, NULL),
(9, 'TKT-2026-34536', 'Prueba', 'Descripción de prueba', 2, 1, 1, 1, NULL, '2026-09-04 14:00:03', '2026-09-04 14:00:03', '2026-09-06 22:00:03', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(10, 'TKT-2026-81109', 'Prueba', 'Sistema afectado: plataforma garza\r\n\r\nqwe', 2, 1, 4, 5, NULL, '2026-09-04 14:02:08', '2026-09-04 14:02:08', '2026-09-06 22:02:08', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(11, 'TKT-2026-19516', 'Prueba', 'Sistema afectado: plataforma garza\r\n\r\nqwe', 2, 1, 4, 5, NULL, '2026-09-04 14:25:48', '2026-09-04 14:25:48', '2026-09-06 22:25:48', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(12, 'TKT-2026-45908', 'Prueba', 'Sistema afectado: qwe\r\n\r\nqwe', 2, 1, 4, 2, NULL, '2026-09-04 14:26:24', '2026-09-04 14:26:24', '2026-09-06 22:26:24', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(13, 'TKT-2026-06180', 'Prueba user', 'Sistema afectado: plataforma garza\r\n\r\nasda', 3, 2, 2, 3, 2, '2026-09-04 16:06:14', '2026-09-04 16:08:20', '2026-09-07 00:06:14', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(14, 'TKT-2026-50981', 'Prueba 200', 'Sistema afectado: Ahorro retiro\r\n\r\nFalla a la hora de capturar', 3, 2, 3, 5, 2, '2026-09-04 16:36:29', '2026-09-04 16:44:01', '2026-09-07 11:36:29', NULL, NULL, NULL, 11, '2026-09-04 16:44:01', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `Id_usuario` int(11) NOT NULL,
  `No_empleado` varchar(50) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido_paterno` varchar(100) NOT NULL,
  `Apellido_materno` varchar(100) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `Puesto` varchar(100) DEFAULT NULL,
  `estado` int(11) DEFAULT 1,
  `Fecha_registro` datetime DEFAULT current_timestamp(),
  `intentos_fallidos` int(11) DEFAULT 0,
  `ultimo_intento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`Id_usuario`, `No_empleado`, `Nombre`, `Apellido_paterno`, `Apellido_materno`, `correo`, `contraseña`, `Puesto`, `estado`, `Fecha_registro`, `intentos_fallidos`, `ultimo_intento`) VALUES
(1, 'ADMIN001', 'Andrea', 'Rio', 'Rojas', 'admin@dar.com', 'admin123', 'Administrador', 1, '2026-09-04 12:54:48', 0, NULL),
(2, 'TEC001', 'Luis', 'Gómez', 'Pérez', 'luis.gomez@dar.com', 'tec123', 'Tecnico', 1, '2026-09-04 12:54:48', 0, '2026-09-04 15:33:49'),
(3, 'USR001', 'José', 'Ramírez', 'Martínez', 'jose.ramirez@dar.com', 'user123', 'Analista', 1, '2026-09-04 12:54:48', 0, '2026-09-04 16:16:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariorol`
--

CREATE TABLE `usuariorol` (
  `Id_rol` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuariorol`
--

INSERT INTO `usuariorol` (`Id_rol`, `Id_usuario`) VALUES
(1, 1),
(2, 2),
(3, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivoadjunto`
--
ALTER TABLE `archivoadjunto`
  ADD PRIMARY KEY (`Id_archivo`),
  ADD KEY `Id_ticket` (`Id_ticket`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`Id_bitacora`),
  ADD KEY `Id_usuario` (`Id_usuario`),
  ADD KEY `Id_ticket` (`Id_ticket`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`Id_categoria`);

--
-- Indices de la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`Id_comentario`),
  ADD KEY `Id_ticket` (`Id_ticket`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `encuestasatisfaccion`
--
ALTER TABLE `encuestasatisfaccion`
  ADD PRIMARY KEY (`Id_encuesta`),
  ADD KEY `Id_ticket` (`Id_ticket`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`Id_estado`);

--
-- Indices de la tabla `historialticket`
--
ALTER TABLE `historialticket`
  ADD PRIMARY KEY (`Id_historial`),
  ADD KEY `Id_ticket` (`Id_ticket`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`Id_notificacion`),
  ADD KEY `Id_usuario` (`Id_usuario`),
  ADD KEY `Id_ticket` (`Id_ticket`);

--
-- Indices de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  ADD PRIMARY KEY (`Id_prioridad`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`Id_rol`);

--
-- Indices de la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`Id_ticket`),
  ADD UNIQUE KEY `Folio` (`Folio`),
  ADD KEY `Id_usuario` (`Id_usuario`),
  ADD KEY `Id_estado` (`Id_estado`),
  ADD KEY `Id_prioridad` (`Id_prioridad`),
  ADD KEY `Id_categoria` (`Id_categoria`),
  ADD KEY `Id_tecnico_asignado` (`Id_tecnico_asignado`),
  ADD KEY `Id_encuesta` (`Id_encuesta`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Id_usuario`),
  ADD UNIQUE KEY `No_empleado` (`No_empleado`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuariorol`
--
ALTER TABLE `usuariorol`
  ADD PRIMARY KEY (`Id_rol`,`Id_usuario`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivoadjunto`
--
ALTER TABLE `archivoadjunto`
  MODIFY `Id_archivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `Id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `Id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comentario`
--
ALTER TABLE `comentario`
  MODIFY `Id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `encuestasatisfaccion`
--
ALTER TABLE `encuestasatisfaccion`
  MODIFY `Id_encuesta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `Id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historialticket`
--
ALTER TABLE `historialticket`
  MODIFY `Id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `Id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  MODIFY `Id_prioridad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `Id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ticket`
--
ALTER TABLE `ticket`
  MODIFY `Id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `Id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivoadjunto`
--
ALTER TABLE `archivoadjunto`
  ADD CONSTRAINT `archivoadjunto_ibfk_1` FOREIGN KEY (`Id_ticket`) REFERENCES `ticket` (`Id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`Id_ticket`) REFERENCES `ticket` (`Id_ticket`) ON DELETE SET NULL;

--
-- Filtros para la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `comentario_ibfk_1` FOREIGN KEY (`Id_ticket`) REFERENCES `ticket` (`Id_ticket`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentario_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `encuestasatisfaccion`
--
ALTER TABLE `encuestasatisfaccion`
  ADD CONSTRAINT `encuestasatisfaccion_ibfk_1` FOREIGN KEY (`Id_ticket`) REFERENCES `ticket` (`Id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historialticket`
--
ALTER TABLE `historialticket`
  ADD CONSTRAINT `historialticket_ibfk_1` FOREIGN KEY (`Id_ticket`) REFERENCES `ticket` (`Id_ticket`) ON DELETE CASCADE,
  ADD CONSTRAINT `historialticket_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificacion_ibfk_2` FOREIGN KEY (`Id_ticket`) REFERENCES `ticket` (`Id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`),
  ADD CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`Id_estado`) REFERENCES `estado` (`Id_estado`),
  ADD CONSTRAINT `ticket_ibfk_3` FOREIGN KEY (`Id_prioridad`) REFERENCES `prioridad` (`Id_prioridad`),
  ADD CONSTRAINT `ticket_ibfk_4` FOREIGN KEY (`Id_categoria`) REFERENCES `categoria` (`Id_categoria`),
  ADD CONSTRAINT `ticket_ibfk_5` FOREIGN KEY (`Id_tecnico_asignado`) REFERENCES `usuario` (`Id_usuario`),
  ADD CONSTRAINT `ticket_ibfk_6` FOREIGN KEY (`Id_encuesta`) REFERENCES `encuestasatisfaccion` (`Id_encuesta`);

--
-- Filtros para la tabla `usuariorol`
--
ALTER TABLE `usuariorol`
  ADD CONSTRAINT `usuariorol_ibfk_1` FOREIGN KEY (`Id_rol`) REFERENCES `rol` (`Id_rol`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuariorol_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
