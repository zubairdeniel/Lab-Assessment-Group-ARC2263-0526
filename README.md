# StudentBase — Dual-Portal Student Management System

A full-stack application with **separate admin and client portals** for comprehensive student record management.

---

## Structure

```
student-management/
├── config.php              ← Shared database configuration
├── schema.sql              ← MySQL database setup
├── README.md               ← This file

├── admin/                  ← Admin management portal
│   ├── index.php           ← Admin dashboard (session-protected)
│   ├── login.html          ← Admin login page
│   ├── login.php           ← Admin login endpoint
│   ├── logout.php          ← Admin logout endpoint
│   ├── auth.php            ← Session authentication helpers
│   ├── api.php             ← Admin CRUD API (full access)
│   ├── app.js              ← Admin JS (add, edit, delete students)
│   └── style.css           ← Admin styling (UPTM blue theme)

└── client/                 ← Student self-service portal
    ├── index.php           ← Student dashboard (session-protected)
    ├── login.html          ← Student login page
    ├── login.php           ← Student login endpoint
    ├── logout.php          ← Student logout endpoint
    ├── auth.php            ← Session authentication helpers
    ├── api.php             ← Student API (view-only profile)
    ├── style.css           ← Student styling (UPTM blue theme)
```

---

## Quick Setup

### 1. Database

```bash
mysql -u root -p < schema.sql
```

Updates `config.php` first if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studentbase');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_pass');
```

### 2. File Deployment

Copy everything into your web root:
```
/var/www/html/studentbase/
```

### 3. Access

| Portal | URL | Demo Login |
|--------|-----|-----------|
| **Admin** | `http://localhost/studentbase/admin/` | `admin` / `Admin@123` |
| **Student** | `http://localhost/studentbase/client/` | `S2024001` / `Student@123` |

---

## Portals Explained

### Admin Portal (`/admin/`)

**Features:**
- Full CRUD: Create, read, update, delete student records
- Search and filter by name, student number, course, status
- Paginated table view (6 records per page)
- Form validation on client and server side
- Delete confirmation modal
- Session-based login (required)

**Admin Users:**
- Default: username `admin`, password `Admin@123`
- Add more admins by inserting into the `admins` table with bcrypt-hashed passwords

---

### Student Portal (`/client/`)

**Features:**
- View-only access to own profile
- Login required (student number + password)
- Read personal info, contact, emergency contact
- View academic details: course, year, GPA, status, intake
- Formatted date display, readable status badges
- Clean, professional dashboard

**Students:**
- Default logins: Any seeded student number + password `Student@123`
  - S2024001, S2024002, S2023003, etc. (all use same demo password)
- Students can ONLY see their own profile, not others'

---

## API Endpoints

### Admin API (`/admin/api.php`)

| Endpoint | Method | Auth Required | Body | Response |
|----------|--------|---------------|------|----------|
| `?action=list` | GET | Yes | — | `{success, total, page, data: [...]}` |
| `?action=create` | POST | Yes | Student object | `{success, id}` |
| `?action=update` | POST | Yes | Student object + id | `{success, affected}` |
| `?action=delete` | POST | Yes | `{id}` | `{success, affected}` |

### Student API (`/client/api.php`)

| Endpoint | Method | Auth Required | Response |
|----------|--------|---------------|----------|
| `?action=profile` | GET | Yes | `{success, data: {current student only}}` |

---

## Database Schema

**Students Table:**
- `id` — auto-increment primary key
- `student_number` — unique, e.g. "S2024001"
- `password` — bcrypt hash (for login)
- `first_name, last_name` — personal info
- `dob, gender, address` — demographics
- `email, phone` — contact
- `emergency_contact, emergency_phone` — emergency
- `course, year_level, intake, gpa, status` — academic
- `created_at, updated_at` — timestamps

**Admins Table:**
- `id` — auto-increment
- `username` — unique login
- `password` — bcrypt hash
- `name` — display name

---

## Security Notes

### For Production:

1. **Update DB credentials** in `config.php` — use environment variables instead of hardcoding
2. **Change demo passwords:**
   - Admin: `UPDATE admins SET password = PASSWORD_HASH('YourSecurePassword', PASSWORD_BCRYPT) WHERE username='admin'`
   - Students: Add individual password setup (don't share password)
3. **Enable HTTPS** on your server
4. **Restrict API access** — add rate limiting
5. **CORS headers** in `api.php` — restrict `Access-Control-Allow-Origin` to your domain
6. **Session timeout** — add auto-logout after inactivity
7. **Input sanitization** — already using prepared statements; good!

---

## Customization

### Add More Courses

Edit `admin/index.php` in the course dropdown:
```html
<select id="course">
  <option>Computer Science</option>
  <option>Your New Course</option>
</select>
```

### Customize Colors

Edit `:root` variables in `admin/style.css` or `client/style.css`:
```css
:root {
  --accent: #1A5BA0;        /* Primary blue */
  --accent-dk: #0F3D6E;     /* Dark blue */
  --gold: #F4C430;          /* Gold accent */
}
```

### Change Page Size

In `admin/api.php`, change `$perPage` default or maximum in the `adminList()` function.

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Not authenticated" error | Clear browser cookies, log in again |
| Database connection error | Check `DB_HOST, DB_USER, DB_PASS` in `config.php` |
| Login fails but credentials are correct | Ensure schema.sql was run completely |
| Admin CRUD not working | Check browser dev tools console for JS errors |
| Student can see other students' data | Verify `client/api.php` checks `$studentId` before returning profile |

---

## Default Test Data

**8 sample students**, all with password `Student@123`:
- S2024001 — Ahmad Razali
- S2024002 — Siti Aminah
- S2023003 — Raj Kumar
- S2022004 — Mei Ling
- S2021005 — Hafiz Osman
- S2023006 — Nurul Huda
- S2020007 — Kevin Lim
- S2024008 — Priya Nair

Remove these before going live via:
```sql
DELETE FROM students WHERE student_number IN ('S2024001', 'S2024002', ...);
```

---

## License & Support

Built for educational purposes. Modify freely for your institution.

For issues or questions, refer to `config.php` and the inline comments in each file.
