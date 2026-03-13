<?php
/**
 * GET /api/member/my-messages.php
 *
 * Returns threaded conversations + legacy contact messages for the logged-in customer.
 * For dashboard tab content (HTML rendering).
 * Bilingual EN/ES support via member-translations.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/member-translations.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

$lang = getMemberLang();

try {
    requireMethod('GET');

    // Check if member is logged in
    if (!MemberAuth::isMemberLoggedIn()) {
        http_response_code(401);
        echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('sign_in_required', $lang)) . '</div>';
        exit;
    }

    $member = MemberAuth::getCurrentMember();
    $memberEmail = $member['email'] ?? null;

    if (!$memberEmail) {
        http_response_code(400);
        echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
        exit;
    }

    // Find customer record
    $memberId = (int) $_SESSION['member_id'];
    $custStmt = $pdo->prepare('SELECT id FROM oretir_customers WHERE member_id = ? OR email = ? LIMIT 1');
    $custStmt->execute([$memberId, $memberEmail]);
    $customerId = (int) $custStmt->fetchColumn();

    // Load conversations (if customer record exists)
    $conversations = [];
    if ($customerId) {
        $convStmt = $pdo->prepare(
            'SELECT c.id, c.subject, c.status, c.last_message_at, c.created_at,
                    (SELECT COUNT(*) FROM oretir_conversation_messages m
                     WHERE m.conversation_id = c.id AND m.is_read = 0 AND m.sender_type != "customer") as unread_count
             FROM oretir_conversations c
             WHERE c.customer_id = ?
             ORDER BY c.last_message_at DESC'
        );
        $convStmt->execute([$customerId]);
        $conversations = $convStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Load legacy contact messages
    $legacyStmt = $pdo->prepare(
        'SELECT id, name, email, subject, message, created_at, status
         FROM oretir_contact_messages
         WHERE email = ?
         ORDER BY created_at DESC
         LIMIT 20'
    );
    $legacyStmt->execute([$memberEmail]);
    $legacyMessages = $legacyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Status badge helper
    $statusBadge = function (string $status) use ($lang): string {
        $colors = [
            'open'          => 'var(--member-accent)',
            'waiting_reply' => '#f59e0b',
            'resolved'      => 'var(--member-success)',
            'closed'        => 'var(--member-text-muted)',
        ];
        $keys = [
            'open'          => 'status_open',
            'waiting_reply' => 'status_waiting',
            'resolved'      => 'status_resolved',
            'closed'        => 'status_closed',
        ];
        $color = $colors[$status] ?? 'var(--member-text-muted)';
        $label = memberT($keys[$status] ?? 'status_open', $lang);
        return '<span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:0.7rem;font-weight:600;background:' . $color . '20;color:' . $color . ';">' . htmlspecialchars($label) . '</span>';
    };

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1><?= htmlspecialchars(memberT('messages', $lang)) ?></h1>
                    <p><?= htmlspecialchars(memberT('messages_subtitle', $lang)) ?></p>
                </div>
                <button type="button" id="btn-new-conversation"
                    style="padding:0.5rem 1rem;background:var(--member-accent);color:#fff;border:none;border-radius:var(--member-radius);cursor:pointer;font-size:0.875rem;font-weight:600;">
                    + <?= htmlspecialchars(memberT('new_message', $lang)) ?>
                </button>
            </div>

            <!-- New Conversation Form (hidden by default) -->
            <div id="new-conversation-form" style="display:none;margin-bottom:1.5rem;padding:1rem;background:var(--member-surface-hover);border-radius:var(--member-radius);border:1px solid var(--member-border);">
                <div style="margin-bottom:0.75rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.25rem;color:var(--member-text);">
                        <?= htmlspecialchars(memberT('subject', $lang)) ?>
                    </label>
                    <input type="text" id="conv-subject" maxlength="255"
                        style="width:100%;padding:0.5rem;border:1px solid var(--member-border);border-radius:var(--member-radius);background:var(--member-surface);color:var(--member-text);font-size:0.875rem;box-sizing:border-box;"
                        placeholder="<?= htmlspecialchars(memberT('subject', $lang)) ?>">
                </div>
                <div style="margin-bottom:0.75rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.25rem;color:var(--member-text);">
                        <?= htmlspecialchars(memberT('message_body', $lang)) ?>
                    </label>
                    <textarea id="conv-body" rows="4" maxlength="5000"
                        style="width:100%;padding:0.5rem;border:1px solid var(--member-border);border-radius:var(--member-radius);background:var(--member-surface);color:var(--member-text);font-size:0.875rem;resize:vertical;box-sizing:border-box;"
                        placeholder="<?= htmlspecialchars(memberT('type_your_message', $lang)) ?>"></textarea>
                </div>
                <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                    <button type="button" id="btn-cancel-conv"
                        style="padding:0.4rem 0.75rem;background:transparent;color:var(--member-text-muted);border:1px solid var(--member-border);border-radius:var(--member-radius);cursor:pointer;font-size:0.8rem;">
                        Cancel
                    </button>
                    <button type="button" id="btn-send-conv"
                        style="padding:0.4rem 0.75rem;background:var(--member-accent);color:#fff;border:none;border-radius:var(--member-radius);cursor:pointer;font-size:0.8rem;font-weight:600;">
                        <?= htmlspecialchars(memberT('send_message', $lang)) ?>
                    </button>
                </div>
                <div id="conv-status" style="display:none;margin-top:0.5rem;font-size:0.8rem;"></div>
            </div>

            <!-- Conversations List -->
            <div style="margin-bottom:2rem;">
                <h2 style="font-size:1rem;margin:0 0 1rem;color:var(--member-text);"><?= htmlspecialchars(memberT('conversations', $lang)) ?></h2>
                <?php if (empty($conversations)): ?>
                    <p class="member-text-muted" style="text-align:center;padding:1.5rem 0;font-size:0.875rem;">
                        <?= htmlspecialchars(memberT('no_conversations', $lang)) ?>
                    </p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        <?php foreach ($conversations as $conv): ?>
                            <a href="javascript:void(0)" class="conv-item" data-conv-id="<?= (int) $conv['id'] ?>"
                                style="display:block;padding:1rem;background:var(--member-surface-hover);border-radius:var(--member-radius);border-left:3px solid var(--member-accent);text-decoration:none;color:inherit;cursor:pointer;transition:background 0.15s;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.4rem;">
                                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                        <h3 style="margin:0;font-size:0.95rem;color:var(--member-text);">
                                            <?= htmlspecialchars($conv['subject'] ?? memberT('no_subject', $lang)) ?>
                                        </h3>
                                        <?= $statusBadge($conv['status'] ?? 'open') ?>
                                        <?php if ((int) ($conv['unread_count'] ?? 0) > 0): ?>
                                            <span style="display:inline-block;padding:2px 6px;border-radius:9999px;font-size:0.65rem;font-weight:700;background:var(--member-accent);color:#fff;">
                                                <?= (int) $conv['unread_count'] ?> <?= htmlspecialchars(memberT('unread', $lang)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span style="font-size:0.75rem;color:var(--member-text-muted);white-space:nowrap;">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($conv['last_message_at'] ?? $conv['created_at'] ?? 'now'))) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Conversation Detail (loaded dynamically) -->
            <div id="conv-detail" style="display:none;margin-bottom:2rem;"></div>

            <!-- Legacy Contact Submissions -->
            <?php if (!empty($legacyMessages)): ?>
                <div style="border-top:1px solid var(--member-border);padding-top:1.5rem;">
                    <h2 style="font-size:1rem;margin:0 0 1rem;color:var(--member-text-muted);">
                        <?= htmlspecialchars(memberT('previous_submissions', $lang)) ?>
                    </h2>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        <?php foreach ($legacyMessages as $msg): ?>
                            <div style="padding:0.75rem;background:var(--member-surface-hover);border-radius:var(--member-radius);border-left:3px solid var(--member-border);opacity:0.8;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.3rem;">
                                    <h3 style="margin:0;font-size:0.875rem;color:var(--member-text-muted);">
                                        <?= htmlspecialchars($msg['subject'] ?? memberT('no_subject', $lang)) ?>
                                    </h3>
                                    <span style="font-size:0.7rem;color:var(--member-text-muted);">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($msg['created_at'] ?? 'now'))) ?>
                                    </span>
                                </div>
                                <p style="margin:0.3rem 0 0;color:var(--member-text-muted);font-size:0.8rem;">
                                    <?= htmlspecialchars(substr($msg['message'] ?? '', 0, 150)) ?>
                                    <?= strlen($msg['message'] ?? '') > 150 ? '...' : '' ?>
                                </p>
                                <?php if (!empty($msg['status'])): ?>
                                    <div style="margin-top:0.3rem;font-size:0.7rem;color:var(--member-success);">
                                        &#10003; <?= htmlspecialchars(ucfirst($msg['status'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        var newBtn = document.getElementById('btn-new-conversation');
        var form = document.getElementById('new-conversation-form');
        var cancelBtn = document.getElementById('btn-cancel-conv');
        var sendBtn = document.getElementById('btn-send-conv');
        var statusEl = document.getElementById('conv-status');
        var detailEl = document.getElementById('conv-detail');
        var lang = '<?= $lang ?>';

        function escHtml(str) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(str || ''));
            return d.innerHTML;
        }

        function formatDate(str) {
            if (!str) return '';
            var d = new Date(str.replace(' ', 'T'));
            return d.toLocaleDateString(lang === 'es' ? 'es' : 'en', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        // Toggle new conversation form
        if (newBtn) {
            newBtn.addEventListener('click', function() {
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
                detailEl.style.display = 'none';
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                form.style.display = 'none';
                document.getElementById('conv-subject').value = '';
                document.getElementById('conv-body').value = '';
                statusEl.style.display = 'none';
            });
        }

        // Send new conversation
        if (sendBtn) {
            sendBtn.addEventListener('click', function() {
                var subject = document.getElementById('conv-subject').value.trim();
                var body = document.getElementById('conv-body').value.trim();
                if (!subject || !body) return;

                sendBtn.disabled = true;
                fetch('/api/member/conversations', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ subject: subject, body: body })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        statusEl.style.display = 'block';
                        statusEl.style.color = 'var(--member-success)';
                        statusEl.textContent = '<?= addslashes(memberT('message_sent', $lang)) ?>';
                        setTimeout(function() {
                            if (window.memberDashboard && window.memberDashboard.loadTab) {
                                window.memberDashboard.loadTab('messages');
                            } else {
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        statusEl.style.display = 'block';
                        statusEl.style.color = 'var(--member-error, #ef4444)';
                        statusEl.textContent = data.error || 'Error';
                        sendBtn.disabled = false;
                    }
                })
                .catch(function() {
                    statusEl.style.display = 'block';
                    statusEl.style.color = 'var(--member-error, #ef4444)';
                    statusEl.textContent = 'Network error';
                    sendBtn.disabled = false;
                });
            });
        }

        // Click conversation to view detail
        document.querySelectorAll('.conv-item').forEach(function(el) {
            el.addEventListener('click', function() {
                var convId = el.getAttribute('data-conv-id');
                loadConversationDetail(convId);
            });
        });

        function buildConversationHtml(conv) {
            var container = document.createElement('div');
            container.style.cssText = 'padding:1rem;background:var(--member-surface-hover);border-radius:var(--member-radius);border:1px solid var(--member-border);';

            // Header
            var header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;';
            var title = document.createElement('h3');
            title.style.cssText = 'margin:0;font-size:1rem;';
            title.textContent = conv.subject;
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.style.cssText = 'background:none;border:none;color:var(--member-text-muted);cursor:pointer;font-size:1.2rem;';
            closeBtn.textContent = '\u00D7';
            closeBtn.addEventListener('click', function() { detailEl.style.display = 'none'; });
            header.appendChild(title);
            header.appendChild(closeBtn);
            container.appendChild(header);

            // Messages
            var msgList = document.createElement('div');
            msgList.style.cssText = 'display:flex;flex-direction:column;gap:0.75rem;max-height:400px;overflow-y:auto;margin-bottom:1rem;';
            (conv.messages || []).forEach(function(msg) {
                var isCustomer = msg.sender_type === 'customer';
                var bubble = document.createElement('div');
                bubble.style.cssText = 'align-self:' + (isCustomer ? 'flex-end' : 'flex-start') + ';max-width:80%;padding:0.6rem 0.8rem;border-radius:0.75rem;background:' + (isCustomer ? 'var(--member-accent)' : 'var(--member-surface)') + ';color:' + (isCustomer ? '#fff' : 'var(--member-text)') + ';font-size:0.85rem;';
                var meta = document.createElement('div');
                meta.style.cssText = 'font-size:0.7rem;opacity:0.7;margin-bottom:0.25rem;';
                meta.textContent = (msg.sender_name || '') + ' \u00B7 ' + formatDate(msg.created_at);
                var bodyEl = document.createElement('div');
                bodyEl.textContent = msg.body;
                bubble.appendChild(meta);
                bubble.appendChild(bodyEl);
                msgList.appendChild(bubble);
            });
            container.appendChild(msgList);

            // Reply form (if not closed)
            if (conv.status !== 'closed') {
                var replyRow = document.createElement('div');
                replyRow.style.cssText = 'display:flex;gap:0.5rem;';
                var textarea = document.createElement('textarea');
                textarea.rows = 2;
                textarea.maxLength = 5000;
                textarea.style.cssText = 'flex:1;padding:0.5rem;border:1px solid var(--member-border);border-radius:var(--member-radius);background:var(--member-surface);color:var(--member-text);font-size:0.85rem;resize:none;box-sizing:border-box;';
                textarea.placeholder = '<?= addslashes(memberT('type_your_message', $lang)) ?>';
                var replyBtn = document.createElement('button');
                replyBtn.type = 'button';
                replyBtn.style.cssText = 'padding:0.5rem 0.75rem;background:var(--member-accent);color:#fff;border:none;border-radius:var(--member-radius);cursor:pointer;font-size:0.8rem;font-weight:600;white-space:nowrap;';
                replyBtn.textContent = '<?= addslashes(memberT('reply', $lang)) ?>';
                replyBtn.addEventListener('click', function() {
                    var replyBody = textarea.value.trim();
                    if (!replyBody) return;
                    replyBtn.disabled = true;
                    fetch('/api/member/conversations?id=' + conv.id, {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ body: replyBody })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            loadConversationDetail(conv.id);
                        } else {
                            replyBtn.disabled = false;
                            alert(res.error || 'Error');
                        }
                    })
                    .catch(function() { replyBtn.disabled = false; });
                });
                replyRow.appendChild(textarea);
                replyRow.appendChild(replyBtn);
                container.appendChild(replyRow);
            }

            return container;
        }

        function loadConversationDetail(convId) {
            form.style.display = 'none';
            detailEl.style.display = 'block';
            while (detailEl.firstChild) detailEl.removeChild(detailEl.firstChild);
            var loadingP = document.createElement('p');
            loadingP.style.cssText = 'text-align:center;padding:1rem;color:var(--member-text-muted);';
            loadingP.textContent = 'Loading...';
            detailEl.appendChild(loadingP);

            fetch('/api/member/conversations?id=' + convId, { credentials: 'include' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                while (detailEl.firstChild) detailEl.removeChild(detailEl.firstChild);
                if (!data.success) {
                    var errP = document.createElement('p');
                    errP.style.color = 'var(--member-error,#ef4444)';
                    errP.textContent = data.error || 'Error';
                    detailEl.appendChild(errP);
                    return;
                }
                detailEl.appendChild(buildConversationHtml(data.data));
            })
            .catch(function() {
                while (detailEl.firstChild) detailEl.removeChild(detailEl.firstChild);
                var errP = document.createElement('p');
                errP.style.color = 'var(--member-error,#ef4444)';
                errP.textContent = 'Network error';
                detailEl.appendChild(errP);
            });
        }
    })();
    </script>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires customer/my-messages error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
