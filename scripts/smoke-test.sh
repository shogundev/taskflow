#!/usr/bin/env bash
# Usage: scripts/smoke-test.sh <base-url> <email> <password> [expected-version]
#
# 1. health check          2. deployed version matches (when expected-version is given)
# 3. real login            4. authenticated page
# 5. write check: create a throwaway project, read it back, delete it, confirm it is gone
set -euo pipefail

BASE_URL="${1:?base url required}"
EMAIL="${2:?email required}"
PASSWORD="${3:?password required}"
EXPECTED_VERSION="${4:-}"

JAR="$(mktemp)"
PROJECT_ID=""

# Every request has a timeout, so an unreachable host fails fast instead of hanging the pipeline.
c() { curl --connect-timeout 5 --max-time 20 "$@"; }

fail() { echo "SMOKE TEST FAILED: $*" >&2; exit 1; }

csrf_token() {
  grep -oE 'name="_token" value="[^"]+"' | head -1 | sed -E 's/.*value="([^"]+)"/\1/'
}

cleanup() {
  if [ -n "$PROJECT_ID" ]; then
    TOKEN="$(c -sS -b "$JAR" -c "$JAR" "$BASE_URL/projects" | csrf_token || true)"
    c -sS -b "$JAR" -c "$JAR" -o /dev/null -X POST \
      --data-urlencode "_token=$TOKEN" --data-urlencode "_method=DELETE" \
      "$BASE_URL/projects/$PROJECT_ID" || true
  fi
  rm -f "$JAR"
}
trap cleanup EXIT

for i in $(seq 1 20); do
  c -fsS --max-time 5 -o /dev/null "$BASE_URL/up" 2>/dev/null && break
  [ "$i" -eq 20 ] && fail "$BASE_URL/up did not become healthy (is the port open in the firewall?)"
  sleep 2
done
echo "ok  /up is healthy"

if [ -n "$EXPECTED_VERSION" ]; then
  VERSION="$(c -fsS "$BASE_URL/version" | sed -nE 's/.*"version":"([^"]*)".*/\1/p')"
  [ -n "$VERSION" ] || fail "/version returned no version"
  case "$VERSION" in
    "$EXPECTED_VERSION"*) ;;
    *) fail "deployed version is '$VERSION' but '$EXPECTED_VERSION' was expected" ;;
  esac
  echo "ok  version matches (${VERSION:0:7})"
fi

TOKEN="$(c -fsS -c "$JAR" "$BASE_URL/login" | csrf_token)"
[ -n "$TOKEN" ] || fail "no CSRF token on /login"
echo "ok  /login renders"

STATUS_AND_LOCATION="$(c -sS -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code} %{redirect_url}' \
  --data-urlencode "_token=$TOKEN" --data-urlencode "email=$EMAIL" --data-urlencode "password=$PASSWORD" \
  "$BASE_URL/login")"
[ "$STATUS_AND_LOCATION" = "302 $BASE_URL/dashboard" ] || fail "login returned '$STATUS_AND_LOCATION' (expected redirect to /dashboard)"
echo "ok  login works"

c -fsS -b "$JAR" "$BASE_URL/dashboard" | grep -q "Dashboard" || fail "/dashboard did not render for logged-in user"
echo "ok  /dashboard renders"

PROJECT_NAME="smoke-$(date +%s)"
TOKEN="$(c -fsS -b "$JAR" -c "$JAR" "$BASE_URL/projects/create" | csrf_token)"
[ -n "$TOKEN" ] || fail "no CSRF token on /projects/create"

CREATED="$(c -sS -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code} %{redirect_url}' \
  --data-urlencode "_token=$TOKEN" --data-urlencode "name=$PROJECT_NAME" --data-urlencode "description=created by the smoke test" \
  "$BASE_URL/projects")"
PROJECT_ID="$(echo "$CREATED" | sed -nE 's#^302 .*/projects/([0-9]+)$#\1#p')"
[ -n "$PROJECT_ID" ] || fail "creating a project returned '$CREATED' (expected redirect to the new project)"
echo "ok  project created (id $PROJECT_ID)"

c -fsS -b "$JAR" "$BASE_URL/projects/$PROJECT_ID" | grep -q "$PROJECT_NAME" || fail "created project was not readable"
echo "ok  project read back"

TOKEN="$(c -fsS -b "$JAR" -c "$JAR" "$BASE_URL/projects" | csrf_token)"
DELETED="$(c -sS -b "$JAR" -c "$JAR" -o /dev/null -w '%{http_code}' -X POST \
  --data-urlencode "_token=$TOKEN" --data-urlencode "_method=DELETE" "$BASE_URL/projects/$PROJECT_ID")"
[ "$DELETED" = "302" ] || fail "deleting the project returned $DELETED (expected 302)"
GONE="$(c -sS -b "$JAR" -o /dev/null -w '%{http_code}' "$BASE_URL/projects/$PROJECT_ID")"
[ "$GONE" = "404" ] || fail "deleted project still answers with $GONE (expected 404)"
PROJECT_ID=""
echo "ok  project deleted"

echo "All smoke checks passed"
