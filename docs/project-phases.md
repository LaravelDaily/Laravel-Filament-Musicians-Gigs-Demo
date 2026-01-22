# Project Phases - Mod Society Gig Management Platform

This document outlines the implementation phases for the Mod Society platform based on user stories and technical requirements. Each task includes specific automated feature tests as acceptance criteria.

**Legend:**
- [x] Completed
- [ ] Not started

---

## Phase 1: Foundation (Database & Core Models)

### Phase 1.1: Enums
Create PHP Enum classes with Filament interfaces (HasLabel, HasColor, HasIcon).

- [ ] Create `UserRole` enum (`Admin`, `Musician`)
- [ ] Create `GigStatus` enum (`Draft`, `Active`, `Cancelled`)
- [ ] Create `AssignmentStatus` enum (`Pending`, `Accepted`, `Declined`, `SuboutRequested`)

**Tests:** `tests/Unit/Enums/EnumTest.php`
```
- it has correct values for UserRole enum
- it has correct values for GigStatus enum
- it has correct values for AssignmentStatus enum
- it returns correct labels for all enums
- it returns correct colors for all enums
```

---

### Phase 1.2: Database Migrations
Create all database tables according to `/docs/database-schema.md`.

- [ ] Add `role`, `phone`, `region_id`, `notes`, `is_active`, `deleted_at` columns to users table
- [ ] Create `regions` table (id, name, timestamps)
- [ ] Create `instruments` table (id, name, timestamps)
- [ ] Create `tags` table (id, name, timestamps)
- [ ] Create `instrument_user` pivot table (user_id, instrument_id, unique constraint)
- [ ] Create `tag_user` pivot table (user_id, tag_id, unique constraint)
- [ ] Create `gigs` table (all fields per schema)
- [ ] Create `gig_assignments` table (all fields per schema)
- [ ] Create `assignment_status_logs` table (audit log)
- [ ] Create `settings` table (key-value store)
- [ ] Install and configure `spatie/laravel-medialibrary` for file attachments

**Tests:** `tests/Feature/Database/MigrationTest.php`
```
- it creates regions table with correct columns
- it creates instruments table with correct columns
- it creates tags table with correct columns
- it creates gigs table with correct columns and indexes
- it creates gig_assignments table with unique constraint on gig_id and user_id
- it creates assignment_status_logs table
- it creates settings table
- it adds musician fields to users table
- it enforces foreign key constraints
```

---

### Phase 1.3: Models & Relationships
Create Eloquent models with proper relationships, casts, and fillable attributes.

- [ ] Create `Region` model with `users()` and `gigs()` relationships
- [ ] Create `Instrument` model with `users()` relationship
- [ ] Create `Tag` model with `users()` relationship
- [ ] Create `Gig` model with relationships, soft deletes, media library trait
- [ ] Create `GigAssignment` model with relationships and status casting
- [ ] Create `AssignmentStatusLog` model
- [ ] Create `Setting` model with helper methods (get/set)
- [ ] Update `User` model: add relationships, role casting, `isAdmin()`, `isMusician()`, soft deletes
- [ ] Add global scope to exclude inactive users from queries where appropriate

**Tests:** `tests/Unit/Models/RegionTest.php`
```
- it has users relationship
- it has gigs relationship
```

**Tests:** `tests/Unit/Models/InstrumentTest.php`
```
- it has users relationship
```

**Tests:** `tests/Unit/Models/TagTest.php`
```
- it has users relationship
```

**Tests:** `tests/Unit/Models/GigTest.php`
```
- it has region relationship
- it has assignments relationship
- it has media relationship for attachments
- it casts status to GigStatus enum
- it uses soft deletes
- it scopes upcoming gigs
- it scopes active gigs
```

**Tests:** `tests/Unit/Models/GigAssignmentTest.php`
```
- it has gig relationship
- it has user relationship
- it has instrument relationship
- it has statusLogs relationship
- it casts status to AssignmentStatus enum
- it has unique constraint on gig_id and user_id
```

**Tests:** `tests/Unit/Models/UserTest.php`
```
- it has instruments relationship
- it has tags relationship
- it has region relationship
- it has assignments relationship
- it has gigs relationship through assignments
- it casts role to UserRole enum
- it returns true for isAdmin when role is admin
- it returns true for isMusician when role is musician
- it uses soft deletes
- it scopes active users
- it scopes musicians only
- it scopes admins only
```

---

### Phase 1.4: Factories & Seeders
Create test factories and database seeders.

- [ ] Create `RegionFactory` with realistic region names
- [ ] Create `InstrumentFactory` with realistic instrument names
- [ ] Create `TagFactory`
- [ ] Create `GigFactory` with all fields and states (draft, active, cancelled, past, future)
- [ ] Create `GigAssignmentFactory` with status states
- [ ] Update `UserFactory`: add musician state, admin state, inactive state, with instruments/tags/region states
- [ ] Create `RegionSeeder` with default regions
- [ ] Create `InstrumentSeeder` with default instruments (drums, bass, guitar, keys, vocals, etc.)
- [ ] Create `DatabaseSeeder` that creates admin user and sample data

