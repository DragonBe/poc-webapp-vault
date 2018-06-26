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