#!/usr/bin/env bash
set -euo pipefail

# Ensure required environment variables are set
missing=()

required_base=E2E_BASE_URL
if [[ -z "${!required_base:-}" ]]; then
  missing+=("$required_base")
fi

roles=(admin member artist organization)

for role in "${roles[@]}"; do
  upper_role=$(echo "$role" | tr '[:lower:]' '[:upper:]')
  user_var="E2E_${upper_role}_USER"
  pass_var1="E2E_${upper_role}_PASS"
  pass_var2="E2E_${upper_role}_PASSWORD"

  user="${!user_var:-}"
  pass="${!pass_var1:-${!pass_var2:-}}"

  if [[ -z "$user" ]]; then
    missing+=("$user_var")
  fi
  if [[ -z "$pass" ]]; then
    missing+=("$pass_var1 or $pass_var2")
  fi
done

if (( ${#missing[@]} > 0 )); then
  echo "Missing required environment variables: ${missing[*]}" >&2
  exit 1
fi

# Helper to ensure a user exists
create_user() {
  local username="$1"
  local password="$2"
  local wp_role="$3"

  if ! wp user get "$username" --url="$E2E_BASE_URL" >/dev/null 2>&1; then
    wp user create "$username" "$username@example.com" --role="$wp_role" --user_pass="$password" --url="$E2E_BASE_URL" >/dev/null
  fi
}

create_user "$E2E_ADMIN_USER" "${E2E_ADMIN_PASS:-${E2E_ADMIN_PASSWORD}}" administrator
create_user "$E2E_MEMBER_USER" "${E2E_MEMBER_PASS:-${E2E_MEMBER_PASSWORD}}" member
create_user "$E2E_ARTIST_USER" "${E2E_ARTIST_PASS:-${E2E_ARTIST_PASSWORD}}" artist
create_user "$E2E_ORGANIZATION_USER" "${E2E_ORGANIZATION_PASS:-${E2E_ORGANIZATION_PASSWORD}}" organization

echo "Users ready."
