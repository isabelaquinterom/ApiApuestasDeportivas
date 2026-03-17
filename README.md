# ApiApuestasDeportivas

API REST desarrollada con laravel para gestionar un sistema de apuestas deportivas.

Permite registrar usuarios, iniciar sesion, autenticarse con JWT Y OTP por correo, consultar eventos deportivos, realizar apuestas, cobrar ganancias y administar eventos y resultados 

---
## TECNOLOGIAS USADAS 

- PHP
- Laravel
- JWT para autenticacion
- MySQL
- Postman para pruebas de la API
- Laragon

----

## FUNCIONALIDADES PRINCIPALES

- Registro de usuarios 
- Inicio de sesion con JWT
- Autenticacion de doble factor (2FA) con OTP por correo
- Consulta de eventos deportivos
- Creacion de eventos con cuotas
- Realizacion de apuestas
- Consulta de apuestas realizadas
- Cobro de apuestas ganadas
- Simulacion de resultados 
- Ajuste de saldo de usuario 
- Gestion de eventos, apuestas y resultados por parte del administrador

----
## ROLES DEL SISTEMA

## Administrador 

Puede relizar las siguientes acciones:

- Crear eventos deportivos
- Definir cuotas de apuestas 
- Simular resultados de eventos
- Ver todas las apuestas
- Listar usuarios 
- Ajustar saldo de usuarios 

## Usuario

Puede realizar las siguientes acciones:

- Registrarse en el sistema
- Iniciar sesion 
- verificar OTP
- Cosultar eventos deportivos 
- Realizar apuestas 
- Consultar sus apuestas 
- Consultar su saldo
- Cobrar apuestas ganadas

----
## INSTALACION

### 1. Clonar el repositorio

```bash
git clone https://github.com/TU_USUARIO/ApiApuestasDeportivas.git

2. Entrar a la carpeta del proyecto
cd ApiApuestasDeportivas

3. Instalar dependencias
composer install

4. Copiar el archivo de entorno
cp .env.example .env

5. Generar la clave de la aplicacion
php artisan key:generate

6. Configurar la base de datos en el archivo .env

Debes configurar los datos de conexion a MySQL, por ejemplo:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_apuestas_deportivas
DB_USERNAME=root
DB_PASSWORD=

7. Configurar el correo en el archivo .env

Ejemplo:

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_password_de_aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_correo@gmail.com
MAIL_FROM_NAME="Apuestas Deportivas"

8. Ejecutar migraciones
php artisan migrate

9. Iniciar el servidor
php artisan serve
```

## FLUJO DE AUTENTICACION 

La Api utiliza autenticacion en dos pasos:
1. El usuario envia correo y contraseña 
2. El sistema valida credenciales 
3. Se genera un codigo OTP
4. El codigo OTP se envia al correo del usuario 
5. EL usuario envia el OTP al endpint de verificacion
6. Si el OTP es correcto, el sistema genera el token JWT
---
## DOCUMENTACION DE LA API

## Documentacion de la API

La coleccion de Postman del proyecto se encuentra en el siguiente archivo:

- [ApiApuestasDeportivas.postman_collection.json](docs/ApiApuestasDeportivas.postman_collection.json)

Este archivo puede importarse en Postman para probar todos los endpoints de la API.
 
## AUTENTICACION DE ENDPINTS PROTEGIDOS 

Despues de iniciar sesion y verificar el OTP, se debe enviar el token en los endpoints protegidos de esta forma 

Authorization:Bearer TOKEN

## BASE DE DATOS 

Tablas principales del sistema 

- Users
- Eventos
- Cuotas
- Apuestas
- Resultados 

## Autora

- Proyecto desarrollado por:

ISABELA QUINTERO MURILLO 

- Asignatura: 

PROGRAMACION BACKEND 

