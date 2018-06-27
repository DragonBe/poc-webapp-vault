#!/usr/bin/env bash

# Let's make sure we're connecting to the correct vault instance
export VAULT_ADDR=http://127.0.0.1:8200

rootToken=$(docker-compose logs vault | grep "Root Token" | awk -F ": " {'print $2'})

if [ -z $rootToken ]
then
  echo "Couldn't find the root token"
  exit 1
fi

echo "Using root token: $rootToken to set up Vault"

vault login $rootToken
vault audit enable file file_path=/vault/logs/vault_audit.log
vault policy write admin vault/config/policies/admin-policy.hcl

adminToken=00c75328-4331-1f5e-0a5a-dcbc5048918c
adminToken=$(vault token create -policy="admin" | grep "^token " | awk {'print $2'})

if [ -z $adminToken ]
then
  echo "Couldn't find an admin token"
  exit 1
fi

echo "Using admin token: $adminToken to set up Vault Database secrets engine"

vault login $adminToken
vault auth enable approle
vault policy write webapp vault/config/policies/webapp-policy.hcl
vault write auth/approle/role/webapp policies="webapp"

roleId=$(vault read auth/approle/role/webapp/role-id | grep "^role_id" | awk {'print $2'})
if [ -z $roleId ]
then
  echo "Couldn't find the roleId"
  exit 1
fi

echo "Found role ID $roleId"

secretId=$(vault write -f auth/approle/role/webapp/secret-id | grep "^secret_id " | awk {'print $2'})
if [ -z $secretId ]
then
  echo "Couldn't find the secretId"
  exit 1
fi

echo "Found secret ID $secretId"


echo "{\"role_id\": \"$roleId\", \"secret_id\": \"$secretId\"}" > config/payload.json

vault secrets enable database
vault write database/config/europe plugin_name=mysql-database-plugin connection_url="{{username}}:{{password}}@tcp(mysql:3306)/" allowed_roles="webapp" username="vault" password="vault"
vault write database/roles/webapp db_name=europe creation_statements="CREATE USER '{{name}}'@'%' IDENTIFIED BY '{{password}}';GRANT SELECT ON *.* TO '{{name}}'@'%'; GRANT SELECT, INSERT, UPDATE ON europe.* TO '{{name}}'@'%';" default_ttl="1h" max_ttl="24h"

exit 0