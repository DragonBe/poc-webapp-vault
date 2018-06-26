# Hashicorp Vault - Application Access

To provide a secure way of dealing with application resource access credentials, we're going to look at [Hashicorp Vault](https://vaultproject.io) to manage our application secrets.

## Step 1: Login into Vault as root

In order to get started, we need to login as **root** user. Be sure to have your credentials at hand, as you will need them to login.

```bash
vault login
Token (will be hidden):
```

You should get a response like the following:

```
Success! You are now authenticated. The token information displayed below
is already stored in the token helper. You do NOT need to run "vault login"
again. Future Vault requests will automatically use this token.

Key                Value
---                -----
token              afbbe7a1-44bc-dead-f964-7ccc39e5ccf4
token_accessor     293b4386-4665-a61c-8e3a-4286027c0518
token_duration     ∞
token_renewable    false
token_policies     [root]
```

Let's enable logging at this point.

```bash
vault audit enable file file_path=/vault/logs/vault_audit.log
```

## Step 2: Create an admin policy

By default Vault is set with root privileges, but this privilege level has too much power and should be avoided. It's better to create an administrative account that has less rights than the root privilege, but can still create accounts within Vault.

Reference: 

- https://www.vaultproject.io/guides/identity/policies.html

### Example admin policy

Place the following policy rules in `policies/admin-policy.hcl`:

```json
# Manage auth methods broadly across Vault
path "auth/*"
{
  capabilities = ["create", "read", "update", "delete", "list", "sudo"]
}

# List, create, update, and delete auth methods
path "sys/auth/*"
{
  capabilities = ["create", "read", "update", "delete", "sudo"]
}

# List existing policies
path "sys/policy"
{
  capabilities = ["read"]
}

# Create and manage ACL policies broadly across Vault
path "sys/policy/*"
{
  capabilities = ["create", "read", "update", "delete", "list", "sudo"]
}

# List, create, update, and delete key/value secrets
path "secret/*"
{
  capabilities = ["create", "read", "update", "delete", "list", "sudo"]
}

# Manage and manage secret engines broadly across Vault.
path "sys/mounts/*"
{
  capabilities = ["create", "read", "update", "delete", "list", "sudo"]
}

# Manage database secret engines
path "database/*"
{
  capabilities = ["create", "read", "update", "delete", "list", "sudo"]
}

# Read health checks
path "sys/health"
{
  capabilities = ["read", "sudo"]
}
```

Now it's time to create the **admin policy** in Vault so we can start using it.

```bash
vault policy write admin vault/config/policies/admin-policy.hcl
```

Vault will respond with a success statement making sure everything is registered correctly.

```
Success! Uploaded policy: admin
```

