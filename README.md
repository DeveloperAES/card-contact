# 💳 PHP-TARJETAS

Proyecto web desarrollado en **PHP** con base de datos **SQLite** para la gestión de personas y compañías.

---

## 🚀 Características

- Sistema básico de autenticación.
- Gestión de personas y compañías.
- Conexión a base de datos SQLite (sin necesidad de servidor MySQL).
- Estructura modular con includes y templates.
- Interfaz sencilla y liviana.

---

## 🧩 Estructura del proyecto

PHP-TARJETAS/
│
├── assets/ # Recursos estáticos (CSS, JS, imágenes)
├── includes/ # Archivos PHP reutilizables (auth, db, header)
├── libs/ # Librerías o clases adicionales
├── templates/ # Plantillas HTML o componentes
├── tmp/ # Archivos temporales (excluidos del repo)
├── views/ # Vistas principales del sistema
├── database.db # Base de datos SQLite (ignorada en Git)
├── .gitignore
└── README.md


---

## ⚙️ Requisitos

- **PHP 8.0+**
- **SQLite3**
- Navegador web moderno

---

## ▶️ Ejecución local (sin XAMPP)

1. Abre una terminal en la carpeta del proyecto.
2. Ejecuta el servidor embebido de PHP:
   ```bash
   php -S localhost:8000