**Tests:** `tests/Unit/Factories/FactoryTest.php`
```
- it creates region using factory
- it creates instrument using factory
- it creates tag using factory
- it creates gig using factory with default values
- it creates gig with draft state
- it creates gig with active state
- it creates gig assignment using factory
- it creates user with musician state
- it creates user with admin state
- it creates user with inactive state
- it creates user with instruments
- it creates user with tags
- it creates user with region
```

---

## Phase 2: Authentication & Access Control

### Phase 2.1: Role-Based Routing
Configure routing based on user roles.

- [x] User authentication system (Fortify) - **US-1.1, US-1.2**
- [x] Password reset functionality - **US-1.3**
- [x] Create middleware `EnsureUserIsActive` to block inactive users
- [x] Create middleware `EnsureUserIsMusician` for portal routes
- [x] Configure Filament admin panel to only allow admin users via `FilamentUser` contract
- [x] Create redirect logic: admins → /admin, musicians → /portal after login
- [x] Create musician portal route group at `/portal`

**Tests:** `tests/Feature/Auth/RoleBasedAccessTest.php`
```
- it redirects admin to admin panel after login
- it redirects musician to portal after login
- it prevents musician from accessing admin panel
- it prevents admin from accessing musician portal (optional: or allows)
- it prevents inactive user from logging in
- it prevents unauthenticated user from accessing portal
- it prevents unauthenticated user from accessing admin panel
```

**Tests:** `tests/Feature/Middleware/EnsureUserIsActiveTest.php`
```
- it allows active user to proceed
- it blocks inactive user and redirects to login
- it logs out inactive user
```

---

### Phase 2.2: Authorization Policies
Create Laravel policies for authorization.

- [x] Create `GigPolicy` (viewAny, view, create, update, delete - admin only)
- [x] Create `GigAssignmentPolicy` (respond - own assignment only, view - own or admin)
- [x] Create `UserPolicy` (viewAny, create, update, delete - admin only, cannot delete self)
- [x] Register policies using Laravel auto-discovery

**Tests:** `tests/Feature/Policies/GigPolicyTest.php`
```
- it allows admin to view any gigs
- it allows admin to create gig
- it allows admin to update gig
- it allows admin to delete gig
- it denies musician from creating gig
- it denies musician from updating gig
- it denies musician from deleting gig
```

**Tests:** `tests/Feature/Policies/GigAssignmentPolicyTest.php`
```
- it allows musician to respond to own assignment
- it denies musician from responding to other musician assignment
- it allows admin to respond to any assignment
- it allows musician to view own assignment
- it denies musician from viewing other musician assignment
- it allows admin to view any assignment
```

**Tests:** `tests/Feature/Policies/UserPolicyTest.php`
```
- it allows admin to view any users
- it allows admin to create user
- it allows admin to update user
- it allows admin to delete other user
- it denies admin from deleting self
- it denies musician from creating user
- it denies musician from updating other user
```

---

## Phase 3: Admin Panel - Lookup Tables Management

### Phase 3.1: Instruments Resource (US-2.5)
Filament resource for managing instruments list.

- [x] Create `InstrumentResource` with list, create, edit pages
- [x] Add name field with required validation
- [x] Add delete action with check for usage (prevent if musicians assigned)
- [x] Add bulk delete action

**Tests:** `tests/Feature/Filament/InstrumentResourceTest.php`
```
- it can render instruments list page
- it can render create instrument page
- it can create instrument
- it can render edit instrument page
- it can update instrument
- it can delete instrument without musicians
- it cannot delete instrument with musicians assigned
- it validates name is required
- it validates name is unique
```

---

### Phase 3.2: Regions Resource (US-2.6)
Filament resource for managing regions list.

- [x] Create `RegionResource` with list, create, edit pages
- [x] Add name field with required validation
- [x] Add delete action with check for usage

**Tests:** `tests/Feature/Filament/RegionResourceTest.php`
```
- it can render regions list page
- it can render create region page
- it can create region
- it can render edit region page
- it can update region
- it can delete region without users or gigs
- it cannot delete region with users assigned
- it cannot delete region with gigs assigned
- it validates name is required
- it validates name is unique
```

---

### Phase 3.3: Tags Resource (US-2.7)
Filament resource for managing tags list.

- [x] Create `TagResource` with list, create, edit pages
- [x] Add name field with required validation
- [x] Add delete action with check for usage

**Tests:** `tests/Feature/Filament/TagResourceTest.php`
```
- it can render tags list page
- it can render create tag page
- it can create tag
- it can render edit tag page
- it can update tag
- it can delete tag without musicians
- it cannot delete tag with musicians assigned
- it validates name is required
- it validates name is unique
```

---

## Phase 4: Admin Panel - Musician Roster Management

### Phase 4.1: Musician Resource - List View (US-2.3)
Filament resource for viewing and managing musician roster.

- [x] Create `MusicianResource` (scoped to role=musician users)
- [x] List columns: name, email, phone, instruments (badges), region, tags, is_active
- [x] Add search by name and email
- [x] Add filter by instrument
- [x] Add filter by region
- [x] Add filter by tag
- [x] Add filter by active status
- [x] Add sort by name (default)
- [x] Implement pagination

