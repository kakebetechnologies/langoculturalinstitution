# 8. Architecture, Security and Data Protection

*Plain, proven technology on affordable hosting, with security and privacy designed in and a clear route to grow.*

![fig|Figure 8.1: Architecture. Three surfaces share one API, one database and one media store; services sit behind the API.](figures/fig-architecture.svg)

| Layer | Choice | Why |
|---|---|---|
| **Website, admin** | PHP 8.2, MySQL or MariaDB, no framework, no build step | Runs on affordable hosting; easy for local developers to maintain; the approach used in several Kakebe systems |
| **API** | Stateless JSON API with bearer tokens, paging, rate limits, documented errors | Any future channel can use it |
| **Mobile app** | Expo and React Native, one JavaScript codebase; web app from the same code | Android, iOS and web from one build; over-the-air fixes |
| **Services** | Push (Firebase, Apple), SMTP email, local SMS gateway, maps; mobile money (ioTec Pay, Pesapal) as an optional upgrade | Already integrated in other Kakebe products |

**Hosting.** Managed by Kakebe, or on the Foundation's own account; staging and production environments; HTTPS mandatory. Domain, hosting and store accounts are in the Foundation's name [TBC: preferred hosting location and domain]. **Backups:** automated daily database backups kept off the server, regular copies of uploads, an on-demand SQL backup, and a restore drill before launch.

## Security

**In the demonstrator today:** hashed passwords; API tokens stored only as SHA-256 hashes; no session cookies on the API; six roles with scoped permissions; rate limits on sign-in, registration, uploads, translator and assistant; duplicate-safe uploads; CSRF protection on forms; forced password change at first sign-in; audit log and version history; secure token storage on the phone (Keychain, Keystore); account deletion. **Added before launch:** HTTPS and security headers, a dependency and configuration audit, input and upload hardening, and a **penetration test** against the OWASP Top 10 by Kakebe's cybersecurity team.

## Data protection and privacy

The platform will hold personal data (names, contacts, clan, location, recordings), treated as a duty of care and aligned with the **Uganda Data Protection and Privacy Act, 2019**:

- **Consent and purpose:** plain-language consent at registration and on forms, with a privacy notice in English and Leb Lango; data used only to run the platform and the Foundation's work, never sold.
- **Minimisation:** only the fields needed; phone, country and clan optional where possible.
- **Rights:** members can view, correct and delete their account; administrators can export or erase a person's data on request.
- **Accountability:** a retention schedule, a nominated data protection focal person, and registration with the Personal Data Protection Office [TBC: confirm with the Foundation's legal adviser]. The demonstrator's privacy and terms pages are drafts that we finalise with that adviser before launch.
- **Children and hosting:** clear rules for children's recordings and a disclosed hosting location.

**Reliability and inclusion.** Caching, uptime monitoring under the support plan, WCAG 2.1 AA, Leb Lango beside English, and assisted contribution so a youth ambassador can record for an elder with consent captured.
