# Guardia Nocturna — Requisitos de Servidor y Guía de Producción (Laravel)

Este documento describe:

- Qué **servidor (VPS)** contratar para ejecutar el sistema.
- Cómo **instalar, configurar y operar** el sistema en producción (comandos, servicios, seguridad, respaldos).

> Stack detectado en el proyecto: **Laravel 12**, **PHP ^8.2**, **Vite/Tailwind**, **MySQL/MariaDB**, generación de **PDF (dompdf)** y **QR**.

---

## 1) Requisitos para contratar un VPS (Nube)

### 1.1 Recomendación por tamaño

**Opción mínima (uso bajo / pocas personas simultáneas)**

- CPU: **2 vCPU**
- RAM: **4 GB**
- Disco: **60–80 GB SSD/NVMe**
- Sistema: Ubuntu LTS (22.04 o 24.04)
- Backups: snapshot diario (ideal) + backup lógico de BD

**Opción recomendada (cuartel / operación diaria / margen de crecimiento)**

- CPU: **4 vCPU**
- RAM: **8 GB**
- Disco: **100–160 GB SSD/NVMe**
- Backups: snapshot diario + retención 7–14 días

**Opción alta (mucho tráfico, reportes pesados, crecimiento, mayor seguridad operativa)**

- CPU: **6–8 vCPU**
- RAM: **16 GB**
- Disco: **200+ GB SSD/NVMe**
- BD separada o servicio administrado de MySQL (opcional)

### 1.2 Componentes a exigir al proveedor

- **IP pública fija**
- **Backups automáticos** (ideal: diario) y posibilidad de restauración
- **Disco SSD/NVMe**
- **Firewall** (a nivel proveedor) + reglas de red
- **Monitoreo** básico (CPU/RAM/Disco/Red)
- **Soporte** y SLA razonable

### 1.3 Requisitos de software (en el VPS)

- **Nginx** (o Apache; recomendado Nginx)
- **PHP 8.2+** con PHP-FPM
- **MySQL 8** o **MariaDB 10.6+**
- **Node.js 20 LTS** (para compilar assets con Vite)
- **Composer**
- **Certificado SSL** (Let’s Encrypt)

### 1.4 Puertos de red

- **80/tcp** (HTTP) → redirigir a HTTPS
- **443/tcp** (HTTPS)
- **22/tcp** (SSH) → restringir por IP si es posible

**No exponer públicamente**:

- 3306/tcp (MySQL) salvo que sea estrictamente necesario y con IP allowlist.

---

## 2) Guía de instalación y despliegue en producción (Ubuntu + Nginx)

### 2.1 Supuestos

- Dominio: `guardia.tudominio.cl`
- Ruta de proyecto: `/var/www/guardia_nocturna`
- Usuario de despliegue: `deploy`
- Web server: Nginx
- PHP-FPM: php8.2-fpm

---

## 3) Instalación de dependencias del sistema

### 3.1 Paquetes base

```bash
sudo apt update && sudo apt -y upgrade
sudo apt -y install git unzip curl ca-certificates software-properties-common
```

### 3.2 PHP 8.2 + extensiones

Instalar PHP y extensiones típicas para Laravel:

```bash
sudo apt -y install php8.2-fpm php8.2-cli php8.2-common \
  php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
  php8.2-gd php8.2-bcmath php8.2-intl
```

Reiniciar PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

### 3.3 Nginx

```bash
sudo apt -y install nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 3.4 MySQL o MariaDB

**Opción A (simple): instalar MySQL en el mismo VPS**

```bash
sudo apt -y install mysql-server
sudo systemctl enable mysql
sudo systemctl start mysql
```

Asegurar instalación:

```bash
sudo mysql_secure_installation
```

Crear BD y usuario (ejemplo):

```sql
CREATE DATABASE guardia_nocturna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'guardia_user'@'localhost' IDENTIFIED BY 'PASSWORD_FUERTE_AQUI';
GRANT ALL PRIVILEGES ON guardia_nocturna.* TO 'guardia_user'@'localhost';
FLUSH PRIVILEGES;
```

**Opción B (recomendada si el presupuesto lo permite): BD administrada**

- Reduce riesgos operativos
- Backups/restores suelen ser más fáciles

### 3.5 Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 3.6 Node.js (para Vite)

Ejemplo con Node 20:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt -y install nodejs
node -v
npm -v
```

---

## 4) Despliegue del código

### 4.1 Crear usuario y carpeta

```bash
sudo adduser deploy
sudo mkdir -p /var/www/guardia_nocturna
sudo chown -R deploy:www-data /var/www/guardia_nocturna
sudo chmod -R 775 /var/www/guardia_nocturna
```

### 4.2 Clonar repositorio

```bash
cd /var/www/guardia_nocturna
git clone <URL_DEL_REPO> .
```