**Tests:** `tests/Feature/Filament/MusicianResourceTest.php`
```
- it can render musicians list page
- it displays musician data in table
- it can search musicians by name
- it can search musicians by email
- it can filter musicians by instrument
- it can filter musicians by region
- it can filter musicians by tag
- it can filter musicians by active status
- it can sort musicians by name
- it paginates musicians list
- it only shows users with musician role
```

---

### Phase 4.2: Musician Resource - Create (US-2.1)
Form for adding new musicians to roster.

- [x] Create form with fields: name (required), email (required, unique), phone
- [x] Add CheckboxList for instruments (multi-select)
- [x] Add Select for region
- [x] Add CheckboxList for tags (multi-select)
- [x] Add Textarea for notes
- [x] Auto-set role to 'musician' on creation
- [x] Generate random password and send welcome email with password reset link

**Tests:** `tests/Feature/Filament/MusicianResource/CreateMusicianTest.php`
```
- it can render create musician page
- it can create musician with required fields
- it can create musician with all fields including instruments
- it can create musician with tags
- it can create musician with region
- it sets role to musician automatically
- it sets is_active to true by default
- it validates name is required
- it validates email is required
- it validates email is unique
- it validates email format
- it sends welcome email to new musician
```

---

### Phase 4.3: Musician Resource - Edit (US-2.2)
Form for editing musician profiles.

- [x] Edit form with all musician fields
- [x] Validate email uniqueness on update (ignoring current record)
- [x] Allow editing instruments, region, and tags
- [x] Show created_at and last login info (read-only)

**Tests:** `tests/Feature/Filament/MusicianResource/EditMusicianTest.php`
```
- it can render edit musician page
- it can update musician name
- it can update musician email
- it can update musician phone
- it can update musician instruments
- it can update musician region
- it can update musician tags
- it can update musician notes
- it validates email uniqueness on update
- it allows same email when not changed
```

---

### Phase 4.4: Musician Resource - Deactivate/Reactivate (US-2.4)
Actions for managing musician active status.

- [x] Add toggle action for is_active status
- [x] Add bulk deactivate action
- [x] Deactivated musicians excluded from assignment dropdowns (global scope or query filter)
- [x] Show deactivated musicians in list with visual indicator (badge/color)

**Tests:** `tests/Feature/Filament/MusicianResource/DeactivateMusicianTest.php`
```
- it can deactivate active musician
- it can reactivate inactive musician
- it prevents deactivated musician from logging in
- it excludes inactive musicians from assignment dropdowns
- it preserves historical assignments when deactivating
- it can bulk deactivate musicians
```

---

## Phase 5: Admin Panel - Gig Management

### Phase 5.1: Gig Resource - Create (US-3.1)
Filament resource for creating gigs.

- [x] Create `GigResource` with list, create, edit, view pages
- [x] Form section: Basic Info - name (required), date (required), call_time (required)
- [x] Form section: Times - performance_time (optional), end_time (optional)
- [x] Form section: Venue - venue_name (required), venue_address (required)
- [x] Form section: Client Contact - client_contact_name, client_contact_phone, client_contact_email
- [x] Form section: Details - dress_code, notes, pay_info
- [x] Add Select for region
- [x] Add Select for status (draft/active) with draft as default
- [x] Add SpatieMediaLibraryFileUpload for PDF attachments

**Tests:** `tests/Feature/Filament/GigResource/CreateGigTest.php`
```
- it can render create gig page
- it can create gig with required fields only
- it can create gig with all fields
- it can create gig with region
- it can create gig with PDF attachments
- it validates name is required
- it validates date is required
- it validates call_time is required
- it validates venue_name is required
- it validates venue_address is required
- it validates client_contact_email format when provided
- it sets status to draft by default
- it can set status to active on creation
```

---

### Phase 5.2: Gig Resource - List View (US-3.3)
List view with filtering and staffing status.

- [x] List columns: date, name, venue_name, region, status, staffing status (X/Y filled)
- [x] Add staffing status column showing accepted/total assignments
- [x] Add filter by date range (default: upcoming)
- [x] Add filter by region
- [x] Add filter by status (draft, active, cancelled)
- [x] Add filter by staffing: fully staffed, needs musicians, has pending, has sub-outs
- [x] Add search by name and venue_name
- [x] Default sort by date ascending (upcoming first)
- [x] Add tabs or toggle to show past gigs

**Tests:** `tests/Feature/Filament/GigResource/ListGigsTest.php`
```
- it can render gigs list page
- it displays gig data in table
- it shows staffing status count
- it can search gigs by name
- it can search gigs by venue
- it can filter gigs by date range
- it can filter gigs by region
- it can filter gigs by status
- it can filter gigs needing musicians
- it can filter gigs with pending responses
- it can filter gigs with sub-out requests
- it sorts gigs by date ascending by default
- it can view past gigs
```

---

### Phase 5.3: Gig Resource - Edit (US-3.2)
Edit form for existing gigs.

- [x] Edit form with all gig fields
- [x] Allow adding/removing PDF attachments
- [x] Show attachment list with download links

**Tests:** `tests/Feature/Filament/GigResource/EditGigTest.php`
```
- it can render edit gig page
- it can update gig details
- it can add attachments to gig
- it can remove attachments from gig
- it can change gig status
- it can change gig region
```

---

