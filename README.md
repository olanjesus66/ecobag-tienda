# EcoBag — Tienda en línea

Tienda de bolsas sustentables desarrollada con PHP, MySQL, JavaScript y jQuery.

## Funciones
- Catálogo con filtros por categoría, precio y material
- Detalle de producto con galería y selector de cantidad
- Carrito de compras dinámico (AJAX)
- Registro e inicio de sesión con contraseñas cifradas
- Finalización de compra con transacciones en base de datos
- Diseño responsive

## Tecnologías
PHP 8 · MySQL · PDO · HTML5 · CSS3 · JavaScript · jQuery

## Seguridad
Consultas preparadas, protección CSRF, escape de salida (XSS), hash de contraseñas con bcrypt, sesiones seguras.

## Instalación
1. Crear una base de datos llamada `ecobag` e importar `ecobag.sql`.
2. Copiar `includes/config.example.php` como `includes/config.php` y completar los datos.
3. Colocar el proyecto en un servidor con PHP 8+ (por ejemplo, XAMPP) y abrir `index.php`.