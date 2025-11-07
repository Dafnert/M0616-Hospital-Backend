#  M0616 Hospital Backend

## Descripción

Este proyecto es el backend del sistema **Hospital M0616**, desarrollado en **PHP con el framework Symfony**.  
Su objetivo es ofrecer una **API REST** que gestione la información de un hospital, incluyendo personal sanitario (enfermeros, médicos, etc.), pacientes y datos asociados.  
Forma parte del módulo **M0616**.



## Tecnologías utilizadas

- **PHP 8.2+**
- **Symfony 7.3**
- **Composer**
- **MySQL / MariaDB**
- **Doctrine ORM 3.5**
- **JSON (para pruebas de API)**



## Instalación

### 1️Clonar el repositorio

bash
git clone https://github.com/Dafnert/M0616-Hospital-Backend.git


### 2️Entrar al directorio del proyecto

bash
cd M0616-Hospital-Backend


### Instalar dependencias

Asegúrate de tener Composer instalado, luego ejecuta:

bash
composer install


### Configurar variables de entorno

Copia el archivo `.env` y adapta los valores a tu entorno local:

bash
cp .env .env.local


Configura especialmente la conexión a la base de datos:

ini
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/hospital"


### Crear la base de datos

bash
php bin/console doctrine:database:create


### Ejecutar las migraciones

bash
php bin/console doctrine:migrations:migrate


### Iniciar el servidor de desarrollo

bash
symfony server:start


El backend estará disponible en:  
 **http://127.0.0.1:8000**



## Uso de la API

Puedes probar los endpoints con herramientas como **Postman** o **Insomnia**.

### Ejemplos de uso

#### Obtener todos los enfermeros

bash
GET http://127.0.0.1:8000/nurse


#### Crear un nuevo enfermero

http
POST http://127.0.0.1:8000/nurse
Content-Type: application/json

{
  "nombre": "Laura",
  "turno": "Noche"
}
```

## Eliminar un enfermero

bash
DELETE http://127.0.0.1:8000/nurse/{id}


*(Cambia las rutas según tu configuración real del controlador.)*

---

## Estructura del proyecto

```
config/         → Configuración del proyecto Symfony  
migrations/     → Migraciones de base de datos  
public/         → Punto de entrada público (index.php)  
src/            → Código fuente (Controladores, Entidades, Repositorios)  
test/           → Pruebas de la API  
.env            → Configuración del entorno  
composer.json   → Dependencias del proyecto
```