### 4.3 Variables de entorno

Crear `.env` (NO commitearlo) y configurar:

- `APP_NAME`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://guardia.tudominio.cl`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_DATABASE=guardia_nocturna`
- `DB_USERNAME=guardia_user`
- `DB_PASSWORD=...`
- `MAIL_*` (si se enviarán correos)

Generar llave de app:

```bash
php artisan key:generate
```

---

## 5) Instalación de dependencias del proyecto

### 5.1 Composer (producción)

```bash
composer install --no-dev --optimize-autoloader
```

### 5.2 Compilar assets (Vite)

```bash
npm ci
npm run build
```

---

## 6) Permisos y almacenamiento

Laravel necesita escritura en `storage/` y `bootstrap/cache/`.

```bash
sudo chown -R deploy:www-data /var/www/guardia_nocturna
sudo find /var/www/guardia_nocturna/storage -type d -exec chmod 775 {} \;
sudo find /var/www/guardia_nocturna/bootstrap/cache -type d -exec chmod 775 {} \;
```

Crear enlace público (si se usa almacenamiento público):

```bash
php artisan storage:link
```

---

## 7) Base de datos (migraciones)

Ejecutar migraciones en producción:

```bash
php artisan migrate --force
```

> `--force` es necesario en producción para evitar confirmaciones interactivas.

---

## 8) Optimización y cachés (Laravel)

Recomendado tras cada despliegue:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

Si hay problemas, limpiar:

```bash
php artisan optimize:clear
```

---

## 9) Nginx (Virtual Host)

Crear archivo:

- `/etc/nginx/sites-available/guardia_nocturna`

Ejemplo:

```nginx
server {
    listen 80;
    server_name guardia.tudominio.cl;

    root /var/www/guardia_nocturna/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\. {
        deny all;
    }
}
```

Activar sitio:

```bash
sudo ln -s /etc/nginx/sites-available/guardia_nocturna /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 10) SSL (HTTPS) con Let’s Encrypt

```bash
sudo apt -y install certbot python3-certbot-nginx
sudo certbot --nginx -d guardia.tudominio.cl
```

---

## 11) Cron de Laravel (Scheduler)

Agregar al crontab (usuario root o deploy):

```bash
* * * * * cd /var/www/guardia_nocturna && php artisan schedule:run >> /dev/null 2>&1
```

---

## 12) Colas (Queues) y Supervisor (si aplica)

Si el sistema usa trabajos en segundo plano (emails, importaciones, etc.), usar `queue:work`.

Instalar supervisor:

```bash
sudo apt -y install supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

Config ejemplo: `/etc/supervisor/conf.d/guardia_queue.conf`

```ini
[program:guardia_queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/guardia_nocturna/artisan queue:work --sleep=1 --tries=3 --timeout=120
autostart=true
autorestart=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/guardia_nocturna/storage/logs/queue.log
```

Aplicar:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Tras un despliegue:

```bash
php artisan queue:restart
```

---

## 13) Seguridad básica recomendada

- UFW:

```bash
sudo apt -y install ufw
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

- Fail2ban (opcional pero recomendado):

```bash
sudo apt -y install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

- SSH:
  - Deshabilitar login por password (usar llaves)
  - Restringir por IP si es posible

---

## 14) Respaldos (Backups)

### 14.1 Backup de Base de Datos (lógico)

Ejemplo comando:

```bash
mysqldump -u guardia_user -p guardia_nocturna | gzip > /backups/guardia_nocturna_$(date +%F).sql.gz
```

Recomendación:

- Retención local (7–14 días)
- Copia a almacenamiento externo (S3 / Backblaze / Google Drive corporativo)

### 14.2 Backups del servidor

- Snapshot diario del proveedor
- Retención mínima 7 días

---

## 15) Operación: comandos útiles

- Ver estado aplicación (cachés):

```bash
php artisan about
php artisan optimize
php artisan optimize:clear
```

- Mantenimiento:

```bash
php artisan down
php artisan up
```

- Logs:

```bash
tail -f storage/logs/laravel.log
```

---

## 16) Checklist de despliegue (resumen)

En cada actualización (deploy):

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart
sudo systemctl reload nginx
```

---

## 17) Recomendación final para “qué contratar” (texto para cotización)

Contratar un VPS con:

- **4 vCPU / 8 GB RAM / 100 GB SSD/NVMe**
- Ubuntu LTS
- IP fija
- Backups automáticos diarios
- Firewall administrable

Y desplegar un stack:

- Nginx + PHP-FPM (PHP 8.2)
- MySQL/MariaDB
- Node.js (solo para compilar assets durante despliegue)
- Certificado SSL Let’s Encrypt
- Cron de Laravel scheduler
- Supervisor para colas (si aplica)

