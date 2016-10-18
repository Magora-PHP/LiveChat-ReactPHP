### Chat server:

`php artisan chat:serve`

### Installation:

Create empty mysql db for example `livechat`

`composer create-project`

Setup `.env` file

`chmod -R 777 storage`

`php artisan migrate`

Configure webserver documentroot to `public` folder

Start chat server

Done. All room messages will be updated immediately for all room clients.