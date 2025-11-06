#!/usr/bin/env bash
set -euo pipefail

# Build script: usage ./build.sh [image:tag]
# Default tag: myproject:latest
TAG="${1:-myproject:latest}"
DIR="$(cd "$(dirname "$0")" && pwd)"

if command -v podman >/dev/null 2>&1; then
  BUILDER=podman
elif command -v docker >/dev/null 2>&1; then
  BUILDER=docker
else
  echo "No podman or docker binary found in PATH." >&2
  exit 1
fi

echo "Using builder: ${BUILDER}"
echo "Building image '${TAG}' from context: ${DIR}"
# Ensure we pass the directory as the context (not a stray character like 'o')
"${BUILDER}" build -t "${TAG}" "${DIR}"