Make sure you look at the example and modify the policy for your own use case! This example is a default example taken from the [Hashicorp Vault Policies Guide](https://www.vaultproject.io/guides/identity/policies.html).

## Step 3: Create a token for policy admin

Now that we have defined an administrative policy, we now should create a token that we can use for administrative purposes.

```bash
vault token create -policy="admin"
```

This will return you some information for the policy purpose.

```
Key                Value
---                -----
token              0e7f0f87-3f60-cbe7-1aa8-6c4f707ffba8
token_accessor     e6c0f09d-73a9-5970-3f45-1973cc7cef90
token_duration     768h
token_renewable    true
token_policies     [admin default]
```

With this token we can now check the capabilities for the role **approle** as we're going to use it for setting up the permissions for our web application.

```bash
vault token capabilities 0e7f0f87-3f60-cbe7-1aa8-6c4f707ffba8 sys/auth/approle
```

This will return you the following capabilities:

```
create, delete, read, sudo, update
```

## Step 4: Login as admin

Now that we have created a policy and token for an administrative user, let's login as user **admin** to get started.

```bash
vault login
Token (will be hidden):
```

When you have completed above steps, you should now be successfully logged in as user **admin**. Please check the output that it's really the case.

```
Success! You are now authenticated. The token information displayed below
is already stored in the token helper. You do NOT need to run "vault login"
again. Future Vault requests will automatically use this token.

Key                Value
---                -----
token              0e7f0f87-3f60-cbe7-1aa8-6c4f707ffba8
token_accessor     e6c0f09d-73a9-5970-3f45-1973cc7cef90
token_duration     767h50m39s
token_renewable    true
token_policies     [admin default]
```

As you can see, our time is limited as an administrative account should perform only those tasks to set up and configure rules, policies and tokens.

## Step 5: Enable AppRole in Vault

The special **AppRole** is not enabled by default in Vault, so we need to enable it.

```bash
vault auth enable approle
```

This will return us again with a success statement that the AppRole authentication method is activated and available in path `auth/approle/`.

```
Success! Enabled approle auth method at: approle/
```

## Step 6: Create the AppRole with the correct policies

Just like we did for the administrative account, we need to define the policy for the AppRole. The following is an example policy for a web application that needs to connect to a database. Store this policy in `webapp-policy.hcl`.

```json
# Login with AppRole
path "auth/approle/login" {
  capabilities = [ "create", "read" ]
}

# Read test data
path "secret/mysql/*" {
  capabilities = [ "read" ]
}

# Read mysql secrets
path "database/*" {
  capabilities = [ "create", "read" ]
}
```

Please make sure you set your policies for your own application as this example only focusses on giving the web application permission to login and read permissions for the MySQL database.

Now that we have the policy set, we can attach this policy to role **webapp**.

```bash
vault policy write webapp vault/config/policies/webapp-policy.hcl
```

Vault will return a success statement to inform you the policy is correctly set.

```
Success! Uploaded policy: webapp
```

Now that the policy is set for our web applications, we need to create the role webapp that uses these policies.

```bash
vault write auth/approle/role/webapp policies="webapp"
```

```
Success! Data written to: auth/approle/role/webapp
```

Check to see all is set correctly for our role webapp.

```bash
vault read auth/approle/role/webapp
```

This will give you a table with the settings for role webapp.

```
Key                      Value
---                      -----
bind_secret_id           true
bound_cidr_list          <nil>
local_secret_ids         false
period                   0
policies                 [webapp]
secret_id_bound_cidrs    <nil>
secret_id_num_uses       0
secret_id_ttl            0
token_bound_cidrs        <nil>
token_max_ttl            0
token_num_uses           0
token_ttl                0
```

## Step 7: Get role and secret ID's to authenticate

Now that the policy is set and the role created for our web application, it's time to collect the credentials our application needs to login into Vault.

### Get the role ID

```bash
vault read auth/approle/role/webapp/role-id
```

```
Key        Value
---        -----
role_id    c5b4edc1-63bf-4b2a-b16d-4ec168328209
```

### Generate a new secret ID

```bash
vault write -f auth/approle/role/webapp/secret-id
```

The `-f` argument ensures that we don't need to provide further parameters.

```
Key                   Value
---                   -----
secret_id             ccdddbfe-449b-07ff-8370-a3f0c6a34155
secret_id_accessor    9507facd-b89a-6a05-d317-8b3373407676
```

## Step 8: Time for the application to login

Now that we have created the `role_id` and `secret_id` we can now use them in our application to login.

The easiest way is to store a `payload.json` file with both ID's on the server that needs to connect (of course ensure only the application has access to it).

```json
{
  "role_id": "c5b4edc1-63bf-4b2a-b16d-4ec168328209",
  "secret_id": "ccdddbfe-449b-07ff-8370-a3f0c6a34155"
}
```

Now we can use [Guzzle](https://packagist.org/packages/guzzlehttp/guzzle) in our web application to connect to our Vault instance and login with our credentials.

A simple PHP script will fetch the client token for us.

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$payload = json_decode(file_get_contents(__DIR__ . '/payload.json'));
$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://vault:8200',
    'timeout'  => 2.0,
    'headers' => [
        'User-Agent' => 'dragonbe/hashicorp-vault/0.0.1 PHP 7.2',
        'Accept' => 'application/json',
    ]
]);

try {
    $loginResponse = $client->request('POST', '/v1/auth/approle/login', [
        'json' => [
            'role_id' => $payload->role_id,
            'secret_id' => $payload->secret_id,
        ],
    ]);
} catch (\GuzzleHttp\Exception\RequestException $requestException) {
    echo 'Unable to get credentials from Vault';
    exit;
} catch (\GuzzleHttp\Exception\ConnectException $connectException) {
    echo 'Unable to connect to Vault';
    exit;
}

