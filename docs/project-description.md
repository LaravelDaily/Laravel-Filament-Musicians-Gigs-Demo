Overview
We run a live music business (Mod Society) with a roster of ~50, 60 musicians today and plan to scale to 100+ across multiple regions. We need a secure web app where admins manage gigs and staffing, and musicians log in to view their gigs, accept/decline, and request a sub-out.

This is a long-term internal operations product, not a marketing website.

---

Tech-stack
- Laravel 12
- MySQL
- Filament v5 (for admin users, not musicians)
- Livewire v4 (user panel)

---

Users & Roles (RBAC)

Admin (2 users): full access to gigs, staffing, roster, notifications, and exports

Musician (50–100+ users): can view assigned gigs, accept/decline, and request sub-out

(Optional later) Band Leader/Sub-admin role (not required for v1)

---

Core Workflows

Admin creates/edit gig with details: date/time, call time, venue/address, client contact, dress code, notes, pay fields, attachments (PDFs), etc.

Admin assigns musicians to a gig (with instrument/role tags).

Musician portal: musician logs in and sees “My Upcoming Gigs” and gig detail pages.

Accept/Decline: musician can accept or decline each assignment.

Sub-Out: musician can request a sub-out for a gig (optional reason). This triggers an admin notification/workflow so we can restaff quickly.

---

Admin Tools

Roster management (add/edit musician profile, instruments, phone/email, notes, region/tags)

Gig staffing dashboard (filter by date, region, status)

View assignment statuses (accepted/declined/pending/sub-out requested)

Print-friendly “gig worksheet” page per gig (for day-of use)

Audit log or basic activity history (who changed what, minimal is fine)

Export CSV (gigs + assignments at minimum)

---

Notifications / Messaging (v1)
When a musician declines or requests sub-out, admins must be notified immediately.
Preferred approach for v1: email notification to admins (SMS/Push can be future).
System should store the sub-out request + reason + timestamp.

---

Tech Requirements (open to your recommendation, but must be production-ready)

Secure authentication + password reset

RBAC enforced on the backend (not just hidden on the UI)

Mobile-friendly UI (musicians will use phones)