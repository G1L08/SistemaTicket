-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-08-2026 a las 19:43:04
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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
  `Id_comentario` int(11) DEFAULT NULL,
  `Nombre_original` varchar(255) NOT NULL,
  `Ruta_almacenamiento` varchar(512) NOT NULL,
  `Tipo` varchar(100) DEFAULT NULL,
  `Tamanio_bytes` int(11) DEFAULT NULL,
  `Fecha_carga` datetime DEFAULT current_timestamp(),
  `Id_ticket` int(11) NOT NULL,
  `Folio` varchar(50) NOT NULL,
  `Nombre_guardado` varchar(255) NOT NULL,
  `Ruta` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivoadjunto`
--

INSERT INTO `archivoadjunto` (`Id_archivo`, `Id_comentario`, `Nombre_original`, `Ruta_almacenamiento`, `Tipo`, `Tamanio_bytes`, `Fecha_carga`, `Id_ticket`, `Folio`, `Nombre_guardado`, `Ruta`) VALUES
(1, NULL, 'Captura de pantalla 2026-06-01 164323.png', '', 'image/png', 131416, '2026-08-23 04:41:59', 1, 'TKT-2026-85796', '6a8ace772e36b.png', 'uploads/TKT-2026-85796/6a8ace772e36b.png'),
(2, NULL, 'Interfaces (1).pdf', '', 'application/pdf', 3841989, '2026-08-23 04:42:40', 1, 'TKT-2026-85796', '6a8acea0ed02a.pdf', 'uploads/TKT-2026-85796/6a8acea0ed02a.pdf'),
(3, NULL, 'Diseño sin título (1).pdf', '', 'application/pdf', 1480442, '2026-08-23 05:45:46', 1, 'TKT-2026-85796', '6a8add6a49893.pdf', 'uploads/TKT-2026-85796/6a8add6a49893.pdf'),
(4, NULL, 'Diseño sin título (1).pdf', '', 'application/pdf', 1480442, '2026-08-24 09:22:59', 7, 'TKT-2026-38677', '6a8c61d3bd766.pdf', 'uploads/TKT-2026-38677/6a8c61d3bd766.pdf');

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
(1, 'Software', 'Problemas con aplicaciones', 4, 24, 1),
(2, 'Hardware', 'Problemas físicos de equipo', 8, 48, 1),
(3, 'Redes', 'Problemas de conectividad', 4, 12, 1),
(4, 'Sistema', 'Problemas del sistema operativo', 6, 24, 1),
(5, 'Software', 'Problemas con aplicaciones y sistemas', NULL, NULL, 1),
(6, 'Hardware', 'Problemas físicos de equipos', NULL, NULL, 1),
(7, 'Redes', 'Problemas de conectividad y red', NULL, NULL, 1),
(8, 'Sistema', 'Problemas del sistema operativo', NULL, NULL, 1),
(9, 'Base de Datos', 'Problemas con bases de datos', NULL, NULL, 1),
(10, 'Seguridad', 'Problemas de seguridad y accesos', NULL, NULL, 1);

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
(1, 1, 1, 'Prueba de comentario 1 prueba 1', 0, '2026-08-22 23:55:05'),
(2, 1, 3, 'Prueba tecnico 1 con archivo adjunto', 0, '2026-08-23 03:39:37'),
(3, 1, 3, 'ya quedo cierro el ticket', 0, '2026-08-23 04:56:41'),
(4, 1, 1, '🔄 Ticket reabierto. Motivo: Reabierto por el usuario', 0, '2026-08-23 05:45:10'),
(5, 1, 1, '🔄 Ticket reabierto. Motivo: Preuba de tiempo', 0, '2026-08-23 05:51:26'),
(6, 2, 3, '🔄 Ticket reabierto. Motivo: Prueba', 0, '2026-08-23 18:41:43'),
(7, 7, 3, 'entra y toma cap', 0, '2026-08-24 09:22:27'),
(8, 7, 3, 'ejecuta x comando en cmd  y te adjunto evidencia', 0, '2026-08-24 09:22:46'),
(9, 7, 1, '🔄 Ticket reabierto. Motivo: Cuando la volvi a prender volvio a fallar', 0, '2026-08-24 09:26:39'),
(10, 4, 1, 'Holaa', 0, '2026-08-26 13:57:15');

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
  `Fecha_respuesta` datetime DEFAULT current_timestamp(),
  `Respondida` tinyint(1) DEFAULT 0,
  `Fecha_cierre` datetime DEFAULT NULL,
  `Fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuestasatisfaccion`
--

INSERT INTO `encuestasatisfaccion` (`Id_encuesta`, `Id_ticket`, `Calificacion_respeto`, `Calificacion_tiempo`, `Calificacion_conocimiento`, `Informado`, `Calificacion_archivos`, `Calificacion_facilidad`, `Contacto_externo`, `Interacciones`, `Comentarios`, `Fecha_respuesta`, `Respondida`, `Fecha_cierre`, `Fecha_envio`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 04:57:02', 0, '2026-09-07 05:08:34', '2026-08-23 05:08:34'),
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 18:41:08', 0, NULL, '2026-08-23 18:41:08'),
(3, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-24 09:24:43', 0, NULL, '2026-08-24 09:24:43'),
(4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-26 14:00:31', 0, NULL, '2026-08-26 14:00:31');

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
(1, 1, 'Abierto', 'En proceso', 3, '2026-08-23 03:34:36'),
(2, 2, 'Nuevo', 'Abierto', 3, '2026-08-23 04:44:30'),
(3, 3, 'Nuevo', 'Abierto', 1, '2026-08-23 04:55:29'),
(4, 1, 'En proceso', 'Cerrado', 3, '2026-08-23 04:57:02'),
(5, 1, 'Cerrado', 'Reabierto - Pool', 1, '2026-08-23 05:45:10'),
(6, 1, 'Abierto', 'En proceso', 1, '2026-08-23 05:49:18'),
(7, 1, 'En proceso', 'Cerrado', 1, '2026-08-23 05:51:08'),
(8, 1, 'Cerrado', 'Reabierto - Pool', 1, '2026-08-23 05:51:26'),
(9, 1, 'Abierto', 'En proceso', 3, '2026-08-23 05:51:46'),
(10, 3, 'Abierto', 'En proceso', 3, '2026-08-23 18:11:40'),
(11, 2, 'Abierto', 'Prioridad confirmada: Baja', 3, '2026-08-23 18:29:31'),
(12, 2, 'Abierto', 'Prioridad confirmada: Media', 3, '2026-08-23 18:34:20'),
(13, 2, 'Abierto', 'Prioridad confirmada: Crítica', 3, '2026-08-23 18:34:30'),
(14, 2, 'Abierto', 'En proceso', 3, '2026-08-23 18:34:42'),
(15, 2, 'Abierto', 'Prioridad confirmada: Baja', 3, '2026-08-23 18:34:51'),
(16, 2, 'Abierto', 'Prioridad confirmada: Media', 3, '2026-08-23 18:40:42'),
(17, 2, 'En proceso', 'Cerrado', 3, '2026-08-23 18:41:08'),
(18, 2, 'Cerrado', 'Reabierto - Pool', 3, '2026-08-23 18:41:43'),
(19, 2, 'Abierto', 'En proceso', 3, '2026-08-23 18:41:57'),
(20, 4, 'Nuevo', 'Abierto', 3, '2026-08-23 18:42:15'),
(21, 4, 'Abierto', 'Prioridad confirmada: Media', 3, '2026-08-23 18:42:31'),
(22, 5, 'Nuevo', 'Abierto', 1, '2026-08-24 01:01:44'),
(23, 5, 'Abierto', 'En proceso', 3, '2026-08-24 01:13:05'),
(24, 5, 'Abierto', 'Prioridad confirmada: Baja', 3, '2026-08-24 01:13:45'),
(25, 6, 'Nuevo', 'Abierto', 1, '2026-08-24 01:28:57'),
(26, 6, 'Abierto', 'Prioridad confirmada: Media', 3, '2026-08-24 01:32:07'),
(27, 7, 'Nuevo', 'Abierto', 1, '2026-08-24 09:18:55'),
(28, 7, 'Abierto', 'Prioridad confirmada: Alta', 3, '2026-08-24 09:20:58'),
(29, 7, 'Abierto', 'En proceso', 3, '2026-08-24 09:22:00'),
(30, 7, 'En proceso', 'Extendido +123h', 1, '2026-08-24 09:24:06'),
(31, 7, 'En proceso', 'Cerrado', 1, '2026-08-24 09:24:42'),
(32, 7, 'Cerrado', 'Reabierto - Pool', 1, '2026-08-24 09:26:39'),
(33, 6, 'Abierto', 'En proceso', 3, '2026-08-26 13:45:51'),
(34, 7, 'Abierto', 'En proceso', 3, '2026-08-26 13:46:21'),
(35, 4, 'Abierto', 'En proceso', 1, '2026-08-26 14:00:21'),
(36, 4, 'En proceso', 'Cerrado', 1, '2026-08-26 14:00:31');

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
(1, 'Baja', 'Problema menor', 48, 1),
(2, 'Media', 'Problema con impacto moderado', 24, 1),
(3, 'Alta', 'Problema urgente', 8, 1),
(4, 'Critica', 'Problema crítico - sistema caído', 2, 1);

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
(2, 'Técnico', 'Puede gestionar tickets'),
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
(1, 'TKT-2026-85796', 'Prueba del titulo', 'Sistema afectado: Plataforma garza\n\nPrueba de detalles', 1, 2, 1, 2, 3, '2026-08-22 23:33:53', '2026-08-23 05:51:46', '2026-08-25 07:33:53', 'Preuba de tiempo', NULL, 1, NULL, NULL, 1, '2026-08-23 05:51:26'),
(2, 'TKT-2026-40447', 'Prueba del titulo 2', 'Sistema afectado: Plataforma garza\n\naaa', 3, 2, 2, 2, 3, '2026-08-23 04:44:30', '2026-08-23 18:41:57', '2026-08-25 12:44:30', 'Asi?', 'Media', 2, NULL, NULL, 1, '2026-08-23 18:41:43'),
(3, 'TKT-2026-85615', 'Solo pool', 'Sistema afectado: Plataforma garza\n\ndf', 1, 2, 3, 2, 3, '2026-08-23 04:55:29', '2026-08-23 18:11:40', '2026-08-25 12:55:29', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(4, 'TKT-2026-21279', 'Prueba', 'Sistema afectado: sda\n\nada', 3, 4, 2, 9, 1, '2026-08-23 18:42:15', '2026-08-26 14:00:31', '2026-08-26 02:42:15', 'a', 'Media', 4, NULL, NULL, 0, NULL),
(5, 'TKT-2026-07680', 'Prueba del titulo', 'Sistema afectado: Plataforma garza\n\nw', 1, 2, 1, 6, 3, '2026-08-24 01:01:44', '2026-08-24 01:13:45', '2026-08-26 09:01:44', NULL, 'Baja', NULL, NULL, NULL, 0, NULL),
(6, 'TKT-2026-16065', 'PRIORIDADA', 'Sistema afectado: ASDA\n\nASD', 1, 2, 3, 2, 3, '2026-08-24 01:28:57', '2026-08-26 13:45:51', '2026-08-26 09:28:57', NULL, 'Media', NULL, NULL, NULL, 0, NULL),
(7, 'TKT-2026-38677', 'Prueba del titulo 3', 'Sistema afectado: sda\n\n123456', 1, 2, 2, 9, 3, '2026-08-24 09:18:55', '2026-08-26 13:46:21', '2026-08-31 20:18:55', 'error en el comanpront', 'Alta', 3, 123, '2026-08-24 09:24:06', 1, '2026-08-24 09:26:39');

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
(1, 'ADMIN001', 'Administrador', 'Sistema', '', 'admin@dar.com', 'admin123', 'Administrador', 1, '2026-08-22 21:32:29', 0, '2026-08-26 14:51:55'),
(3, 'TEC001', 'Luis', 'Gómez', 'Pérez', 'luis.gomez@dar.com', 'tec123', 'Técnico de TI', 1, '2026-08-23 03:16:31', 0, NULL),
(4, 'USR001', 'José', 'Ramírez', 'Martínez', 'jose.ramirez@dar.com', 'user123', 'Analista', 1, '2026-08-23 03:16:31', 0, NULL),
(7, 'INACTIVO01', 'Usuario', 'Inactivo', NULL, 'inactivo@dar.com', 'test123', 'Empleado', 0, '2026-08-26 14:41:51', 0, NULL);

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
(2, 3),
(3, 4),
(3, 7);

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
  MODIFY `Id_archivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `Id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `Id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `comentario`
--
ALTER TABLE `comentario`
  MODIFY `Id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `encuestasatisfaccion`
--
ALTER TABLE `encuestasatisfaccion`
  MODIFY `Id_encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `Id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historialticket`
--
ALTER TABLE `historialticket`
  MODIFY `Id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `Id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  MODIFY `Id_prioridad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `Id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ticket`
--
ALTER TABLE `ticket`
  MODIFY `Id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `Id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  ADD CONSTRAINT `ticket_ibfk_6` FOREIGN KEY (`Id_tecnico_asignado`) REFERENCES `usuario` (`Id_usuario`),
  ADD CONSTRAINT `ticket_ibfk_7` FOREIGN KEY (`Id_encuesta`) REFERENCES `encuestasatisfaccion` (`Id_encuesta`),
  ADD CONSTRAINT `ticket_ibfk_8` FOREIGN KEY (`Id_encuesta`) REFERENCES `encuestasatisfaccion` (`Id_encuesta`);

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
