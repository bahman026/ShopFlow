#!/usr/bin/env bash
#
# Build the production images on a workstation and stream them into the VPS's
# image store over SSH. Run from the repository root:
#
#   ./infrastructure/production/ship-images.sh deploy@203.0.113.10 [ssh-port]
#
# Why this exists: deploy.sh builds on the server, which needs Docker Hub,
# Debian's apt mirrors, packagist and the npm registry. On a host where those
# are filtered — an Iranian IP, for instance — every build stage fails and a
# registry mirror alone does not help, because the PHP base stage still has to
# `apt-get install` from deb.debian.org. So the images are built where the
# network works and shipped as a layer tarball. No registry involved.
#
# The server is x86-64 and a workstation may not be, so the build is pinned to
# linux/amd64 and every image's architecture is verified before it is shipped:
# loading an arm64 image would leave containers crash-looping with "exec format
# error" long after the cause scrolled away.
set -euo pipefail

cd "$(dirname "$0")/../.."

TARGET="${1:-}"
SSH_PORT="${2:-22}"
PLATFORM="${PLATFORM:-linux/amd64}"

if [ -z "$TARGET" ]; then
    echo "usage: $0 user@host [ssh-port]" >&2
    exit 1
fi

if [ -z "${IMAGE_TAG:-}" ]; then
    IMAGE_TAG="$(git rev-parse --short HEAD)"
fi

# Built from the commit, not the working tree: the tag has to mean something
# for `IMAGE_TAG=<old-sha> docker compose up -d` to be a usable rollback.
TREE="$(mktemp -d)"
trap 'rm -rf "$TREE"' EXIT
git archive "${GIT_REF:-HEAD}" | tar -x -C "$TREE"

APP_IMAGES=(
    "shopflow/admin-app:$IMAGE_TAG"
    "shopflow/admin-web:$IMAGE_TAG"
    "shopflow/shop-app:$IMAGE_TAG"
    "shopflow/shop-web:$IMAGE_TAG"
    "shopflow/shop-ssr:$IMAGE_TAG"
)
# Pulled rather than built, but the server cannot reach Docker Hub either, so
# they travel in the same tarball.
SERVICE_IMAGES=(postgres:16-alpine redis:7-alpine caddy:2-alpine)

build() {
    local ctx="$1" target="$2" image="$3"
    echo "==> building $image ($PLATFORM, target=$target)"
    docker buildx build \
        --platform "$PLATFORM" \
        --file "$TREE/$ctx/docker/Dockerfile.prod" \
        --target "$target" \
        --tag "$image" \
        --load \
        "$TREE/$ctx"
}

echo "==> shipping ${IMAGE_TAG} to ${TARGET} (ssh port ${SSH_PORT})"

build admin app "shopflow/admin-app:$IMAGE_TAG"
build admin web "shopflow/admin-web:$IMAGE_TAG"
build shop  app "shopflow/shop-app:$IMAGE_TAG"
build shop  web "shopflow/shop-web:$IMAGE_TAG"
build shop  ssr "shopflow/shop-ssr:$IMAGE_TAG"

echo "==> fetching shared service images"
for img in "${SERVICE_IMAGES[@]}"; do
    docker pull --quiet --platform "$PLATFORM" "$img"
done

# --platform is not optional here. A plain `docker image inspect` resolves a
# multi-platform image to the *host* variant, so on an arm64 workstation the
# pulled postgres/redis/caddy report arm64 and the check would pass while
# shipping images the server cannot execute.
echo "==> verifying every image is $PLATFORM"
for img in "${APP_IMAGES[@]}" "${SERVICE_IMAGES[@]}"; do
    got="$(docker image inspect --platform "$PLATFORM" "$img" --format '{{.Os}}/{{.Architecture}}')"
    if [ "$got" != "$PLATFORM" ]; then
        echo "$img is $got, expected $PLATFORM — refusing to ship" >&2
        exit 1
    fi
    printf '    %-40s %s\n' "$img" "$got"
done

# One `save` for all eight: the two php-fpm images share their whole base and
# the tarball only carries those layers once. --platform again, or a
# multi-platform entry exports every variant it has locally and doubles the
# upload over a link where the upload is the whole cost.
echo "==> streaming images over SSH (this is the slow part)"
docker save --platform "$PLATFORM" "${APP_IMAGES[@]}" "${SERVICE_IMAGES[@]}" \
    | gzip \
    | ssh -p "$SSH_PORT" "$TARGET" 'gunzip | docker load'

echo
echo "==> images on the server"
ssh -p "$SSH_PORT" "$TARGET" \
    "docker images --filter reference='shopflow/*' --format '{{.Repository}}:{{.Tag}} {{.Size}}'"

echo
echo "done. Deploy with, on the server:"
echo "    IMAGE_TAG=$IMAGE_TAG ./infrastructure/production/deploy-prebuilt.sh"
