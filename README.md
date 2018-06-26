# Hashicorp Vault for PHP Web Application Security - PoC

This is a **Proof-of-Concept** for having a PHP web application connect to a database using credentials provided by [Hashicorp Vault]. The goal is to have a basic understanding how [Hashicorp Vault] works in managing secrets and how web applications can make use of it to secure their authentication credentials.

## Requirements

We're using [Docker] to set up our environment:

- Web server: running [Nginx](https://www.nginx.com/)
- PHP FPM: running [PHP FPM 7.2](https://secure.php.net/fpm)
- Database server: running [MySQL 5.7](https://www.mysql.com)
- Secrets manager: running [Hashicorp Vault]

## Setup

Follow these steps to get started quickly

### 1. Clone repository

Clone this project from [github.com/dragonbe/poc-webapp-vault](https://github.com/dragonbe/poc-webapp-vault)

```
git clone https://github.com/dragonbe/poc-webapp-vault
cd poc-webapp-vault/
```

### 2. Launch Docker Compose

We have everything setup with Docker Compose so it's easy to launch the application

```
docker-compose up
```

### 3. Provision Hashicorp Vault

For your convenience we have created a provision script to set up vault so it's ready to accept your requests

```
/bin/bash ./setup_vault.sh
```

### 4. Check if it's all working

Point your browser to [localhost:8080](http://localhost:8080/index.php) and you should see the static EU countries page.

For the dynamic page, point to [localhost:8080/vault.php](http://localhost:8080/vault.php).

At first you'll see an error message that states you don't have permissions.

> Not allowed to retrieve credentials: Client error: `GET http://vault:8200/v1/database/creds/webapp` resulted in a `403 Forbidden` response: {"errors":["permission denied"]} 

Follow the link to retrieve the credentials and come back.

Now you should see the same EU countries page, but now coming from the database.

This PoC is provided "as-is" and is licensed MIT.

## Notes available

Please see also my [notes](docs/notes.md) for more details about the setup and configuration of [Hashicorp Vault].

[Hashicorp Vault]: https://vaultproject.io
[Docker]: https://docker.com