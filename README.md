# meets
Тестовое задание. [Текст задания](Reference.md)

Установка:
===

Требования:
* Symfony CLI 5.2+
* Yarn
* RabbitMQ

Процесс установки:
* Склонировать проект
* В папке проекта выполнить команды: 
```
symfony composer install
yarn install
```
* В файле `.env.local` прописать `DATABASE_URL`, `MESSENGER_TRANSPORT_DSN` и `MAILER_DSN`
* применить миграции: ```symfony console doctrine:migrations:migrate```
