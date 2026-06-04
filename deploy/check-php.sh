#!/usr/bin/env bash
# Vérifie que le PHP CLI du VPS a tout ce qu'il faut pour Symfony.
set -euo pipefail

required=(pdo pdo_mysql zip curl xml dom simplexml mbstring intl redis gd bcmath)

missing=()
for ext in "${required[@]}"; do
  if ! php -m | grep -qi "^${ext}$"; then
    missing+=("${ext}")
  fi
done

if ((${#missing[@]} > 0)); then
  echo "Extensions PHP manquantes : ${missing[*]}" >&2
  echo "Sur le VPS (root) : sudo bash deploy/vps-setup.sh" >&2
  exit 1
fi

echo "PHP $(php -r 'echo PHP_VERSION;') — extensions OK."