### Phase 5.4: Gig Resource - View Detail (US-3.4)
View page showing complete gig details and assignments.

- [x] Display all gig information in organized sections
- [x] Show assignments table: musician name, instrument, status (with color), responded_at
- [x] Show sub-out requests prominently with reason
- [x] Add action buttons: add assignment, edit gig, print worksheet
- [x] Link musician names to their profiles

**Tests:** `tests/Feature/Filament/GigResource/ViewGigTest.php`
```
- it can render view gig page
- it displays all gig information
- it displays assignments list
- it shows assignment status with visual indicator
- it highlights sub-out requests
- it shows sub-out reason
- it shows response timestamps
```

---

### Phase 5.5: Gig Resource - Cancel/Delete (US-3.5)
Actions for cancelling or deleting gigs.

- [x] Add cancel action (sets status to cancelled, requires confirmation)
- [x] Add delete action (soft delete, requires confirmation)
- [x] Cancelled gigs remain visible in list with indicator
- [x] Deleting gig soft-deletes associated assignments

**Tests:** `tests/Feature/Filament/GigResource/CancelDeleteGigTest.php`
```
- it can cancel gig
- it shows cancelled gig in list with indicator
- it can soft delete gig
- it soft deletes associated assignments when gig deleted
- it requires confirmation before cancel
- it requires confirmation before delete
- it can restore soft deleted gig
```

---

### Phase 5.6: Gig Resource - Duplicate (US-3.6)
Action for duplicating existing gigs.

- [x] Add replicate action on gig
- [x] Copy all fields, status set to draft (date kept for user to update)
- [x] Do NOT copy assignments
- [x] Copy attachments
- [x] Redirect to edit page for new gig

**Tests:** `tests/Feature/Filament/GigResource/DuplicateGigTest.php`
```
- it can duplicate gig
- it clears date on duplicated gig
- it sets status to draft on duplicated gig
- it copies all other fields
- it does not copy assignments
- it copies attachments
- it redirects to edit page after duplication
```

---

## Phase 6: Admin Panel - Gig Staffing

### Phase 6.1: Assignment Relation Manager (US-4.1)
Relation manager on GigResource for managing assignments.

- [x] Create `AssignmentsRelationManager` on GigResource
- [x] Add assignment form: musician (select, filtered to active musicians), instrument (select), notes, pay_amount
- [x] Exclude already-assigned musicians from dropdown
- [x] Set status to 'pending' automatically
- [x] Show status with color badge in table
- [x] Show response timestamp

**Tests:** `tests/Feature/Filament/GigResource/AssignmentsRelationManagerTest.php`
```
- it can render assignments relation manager
- it can create assignment
- it sets assignment status to pending by default
- it can assign instrument to assignment
- it can add notes to assignment
- it can add pay amount to assignment
- it prevents duplicate assignment of same musician
- it excludes already assigned musicians from dropdown
- it excludes inactive musicians from dropdown
- it displays assignment status with color
```

---

### Phase 6.2: Remove Assignment (US-4.2)
Action to remove assignments from gigs.

- [x] Add delete action on assignment relation manager
- [x] Require confirmation before removal
- [x] Log removal in audit log

**Tests:** `tests/Feature/Filament/GigResource/RemoveAssignmentTest.php`
```
- it can remove assignment from gig
- it requires confirmation before removal
- it logs removal in assignment status log
- it removes assignment regardless of status
```

---

### Phase 6.3: Inline Status Update (US-4.3)
Visual indicators and quick status updates.

- [x] Color-coded status badges (pending=yellow, accepted=green, declined=red, subout=orange)
- [x] Show counts summary: X pending, Y accepted, Z declined, W sub-out
- [x] Allow admin to manually change assignment status
- [x] Log manual status changes in audit log

**Tests:** `tests/Feature/Filament/GigResource/AssignmentStatusTest.php`
```
- it displays correct status colors
- it shows status counts summary
- it can manually change assignment status
- it logs manual status change in audit log
```

---

### Phase 6.4: Bulk Assign Musicians (US-4.4)
Action for assigning multiple musicians at once.

- [x] Create bulk assign action on GigResource view page
- [x] Modal with: multi-select musicians, instrument for each (or shared instrument)
- [x] Create all assignments with 'pending' status
- [x] Validate no duplicates

**Tests:** `tests/Feature/Filament/GigResource/BulkAssignTest.php`
```
- it can bulk assign musicians to gig
- it creates all assignments with pending status
- it prevents duplicate assignments in bulk
- it excludes already assigned musicians
- it excludes inactive musicians
```

---

### Phase 6.5: Reassign After Sub-Out (US-4.5)
Workflow for handling sub-out requests.

- [x] Show sub-out badge on assignment with reason tooltip
- [x] Add "Find Replacement" action on sub-out assignment
- [x] Open modal showing available musicians filtered by same instrument
- [x] Show warning for musicians with conflicting gigs on same date
- [x] Can create new assignment as replacement

**Tests:** `tests/Feature/Filament/GigResource/ReassignSuboutTest.php`
```
- it shows sub-out assignments prominently
- it can find replacement for sub-out
- it filters musicians by sub-out instrument
- it warns about musicians with conflicting gigs
- it can create replacement assignment
```

---

## Phase 7: Musician Portal

