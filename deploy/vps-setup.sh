#!/usr/bin/env bash
# Configuration PHP du VPS — à lancer UNE SEULE FOIS en root :
#   curl -fsSL https://gitlab.com/.../raw/main/deploy/vps-setup.sh | bash
# ou depuis le projet :
#   sudo bash deploy/vps-setup.sh
set -euo pipefail

PHP_VERSION="${PHP_VERSION:-8.5}"

if [[ "${EUID}" -ne 0 ]]; then
  echo "Relancez ce script avec sudo." >&2
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive

apt-get update -qq
apt-get install -y \
  "php${PHP_VERSION}-cli" \
  "php${PHP_VERSION}-fpm" \
  "php${PHP_VERSION}-mysql" \
  "php${PHP_VERSION}-zip" \
  "php${PHP_VERSION}-curl" \
  "php${PHP_VERSION}-xml" \
  "php${PHP_VERSION}-mbstring" \
  "php${PHP_VERSION}-intl" \
  "php${PHP_VERSION}-redis" \
  "php${PHP_VERSION}-gd" \
  "php${PHP_VERSION}-bcmath" \
  "php${PHP_VERSION}-opcache"

echo ""
echo "Extensions PHP ${PHP_VERSION} installées :"
php -m | sort

echo ""
if php -m | grep -qi '^pdo_mysql$'; then
  echo "OK — pdo_mysql disponible pour Doctrine."
else
  echo "ERREUR — pdo_mysql toujours absent." >&2
  exit 1
fi
