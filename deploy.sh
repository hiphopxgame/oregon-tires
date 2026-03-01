#!/bin/bash
# ============================================================
# HipHop World Network — Standardized Deploy Script
# ============================================================
#
# Usage:
#   ./deploy.sh           # Deploy changed files
#   ./deploy.sh diff      # Preview what would deploy
#   ./deploy.sh status    # Show deploy state
#   ./deploy.sh build     # Build CSS + images only (no deploy)
#   ./deploy.sh help      # Show usage
#
# Tiers:
#   Full     — maintenance mode, migrations, health checks, rollback
#   Standard — git-diff sync, optional CSS build, health check
#   Simple   — git-diff sync only
#
# Configure the SITE CONFIG section below for your site.
# ============================================================

set -euo pipefail

# ============================================================
# SITE CONFIG — customize per site
# ============================================================
SITE_NAME="Oregon Tires"
SITE_DOMAIN="oregon.tires"
SSH_HOST="hiphopworld"
REMOTE_ROOT="public_html/---oregon.tires"
DEPLOY_TIER="standard"          # full | standard | simple
HAS_CSS_BUILD=true              # Tailwind CSS v4

# Oregon Tires uses public_html/ prefix locally
# Files are stripped to flat on the remote
LOCAL_ROOT="$(cd "$(dirname "$0")" && pwd)"
SOURCE_DIR="${LOCAL_ROOT}/public_html"
DEPLOY_STATE="${LOCAL_ROOT}/.last-deploy"

# ============================================================
# Colors
# ============================================================
CYAN='\033[0;36m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
RED='\033[0;31m'; DIM='\033[2m'; NC='\033[0m'