### Phase 7.1: Portal Layout & Navigation
Create the musician portal structure using controllers and Blade views.

- [x] Create `PortalController` base controller
- [x] Create portal layout blade with mobile-friendly navigation
- [x] Add navigation: Dashboard, Past Gigs, My Profile
- [x] Create portal-specific CSS/styling (or use Flux components)
- [x] Add logout functionality

**Tests:** `tests/Feature/Portal/LayoutTest.php`
```
- it shows portal navigation when authenticated as musician
- it redirects to login when not authenticated
- it blocks admin from accessing portal (or allows - define behavior)
- it can logout from portal
```

---

### Phase 7.2: Dashboard - Upcoming Gigs (US-5.1)
Main dashboard showing musician's upcoming assignments.

- [x] Create `PortalDashboardController@index` returning view
- [x] Query upcoming gig assignments for authenticated musician
- [x] Display: date, day of week, call time, venue name, gig name
- [x] Show assignment status (pending, accepted, subout_requested)
- [x] Visual indicator for gigs needing response (pending status)
- [x] Sort by date ascending (soonest first)
- [x] Mobile-responsive card layout
- [x] Link each gig to detail page

**Tests:** `tests/Feature/Portal/DashboardTest.php`
```
- it shows upcoming gigs for authenticated musician
- it only shows gigs assigned to current musician
- it sorts gigs by date ascending
- it shows gig date and call time
- it shows venue name
- it shows assignment status
- it highlights pending assignments
- it does not show past gigs on dashboard
- it does not show cancelled gigs
- it links to gig detail page
```

---

### Phase 7.3: Gig Detail View (US-5.2)
Detailed view of a specific gig assignment.

- [x] Create `PortalGigController@show` with gig route model binding
- [x] Authorize: musician can only view own assignments
- [x] Display: gig name, date, day of week
- [x] Display: call time, performance time (if set), end time (if set)
- [x] Display: venue name, venue address with Google Maps link
- [x] Display: dress code, notes/instructions
- [x] Display: musician's assigned instrument, pay amount (if set)
- [x] List attached PDFs with download links
- [x] Show other assigned musicians (names and instruments only)
- [x] Show Accept/Decline buttons (if status is pending)
- [x] Show Sub-out button (if status is accepted)

**Tests:** `tests/Feature/Portal/GigDetailTest.php`
```
- it shows gig detail page for assigned musician
- it denies access to gig not assigned to musician
- it displays all gig information
- it displays call time and optional times
- it displays venue with map link
- it displays dress code and notes
- it displays musician assignment details
- it lists downloadable attachments
- it shows other assigned musicians
- it shows accept and decline buttons for pending assignment
- it shows sub-out button for accepted assignment
- it hides action buttons for declined assignment
```

---

### Phase 7.4: Accept Assignment (US-5.3)
Action for musician to accept a gig assignment.

- [x] Create `PortalGigController@accept` POST route
- [x] Validate assignment is in 'pending' status
- [x] Update status to 'accepted'
- [x] Set responded_at timestamp
- [x] Create audit log entry
- [x] Redirect back with success message

**Tests:** `tests/Feature/Portal/AcceptAssignmentTest.php`
```
- it can accept pending assignment
- it cannot accept already accepted assignment
- it cannot accept declined assignment
- it cannot accept sub-out requested assignment
- it sets status to accepted
- it sets responded_at timestamp
- it creates audit log entry
- it redirects with success message
- it cannot accept assignment for other musician
```

---

### Phase 7.5: Decline Assignment (US-5.4)
Action for musician to decline a gig assignment.

- [x] Create `PortalGigController@decline` POST route with optional reason
- [x] Validate assignment is in 'pending' or 'accepted' status
- [x] Update status to 'declined'
- [x] Store decline_reason
- [x] Set responded_at timestamp
- [x] Create audit log entry
- [x] Send notification to all admins (implemented in Phase 8)
- [x] Redirect back with success message

**Tests:** `tests/Feature/Portal/DeclineAssignmentTest.php`
```
- it can decline pending assignment
- it can decline accepted assignment (edge case - same as sub-out?)
- it cannot decline already declined assignment
- it sets status to declined
- it stores decline reason when provided
- it sets responded_at timestamp
- it creates audit log entry
- it sends notification to admins
- it redirects with success message
- it cannot decline assignment for other musician
```

---

### Phase 7.6: Request Sub-Out (US-5.5)
Action for musician to request a sub-out after accepting.

- [x] Create `PortalGigController@subout` POST route with required reason
- [x] Validate assignment is in 'accepted' status
- [x] Update status to 'subout_requested'
- [x] Store subout_reason (required)
- [x] Set responded_at timestamp
- [x] Create audit log entry
- [x] Send urgent notification to all admins (implemented in Phase 8)
- [x] Redirect back with success message

**Tests:** `tests/Feature/Portal/SuboutRequestTest.php`
```
- it can request sub-out for accepted assignment
- it cannot request sub-out for pending assignment
- it cannot request sub-out for declined assignment
- it requires reason for sub-out
- it validates reason is not empty
- it sets status to subout_requested
- it stores subout reason
- it sets responded_at timestamp
- it creates audit log entry
- it sends urgent notification to admins
- it redirects with success message
- it cannot request sub-out for other musician assignment
```

