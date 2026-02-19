#!/bin/bash
# Oregon Tires — Deploy Script
# Usage:
#   ./deploy.sh          Deploy changed files to production
#   ./deploy.sh diff     Show what would be deployed (dry run)
#   ./deploy.sh status   Check remote server state
#   ./deploy.sh build    Build CSS only (no deploy)

set -euo pipefail

REMOTE="hiphopworld"
REMOTE_PATH="/home/hiphopwo/public_html/---oregon.tires"
LOCAL_DIR="$(cd "$(dirname "$0")" && pwd)"
UPLOADS_DIR="${LOCAL_DIR}/_uploads"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()  { echo -e "${GREEN}[deploy]${NC} $1"; }
warn() { echo -e "${YELLOW}[warn]${NC} $1"; }
err()  { echo -e "${RED}[error]${NC} $1"; }

# ─── Build CSS ──────────────────────────────────────────────────────────────
build_css() {
  log "Building Tailwind CSS..."
  cd "$LOCAL_DIR"
  npx @tailwindcss/cli -i src/input.css -o public_html/assets/styles.css --minify 2>&1
  local size=$(ls -la public_html/assets/styles.css | awk '{print $5}')
  log "Built styles.css (${size} bytes)"
}

# ─── Diff (dry run) ────────────────────────────────────────────────────────
cmd_diff() {
  log "Files that would be deployed:"
  echo ""
  cd "$LOCAL_DIR"
  # Show git status of tracked files
  git diff --name-only HEAD
  echo ""
  # Show staged changes
  git diff --cached --name-only 2>/dev/null || true
  echo ""
  log "Untracked files in public_html/:"
  git ls-files --others --exclude-standard public_html/ | head -20
}

# ─── Status ─────────────────────────────────────────────────────────────────
cmd_status() {
  log "Remote server state:"
  echo ""
  ssh "$REMOTE" "ls -la ${REMOTE_PATH}/ | head -20"
  echo ""
  log "Remote disk usage:"
  ssh "$REMOTE" "du -sh ${REMOTE_PATH}/"
  echo ""
  log "Local git status:"
  cd "$LOCAL_DIR"
  git status -s
}

# ─── Deploy ─────────────────────────────────────────────────────────────────
cmd_deploy() {
  cd "$LOCAL_DIR"

  # Build CSS first
  build_css

  # Get list of changed files (tracked, modified since last commit)
  local changed_files
  changed_files=$(git diff --name-only HEAD 2>/dev/null || true)

  if [ -z "$changed_files" ]; then
    warn "No uncommitted changes to deploy. Deploying last commit's changes..."
    changed_files=$(git diff --name-only HEAD~1 HEAD 2>/dev/null || true)
  fi

  if [ -z "$changed_files" ]; then
    err "No changes detected. Nothing to deploy."
    exit 1
  fi

  # Filter to only public_html files
  local deploy_files
  deploy_files=$(echo "$changed_files" | grep "^public_html/" || true)

  # Always include styles.css (freshly built)
  deploy_files=$(echo -e "${deploy_files}\npublic_html/assets/styles.css" | sort -u | grep -v "^$")

  if [ -z "$deploy_files" ]; then
    err "No public_html files to deploy."
    exit 1
  fi

  log "Files to deploy:"
  echo "$deploy_files" | sed 's/^/  /'
  echo ""

  # Clean and create _uploads
  rm -rf "$UPLOADS_DIR"
  mkdir -p "$UPLOADS_DIR"

  # Copy files to _uploads maintaining directory structure
  for file in $deploy_files; do
    local rel_path="${file#public_html/}"
    local dir_path=$(dirname "$rel_path")
    mkdir -p "${UPLOADS_DIR}/${dir_path}"
    cp "$LOCAL_DIR/$file" "${UPLOADS_DIR}/${rel_path}"
  done

  local file_count=$(echo "$deploy_files" | wc -l | tr -d ' ')
  log "Uploading ${file_count} file(s) to ${REMOTE}:${REMOTE_PATH}/"

  # SCP to remote
  scp -r "${UPLOADS_DIR}/"* "${REMOTE}:${REMOTE_PATH}/"

  # Clean up
  rm -rf "$UPLOADS_DIR"

  log "Deploy complete!"
}

# ─── Main ───────────────────────────────────────────────────────────────────
case "${1:-deploy}" in
  diff)   cmd_diff ;;
  status) cmd_status ;;
  build)  build_css ;;
  deploy) cmd_deploy ;;
  *)
    echo "Usage: $0 {deploy|diff|status|build}"
    exit 1
    ;;
esac
