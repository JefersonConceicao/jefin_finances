#!/bin/sh
# Atualiza a produção com a master do GitHub. Rodar no servidor, na pasta do projeto.
set -e
cd "$(dirname "$0")/../.."

git fetch --quiet origin master
git checkout --quiet -B master origin/master

docker compose -f docker-compose.prod.yml up -d --build --remove-orphans
docker image prune -f >/dev/null

docker compose -f docker-compose.prod.yml ps