---

### Phase 7.7: Past Gigs (US-5.6)
View for musician to see their past assignments.

- [x] Create `PortalGigController@past` returning view
- [x] Query past gig assignments (gig date < today)
- [x] Display: date, venue, gig name, instrument/role
- [x] Show final status (accepted, declined, etc.)
- [x] Link to read-only detail view
- [x] Paginate results

**Tests:** `tests/Feature/Portal/PastGigsTest.php`
```
- it shows past gigs for authenticated musician
- it only shows gigs with date in the past
- it sorts by date descending (most recent first)
- it shows gig details
- it shows final assignment status
- it links to detail view
- it paginates results
- it does not show action buttons on past gig detail
```

---

### Phase 7.8: My Profile (US-5.7)
Read-only profile view for musician.

- [x] Create `PortalProfileController@show` returning view
- [x] Display: name, email, phone
- [x] Display: instruments (list)
- [x] Display: region
- [x] Display: tags (list)
- [x] Show "Contact admin to update" message
- [x] No edit functionality (admin manages)

**Tests:** `tests/Feature/Portal/ProfileTest.php`
```
- it shows profile page for authenticated musician
- it displays musician name
- it displays musician email
- it displays musician phone
- it displays musician instruments
- it displays musician region
- it displays musician tags
- it shows contact admin message
- it does not allow editing profile
```

---

## Phase 8: Notifications

### Phase 8.1: Decline Notification (US-6.1)
Email notification when musician declines assignment.

- [x] Create `GigAssignmentDeclined` notification class
- [x] Email content: gig name, gig date, musician name, instrument, decline reason (if provided)
- [x] Include link to gig in admin panel
- [x] Queue the notification
- [x] Send to all users with admin role

**Tests:** `tests/Feature/Notifications/DeclineNotificationTest.php`
```
- it sends notification when assignment declined
- it sends to all admin users
- it includes gig name in notification
- it includes gig date in notification
- it includes musician name in notification
- it includes instrument in notification
- it includes decline reason when provided
- it includes link to admin panel
- it queues the notification
```

---

### Phase 8.2: Sub-Out Notification (US-6.2)
Urgent email notification when musician requests sub-out.

- [x] Create `SubOutRequested` notification class
- [x] Urgent subject line (e.g., "URGENT: Sub-Out Request for [Gig Name]")
- [x] Email content: gig name, gig date, musician name, instrument, sub-out reason
- [x] Include link to gig in admin panel
- [x] Queue the notification
- [x] Send to all users with admin role

**Tests:** `tests/Feature/Notifications/SuboutNotificationTest.php`
```
- it sends notification when sub-out requested
- it sends to all admin users
- it has urgent subject line
- it includes gig name in notification
- it includes gig date in notification
- it includes musician name in notification
- it includes instrument in notification
- it includes sub-out reason
- it includes link to admin panel
- it queues the notification
```

---

## Phase 9: Admin Tools & Reports

### Phase 9.1: Print Gig Worksheet (US-7.1)
Print-friendly worksheet for day-of gig use.

- [x] Create `GigWorksheetController@show` route
- [x] Print-friendly Blade template (no navigation, minimal styling)
- [x] Include: gig name, date, day of week
- [x] Include: call time, performance time, end time
- [x] Include: venue name and full address
- [x] Include: client contact info (name, phone, email)
- [x] Include: dress code, notes
- [x] Include: table of musicians with name, instrument, phone number
- [x] Optimize for letter/A4 paper

**Tests:** `tests/Feature/Admin/GigWorksheetTest.php`
```
- it renders worksheet for gig
- it includes all gig information
- it includes all assigned musicians
- it includes musician phone numbers
- it includes musician instruments
- it has print-friendly layout
- it requires admin authentication
```

---

### Phase 9.2: Export Gigs CSV (US-7.2)
CSV export of gig data.

- [ ] Add export action on GigResource
- [ ] Use Filament's built-in export or custom action
- [ ] Include columns: date, name, venue_name, venue_address, region, status, staffing count
- [ ] Apply current filters before export
- [ ] Proper date formatting

**Tests:** `tests/Feature/Admin/ExportGigsTest.php`
```
- it exports gigs to CSV
- it includes all required columns
- it applies date filters
- it applies region filters
- it applies status filters
- it formats dates correctly
- it requires admin authentication
```

---

### Phase 9.3: Export Assignments CSV (US-7.3)
CSV export of assignment data.

- [ ] Create export action or page for assignments
- [ ] Include columns: gig date, gig name, musician name, musician email, instrument, status, pay_amount
- [ ] Filter by date range
- [ ] Filter by musician

**Tests:** `tests/Feature/Admin/ExportAssignmentsTest.php`
```
- it exports assignments to CSV
- it includes all required columns
- it can filter by date range
- it can filter by musician
- it formats data correctly
- it requires admin authentication
```

---

### Phase 9.4: Audit Log View (US-7.4)
View assignment status change history.

- [ ] Create `AuditLogResource` or custom Filament page
- [ ] Display: timestamp, gig name, musician name, old status, new status, reason, changed by
- [ ] Filter by date range
- [ ] Filter by gig
- [ ] Filter by musician
- [ ] Sort by timestamp descending (default)

