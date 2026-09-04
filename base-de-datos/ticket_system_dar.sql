-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-09-2026 a las 20:38:11
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `Id_rol` int(11) NOT NULL,
  `Nombre_rol` varchar(50) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariorol`
--

CREATE TABLE `usuariorol` (
  `Id_rol` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `Id_archivo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `Id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `Id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentario`
--
ALTER TABLE `comentario`
  MODIFY `Id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestasatisfaccion`
--
ALTER TABLE `encuestasatisfaccion`
  MODIFY `Id_encuesta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `Id_estado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historialticket`
--
ALTER TABLE `historialticket`
  MODIFY `Id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `Id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  MODIFY `Id_prioridad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `Id_rol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ticket`
--
ALTER TABLE `ticket`
  MODIFY `Id_ticket` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `Id_usuario` int(11) NOT NULL AUTO_INCREMENT;

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
