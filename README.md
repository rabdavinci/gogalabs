# SAP Website (Dockerized with HTTPS)

Файлы:
- `html/index.html` — статический сайт (Bootstrap, русский язык).
- `nginx.conf` — конфигурация nginx для статической выдачи с поддержкой HTTPS.
- `Dockerfile` — собирает образ на базе nginx.
- `docker-compose.yml` — простой compose для локального запуска с поддержкой SSL.

## Локальная сборка и запуск

1. Сборка образа:

   docker build -t sapsite:latest .

2. Запуск контейнера (HTTP только):

   docker run -d --name sapsite -p 80:80 sapsite:latest

Или с docker-compose:

   docker-compose up -d --build

## HTTPS Setup с Let's Encrypt

### Предварительные требования:
1. Домен должен указывать на ваш сервер (A-записи в DNS)
2. Порты 80 и 443 должны быть открыты в файерволе

### Шаги настройки HTTPS:

1. **Установите certbot на хосте:**
   ```bash
   sudo apt update && sudo apt install -y certbot
   ```

2. **Замените `gogalabs.com` в nginx.conf на ваш реальный домен (уже настроено)**

3. **Временно запустите контейнер только для HTTP (для получения сертификатов):**
   ```bash
   # Создайте временную конфигурацию nginx только для HTTP
   docker run -d --name temp-nginx -p 80:80 -v $(pwd)/html:/usr/share/nginx/html nginx
   ```

4. **Получите SSL сертификаты:**
   ```bash
   sudo certbot certonly --webroot -w $(pwd)/html -d gogalabs.com -d www.gogalabs.com
   ```

5. **Остановите временный контейнер и запустите полную конфигурацию:**
   ```bash
   docker stop temp-nginx && docker rm temp-nginx
   docker-compose up -d --build
   ```

6. **Ваш сайт теперь доступен по HTTPS:** `https://gogalabs.com`

## Автоматическое обновление сертификатов

Добавьте cron job на хосте для автоматического обновления:

```bash
# Откройте crontab
sudo crontab -e

# Добавьте эту строку:
0 2 * * * /usr/bin/certbot renew --quiet && docker exec sapsite nginx -s reload
```

Это будет обновлять сертификаты каждый день в 2:00 AM и перезагружать nginx.

## Деплой на сервер (DigitalOcean Droplet)

1. Залейте файлы на сервер (scp/rsync/git).
2. Настройте DNS записи для вашего домена.
3. Выполните шаги HTTPS Setup выше.
4. На сервере выполните `docker-compose up -d --build`.

## Альтернативные варианты HTTPS

Для production также рекомендуется рассмотреть:
- **DigitalOcean Load Balancer** с автоматическим Let's Encrypt.
- **Traefik** или **Caddy** в качестве reverse-proxy с автоматическими сертификатами.
- **Cloudflare** для SSL termination и CDN.