$jsonResponse = (string) $loginResponse->getBody();
$response = json_decode($jsonResponse);
echo 'Client token: ' . $response->auth->client_token . '<br>' . PHP_EOL;
echo 'Policies: ' . implode(', ', $response->auth->policies) . '<br>' . PHP_EOL;
echo 'Lease time: ' . $response->auth->lease_duration . '<br>' . PHP_EOL;
```

This will give the following result:

```
Client token: f43b2a4a-3a05-a22c-528e-7b5119dc1e31
Policies: default, webapp
Lease time: 2764800
```

This **Client token** is the token we're going to use for connecting with the database, so keep this in memory for the duration of **Lease time**. Rerun this script to retrieve another token.

## Step 9: Set up database authentication in Vault

To connect with our database, we need to activate the database authentication in Vault.

Reference: 

- [www.vaultproject.io/guides/secret-mgmt/db-root-rotation.html](https://www.vaultproject.io/guides/secret-mgmt/db-root-rotation.html)
- [www.vaultproject.io/docs/secrets/databases/mysql-maria.html](https://www.vaultproject.io/docs/secrets/databases/mysql-maria.html)

To continue, we need to login again as user **admin** in Vault as we need to configure the database secrets engine.

```bash
vault login
Token (will be hidden):
```

Make sure that we're using the administrative policy for managing the database secrets!

```
Success! You are now authenticated. The token information displayed below
is already stored in the token helper. You do NOT need to run "vault login"
again. Future Vault requests will automatically use this token.

Key                Value
---                -----
token              0e7f0f87-3f60-cbe7-1aa8-6c4f707ffba8
token_accessor     e6c0f09d-73a9-5970-3f45-1973cc7cef90
token_duration     766h27m6s
token_renewable    true
token_policies     [admin default]
```

Now that we're **admin** we can enable the database secrets engine (off by default).

```bash
vault secrets enable database
```

This will return a success message. If you can't enable the database secrets engine, you might not have set the correct policy for user **admin**. Review **Step 2: Create an admin policy**.

```
Success! Enabled the database secrets engine at: database/
```

## Step 10: Define the MySQL plugin connection details

Vault comes with a plugin for MySQL that we can use to connect to our database. We just need to provide a connection template and the **vault** account details.

First we're going to activate the MySQL plugin for role `webapp`

```bash
vault write database/config/europe plugin_name=mysql-database-plugin connection_url="{{username}}:{{password}}@tcp(mysql:3306)/" allowed_roles="webapp" username="vault" password="vault"
```

Next we're going to map the role webapp in Vault so it can execute SQL commands.

```bash
vault write database/roles/webapp db_name=europe creation_statements="CREATE USER '{{name}}'@'%' IDENTIFIED BY '{{password}}';GRANT SELECT ON *.* TO '{{name}}'@'%'; GRANT SELECT, INSERT, UPDATE ON europe.* TO '{{name}}'@'%';" default_ttl="1h" max_ttl="24h"
```

If all went well, you should receive a nice success response from Vault.

```
Success! Data written to: database/roles/webapp
```

Let's also test if we can retrieve a MySQL credential that we can use for our application.

```bash
vault read database/creds/webapp
```

This should return you a table with a username and password.

```
Key                Value
---                -----
lease_id           database/creds/webapp/559e0568-e824-1432-c6de-74b8a8d579da
lease_duration     1h
lease_renewable    true
password           A1a-3UcW9XxLXp8Ss8I7
username           v-root-webapp-w6Ls9pM976n0RHVoEs
```

Let's see if we can log in our database with these credentials.

```bash
mysql -uv-root-webapp-w6Ls9pM976n0RHVoEs -pA1a-3UcW9XxLXp8Ss8I7
```

We should see the welcome screen from MySQL.

```
mysql: [Warning] Using a password on the command line interface can be insecure.
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 313
Server version: 5.7.22 MySQL Community Server (GPL)

