<?php
?>
<div id="member-keyboard-help-overlay" class="member-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="keyboard-help-title" aria-describedby="keyboard-help-desc" tabindex="-1" style="display:none;">
    <div class="member-modal-panel member-keyboard-help-panel">
        <div class="member-modal-header">
            <h2 id="keyboard-help-title" class="member-modal-title">Keyboard Shortcuts</h2>
            <button type="button" class="member-modal-close" id="keyboard-help-close" aria-label="Close keyboard shortcuts help">&#215;</button>
        </div>
        <p id="keyboard-help-desc" class="member-modal-desc">These shortcuts work anywhere on member pages. They are disabled when a text input or textarea has focus.</p>
        <dl class="member-shortcut-list">
            <div class="member-shortcut-row">
                <dt class="member-shortcut-keys">
                    <kbd class="member-kbd">Ctrl</kbd>
                    <span class="member-kbd-plus" aria-hidden="true">+</span>
                    <kbd class="member-kbd">Enter</kbd>
                    <small class="member-kbd-note">(Cmd+Enter on Mac)</small>
                </dt>
                <dd class="member-shortcut-desc">Submit the nearest form</dd>
            </div>
            <div class="member-shortcut-row">
                <dt class="member-shortcut-keys"><kbd class="member-kbd">?</kbd></dt>
                <dd class="member-shortcut-desc">Show / hide this help overlay</dd>
            </div>
            <div class="member-shortcut-row">
                <dt class="member-shortcut-keys"><kbd class="member-kbd">Esc</kbd></dt>
                <dd class="member-shortcut-desc">Close this overlay</dd>
            </div>
        </dl>
        <p class="member-modal-footer-note">Press <kbd class="member-kbd">Esc</kbd> or click outside to dismiss.</p>
    </div>
</div>
