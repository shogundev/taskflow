#!/usr/bin/env bash
# Usage: scripts/smoke-test.sh <base-url> <email> <password>
# Health check, then a real login through the CSRF form, then an authenticated page.
set -euo pipefail

BASE_URL="${1:?base url required}"
EMAIL="${2:?email required}"
PASSWORD="${3:?password required}"

JAR="$(mktemp)"
trap 'rm -f "$JAR"' EXIT

fail() { echo "SMOKE TEST FAILED: $*" >&2; exit 1; }

for i in $(seq 1 30); do
  curl -fsS -o /dev/null "$BASE_URL/up" 2>/dev/null && break
  [ "$i" -eq 30 ] && fail "$BASE_URL/up did not become healthy"
  sleep 2
done
echo "ok  /up is healthy"

TOKEN="$(curl -fsS -c "$JAR" "$BASE_URL/login" | grep -oE 'name="_token" value="[^"]+"' | head -1 | sed -E 's/.*value="([^"]+)"/\1/')"
[ -n "$TOKEN" ] || fail "no CSRF token on /login"
echo "ok  /login renders"

STATUS_AND_LOCATION="$(curl -sS -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code} %{redirect_url}' \
  --data-urlencode "_token=$TOKEN" --data-urlencode "email=$EMAIL" --data-urlencode "password=$PASSWORD" \
  "$BASE_URL/login")"
[ "$STATUS_AND_LOCATION" = "302 $BASE_URL/dashboard" ] || fail "login returned '$STATUS_AND_LOCATION' (expected redirect to /dashboard)"
echo "ok  login works"

curl -fsS -b "$JAR" "$BASE_URL/dashboard" | grep -q "Dashboard" || fail "/dashboard did not render for logged-in user"
echo "ok  /dashboard renders"

echo "All smoke checks passed"
