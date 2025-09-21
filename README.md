# SAP Website (Dockerized)

Файлы:
- `html/index.html` — статический сайт (Bootstrap, русский язык).
- `nginx.conf` — конфигурация nginx для статической выдачи.
- `Dockerfile` — собирает образ на базе nginx.
- `docker-compose.yml` — простой compose для локального запуска.

## Локальная сборка и запуск

1. Сборка образа:

   docker build -t sapsite:latest .

2. Запуск контейнера:

   docker run -d --name sapsite -p 80:80 sapsite:latest

Или с docker-compose:

   docker-compose up -d --build

## Деплой на сервер (DigitalOcean Droplet)

1. Залейте файлы на сервер (scp/rsync/git).
2. На сервере выполните `docker-compose up -d --build`.
3. Убедитесь, что в DNS добавлены A-записи и nameservers настроены.

## HTTPS (Let's Encrypt)

Для production рекомендую использовать HTTPS. Варианты:
- Воспользоваться DigitalOcean Load Balancer + автоматическим Let's Encrypt.
- Использовать Traefik / Caddy в качестве reverse-proxy, которые умеют автоматически получать сертификаты.
- Запустить certbot на сервере и настроить nginx для выдачи сертификатов (сложнее при контейнерах).