<?php
// Redirect /member/profile → /members?tab=account
header('Location: /members?tab=account', true, 301);
exit;
