#!/usr/bin/env bash

# Let's make sure we're connecting to the correct vault instance
VAULT_ADDR=http://127.0.0.1:8200

# simplest way to get the root token in dev mode
ROOT_TOKEN=$(docker-compose logs vault | grep "Root Token" | awk -F ": " {'print $2'})

if [ -z $ROOT_TOKEN ]
then
  echo "Couldn't find the root token"
  exit 1
fi

echo "Using root token: $ROOT_TOKEN to set up Vault"

echo "Enable audit log"
curl \
    -s \
    --header "X-Vault-Token: $ROOT_TOKEN" \
    --request PUT \
    --data '{ "type": "file", "options": {  "path": "/vault/logs/vault_audit.log"  } }' \
    $VAULT_ADDR/v1/sys/audit/audit_log

echo "Create new admin policy"
ADMIN_POLICY_RULES=$(cat vault/config/policies/admin-policy.hcl)
jq -n --arg rules "$ADMIN_POLICY_RULES" \
   '{rules: $rules }' |
curl \
    -s \
    --header "X-Vault-Token: $ROOT_TOKEN" \
    --request PUT \
    -d@- \
    $VAULT_ADDR/v1/sys/policy/admin

ADMIN_TOKEN=$(curl \
    -s \
    --header "X-Vault-Token: $ROOT_TOKEN" \
    --request POST \
    --data '{ "policies": [ "admin" ] }' \
    $VAULT_ADDR/v1/auth/token/create | jq -r ".auth.client_token")

if [ -z $ADMIN_TOKEN ]
then
  echo "Couldn't find an admin token"
  exit 1
fi

echo "Using admin token: $ADMIN_TOKEN to set up Vault Database secrets engine"

echo "Enable approle"
curl \
    -s \
    --header "X-Vault-Token: $ADMIN_TOKEN" \
    --request POST \
    --data '{"type":"approle"}' \
    $VAULT_ADDR/v1/sys/auth/approle

echo "Create new webapp policy"
WEBAPP_POLICY_RULES=$(cat vault/config/policies/webapp-policy.hcl)
jq -n --arg rules "$WEBAPP_POLICY_RULES" \
   '{rules: $rules }' |
curl \
    -s \
    --header "X-Vault-Token: $ADMIN_TOKEN" \
    --request PUT \
    -d@- \
    $VAULT_ADDR/v1/sys/policy/webapp

echo "Add webapp role to webapp policy"
curl \
    -s \
    --header "X-Vault-Token: $ADMIN_TOKEN" \
    --request POST \
    --data '{ "policies": [ "webapp" ] }' \
    $VAULT_ADDR/v1/auth/approle/role/webapp

echo "Get role id"
roleId=$(curl \
-s \
--header "X-Vault-Token: $ADMIN_TOKEN" \
$VAULT_ADDR/v1/auth/approle/role/webapp/role-id | jq -r ".data.role_id")

if [ -z $roleId ]
then
  echo "Couldn't find the roleId"
  exit 1
fi

echo "Found role ID $roleId"

echo "Get secret id"
secretId=$(curl \
-s \
--header "X-Vault-Token: $ADMIN_TOKEN" \
--request POST \
$VAULT_ADDR/v1/auth/approle/role/webapp/secret-id | jq -r ".data.secret_id")

if [ -z $secretId ]
then
  echo "Couldn't find the secretId"
  exit 1
fi

echo "Found secret ID $secretId"


echo "{\"role_id\": \"$roleId\", \"secret_id\": \"$secretId\"}" > config/payload.json

echo "Lets mount database endpoint"
curl \
    -s \
    --header "X-Vault-Token: $ADMIN_TOKEN" \
    --request POST \
    --data '{ "type": "database" }' \
    $VAULT_ADDR/v1/sys/mounts/database

echo "Write to database"
jq -n --arg plugin_name "mysql-database-plugin" \
      --arg allowed_roles "webapp" \
      --arg connection_url "{{username}}:{{password}}@tcp(mysql:3306)/" \
      --arg username "vault" \
      --arg password "vault" \
   '{plugin_name: $plugin_name, connection_url: $connection_url, allowed_roles: $allowed_roles, username: $username, password: $password }' |
curl \
    -s \
    --header "X-Vault-Token: $ADMIN_TOKEN" \
    --request PUT \
    -d@- \
    $VAULT_ADDR/v1/database/config/europe

jq -n --arg db_name "europe" \
      --arg creation_statements "CREATE USER '{{name}}'@'%' IDENTIFIED BY '{{password}}';GRANT SELECT ON *.* TO '{{name}}'@'%'; GRANT SELECT, INSERT, UPDATE ON europe.* TO '{{name}}'@'%';" \
      --arg connection_url "{{username}}:{{password}}@tcp(mysql:3306)/" \
      --arg default_ttl "1h" \
      --arg max_ttl "24h" \
   '{db_name: $db_name, creation_statements: $creation_statements, default_ttl: $default_ttl, max_ttl: $max_ttl}' |
curl \
    -s \
    --header "X-Vault-Token: $ADMIN_TOKEN" \
    --request PUT \
    -d@- \
    $VAULT_ADDR/v1/database/roles/webapp

exit 0
