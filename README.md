### Anemic domain model and business logic in services

The project contains an implementation of complex business requirements with Anemic Domain Model and business logic in services.
The idea is to show that it's not possible to move everything which is in services to entities
without complicating the code and breaking level of abstraction, which means that services are always necessary.  

Description:\
https://habr.com/ru/articles/800789/

Running:
```sh
php composer.phar install
php init --env=Development
docker-compose up -d
docker exec -it adm-frontend /bin/bash -c 'php yii migrate/up --interactive=0'
```

Project opens ports 80, 81, 3306. Change them in `docker-compose.yml` and one port in `frontend/views/site/index.php` before running, if they are busy on your machine.

Endpoints `/product/save` and `/product/send-for-review` call `sleep(3)` for testing locks in parallel processes.

Test page:\
[http://127.0.0.1](http://127.0.0.1)

You can edit GET-parameters by clicking on them.

Login page:\
[http://127.0.0.1/site/login](http://127.0.0.1/site/login)

```
user
123456
```
