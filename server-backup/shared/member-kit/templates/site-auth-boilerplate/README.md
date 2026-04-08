# 1vsM Site Auth Boilerplate

Standard authentication setup for any 1vsM/client site using the member-kit.

## Quick Start

1. Copy these files to your site:
   - `auth.php` → `yoursite/includes/auth.php`
   - `members.php` → `yoursite/public_html/members.php`
   - `login.php` → `yoursite/public_html/templates/member-login.php`
   - `.env.example` → `yoursite/.env` (fill in values)

2. Configure `.env` with your site's details (see `.env.example`)

3. Run the member-kit migration to create the `members` table:
   ```bash
   php /path/to/member-kit/install.php
   ```

4. Visit `yoursite.com/members` — you should see the login form.

## Architecture

- **MEMBER_MODE=independent**: Each site has its own `members` table and branding
- **No HipHop.World branding**: Login forms use your site's name, logo, and colors
- **Auth methods**: Email/password (primary) + Google OAuth (optional via env var)
- **Post-login**: Redirects to `/members` (dashboard) or honors `?return=` param
- **Security**: CSRF protection, bcrypt passwords, rate limiting (from member-kit)

## Auth Rules

| Feature | HipHop Sites (MEMBER_MODE=hw) | 1vsM Sites (MEMBER_MODE=independent) |
|---------|-------------------------------|---------------------------------------|
| Login URL | hiphop.world/members | domain.com/members |
| Branding | HipHop.World gold/dark | Site's own name/logo/colors |
| SSO | Cross-domain token-based | None (standalone) |
| Auth methods | All OAuth + Web3 + email | Email/password + Google |
| User table | Shared `users` table | Site-specific `members` table |
| Profile domain | hiphop.id/{username} | N/A |

## Customization

- **Colors**: Set `SITE_PRIMARY_COLOR` in `.env` (any CSS color)
- **Logo**: Set `SITE_LOGO` in `.env` (path to your logo image)
- **Google SSO**: Set `GOOGLE_CLIENT_ID` + `GOOGLE_CLIENT_SECRET` to enable
- **Dashboard**: Customize `templates/member-dashboard.php` for your site
