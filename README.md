# PRC-BACKEND
PRC-backend is a server side application that works as a RESTful API to the PRC-frontend application.

## Configurations
To configure the project we need to follow the next steps:

### Clone the repository and access
```shell script
git clone https://github.com/rcebrian/PRC-backend.git
```
```shell script
cd PRC-backend
``` 

### Laravel configuration
##### Install composer
```shell script
composer install
```

##### Create a .env file
```shell script
cp .env.example .env
```

##### Generate a new key
```shell script
php artisan key:generate
```

##### Run npm dependencies
```shell script
npm install
```

### Python configuration
```shell script
# create the virtual environment for Linux
virtualenv -p python3 --no-site-packages vendor/python/venv
# install requirements
$ vendor/python/venv/bin/python -m pip install -r app/Python/requirements.txt

# create the virtual environment for Windows
virtualenv -p python3 vendor/python/venv
vendor\python\venv\Scripts\python -m pip install -r storage/app/python/requirements.txt
```

###Laravel authentication
```shell script
#Install the Vue scaffolding provided by Laravel
composer require laravel/ui
php artisan ui vue --auth
npm install
npm run dev
```