info()  { echo -e "${CYAN}[INFO]${NC} $1"; }
ok()    { echo -e "${GREEN}[OK]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
fail()  { echo -e "${RED}[FAIL]${NC} $1"; }
dim()   { echo -e "${DIM}$1${NC}"; }

# ============================================================
# Helpers
# ============================================================

last_deploy_sha() {
    if [ -f "$DEPLOY_STATE" ]; then
        cat "$DEPLOY_STATE"
    fi
}

changed_files() {
    local last_sha
    last_sha=$(last_deploy_sha)

    if [ -z "$last_sha" ]; then
        # First deploy — all tracked files under public_html/
        git -C "$LOCAL_ROOT" ls-files "public_html/" 2>/dev/null || find "$SOURCE_DIR" -type f | sed "s|^${LOCAL_ROOT}/||"
    else
        # Files changed since last deploy
        git -C "$LOCAL_ROOT" diff --name-only "$last_sha" HEAD -- "public_html/" 2>/dev/null || true
        # Include untracked files
        git -C "$LOCAL_ROOT" ls-files --others --exclude-standard "public_html/" 2>/dev/null || true
    fi
}

deleted_files() {
    local last_sha
    last_sha=$(last_deploy_sha)
    if [ -n "$last_sha" ]; then
        git -C "$LOCAL_ROOT" diff --diff-filter=D --name-only "$last_sha" HEAD -- "public_html/" 2>/dev/null || true
    fi
}

record_deploy() {
    local sha
    sha=$(git -C "$LOCAL_ROOT" rev-parse HEAD 2>/dev/null || echo "manual")
    echo "$sha" > "$DEPLOY_STATE"

    # Tag the deploy
    local tag="deploy/$(date +%Y-%m-%d-%H%M%S)"
    git -C "$LOCAL_ROOT" tag "$tag" 2>/dev/null || true
    dim "Tagged: $tag"
}

# ============================================================
# Build Steps (Oregon Tires specific)
# ============================================================

build_css() {
    if [ "$HAS_CSS_BUILD" = true ]; then
        info "Building Tailwind CSS..."
        if [ -f "${LOCAL_ROOT}/package.json" ] || [ -f "${LOCAL_ROOT}/src/input.css" ]; then
            (cd "$LOCAL_ROOT" && npx @tailwindcss/cli -i src/input.css -o public_html/assets/styles.css --minify 2>&1) || warn "CSS build failed"
            local size
            size=$(ls -la public_html/assets/styles.css 2>/dev/null | awk '{print $5}')
            ok "Built styles.css (${size} bytes)"
        fi
    fi
}

build_images() {
    local shared_script="${LOCAL_ROOT}/../../scripts/optimize-images.sh"
    if [ ! -f "$shared_script" ]; then
        return 0
    fi

    source "$shared_script"

    local img_dir="${LOCAL_ROOT}/public_html/assets/img"
    if [ -d "$img_dir" ]; then
        info "Optimizing images (WebP + AVIF)..."
        optimize_images_in_dir "$img_dir"
    fi
}

# ============================================================
# Sync
# ============================================================

sync_files() {
    local files
    files=$(changed_files | sort -u | grep -v '^$')

    # Always include freshly-built CSS
    if [ "$HAS_CSS_BUILD" = true ] && [ -f "${SOURCE_DIR}/assets/styles.css" ]; then
        files=$(echo -e "${files}\npublic_html/assets/styles.css" | sort -u | grep -v '^$')
    fi

    if [ -z "$files" ]; then
        ok "No changes to deploy"
        return 0
    fi

    local file_count
    file_count=$(echo "$files" | wc -l | tr -d ' ')
    info "Syncing ${file_count} files..."

    # Strip public_html/ prefix for rsync --files-from
    local tmpfile
    tmpfile=$(mktemp)
    echo "$files" | sed 's|^public_html/||' > "$tmpfile"

    rsync -avz --files-from="$tmpfile" "$SOURCE_DIR/" "${SSH_HOST}:~/${REMOTE_ROOT}/"
    rm -f "$tmpfile"

    # Handle deletions
    local deleted
    deleted=$(deleted_files)
    if [ -n "$deleted" ]; then
        info "Removing deleted files from remote..."
        echo "$deleted" | sed 's|^public_html/||' | while read -r f; do
            ssh "$SSH_HOST" "rm -f ~/${REMOTE_ROOT}/${f}" 2>/dev/null || true
            dim "  Deleted: $f"
        done
    fi

    ok "Files synced"
}

health_check() {
    info "Running health check..."
    local status_code
    status_code=$(curl -sf -o /dev/null -w "%{http_code}" "https://${SITE_DOMAIN}/" 2>/dev/null || echo "000")
    if [ "$status_code" = "200" ]; then
        ok "Health check passed (HTTP ${status_code})"
    else
        warn "Health check returned HTTP ${status_code}"
    fi
}

opcache_reset() {
    info "Resetting OPcache..."
    ssh "$SSH_HOST" "cd ~/${REMOTE_ROOT} && php -r \"if(function_exists('opcache_reset')){opcache_reset();echo 'OPcache cleared';}else{echo 'OPcache not available';}\"" 2>/dev/null || warn "OPcache reset skipped"
}

# ============================================================
# Commands
# ============================================================

cmd_deploy() {
    echo -e "\n${CYAN}=== Deploying ${SITE_NAME} ===${NC}\n"

    build_css
    build_images
    sync_files
    opcache_reset
    health_check
    record_deploy

    echo -e "\n${GREEN}=== Deploy complete ===${NC}\n"
}

cmd_diff() {
    echo -e "\n${CYAN}=== Deploy Preview: ${SITE_NAME} ===${NC}\n"

    local last_sha
    last_sha=$(last_deploy_sha)
    if [ -n "$last_sha" ]; then
        dim "Last deploy: $last_sha"
        dim "$(git -C "$LOCAL_ROOT" log --oneline -1 "$last_sha" 2>/dev/null || echo 'Unknown commit')"
    else
        warn "First deploy — all files will be synced"
    fi

    echo ""
    local files
    files=$(changed_files | sort -u | grep -v '^$')
    if [ -z "$files" ]; then
        ok "No changes to deploy"
    else
        info "Files to deploy:"
        echo "$files" | while read -r f; do
            echo -e "  ${GREEN}+${NC} $f"
        done

        local deleted
        deleted=$(deleted_files)
        if [ -n "$deleted" ]; then
            echo ""
            echo "$deleted" | while read -r f; do
                echo -e "  ${RED}-${NC} $f"
            done
        fi

        echo ""
        info "Total: $(echo "$files" | wc -l | tr -d ' ') files"
    fi
}

cmd_status() {
    echo -e "\n${CYAN}=== Deploy Status: ${SITE_NAME} ===${NC}\n"

    local last_sha
    last_sha=$(last_deploy_sha)

    if [ -z "$last_sha" ]; then
        warn "Never deployed (no .last-deploy file)"
    else
        info "Last deploy SHA: $last_sha"
        dim "$(git -C "$LOCAL_ROOT" log --oneline -1 "$last_sha" 2>/dev/null || echo 'Unknown')"

        local pending
        pending=$(changed_files | grep -v '^$' | wc -l | tr -d ' ')
        if [ "$pending" -gt 0 ]; then
            warn "${pending} files pending deploy"
        else
            ok "Up to date"
        fi
    fi

    echo ""
    info "Recent deploys:"
    git -C "$LOCAL_ROOT" tag -l "deploy/*" --sort=-creatordate 2>/dev/null | head -5 | while read -r tag; do
        dim "  $tag"
    done
}

cmd_help() {
    echo "Usage: ./deploy.sh [command]"
    echo ""
    echo "Commands:"
    echo "  deploy    Deploy changed files (default)"
    echo "  diff      Preview what would deploy"
    echo "  status    Show deploy state"
    echo "  build     Build CSS + images only (no deploy)"
    echo "  help      Show this message"
    echo ""
    echo "Site: ${SITE_NAME}"
    echo "Tier: ${DEPLOY_TIER}"
    echo "Domain: ${SITE_DOMAIN}"
    echo "Remote: ${SSH_HOST}:~/${REMOTE_ROOT}"
}

# ============================================================
# Main
# ============================================================

case "${1:-deploy}" in
    deploy)  cmd_deploy ;;
    diff)    cmd_diff ;;
    status)  cmd_status ;;
    build)   build_css; build_images ;;
    help)    cmd_help ;;
    *)       cmd_help ;;
esac
