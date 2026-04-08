#!/bin/bash
# deploy.sh — Deploy shared member-kit to server
#
# Usage:
#   ./deploy.sh              Deploy member-kit to server
#   ./deploy.sh rollback     Restore previous deployed version
#   ./deploy.sh status       Show deploy status
#
# Syncs PHP classes + templates to the shared server path,
# CSS/JS to the 1vsm.com public directory (web-accessible),
# and keeps local 1vsm.com copy in sync.

set -euo pipefail

REMOTE="hiphopworld"
SHARED_PATH="/home/hiphopwo/shared/member-kit"
ASSETS_PATH="/home/hiphopwo/public_html/---1vsm.com/shared/member-kit"
LOCAL_ASSETS="../---1vsm.com/shared/member-kit"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
DEPLOY_STATE="${SCRIPT_DIR}/.last-deploy"

cd "$SCRIPT_DIR"

# ── Helpers ──────────────────────────────────────────────────────────────────
last_deploy_sha() {
    if [ -f "$DEPLOY_STATE" ]; then
        cat "$DEPLOY_STATE"
    else
        echo ""
    fi
}

record_deploy() {
    local sha
    sha=$(git rev-parse HEAD 2>/dev/null || echo "")
    if [ -n "$sha" ]; then
        echo "$sha" > "$DEPLOY_STATE"
        local tag="deploy/mk-$(date +%Y-%m-%d-%H%M%S)"
        git tag "$tag" "$sha" 2>/dev/null || true
        echo "  -> Tagged as ${tag}"
    fi
}

# ── Commands ─────────────────────────────────────────────────────────────────
cmd_status() {
    local last_sha
    last_sha=$(last_deploy_sha)

    if [ -z "$last_sha" ]; then
        echo "No deploys recorded yet."
    else
        local last_msg
        last_msg=$(git log -1 --format='%s (%ci)' "$last_sha" 2>/dev/null || echo "unknown")
        echo "Last deploy: ${last_sha:0:7} — ${last_msg}"

        local current_sha
        current_sha=$(git rev-parse HEAD 2>/dev/null || echo "")
        if [ "$last_sha" = "$current_sha" ]; then
            echo "Server is up to date."
        else
            local pending
            pending=$(git diff --name-only "$last_sha" HEAD 2>/dev/null | wc -l | tr -d ' ')
            echo "${pending} file(s) changed since last deploy."
        fi
    fi

    # Recent deploy tags
    local tags
    tags=$(git tag -l 'deploy/mk-*' --sort=-version:refname 2>/dev/null | head -5)
    if [ -n "$tags" ]; then
        echo ""
        echo "Recent deploys:"
        echo "$tags" | while read -r tag; do
            local tag_date
            tag_date=$(git log -1 --format='%ci' "$tag" 2>/dev/null | cut -d' ' -f1,2)
            local tag_msg
            tag_msg=$(git log -1 --format='%s' "$tag" 2>/dev/null)
            echo "  ${tag}  ${tag_msg}  (${tag_date})"
        done
    fi
}

