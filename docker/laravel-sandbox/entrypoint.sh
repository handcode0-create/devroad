#!/bin/sh
set -eu

TEMPLATE="/opt/devroad-template"
WORKSPACE="/workspace"
MARKER="$WORKSPACE/.devroad_initialized"

mkdir -p "$WORKSPACE"

if [ ! -f "$MARKER" ]; then
    cp -a "$TEMPLATE"/. "$WORKSPACE"/
    touch "$MARKER"
fi

cd "$WORKSPACE"

exec "$@"
