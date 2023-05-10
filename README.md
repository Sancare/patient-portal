# patient-portal

🔭 A web-based repository to share information about clinal trials and statistical analyses on real-life data

## ❯ Installation

TODO

## ❯ Local development

### Windows

To run this project, you will need:

* Docker with WSL as a backend
* A WSL environment, with docker enabled (it's been tested on WSL2 only)

You can then start a terminal and follow the linux steps (except the docker setup).

### Linux

You need Docker intalled, see [here](https://docs.docker.com/engine/install/ubuntu/).

You also need PHP with the appropriate extensions. You can install them with:

```bash
# Add the PHP PPA
sudo add-apt-repository ppa:ondrej/php
sudo apt update

sudo apt install php8.2 php8.2-xml php8.2-intl php8.2-pdo php8.2-mbstring php8.2-pgsql
```

Then install [composer](https://getcomposer.org/download/) and the [Symfony-cli](https://symfony.com/download).

Then you can setup the database with:

```bash
docker compose up -d
```

Then setup the project using:

```bash
composer install
bin/console doctrine:migrations:migrate
```

You can then start the dev server with:

```bash
symfony server:start
```

The server should be available on a random port on localhost (See the output log for details)

### MacOS

TODO
