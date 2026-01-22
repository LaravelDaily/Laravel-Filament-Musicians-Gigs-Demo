# Database Schema - Mod Society Gig Management Platform

This document defines the database schema in DBML format for the gig management platform.

## Design Decisions

- **User roles**: Stored as string field with PHP Enum expected (`admin`, `musician`)
- **Status fields**: Stored as string fields with PHP Enums expected (allows future additions without migrations)
- **Lookup tables**: Instruments, Regions, and Tags are separate tables allowing admin management
- **File uploads**: Uses `spatie/laravel-medialibrary` package (media table managed by package)
- **Soft deletes**: Used on core entities to preserve historical data
- **Audit log**: Dedicated table for assignment status changes only (per requirements)

---

## DBML Schema

```dbml
// ===========================================
// USERS TABLE (extends Laravel default)
// ===========================================

Table users {
  id bigint [pk, increment]
  name varchar(255) [not null]
  email varchar(255) [not null, unique]
  email_verified_at timestamp [null]
  password varchar(255) [not null]
  remember_token varchar(100) [null]

  // Role: 'admin', 'musician' (PHP Enum expected)
  role varchar(50) [not null, default: 'musician']

  // Musician-specific fields (null for admins)
  phone varchar(50) [null]
  region_id bigint [null, ref: > regions.id]
  notes text [null]
  is_active boolean [not null, default: true]

  // Fortify 2FA fields (already in schema)
  two_factor_secret text [null]
  two_factor_recovery_codes text [null]
  two_factor_confirmed_at timestamp [null]

  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    role
    is_active
    region_id
  }
}

// ===========================================
// LOOKUP TABLES
// ===========================================

Table regions {
  id bigint [pk, increment]
  name varchar(255) [not null, unique]
  created_at timestamp [null]
  updated_at timestamp [null]
}

Table instruments {
  id bigint [pk, increment]
  name varchar(255) [not null, unique]
  created_at timestamp [null]
  updated_at timestamp [null]
}

Table tags {
  id bigint [pk, increment]
  name varchar(255) [not null, unique]
  created_at timestamp [null]
  updated_at timestamp [null]
}

// ===========================================
// PIVOT TABLES FOR USER RELATIONSHIPS
// ===========================================

Table instrument_user {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  instrument_id bigint [not null, ref: > instruments.id]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (user_id, instrument_id) [unique]
  }
}

Table tag_user {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  tag_id bigint [not null, ref: > tags.id]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (user_id, tag_id) [unique]
  }
}

// ===========================================
// GIGS TABLE
// ===========================================

Table gigs {
  id bigint [pk, increment]
  name varchar(255) [not null]

  // Date and times
  date date [not null]
  call_time time [not null]
  performance_time time [null]
  end_time time [null]

  // Venue information
  venue_name varchar(255) [not null]
  venue_address text [not null]

  // Client contact (optional)
  client_contact_name varchar(255) [null]
  client_contact_phone varchar(50) [null]
  client_contact_email varchar(255) [null]

  // Additional details
  dress_code text [null]
  notes text [null]
  pay_info varchar(255) [null, note: 'Display-only pay information']

  // Relationships
  region_id bigint [null, ref: > regions.id]

  // Status: 'draft', 'active', 'cancelled' (PHP Enum expected)
  status varchar(50) [not null, default: 'draft']

  created_at timestamp [null]
  updated_at timestamp [null]
  deleted_at timestamp [null]

  indexes {
    date
    status
    region_id
    (date, status)
  }
}

// ===========================================
// GIG ASSIGNMENTS TABLE
// ===========================================

Table gig_assignments {
  id bigint [pk, increment]
  gig_id bigint [not null, ref: > gigs.id]
  user_id bigint [not null, ref: > users.id]
  instrument_id bigint [not null, ref: > instruments.id]

  // Status: 'pending', 'accepted', 'declined', 'subout_requested' (PHP Enum expected)
  status varchar(50) [not null, default: 'pending']

  // Assignment-specific details
  pay_amount decimal(10,2) [null, note: 'Display-only pay for this assignment']
  notes text [null, note: 'Admin notes for this specific assignment']

  // Response tracking
  responded_at timestamp [null]
  subout_reason text [null]
  decline_reason text [null]

  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (gig_id, user_id) [unique]
    status
    gig_id
    user_id
  }
}

// ===========================================
// ASSIGNMENT AUDIT LOG
// ===========================================

Table assignment_status_logs {
  id bigint [pk, increment]
  gig_assignment_id bigint [not null, ref: > gig_assignments.id]

  // Status change tracking
  old_status varchar(50) [null]
  new_status varchar(50) [not null]
  reason text [null]

  // Who made the change (null if system/musician action)
  changed_by_user_id bigint [null, ref: > users.id]

  created_at timestamp [null]

  indexes {
    gig_assignment_id
    created_at
  }
}

// ===========================================
// SYSTEM SETTINGS
// ===========================================

Table settings {
  id bigint [pk, increment]
  key varchar(255) [not null, unique]
  value text [null]
  created_at timestamp [null]
  updated_at timestamp [null]
}

// ===========================================
// MEDIA TABLE (spatie/laravel-medialibrary)
// ===========================================
// Note: This table is managed by the spatie/laravel-medialibrary package.
// Run: php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
// The gigs table will use polymorphic relationship for attachments.
// Collection name: 'attachments' for gig PDFs (contracts, maps, set lists)
```

---

## Entity Relationship Diagram

```
                    +-------------+
                    |   regions   |
                    +-------------+
                          |
            +-------------+-------------+
            |                           |
            v                           v
      +-----------+               +-----------+
      |   users   |               |   gigs    |
      +-----------+               +-----------+
            |                           |
      +-----+-----+                     |
      |           |                     |
      v           v                     |
+------------+ +--------+               |
| instrument | | tag    |               |
| _user      | | _user  |               |
+------------+ +--------+               |
      |           |                     |
      v           v                     v
+-------------+ +------+      +------------------+
| instruments | | tags |      | gig_assignments  |
+-------------+ +------+      +------------------+
                                      |
                                      v
                          +------------------------+
                          | assignment_status_logs |
                          +------------------------+
```

---

## Enum Values Reference

### User Role
- `admin` - Platform administrator
- `musician` - Roster musician

### Gig Status
- `draft` - Gig created but not published
- `active` - Gig is active and visible to assigned musicians
- `cancelled` - Gig has been cancelled

### Assignment Status
- `pending` - Awaiting musician response
- `accepted` - Musician accepted the assignment
- `declined` - Musician declined the assignment
- `subout_requested` - Musician requested a sub-out after accepting

---

## Notes

1. **Media Library Integration**: Gig attachments (PDFs) are stored using `spatie/laravel-medialibrary` polymorphic relationship. No separate attachments table needed.

2. **Indexes**: Added for common query patterns:
   - Filtering gigs by date, status, region
   - Filtering users by role, active status
   - Assignment lookups by gig and user

3. **Soft Deletes**: Enabled on `users` and `gigs` to preserve historical assignment data.

4. **Unique Constraints**:
   - One assignment per musician per gig (`gig_assignments.gig_id + user_id`)
   - One instrument per user in pivot (`instrument_user`)
   - One tag per user in pivot (`tag_user`)

5. **Settings Table**: Key-value store for system settings (company name, notification emails, timezone).
