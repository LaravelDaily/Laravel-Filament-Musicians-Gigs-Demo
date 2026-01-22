# User Stories - Mod Society Gig Management Platform

## Overview

This document contains user stories for the Mod Society gig management platform, an internal operations tool for managing live music gigs and musician staffing.

**User Types:**
- **Admin** - Platform administrators (2 users) with full access to gigs, staffing, roster, and reports
- **Musician** - Roster musicians (50-100+) who view assigned gigs, accept/decline, and request sub-outs
- **Visitor** - Unauthenticated user (redirected to login)

**Key Decisions:**
- Sub-out requests are handled by admins (musicians don't suggest replacements)
- Pay fields are display-only (no payment processing)
- Notifications go to admins only for v1
- Musicians accept/decline per-gig (no availability calendar)
- One gig = one event with single call time
- One instrument/role per musician per gig
- No response deadline enforcement
- Minimal audit log (assignment status changes only)
- Predefined regions/tags managed by admin
- Musicians can see other musicians assigned to the same gig

---

## 1. Authentication & Access Control

### US-1.1: Admin Login
**As an** Admin
**I want to** log in with my email and password
**So that** I can access the admin panel to manage gigs and musicians

**Acceptance Criteria:**
- [ ] Login form accepts email and password
- [ ] Invalid credentials show appropriate error message
- [ ] Successful login redirects to Filament admin dashboard
- [ ] Session persists until logout or expiration
- [ ] Only users with admin role can access Filament panel

**Expected Result:** Admin is authenticated and can access the Filament admin panel.

---

### US-1.2: Musician Login
**As a** Musician
**I want to** log in with my email and password
**So that** I can view my assigned gigs and respond to them

**Acceptance Criteria:**
- [ ] Login form accepts email and password
- [ ] Invalid credentials show appropriate error message
- [ ] Successful login redirects to musician portal dashboard
- [ ] Session persists until logout or expiration
- [ ] Musicians cannot access admin panel routes

**Expected Result:** Musician is authenticated and can access the musician portal.

---

### US-1.3: Password Reset
**As a** registered user (Admin or Musician)
**I want to** reset my password if I forget it
**So that** I can regain access to my account

**Acceptance Criteria:**
- [ ] "Forgot password" link on login page
- [ ] User enters email address
- [ ] Password reset link sent to email (valid for 60 minutes)
- [ ] User can set new password via reset link
- [ ] Password must meet minimum security requirements (8+ characters)
- [ ] Confirmation message shown after successful reset

**Expected Result:** User receives reset email and can set a new password.

---

### US-1.4: Role-Based Access Control
**As the** System
**I want to** enforce role-based access on the backend
**So that** users can only access features appropriate to their role

**Acceptance Criteria:**
- [ ] Admin routes return 403 for musician users
- [ ] Musician portal routes return 403 for unauthenticated users
- [ ] API endpoints validate user role before processing
- [ ] Direct URL access to unauthorized resources is blocked
- [ ] RBAC is enforced server-side, not just hidden in UI

**Expected Result:** Users can only access resources and actions permitted by their role.

---

## 2. Musician Roster Management (Admin)

### US-2.1: Add Musician to Roster
**As an** Admin
**I want to** add a new musician to the roster
**So that** they can be assigned to gigs

**Acceptance Criteria:**
- [ ] Form collects: name, email, phone number
- [ ] Form collects: instruments/roles (multi-select from predefined list)
- [ ] Form collects: region (select from predefined list)
- [ ] Form collects: tags (multi-select from predefined list)
- [ ] Form collects: notes (optional free text)
- [ ] Email must be unique in the system
- [ ] Musician account is created with "musician" role
- [ ] Temporary password is generated or musician receives setup email

**Expected Result:** Musician is added to roster and can log in to the portal.

---

### US-2.2: Edit Musician Profile
**As an** Admin
**I want to** edit a musician's profile information
**So that** I can keep their details up to date

**Acceptance Criteria:**
- [ ] Can edit: name, email, phone number
- [ ] Can edit: instruments/roles
- [ ] Can edit: region and tags
- [ ] Can edit: notes
- [ ] Changes are saved immediately
- [ ] Email change validates uniqueness

**Expected Result:** Musician profile is updated with new information.

---

### US-2.3: View Musician Roster
**As an** Admin
**I want to** view all musicians in the roster
**So that** I can manage the team and find musicians for gigs

**Acceptance Criteria:**
- [ ] List displays all musicians with: name, instruments, region, phone, email
- [ ] Can search by name
- [ ] Can filter by instrument/role
- [ ] Can filter by region
- [ ] Can filter by tag
- [ ] Can sort by name
- [ ] Pagination for large rosters

**Expected Result:** Admin can browse and search the complete musician roster.

---

### US-2.4: Deactivate Musician
**As an** Admin
**I want to** deactivate a musician's account
**So that** they can no longer access the portal or be assigned to new gigs

**Acceptance Criteria:**
- [ ] Deactivate action available on musician profile
- [ ] Deactivated musicians cannot log in
- [ ] Deactivated musicians are excluded from assignment dropdowns
- [ ] Existing assignments remain visible for historical records
- [ ] Can reactivate a deactivated musician

**Expected Result:** Musician account is deactivated but historical data is preserved.

---

### US-2.5: Manage Instruments List
**As an** Admin
**I want to** manage the list of available instruments/roles
**So that** I can categorize musicians accurately

**Acceptance Criteria:**
- [ ] Can add new instruments/roles to the list
- [ ] Can edit existing instruments/roles
- [ ] Can delete unused instruments/roles (if not assigned)
- [ ] List is available when editing musicians and assigning to gigs

**Expected Result:** Admin maintains a consistent list of instruments/roles for the roster.

---

### US-2.6: Manage Regions List
**As an** Admin
**I want to** manage the list of available regions
**So that** I can organize musicians and gigs by location

**Acceptance Criteria:**
- [ ] Can add new regions
- [ ] Can edit existing region names
- [ ] Can delete unused regions (if not assigned)
- [ ] Regions are available when filtering musicians and gigs

**Expected Result:** Admin maintains a consistent list of regions for organization.

---

### US-2.7: Manage Tags List
**As an** Admin
**I want to** manage the list of available tags
**So that** I can flexibly categorize musicians

**Acceptance Criteria:**
- [ ] Can add new tags
- [ ] Can edit existing tags
- [ ] Can delete unused tags
- [ ] Tags are available when editing musicians

**Expected Result:** Admin maintains a consistent tagging system for musicians.

---

## 3. Gig Management (Admin)

### US-3.1: Create Gig
**As an** Admin
**I want to** create a new gig with all relevant details
**So that** I can staff it and inform musicians

**Acceptance Criteria:**
- [ ] Form collects required fields:
  - Gig name/title
  - Date
  - Call time
  - Performance time (optional)
  - End time (optional)
  - Venue name
  - Venue address
- [ ] Form collects optional fields:
  - Client contact name
  - Client contact phone
  - Client contact email
  - Dress code (free text)
  - Notes/special instructions
  - Pay rate/amount (display only)
  - Region (select from predefined list)
- [ ] Can attach PDF files (contracts, maps, set lists)
- [ ] Gig is created with "draft" or "active" status
- [ ] Gig appears in admin gig list immediately

**Expected Result:** Gig is created and ready for musician assignments.

---

### US-3.2: Edit Gig
**As an** Admin
**I want to** edit an existing gig's details
**So that** I can update information as plans change

**Acceptance Criteria:**
- [ ] All gig fields can be edited
- [ ] Can add/remove attachments
- [ ] Changes are saved immediately
- [ ] Musicians see updated information on their portal
- [ ] Edit history is not required for v1

**Expected Result:** Gig details are updated and visible to assigned musicians.

---

### US-3.3: View Gig List
**As an** Admin
**I want to** view all gigs with filtering options
**So that** I can manage upcoming and past events

**Acceptance Criteria:**
- [ ] List displays gigs with: date, name, venue, region, staffing status
- [ ] Staffing status shows: X/Y positions filled
- [ ] Can filter by date range
- [ ] Can filter by region
- [ ] Can filter by staffing status (fully staffed, needs musicians, has pending responses)
- [ ] Can search by gig name or venue
- [ ] Default sort by date (upcoming first)
- [ ] Can view past gigs

**Expected Result:** Admin can efficiently browse and find gigs.

---

### US-3.4: View Gig Detail (Admin)
**As an** Admin
**I want to** view complete gig details including all assignments
**So that** I can manage staffing and prepare for the event

**Acceptance Criteria:**
- [ ] Displays all gig information
- [ ] Shows all musician assignments with:
  - Musician name
  - Instrument/role
  - Status (pending, accepted, declined, sub-out requested)
  - Response timestamp
- [ ] Shows sub-out requests with reasons
- [ ] Links to add/edit assignments
- [ ] Link to print-friendly worksheet

**Expected Result:** Admin has complete visibility into gig details and staffing status.

---

### US-3.5: Delete/Cancel Gig
**As an** Admin
**I want to** delete or cancel a gig
**So that** I can remove events that are no longer happening

**Acceptance Criteria:**
- [ ] Can mark gig as "cancelled"
- [ ] Cancelled gigs remain in system for records
- [ ] Can permanently delete gig (with confirmation)
- [ ] Deleting removes all associated assignments
- [ ] Assigned musicians are NOT automatically notified (admin handles communication)

**Expected Result:** Gig is cancelled or removed from the system.

---

### US-3.6: Duplicate Gig
**As an** Admin
**I want to** duplicate an existing gig
**So that** I can quickly create similar events

**Acceptance Criteria:**
- [ ] Creates new gig with copied details
- [ ] Date is cleared (must be set)
- [ ] Assignments are NOT copied
- [ ] Attachments are copied
- [ ] New gig opens in edit mode

**Expected Result:** New gig is created with pre-filled details from the original.

---

## 4. Gig Staffing (Admin)

### US-4.1: Assign Musician to Gig
**As an** Admin
**I want to** assign a musician to a gig with a specific role
**So that** the musician knows they're needed for the event

**Acceptance Criteria:**
- [ ] Can select musician from roster dropdown
- [ ] Can select instrument/role for this assignment
- [ ] Can add assignment-specific notes (optional)
- [ ] Can set pay amount for this assignment (display only)
- [ ] Assignment is created with "pending" status
- [ ] Musician sees new assignment in their portal
- [ ] Cannot assign same musician twice to same gig

**Expected Result:** Musician is assigned to the gig and can view it in their portal.

---

### US-4.2: Remove Assignment
**As an** Admin
**I want to** remove a musician's assignment from a gig
**So that** I can restaff if needed

**Acceptance Criteria:**
- [ ] Can remove any assignment regardless of status
- [ ] Confirmation required before removal
- [ ] Removed assignment no longer appears in musician's portal
- [ ] Admin is NOT required to notify musician (handled separately)

**Expected Result:** Assignment is removed from the gig.

---

### US-4.3: View Assignment Status Dashboard
**As an** Admin
**I want to** see the status of all assignments for a gig at a glance
**So that** I can quickly identify staffing issues

**Acceptance Criteria:**
- [ ] Shows count by status: pending, accepted, declined, sub-out requested
- [ ] Visual indicators (colors/icons) for different statuses
- [ ] Pending assignments are highlighted
- [ ] Sub-out requests are prominently flagged
- [ ] Can filter gig list by staffing status

**Expected Result:** Admin can quickly assess staffing status across gigs.

---

### US-4.4: Bulk Assign Musicians
**As an** Admin
**I want to** assign multiple musicians to a gig at once
**So that** I can staff gigs more efficiently

**Acceptance Criteria:**
- [ ] Can select multiple musicians from roster
- [ ] Must assign instrument/role for each
- [ ] All assignments created with "pending" status
- [ ] Validation prevents duplicate assignments

**Expected Result:** Multiple musicians are assigned to the gig in one action.

---

### US-4.5: Reassign After Sub-Out
**As an** Admin
**I want to** easily reassign a position after a sub-out request
**So that** I can quickly fill the gap

**Acceptance Criteria:**
- [ ] Sub-out request shows the instrument/role needed
- [ ] Can search roster by instrument/role
- [ ] Can see which musicians are already assigned to conflicting gigs on that date
- [ ] New assignment replaces or supplements the sub-out

**Expected Result:** Position is restaffed after sub-out request.

---

## 5. Musician Portal

### US-5.1: View My Upcoming Gigs
**As a** Musician
**I want to** see all my upcoming gig assignments
**So that** I know when and where I need to be

**Acceptance Criteria:**
- [ ] Dashboard shows list of upcoming gigs
- [ ] Each gig shows: date, call time, venue, gig name
- [ ] Shows my assignment status (pending, accepted)
- [ ] Gigs sorted by date (soonest first)
- [ ] Clear visual indicator for gigs needing response
- [ ] Mobile-friendly layout

**Expected Result:** Musician has clear visibility of their upcoming schedule.

---

### US-5.2: View Gig Detail (Musician)
**As a** Musician
**I want to** view complete details for a gig I'm assigned to
**So that** I have all the information I need

**Acceptance Criteria:**
- [ ] Displays:
  - Gig name
  - Date and day of week
  - Call time
  - Performance time (if set)
  - End time (if set)
  - Venue name and address (with map link)
  - Dress code
  - Notes/special instructions
  - My assigned instrument/role
  - My pay amount (if set)
- [ ] Can view/download attached PDFs
- [ ] Shows other musicians assigned to this gig (full names and roles)
- [ ] Accept/Decline/Sub-out buttons visible if status is pending or accepted
- [ ] Mobile-friendly layout

**Expected Result:** Musician has all information needed for the gig.

---

### US-5.3: Accept Gig Assignment
**As a** Musician
**I want to** accept a gig assignment
**So that** the admin knows I'm confirmed

**Acceptance Criteria:**
- [ ] "Accept" button on gig detail page
- [ ] Confirmation prompt before accepting
- [ ] Status changes to "accepted"
- [ ] Timestamp recorded
- [ ] UI updates to show accepted status
- [ ] Can still request sub-out after accepting

**Expected Result:** Assignment status is "accepted" and visible to admin.

---

### US-5.4: Decline Gig Assignment
**As a** Musician
**I want to** decline a gig assignment
**So that** the admin can find a replacement

**Acceptance Criteria:**
- [ ] "Decline" button on gig detail page
- [ ] Optional reason field
- [ ] Confirmation prompt before declining
- [ ] Status changes to "declined"
- [ ] Timestamp recorded
- [ ] Admin receives email notification
- [ ] Gig is removed from musician's upcoming gigs (or shown as declined)

**Expected Result:** Assignment is declined, admin is notified.

---

### US-5.5: Request Sub-Out
**As a** Musician
**I want to** request a sub-out for a gig I previously accepted
**So that** I can be replaced if my availability changes

**Acceptance Criteria:**
- [ ] "Request Sub-Out" button on gig detail page (for accepted assignments)
- [ ] Required: reason for sub-out request
- [ ] Confirmation prompt before submitting
- [ ] Status changes to "sub-out requested"
- [ ] Timestamp and reason recorded
- [ ] Admin receives email notification immediately
- [ ] Gig still shows in musician's list with "sub-out requested" status

**Expected Result:** Sub-out request is submitted, admin is notified to find replacement.

---

### US-5.6: View Past Gigs
**As a** Musician
**I want to** view my past gig assignments
**So that** I can reference historical information

**Acceptance Criteria:**
- [ ] Separate section/tab for past gigs
- [ ] Shows completed gigs with date, venue, role
- [ ] Can view detail page for past gigs
- [ ] Past gigs cannot be modified

**Expected Result:** Musician can access history of past assignments.

---

### US-5.7: View My Profile
**As a** Musician
**I want to** view my profile information
**So that** I can verify my details are correct

**Acceptance Criteria:**
- [ ] Displays: name, email, phone, instruments, region, tags
- [ ] Shows read-only view (admin manages profiles)
- [ ] Link to contact admin if changes needed

**Expected Result:** Musician can see their profile information.

---

## 6. Notifications

### US-6.1: Admin Notification on Decline
**As an** Admin
**I want to** receive an email when a musician declines a gig
**So that** I can find a replacement quickly

**Acceptance Criteria:**
- [ ] Email sent immediately when musician declines
- [ ] Email includes:
  - Gig name and date
  - Musician name
  - Instrument/role they were assigned
  - Reason (if provided)
  - Link to gig detail page in admin panel
- [ ] Email sent to all admin users

**Expected Result:** Admins are immediately notified of declined assignments.

---

### US-6.2: Admin Notification on Sub-Out Request
**As an** Admin
**I want to** receive an email when a musician requests a sub-out
**So that** I can arrange a replacement urgently

**Acceptance Criteria:**
- [ ] Email sent immediately when sub-out requested
- [ ] Email includes:
  - Gig name and date
  - Musician name
  - Instrument/role
  - Sub-out reason
  - Link to gig detail page in admin panel
- [ ] Email sent to all admin users
- [ ] Email subject indicates urgency

**Expected Result:** Admins are immediately notified of sub-out requests.

---

## 7. Admin Tools & Reports

### US-7.1: Print Gig Worksheet
**As an** Admin
**I want to** print a gig worksheet for day-of use
**So that** I have a physical reference at the venue

**Acceptance Criteria:**
- [ ] Print-friendly page layout
- [ ] Includes:
  - Gig name and date
  - Call time, performance time, end time
  - Venue name and full address
  - Client contact information
  - Dress code
  - Notes
  - List of all assigned musicians with roles and phone numbers
- [ ] No navigation or UI elements in print view
- [ ] Fits on standard letter/A4 paper

**Expected Result:** Admin can print a clean worksheet for the gig.

---

### US-7.2: Export Gigs to CSV
**As an** Admin
**I want to** export gig data to CSV
**So that** I can analyze or share information in spreadsheets

**Acceptance Criteria:**
- [ ] Export includes: date, name, venue, region, staffing count
- [ ] Can filter before export (date range, region)
- [ ] CSV downloads immediately
- [ ] Proper formatting for dates and text fields

**Expected Result:** Admin downloads a CSV file of gig data.

---

### US-7.3: Export Assignments to CSV
**As an** Admin
**I want to** export assignment data to CSV
**So that** I can track staffing and payments externally

**Acceptance Criteria:**
- [ ] Export includes: gig date, gig name, musician name, instrument, status, pay amount
- [ ] Can filter by date range
- [ ] Can filter by musician
- [ ] CSV downloads immediately

**Expected Result:** Admin downloads a CSV file of assignment data.

---

### US-7.4: View Audit Log
**As an** Admin
**I want to** view a log of assignment status changes
**So that** I can see when musicians responded

**Acceptance Criteria:**
- [ ] Log shows: timestamp, musician name, gig name, status change, reason (if any)
- [ ] Can filter by date range
- [ ] Can filter by gig
- [ ] Can filter by musician
- [ ] Sorted by most recent first

**Expected Result:** Admin can review history of assignment status changes.

---

### US-7.5: Admin Dashboard Overview
**As an** Admin
**I want to** see a dashboard overview when I log in
**So that** I can quickly assess current status

**Acceptance Criteria:**
- [ ] Shows upcoming gigs count (next 7 days)
- [ ] Shows gigs needing attention (pending responses, sub-out requests)
- [ ] Shows recent activity (declines, sub-outs in last 24 hours)
- [ ] Quick links to common actions
- [ ] Count of total active musicians

**Expected Result:** Admin has immediate visibility into platform status.

---

## 8. System Administration

### US-8.1: Manage Admin Users
**As an** Admin
**I want to** manage other admin user accounts
**So that** I can control who has administrative access

**Acceptance Criteria:**
- [ ] Can create new admin users
- [ ] Can edit admin user details
- [ ] Can deactivate admin users
- [ ] Cannot delete own account
- [ ] At least one admin must remain active

**Expected Result:** Admin user accounts are managed securely.

---

### US-8.2: System Settings
**As an** Admin
**I want to** configure basic system settings
**So that** I can customize the platform for our needs

**Acceptance Criteria:**
- [ ] Can set company name (displayed in emails and portal)
- [ ] Can configure notification email settings
- [ ] Can set default timezone

**Expected Result:** Platform is configured to match business needs.

---

## Appendix: User Story Status

| ID | Story | Priority | Status |
|----|-------|----------|--------|
| US-1.1 | Admin Login | High | Pending |
| US-1.2 | Musician Login | High | Pending |
| US-1.3 | Password Reset | Medium | Pending |
| US-1.4 | Role-Based Access Control | High | Pending |
| US-2.1 | Add Musician to Roster | High | Pending |
| US-2.2 | Edit Musician Profile | High | Pending |
| US-2.3 | View Musician Roster | High | Pending |
| US-2.4 | Deactivate Musician | Medium | Pending |
| US-2.5 | Manage Instruments List | Medium | Pending |
| US-2.6 | Manage Regions List | Medium | Pending |
| US-2.7 | Manage Tags List | Low | Pending |
| US-3.1 | Create Gig | High | Pending |
| US-3.2 | Edit Gig | High | Pending |
| US-3.3 | View Gig List | High | Pending |
| US-3.4 | View Gig Detail (Admin) | High | Pending |
| US-3.5 | Delete/Cancel Gig | Medium | Pending |
| US-3.6 | Duplicate Gig | Low | Pending |
| US-4.1 | Assign Musician to Gig | High | Pending |
| US-4.2 | Remove Assignment | Medium | Pending |
| US-4.3 | View Assignment Status Dashboard | High | Pending |
| US-4.4 | Bulk Assign Musicians | Low | Pending |
| US-4.5 | Reassign After Sub-Out | Medium | Pending |
| US-5.1 | View My Upcoming Gigs | High | Pending |
| US-5.2 | View Gig Detail (Musician) | High | Pending |
| US-5.3 | Accept Gig Assignment | High | Pending |
| US-5.4 | Decline Gig Assignment | High | Pending |
| US-5.5 | Request Sub-Out | High | Pending |
| US-5.6 | View Past Gigs | Low | Pending |
| US-5.7 | View My Profile | Low | Pending |
| US-6.1 | Admin Notification on Decline | High | Pending |
| US-6.2 | Admin Notification on Sub-Out Request | High | Pending |
| US-7.1 | Print Gig Worksheet | Medium | Pending |
| US-7.2 | Export Gigs to CSV | Medium | Pending |
| US-7.3 | Export Assignments to CSV | Medium | Pending |
| US-7.4 | View Audit Log | Low | Pending |
| US-7.5 | Admin Dashboard Overview | Medium | Pending |
| US-8.1 | Manage Admin Users | Low | Pending |
| US-8.2 | System Settings | Low | Pending |
