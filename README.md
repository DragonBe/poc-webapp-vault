# Hashicorp Vault for PHP Web Application Security - PoC

This is a **Proof-of-Concept** for having a PHP web application connect to a database using credentials provided by 
[Hashicorp Vault]. The goal is to have a basic understanding how [Hashicorp Vault] works in managing secrets 
and how web applications can make use of it to secure their authentication credentials.

The server in this scenario is running in **dev** mode. The dev server stores all its data in-memory (but still encrypted), 
listens on localhost without TLS, and automatically unseals and shows you the unseal key and root access key. 
**Do not run a dev server in production!**

## Requirements

We're using [Docker] to set up our environment:

- Web server: running [Nginx]
- PHP FPM: running [PHP FPM 7.2]
- Database server: running [MySQL 5.7]
- Secrets manager: running [Hashicorp Vault]
- [cURL]
- [jq]

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

For your convenience we have created two provisioning scripts to set up vault so it's ready to accept your requests

If you want to use [Vault CLI], you need to have [Hashicorp Vault] installed locally, and then run execute
```bash
/bin/bash ./setup_vault_with_vault_cli.sh
```
in your terminal.

Or, if you want to use [Vault HTTP API], you **do not need** [Hashicorp Vault] installed locally, just execute
```bash
/bin/bash ./setup_vault_with_http_api.sh
```

### 4. Check if it's all working

Point your browser to [localhost:8080](http://localhost:8080/index.php) and you should see the static EU countries page.

For the dynamic page, point to [localhost:8080/vault.php](http://localhost:8080/vault.php).

At first you'll see an error message that states you don't have permissions.

> Not allowed to retrieve credentials: Client error: `GET http://vault:8200/v1/database/creds/webapp` resulted in a `403 Forbidden` response: {"errors":["permission denied"]} 

[![Vault Error](http://cdn.in2it.be/vault/webapp/vault_error.png)](http://cdn.in2it.be/vault/webapp/vault_error.png)

Follow the link to retrieve the credentials and come back.

Now you should see the same EU countries page, but now coming from the database.

[![Vault Success](https://cdn.in2it.be/vault/webapp/vault_success.png)](https://cdn.in2it.be/vault/webapp/vault_success.png)

This PoC is provided "as-is" and is licensed MIT.

## Notes available

Please see also my [notes](docs/notes.md) for more details about the setup and configuration of [Hashicorp Vault].

[Hashicorp Vault]: https://vaultproject.io
[Docker]: https://docker.com
[Nginx]: https://www.nginx.com/
[PHP FPM 7.2]: https://secure.php.net/fpm
[MySQL 5.7]: https://www.mysql.com
[cURL]: https://curl.haxx.se/
[jq]: https://stedolan.github.io/jq/
[Vault CLI]: https://www.vaultproject.io/docs/commands/index.html
[Vault HTTP API]: https://www.vaultproject.io/api/index.html