Copyright (c) 2000, 2018, Oracle and/or its affiliates. All rights reserved.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql>
```

What databases can we see?

```
mysql> show databases;
+--------------------+
| Database           |
+--------------------+
| information_schema |
| europe             |
+--------------------+
2 rows in set (0.03 sec)
```

Let's see if we can also retrieve our necessary data.

```
mysql> select * from europe.eu_country limit 5;
+----+----------+------+----------+
| id | country  | code | flag     |
+----+----------+------+----------+
|  1 | Austria  | AU   | austria  |
|  2 | Belgium  | BE   | belgium  |
|  3 | Bulgaria | BG   | bulgaria |
|  4 | Croatia  | HR   | croatia  |
|  5 | Cyprus   | CY   | cyprus   |
+----+----------+------+----------+
5 rows in set (0.00 sec)
```

That works! Great!!! But can we also access the other data?

```
mysql> select host,user,password from mysql.user;
ERROR 1142 (42000): SELECT command denied to user 'v-root-webapp-yYRyRZxOxpukkkPik7'@'localhost' for table 'user'
```

Super!!! We can safely say our database is secure.

Time to clean up and revoke the test account.

```bash
vault lease revoke database/creds/webapp/559e0568-e824-1432-c6de-74b8a8d579da
```

A success message confirms that the lease is now revoked.

```
Success! Revoked lease: database/creds/webapp/559e0568-e824-1432-c6de-74b8a8d579da
```

## Step 11: Retrieve DB credentials as web application

Now that we both have a **webapp** role defined and a database for this role configured, it’s time to use Vault’s API to retrieve database credentials for our web app.

Here’s a small script to retrieve the lease credentials for the web application.

```php
<?php
putenv('VAULT_ACCESS_TOKEN=c6e29969-de5c-6dbb-94c1-a8ff769c9630');

$pageTitle = 'PoC - Web App security with Hashicorp Vault';
$flagLink = 'https://europa.eu/european-union/sites/europaeu/files/country_images/flags/flag-%s.jpg';


require_once __DIR__ . '/../vendor/autoload.php';

$accessToken = getenv('VAULT_ACCESS_TOKEN');

$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://vault:8200',
    'timeout'  => 2.0,
    'headers' => [
        'X-Vault-Token' => $accessToken,
        'User-Agent' => 'dragonbe/hashicorp-vault/0.0.1 PHP 7.2',
        'Accept' => 'application/json',
    ]
]);

try {
    $responseAccess = $client->request('GET', '/v1/database/creds/webapp');
} catch (\GuzzleHttp\Exception\ClientException $clientException) {
    echo 'Not allowed to retrieve credentials: ' . $clientException->getMessage() . '<br>' . PHP_EOL;
    echo '<a href="/client_token.php">Get new token</a>';
    exit;
}
$responseJson = (string) $responseAccess->getBody();
$responseData = json_decode($responseJson);

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;encoding=%s',
            'mysql',
            3306,
            'europe',
            'utf8'
        ),
        $responseData->data->username,
        $responseData->data->password
    );
} catch (PDOException $PDOException) {
    echo 'Cannot connect to DB: ' . $PDOException->getMessage() . '<br>' . PHP_EOL;
    exit;
}

$queryStmt = $pdo->query('SELECT * FROM `eu_country`');
```

And when we wrap it in some nice HTML, we get something like this:

```html
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <title><?php echo escape($pageTitle) ?></title>
  </head>
  <body>
      <div class="container">
          <h1><?php echo escape($pageTitle) ?></h1>

          <section id="section-intro">
              <h2 class="h2"><?php echo escape('Introduction') ?></h2>
              <p>This is a small application that requires a database, using <a href="https://www.vaultproject.io/" target="_blank" title="Vault by Hashicorp" rel="nofollow">Hashicorp Vault</a> to provide access credentials.</p>
          </section><!-- /#section-intro -->

          <section id="section-data">
              <h2 class="h2"><?php echo escape('EU Member Countries') ?></h2>
              <table class="table">
                  <thead>
                      <tr>
                          <th>Country</th>
                          <th>Code</th>
                          <th>Flag</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php while ($entry = $queryStmt->fetch(PDO::FETCH_ASSOC)): ?>
                      <tr>
                          <td><?php echo escape($entry['country']) ?></td>
                          <td><?php echo escape($entry['code']) ?></td>
                          <td><img src="<?php echo sprintf($flagLink, strtolower($entry['flag'])) ?>" width="24" height="24" alt="<?php echo escape($entry['country']) ?>"></td>
                      </tr>
                      <?php endwhile; ?>
                  </tbody>
              </table>
          </section><!-- /#section-data -->
      </div><!-- /.container -->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
  </body>
</html>
```

## Step 12: Improving secrets management

Web applications are stateless by nature. The application's state is maintained by using caching, sessions and persistent storage in the background.

For the management of our secrets we can persist them on filesystem as the keys are renewed after expiration of lease time. The most important elements we need to protect are the `role-id` and `secret-id`.

A good practice would be that you set them as environment variable for the web server and store the client token and temporarily database secrets in a file with the application.

Vault offers audit logging of secrets access and such, so it would be a good idea to add monitoring on it to spot abnormalities in web application access to secrets. We have enabled it at the start of this step-by-step guide, have a look at it to understand what happened during the setup of this application.