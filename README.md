Build and run the containers:

```bash
docker-compose up -d --build
```

```
composer install
```

```
docker exec app php /app/bin/console doctrine:schema:update --force
```
