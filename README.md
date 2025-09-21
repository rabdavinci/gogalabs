# SAP Website (Dockerized with Automated HTTPS)

Файлы:
- `html/index.html` — статический сайт (Bootstrap, русский язык).
- `nginx.conf` — конфигурация nginx для статической выдачи.
- `Dockerfile` — собирает образ на базе nginx.
- `docker-compose.yml` — автоматизированная настройка с nginx-proxy и Let's Encrypt.

## Автоматическая HTTPS с nginx-proxy

Этот проект использует nginx-proxy и letsencrypt-nginx-proxy-companion для автоматического получения и обновления SSL сертификатов.

### Преимущества этого подхода:
- ✅ **Автоматическое получение SSL сертификатов**
- ✅ **Автоматическое обновление сертификатов**
- ✅ **Автоматическое перенаправление HTTP → HTTPS**
- ✅ **Поддержка нескольких доменов**
- ✅ **Простое добавление новых сайтов**
- ✅ **Функциональная контактная форма с email уведомлениями**

## Быстрый запуск

### 1. Настройте email для контактной формы:
```bash
# Скопируйте пример конфигурации
cp .env.example .env

# Отредактируйте .env файл и добавьте ваш Gmail App Password
nano .env
```

**Получение Gmail App Password:**
1. Перейдите на https://myaccount.google.com/apppasswords
2. Выберите "Mail" и создайте пароль приложения
3. Скопируйте пароль в файл `.env`

### 2. Создайте внешнюю сеть Docker:
```bash
docker network create proxy
```

### 3. Запустите все сервисы:
```bash
docker-compose up -d --build
```

### 4. Готово! 
Ваш сайт будет доступен по адресу:
- **HTTP**: `http://gogalabs.com` (автоматически перенаправляется на HTTPS)
- **HTTPS**: `https://gogalabs.com` (SSL сертификат получается автоматически)
- **Contact Form**: Отправляет письма на `usmonovgayrat89@gmail.com`

## Настройка DNS

Убедитесь, что ваши DNS записи указывают на сервер:
```
A    gogalabs.com     → IP_ВАШЕГО_СЕРВЕРА
A    www.gogalabs.com → IP_ВАШЕГО_СЕРВЕРА
```

## Локальная разработка (без HTTPS)

Для локальной разработки можете использовать упрощенную версию:

```bash
# Остановите полную версию
docker-compose down

# Запустите только ваш сайт на порту 8080
docker build -t sapsite:latest .
docker run -d --name sapsite-dev -p 8080:80 sapsite:latest
```

Сайт будет доступен по адресу: `http://localhost:8080`

## Структура проекта

```
sap-website-docker/
├── docker-compose.yml    # Основная конфигурация
├── Dockerfile           # Образ для вашего сайта
├── nginx.conf          # Конфигурация nginx
├── html/               # Статические файлы сайта
│   └── index.html
├── backend/            # Backend API для контактной формы
│   ├── server.js      # Express.js сервер
│   ├── package.json   # Node.js зависимости
│   └── Dockerfile     # Образ для backend
├── .env.example       # Пример настроек email
└── README.md          # Документация
```

## Функции контактной формы

- 📧 **Email уведомления** на `usmonovgayrat89@gmail.com`
- 🛡️ **Rate limiting** (5 запросов за 15 минут)
- ✅ **Валидация данных** на клиенте и сервере
- 🔒 **Безопасность** с helmet.js и CORS
- 📱 **Responsive UI** с Bootstrap alerts
- 🚀 **Асинхронная отправка** без перезагрузки страницы

## Добавление новых сайтов

Чтобы добавить новый сайт к этой же инфраструктуре:

1. Добавьте новый сервис в `docker-compose.yml`:
```yaml
  newsite:
    build: ./path-to-new-site
    container_name: newsite
    networks:
      - proxy
    environment:
      - VIRTUAL_HOST=newdomain.com,www.newdomain.com
      - LETSENCRYPT_HOST=newdomain.com,www.newdomain.com
      - LETSENCRYPT_EMAIL=usmonovgayrat89@gmail.com
    restart: unless-stopped
```

2. Перезапустите: `docker-compose up -d`

## Мониторинг и логи

```bash
# Проверить статус всех контейнеров
docker-compose ps

# Посмотреть логи nginx-proxy
docker logs nginx-proxy

# Посмотреть логи Let's Encrypt
docker logs nginx-proxy-letsencrypt

# Посмотреть логи вашего сайта
docker logs sapsite
```

## Устранение проблем

### Сертификат не получается
1. Проверьте DNS записи: `nslookup gogalabs.com`
2. Проверьте доступность порта 80: `curl -I http://gogalabs.com`
3. Проверьте логи: `docker logs nginx-proxy-letsencrypt`

### Сайт недоступен
1. Проверьте статус контейнеров: `docker-compose ps`
2. Проверьте сеть: `docker network ls | grep proxy`
3. Перезапустите сервисы: `docker-compose restart`