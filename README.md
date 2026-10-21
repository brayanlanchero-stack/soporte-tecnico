# Sistema de Gestión de Solicitudes de Soporte Técnico

## 1. Descripción del proyecto

Este proyecto consiste en el desarrollo de una aplicación web para gestionar solicitudes de soporte técnico. Permite registrar tickets, consultar su estado, asignarlos a técnicos, registrar comentarios, documentar soluciones y cancelar solicitudes cuando corresponda.

El sistema utiliza roles de usuario para controlar el acceso a las diferentes funcionalidades.

## 2. Objetivo general

Desarrollar un sistema web que facilite la administración y el seguimiento de solicitudes de soporte técnico, permitiendo organizar los tickets y controlar las actividades de los usuarios según sus permisos.

## 3. Tecnologías utilizadas

- **PHP:** lenguaje de programación utilizado para desarrollar la lógica del sistema.
- **MySQL:** sistema de gestión de bases de datos para almacenar usuarios, tickets y comentarios.
- **XAMPP:** entorno local para ejecutar Apache y MySQL.
- **Visual Studio Code:** editor utilizado para desarrollar y modificar el código.
- **HTML:** estructura de las páginas web.
- **Git:** herramienta de control de versiones.
- **GitHub:** plataforma para alojar y compartir el repositorio del proyecto.

## 4. Roles del sistema

### Administrador
- Gestionar usuarios.
- Crear cuentas y administrar sus permisos.
- Asignar tickets a técnicos.
- Consultar y administrar solicitudes.
- Actualizar estados y cancelar tickets.

### Técnico
- Consultar los tickets que tiene asignados.
- Actualizar el estado de sus tickets.
- Registrar comentarios y soluciones según los permisos implementados.

### Solicitante
- Registrar solicitudes de soporte técnico.
- Consultar sus tickets.
- Agregar comentarios cuando la solicitud lo permita.

## 5. Funcionalidades principales

- Inicio y cierre de sesión.
- Administración de usuarios y roles.
- Activación y desactivación de cuentas.
- Registro de solicitudes de soporte técnico.
- Identificación de tickets mediante un número único.
- Consulta y filtrado de tickets por diferentes criterios.
- Asignación de solicitudes a técnicos.
- Actualización del estado de los tickets.
- Registro y consulta de comentarios.
- Registro de la solución y las fechas de resolución y cierre.
- Cancelación de tickets con su respectivo motivo.
- Control de acceso según el rol del usuario.

## 6. Requisitos para ejecutar el proyecto

- Tener instalado XAMPP.
- Tener instalado Visual Studio Code o un editor de código equivalente.
- Contar con un navegador web.
- Tener Git instalado si se desea trabajar con el repositorio.
- Disponer de la base de datos MySQL del proyecto.

## 7. Instalación y ejecución

1. Instalar y abrir XAMPP.
2. Iniciar los servicios Apache y MySQL.
3. Ubicar la carpeta del proyecto en `C:\xampp\htdocs\soporte-tecnico`.
4. Abrir phpMyAdmin desde `http://localhost/phpmyadmin`.
5. Crear o seleccionar la base de datos `soporte_tecnico`.
6. Crear las tablas necesarias del sistema mediante el SQL correspondiente al proyecto.
7. Revisar el archivo `conexion.php` y verificar que los datos de conexión correspondan a la instalación local de MySQL.
8. Abrir el sistema desde `http://localhost/soporte-tecnico/`.

## 8. Pruebas realizadas

Durante el desarrollo se han realizado pruebas funcionales para verificar:

- La conexión entre PHP y MySQL.
- El inicio de sesión y el rechazo de cuentas desactivadas.
- Las restricciones de acceso según los roles.
- La visualización de los tickets asignados a cada técnico.
- La asignación y actualización de estados.
- El registro de comentarios.
- La resolución y el cierre de tickets.
- La cancelación de tickets y el almacenamiento de su motivo en MySQL.

## 9. Control de versiones

Git se utiliza para registrar los cambios realizados durante el desarrollo del sistema. GitHub permite alojar el repositorio remoto y facilitar el seguimiento de las versiones del proyecto.

## 10. Mantenimiento y evolución

El sistema puede ampliarse en el futuro mediante mejoras en la interfaz, generación de reportes, notificaciones sobre cambios de estado, pruebas automatizadas y nuevas funcionalidades para la gestión de solicitudes.

## 11. Estado del proyecto

El sistema se encuentra en etapa de desarrollo y verificación. Las funcionalidades descritas deben comprobarse en el entorno de ejecución antes de considerar terminada la entrega.