**Tests:** `tests/Feature/Filament/AuditLogTest.php`
```
- it can render audit log page
- it displays status changes
- it shows timestamps
- it shows gig name
- it shows musician name
- it shows status transition
- it shows reason when provided
- it can filter by date range
- it can filter by gig
- it can filter by musician
- it sorts by timestamp descending
```

---

### Phase 9.5: Admin Dashboard Widgets (US-7.5)
Dashboard overview widgets for Filament.

- [ ] Create `UpcomingGigsWidget` - count of gigs in next 7 days
- [ ] Create `NeedsAttentionWidget` - gigs with pending/sub-out requests
- [ ] Create `RecentActivityWidget` - declines/sub-outs in last 24 hours
- [ ] Create `ActiveMusiciansWidget` - total count of active musicians
- [ ] Register widgets on Filament dashboard

**Tests:** `tests/Feature/Filament/DashboardWidgetsTest.php`
```
- it shows upcoming gigs count widget
- it shows correct count for next 7 days
- it shows needs attention widget
- it counts gigs with pending responses
- it counts gigs with sub-out requests
- it shows recent activity widget
- it shows declines from last 24 hours
- it shows sub-outs from last 24 hours
- it shows active musicians count widget
```

---

## Phase 10: System Administration

### Phase 10.1: Admin Users Management (US-8.1)
Manage admin user accounts.

- [ ] Create `AdminUserResource` or scope within UserResource
- [ ] List/Create/Edit admin users only
- [ ] Cannot delete own account (validation)
- [ ] Ensure at least one admin remains active
- [ ] Deactivate action (set is_active = false)

**Tests:** `tests/Feature/Filament/AdminUserResourceTest.php`
```
- it can list admin users
- it can create admin user
- it can edit admin user
- it can deactivate admin user
- it cannot delete own account
- it ensures at least one admin remains active
- it only shows users with admin role
```

---

### Phase 10.2: System Settings (US-8.2)
Configurable system settings.

- [ ] Create Settings Filament page
- [ ] Setting: company_name (used in emails and portal header)
- [ ] Setting: notification_email (CC on notifications, optional)
- [ ] Setting: timezone (default timezone for display)
- [ ] Use Setting model with get/set helper methods

**Tests:** `tests/Feature/Filament/SettingsPageTest.php`
```
- it can render settings page
- it can update company name
- it can update notification email
- it can update timezone
- it displays current settings values
- it validates email format for notification email
- it uses settings in email templates
- it uses company name in portal header
```

---

## Phase 11: Final Testing & Polish

### Phase 11.1: End-to-End Integration Tests
Full workflow tests spanning multiple components.

- [ ] Test: Admin creates gig → assigns musician → musician receives email
- [ ] Test: Musician logs in → sees gig → accepts → admin sees accepted status
- [ ] Test: Musician requests sub-out → admin notified → admin reassigns
- [ ] Test: Admin deactivates musician → musician cannot login

**Tests:** `tests/Feature/Integration/FullWorkflowTest.php`
```
- it completes full gig creation and assignment workflow
- it completes musician accept workflow
- it completes musician decline workflow with notification
- it completes sub-out request workflow with notification
- it enforces deactivated musician cannot login
- it enforces role-based access throughout workflow
```

---

### Phase 11.2: Mobile & Browser Testing
Responsive design and cross-browser validation.

- [ ] Test musician portal on mobile viewport
- [ ] Test all forms work on touch devices
- [ ] Test PDF downloads work on mobile
- [ ] Verify no horizontal scroll on mobile

**Tests:** `tests/Browser/MobilePortalTest.php` (Pest browser tests)
```
- it displays portal correctly on mobile viewport
- it can navigate portal on mobile
- it can accept gig on mobile
- it can decline gig on mobile
- it can request sub-out on mobile
- it can download attachments on mobile
```

---

### Phase 11.3: Security Verification
Security audit tests.

- [ ] Verify all policies enforced server-side
- [ ] Test direct URL access to unauthorized resources
- [ ] Verify file upload restrictions (PDFs only, size limits)
- [ ] Test CSRF protection on all forms

**Tests:** `tests/Feature/Security/AuthorizationTest.php`
```
- it blocks musician from admin routes via direct URL
- it blocks musician from other musician data via direct URL
- it blocks unauthenticated access to all protected routes
- it enforces CSRF on all POST routes
- it restricts file uploads to allowed types
- it enforces file size limits
```

---

## Implementation Priority Summary

| Priority | Phase | Description | Estimated Tests |
|----------|-------|-------------|-----------------|
| Critical | 1 | Foundation (Database, Models, Enums) | ~50 |
| Critical | 2 | Authentication & Access Control | ~25 |
| High | 3 | Lookup Tables Management | ~30 |
| High | 4 | Musician Roster Management | ~45 |
| High | 5 | Gig Management | ~60 |
| High | 6 | Gig Staffing | ~35 |
| High | 7 | Musician Portal | ~65 |
| High | 8 | Notifications | ~20 |
| Medium | 9 | Admin Tools & Reports | ~35 |
| Medium | 10 | System Administration | ~20 |
| Low | 11 | Final Testing & Polish | ~20 |

**Total Estimated Tests: ~405**

---

