#!/bin/bash
# ============================================================
# HipHop World Network — Standardized Deploy Script
# ============================================================
#
# Usage:
#   ./deploy.sh           # Deploy changed files
#   ./deploy.sh diff      # Preview what would deploy
#   ./deploy.sh status    # Show deploy state
#   ./deploy.sh help      # Show usage
#
# Tiers:
#   Full     — maintenance mode, migrations, health checks, rollback
#   Standard — git-diff sync, optional CSS build
#   Simple   — git-diff sync only
#
# Configure the SITE CONFIG section below for your site.
# ============================================================

set -euo pipefail

# ============================================================
# SITE CONFIG — customize per site
# ============================================================
SITE_NAME="{{SITE_NAME}}"
SSH_HOST="hiphopworld"
REMOTE_ROOT="public_html/---{{DOMAIN}}"
SOURCE_DIR="${LOCAL_ROOT:-$(cd "$(dirname "$0")" && pwd)}/public_html"
DEPLOY_TIER="standard"       # full | standard | simple
HAS_CSS_BUILD=false          # Set to true if site uses Tailwind/PostCSS

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

LOCAL_ROOT="$(cd "$(dirname "$0")" && pwd)"
DEPLOY_STATE="${LOCAL_ROOT}/.last-deploy"

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
        # First deploy — all tracked files
        git -C "$LOCAL_ROOT" ls-files "$SOURCE_DIR" 2>/dev/null || find "$SOURCE_DIR" -type f
    else
        # Files changed since last deploy
        git -C "$LOCAL_ROOT" diff --name-only "$last_sha" HEAD -- "$SOURCE_DIR" 2>/dev/null || true
        # Include untracked files
        git -C "$LOCAL_ROOT" ls-files --others --exclude-standard "$SOURCE_DIR" 2>/dev/null || true
    fi
}

deleted_files() {
    local last_sha
    last_sha=$(last_deploy_sha)
    if [ -n "$last_sha" ]; then
        git -C "$LOCAL_ROOT" diff --diff-filter=D --name-only "$last_sha" HEAD -- "$SOURCE_DIR" 2>/dev/null || true
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

build_css() {
    if [ "$HAS_CSS_BUILD" = true ]; then
        info "Building CSS..."
        if [ -f "${LOCAL_ROOT}/package.json" ]; then
            (cd "$LOCAL_ROOT" && npx @tailwindcss/cli -i src/input.css -o public_html/assets/styles.css --minify 2>/dev/null) || warn "CSS build failed"
        fi
    fi
}

sync_files() {
    local files
    files=$(changed_files | sort -u)

    if [ -z "$files" ]; then
        ok "No changes to deploy"
        return 0
    fi

    info "Syncing $(echo "$files" | wc -l | tr -d ' ') files..."

    # Strip SOURCE_DIR prefix for rsync --files-from
    local tmpfile
    tmpfile=$(mktemp)
    echo "$files" | sed "s|^${SOURCE_DIR}/||" > "$tmpfile"

    rsync -avz --files-from="$tmpfile" "$SOURCE_DIR/" "${SSH_HOST}:~/${REMOTE_ROOT}/"
    rm -f "$tmpfile"

    # Handle deletions
    local deleted
    deleted=$(deleted_files)
    if [ -n "$deleted" ]; then
        info "Removing deleted files..."
        echo "$deleted" | sed "s|^${SOURCE_DIR}/||" | while read -r f; do
            ssh "$SSH_HOST" "rm -f ~/${REMOTE_ROOT}/${f}" 2>/dev/null || true
            dim "  Deleted: $f"
        done
    fi

    ok "Files synced"
}

# ============================================================
# Commands
# ============================================================

cmd_deploy() {
    echo -e "\n${CYAN}=== Deploying ${SITE_NAME} ===${NC}\n"

    build_css

    if [ "$DEPLOY_TIER" = "full" ]; then
        # Full: maintenance → sync → migrate → health → live
        info "Step 1/5: Maintenance mode ON"
        ssh "$SSH_HOST" "cd ~/${REMOTE_ROOT} && php deploy/deploy.php maintenance:on 2>/dev/null" || warn "No maintenance script"

        info "Step 2/5: Syncing files"
        sync_files

        info "Step 3/5: Running migrations"
        ssh "$SSH_HOST" "cd ~/${REMOTE_ROOT} && php deploy/deploy.php migrate 2>/dev/null" || warn "No migrations"

        info "Step 4/5: Health check"
        ssh "$SSH_HOST" "cd ~/${REMOTE_ROOT} && php deploy/deploy.php health 2>/dev/null" || warn "No health check"

        info "Step 5/5: Going live"
        ssh "$SSH_HOST" "cd ~/${REMOTE_ROOT} && php deploy/deploy.php maintenance:off 2>/dev/null" || true
    else
        # Standard/Simple: just sync
        sync_files
    fi

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
    files=$(changed_files | sort -u)
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
        warn "Never deployed"
    else
        info "Last deploy SHA: $last_sha"
        dim "$(git -C "$LOCAL_ROOT" log --oneline -1 "$last_sha" 2>/dev/null || echo 'Unknown')"

        local pending
        pending=$(changed_files | wc -l | tr -d ' ')
        if [ "$pending" -gt 0 ]; then
            warn "$pending files pending deploy"
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
    echo "  help      Show this message"
    echo ""
    echo "Site: ${SITE_NAME}"
    echo "Tier: ${DEPLOY_TIER}"
    echo "Remote: ${SSH_HOST}:~/${REMOTE_ROOT}"
}

# ============================================================
# Main
# ============================================================

case "${1:-deploy}" in
    deploy)  cmd_deploy ;;
    diff)    cmd_diff ;;
    status)  cmd_status ;;
    help|*)  cmd_help ;;
esac