cmd_rollback() {
    local tags
    tags=$(git tag -l 'deploy/mk-*' --sort=-version:refname 2>/dev/null | head -2)
    local tag_count
    tag_count=$(echo "$tags" | grep -c . || echo 0)

    if [ "$tag_count" -lt 2 ]; then
        echo "ERROR: Not enough deploy history to rollback. Need at least 2 deploys."
        exit 1
    fi

    local previous_tag
    previous_tag=$(echo "$tags" | tail -1)
    local previous_sha
    previous_sha=$(git rev-parse "$previous_tag")
    local previous_msg
    previous_msg=$(git log -1 --format='%s' "$previous_sha")

    echo "Rolling back to: ${previous_tag} — ${previous_msg}"

    # Rsync from the previous commit's state
    mkdir -p /tmp/mk_rollback_$$
    git archive "$previous_sha" | tar -x -C /tmp/mk_rollback_$$

    echo "  -> Syncing rolled-back files..."
    rsync -avz --checksum --delete \
        --exclude='.git' \
        --exclude='.env' \
        --exclude='tests/' \
        --exclude='.DS_Store' \
        --exclude='.last-deploy' \
        "/tmp/mk_rollback_$$/" \
        "${REMOTE}:${SHARED_PATH}/"

    # Also rollback CSS/JS
    if [ -f "/tmp/mk_rollback_$$/css/member.css" ]; then
        scp "/tmp/mk_rollback_$$/css/member.css" "$REMOTE:$ASSETS_PATH/css/member.css"
    fi
    if [ -f "/tmp/mk_rollback_$$/js/member.js" ]; then
        scp "/tmp/mk_rollback_$$/js/member.js" "$REMOTE:$ASSETS_PATH/js/member.js"
    fi

    rm -rf /tmp/mk_rollback_$$

    # Flush OPcache
    echo "  -> Flushing OPcache"
    ssh "$REMOTE" "php -r \"if(function_exists('opcache_reset')){opcache_reset();echo 'OK';}\"" 2>/dev/null || true

    echo "$previous_sha" > "$DEPLOY_STATE"
    echo "Rolled back to ${previous_tag}."
}