## User Story to Phase Mapping

| User Story | Description | Phase |
|------------|-------------|-------|
| US-1.1 | Admin Login | 2.1 |
| US-1.2 | Musician Login | 2.1 |
| US-1.3 | Password Reset | 2.1 (completed) |
| US-1.4 | Role-Based Access Control | 2.1, 2.2 |
| US-2.1 | Add Musician to Roster | 4.2 |
| US-2.2 | Edit Musician Profile | 4.3 |
| US-2.3 | View Musician Roster | 4.1 |
| US-2.4 | Deactivate Musician | 4.4 |
| US-2.5 | Manage Instruments List | 3.1 |
| US-2.6 | Manage Regions List | 3.2 |
| US-2.7 | Manage Tags List | 3.3 |
| US-3.1 | Create Gig | 5.1 |
| US-3.2 | Edit Gig | 5.3 |
| US-3.3 | View Gig List | 5.2 |
| US-3.4 | View Gig Detail (Admin) | 5.4 |
| US-3.5 | Delete/Cancel Gig | 5.5 |
| US-3.6 | Duplicate Gig | 5.6 |
| US-4.1 | Assign Musician to Gig | 6.1 |
| US-4.2 | Remove Assignment | 6.2 |
| US-4.3 | View Assignment Status Dashboard | 6.3 |
| US-4.4 | Bulk Assign Musicians | 6.4 |
| US-4.5 | Reassign After Sub-Out | 6.5 |
| US-5.1 | View My Upcoming Gigs | 7.2 |
| US-5.2 | View Gig Detail (Musician) | 7.3 |
| US-5.3 | Accept Gig Assignment | 7.4 |
| US-5.4 | Decline Gig Assignment | 7.5 |
| US-5.5 | Request Sub-Out | 7.6 |
| US-5.6 | View Past Gigs | 7.7 |
| US-5.7 | View My Profile | 7.8 |
| US-6.1 | Admin Notification on Decline | 8.1 |
| US-6.2 | Admin Notification on Sub-Out Request | 8.2 |
| US-7.1 | Print Gig Worksheet | 9.1 |
| US-7.2 | Export Gigs to CSV | 9.2 |
| US-7.3 | Export Assignments to CSV | 9.3 |
| US-7.4 | View Audit Log | 9.4 |
| US-7.5 | Admin Dashboard Overview | 9.5 |
| US-8.1 | Manage Admin Users | 10.1 |
| US-8.2 | System Settings | 10.2 |

---

## Technical Notes

- **Musician Portal**: Implemented as Laravel Controllers with Blade views (not Livewire) unless dynamic behavior needed
- **Admin Panel**: Filament v5 resources with comprehensive smoke tests
- **File Attachments**: Using `spatie/laravel-medialibrary` for PDF uploads on Gig model
- **Notifications**: Laravel notification system with email channel, queued
- **Testing**: Pest v4 for all tests, browser tests for mobile verification
- **Test Naming**: Tests use `it_does_something` style per Pest conventions

---

## Current Progress

**Completed:**
- [x] Laravel 12 framework installed
- [x] Filament v5 admin panel installed
- [x] Livewire v4 and Flux UI v2 installed
- [x] Fortify authentication (login, registration, password reset, 2FA)
- [x] Basic settings pages (profile, password, appearance)
- [x] Test infrastructure (Pest v4)
- [x] Existing auth tests (authentication, registration, password reset, email verification, 2FA)
- [x] Phase 1: Foundation (Database & Core Models) - All enums, migrations, models, factories
- [x] Phase 2: Authentication & Access Control - Middleware, role-based routing, policies
- [x] Phase 3: Admin Panel - Lookup Tables Management - Instruments, Regions, Tags resources
- [x] Phase 4: Admin Panel - Musician Roster Management - MusicianResource with list/create/edit, deactivate/reactivate
- [x] Phase 5: Admin Panel - Gig Management - GigResource with list/create/edit/view, cancel/delete, duplicate
- [x] Phase 6: Admin Panel - Gig Staffing - AssignmentsRelationManager with create/edit/delete, status updates, bulk assign, find replacement

**Completed:**
- [x] Phase 7: Musician Portal
  - [x] Phase 7.1: Portal Layout & Navigation
  - [x] Phase 7.2: Dashboard - Upcoming Gigs
  - [x] Phase 7.3: Gig Detail View
  - [x] Phase 7.4: Accept Assignment
  - [x] Phase 7.5: Decline Assignment
  - [x] Phase 7.6: Request Sub-Out
  - [x] Phase 7.7: Past Gigs
  - [x] Phase 7.8: My Profile
- [x] Phase 8: Notifications
  - [x] Phase 8.1: Decline Notification
  - [x] Phase 8.2: Sub-Out Notification

**In Progress:**
- [ ] Phase 9: Admin Tools & Reports
  - [x] Phase 9.1: Print Gig Worksheet
  - [ ] Phase 9.2: Export Gigs CSV
  - [ ] Phase 9.3: Export Assignments CSV
  - [ ] Phase 9.4: Audit Log View
  - [ ] Phase 9.5: Admin Dashboard Widgets

**Not Started:**
- [ ] Phase 10: System Administration
- [ ] Phase 11: Final Testing & Polish