cmd_deploy() {
    # Pre-deploy validation — catch broken files before they ship
    if [ -f "tests/pre-deploy-check.php" ]; then
        echo "Running pre-deploy checks..."
        if ! php tests/pre-deploy-check.php; then
            echo "Pre-deploy check FAILED — aborting deploy."
            exit 1
        fi
        echo ""
    fi

    echo "Deploying member-kit..."

    # Ensure remote directories exist
    ssh "$REMOTE" "mkdir -p $SHARED_PATH/includes/member-kit $SHARED_PATH/api/member $SHARED_PATH/templates/member/modals $SHARED_PATH/templates/member/tabs $SHARED_PATH/config $SHARED_PATH/migrations $SHARED_PATH/endpoints/api $ASSETS_PATH/css $ASSETS_PATH/js"

    # PHP: loader + base class + kit classes
    echo "  -> PHP loader + base + classes"
    scp loader.php "$REMOTE:$SHARED_PATH/"
    scp includes/KitBase.php "$REMOTE:$SHARED_PATH/includes/"
    scp includes/member-kit/MemberAuth.php "$REMOTE:$SHARED_PATH/includes/member-kit/"
    scp includes/member-kit/MemberProfile.php "$REMOTE:$SHARED_PATH/includes/member-kit/"
    scp includes/member-kit/MemberSSO.php "$REMOTE:$SHARED_PATH/includes/member-kit/"
    scp includes/member-kit/MemberSync.php "$REMOTE:$SHARED_PATH/includes/member-kit/"
    scp includes/member-kit/MemberMail.php "$REMOTE:$SHARED_PATH/includes/member-kit/"
    scp includes/member-kit/MemberGoogle.php "$REMOTE:$SHARED_PATH/includes/member-kit/"

    # API endpoints (sites delegate to these via MEMBER_KIT_PATH)
    echo "  -> API endpoints"
    scp api/member/login.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/logout.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/register.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/password.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/profile.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/forgot-password.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/reset-password.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/verify-email.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/sso.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/sso-callback.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/sso-unlink.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/google.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/google-callback.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/google-unlink.php "$REMOTE:$SHARED_PATH/api/member/"
    # Phase 5 APIs (Device Management)
    scp api/member/devices.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/rename-device.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/revoke-device.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/login-activity.php "$REMOTE:$SHARED_PATH/api/member/"
    # Phase 6 APIs (2FA & Email Verification)
    scp api/member/2fa-prompt.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/2fa-setup.php "$REMOTE:$SHARED_PATH/api/member/"
    scp api/member/resend-verification.php "$REMOTE:$SHARED_PATH/api/member/"
    # Phase 7 APIs — DISABLED: WebAuthn, anomaly, mobile not wired up yet (no client JS)
    # These files stay in git but are NOT deployed to reduce attack surface.
    # scp api/member/webauthn-register-begin.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/webauthn-register-complete.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/anomaly-check.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/report-suspicious-activity.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/rotate-fingerprint.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/mobile-register-device.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/mobile-auth.php "$REMOTE:$SHARED_PATH/api/member/"
    # scp api/member/mobile-notify.php "$REMOTE:$SHARED_PATH/api/member/"

    # Shared endpoints (SSO callback, site-roles API)
    echo "  -> Shared endpoints"
    ssh "$REMOTE" "mkdir -p $SHARED_PATH/endpoints/api"
    scp endpoints/sso-callback.php "$REMOTE:$SHARED_PATH/endpoints/"
    scp endpoints/api/site-roles.php "$REMOTE:$SHARED_PATH/endpoints/api/"

    # Config template (standalone mode fallback)
    echo "  -> Config template"
    scp config/database.php "$REMOTE:$SHARED_PATH/config/"

    # Migrations
    echo "  -> Migrations"
    scp migrations/*.php "$REMOTE:$SHARED_PATH/migrations/"

    # Templates
    echo "  -> Shared templates"
    scp templates/member/login.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/register.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/settings.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/profile.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/activity.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/forgot-password.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/reset-password.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/verify-email.php "$REMOTE:$SHARED_PATH/templates/member/"
    # Phase 5 Templates (Device Management)
    scp templates/member/devices.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/login-history.php "$REMOTE:$SHARED_PATH/templates/member/"
    # Phase 6 Templates (2FA, Email Verification, Keyboard Help)
    scp templates/member/modals/2fa-suggestion.php "$REMOTE:$SHARED_PATH/templates/member/modals/"
    scp templates/member/modals/keyboard-help.php "$REMOTE:$SHARED_PATH/templates/member/modals/"
    # Phase 7 Templates (Anomaly Dashboard)
    scp templates/member/anomaly-dashboard.php "$REMOTE:$SHARED_PATH/templates/member/"
    # Network SSO Templates (login-network, dashboard, role tabs)
    scp templates/member/login-network.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/dashboard.php "$REMOTE:$SHARED_PATH/templates/member/"
    scp templates/member/tabs/manage-roles.php "$REMOTE:$SHARED_PATH/templates/member/tabs/"
    scp templates/member/tabs/network-roles.php "$REMOTE:$SHARED_PATH/templates/member/tabs/"

    # CSS/JS to 1vsm.com public (web-accessible for cross-origin loading)
    echo "  -> CSS/JS to 1vsm.com"
    scp css/member.css "$REMOTE:$ASSETS_PATH/css/member.css"
    scp js/member.js "$REMOTE:$ASSETS_PATH/js/member.js"
    scp "$LOCAL_ASSETS/.htaccess" "$REMOTE:$ASSETS_PATH/.htaccess"

    # Local sync (keep 1vsm.com copy in sync — only needed if not using symlinks)
    if [ ! -L "$LOCAL_ASSETS/css/member.css" ]; then
        echo "  -> Local CSS/JS sync"
        cp css/member.css "$LOCAL_ASSETS/css/member.css"
        cp js/member.js "$LOCAL_ASSETS/js/member.js"
    else
        echo "  -> Local CSS/JS: symlinked (skipping copy)"
    fi

    # Flush OPcache to prevent stale bytecode after deploy
    echo "  -> Flushing OPcache"
    ssh "$REMOTE" "php -r \"if(function_exists('opcache_reset')){opcache_reset();echo 'OK';}\"" 2>/dev/null || true

    # Record deploy
    record_deploy

    echo "Member-kit deployed."
}

# ── Main ─────────────────────────────────────────────────────────────────────
command="${1:-}"

case "$command" in
    rollback) cmd_rollback ;;
    status)   cmd_status ;;
    *)        cmd_deploy ;;
esac
