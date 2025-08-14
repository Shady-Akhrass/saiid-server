## SAIID Server (Laravel 10 API)

A Laravel 10 REST API for collecting and managing humanitarian data: orphans, aids, students, teachers, shelters, refugees, patients, and employments. Includes authentication via Laravel Sanctum, Excel export/import, basic media delivery, and dashboard aggregation endpoints.

### Tech stack
- **Backend**: Laravel 10 (PHP ^8.1)
- **Auth**: Laravel Sanctum
- **Excel**: maatwebsite/excel
- **HTTP client**: guzzlehttp/guzzle

### Requirements
- PHP ^8.1
- Composer
- MySQL/MariaDB (or any DB supported by Laravel)

### Quick start
1. Clone and install dependencies
```bash
composer install
```
2. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```
- Set DB connection in `.env` (e.g., `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
- Optionally set `APP_URL` (used by Sanctum/CORS contexts).
3. Run migrations
```bash
php artisan migrate
```
4. Serve the API
```bash
php artisan serve
```
The API will be available at `http://127.0.0.1:8000`.

### Authentication
- Register: `POST /api/register` → returns `token`
- Login: `POST /api/login` → returns `user` and `token`
- Logout: `POST /api/logout` (Bearer token required)

Use the token in requests that require auth:
```http
Authorization: Bearer {token}
```

### File storage/serving
- Orphan photos: saved under `public/orphan_photos/`
- Death certificates: saved under `public/death_certificates/`
- Shelter Excel sheets: saved under `public/excel_sheets/`
- Public file serving endpoints:
  - `GET /api/image/{id}` → orphan photo by orphan id
  - `GET /api/death-certificate/{id}` → death certificate by orphan id
  - `GET /api/excel/{id}` → shelter sheet by shelter id

### API endpoints
Auth is required where noted (Bearer token). Unlisted endpoints are public.

#### Authentication
- `POST /api/register` — Create user, returns token
- `POST /api/login` — Login, returns token and user info
- `POST /api/logout` — Invalidate current token (auth)
- `PATCH /api/register/{id}` — Update user fields (auth)
- `GET /api/user/{id}` — Fetch user by id (auth)

#### Orphans
- `POST /api/orphans` — Create orphan with uploads
  - multipart fields include: `orphan_photo` (image, required), `death_certificate` (image, required) plus metadata such as `orphan_id_number`, `orphan_full_name`, `orphan_birth_date`, `orphan_gender`, guardian and mother details, etc. Validation messages are Arabic.
- `POST /api/increment-visitor-orphans-count` — Increment visitor counter (public)
- `GET /api/orphans` — Paginated list with search (auth)
- `GET /api/orphans/dashboard` — Aggregations for charts (auth)
- `GET /api/orphans/export` — Download `orphans.xlsx` (auth)
- `GET /api/image/{id}` — Serve orphan photo (public)
- `GET /api/death-certificate/{id}` — Serve death certificate (public)

#### Aids
- `POST /api/aids` — Create aid record (Arabic validation)
- `POST /api/increment-visitor-aids-count` — Increment visitor counter (public)
- `GET /api/aids` — Paginated list with search (auth)
- `GET /api/aids/dashboard` — Aggregations (auth)
- `GET /api/aids/export` — Download Excel (auth)

#### Students
- `POST /api/students` — Create student
- `POST /api/increment-visitor-students-count` — Increment visitor counter (public)
- `GET /api/students` — Paginated list with search (auth)
- `GET /api/students/dashboard` — Aggregations (auth)
- `GET /api/students/export` — Download Excel (auth)

#### Teachers
- `POST /api/teachers` — Create teacher
- `POST /api/increment-visitor-teachers-count` — Increment visitor counter (public)
- `GET /api/teachers` — Paginated list with search (auth)
- `GET /api/teachers/dashboard` — Aggregations (auth)
- `GET /api/teachers/export` — Download Excel (auth)

#### Employments
- `POST /api/employments` — Create employment
- `GET /api/employments` — Paginated list with search (auth)
- `GET /api/employments/export` — Download Excel (auth)
- `GET /api/epmloyments/dashboard` — Aggregations (auth)

#### Shelters & Refugees
- `POST /api/shelters` — Create shelter with `excel_sheet` (xlsx/xls/csv, saved to `public/excel_sheets/`)
- `GET /api/shelters` — Paginated list with search (auth)
- `POST /api/refugees/import` — Import refugees for a shelter
  - multipart: `file` (xlsx/xls/csv), `manager_id_number` (must exist in shelters)
- `GET /api/refugees/export` — Download refugees Excel (auth)
- `GET /api/excel/{id}` — Serve uploaded shelter sheet (public)

#### Patients
- `POST /api/patients` — Create patient
- `GET /api/patients` — Paginated list with search (auth)
- `GET /api/patients/export` — Download Excel (auth)

#### Visitor counters (public)
- `POST /api/increment-visitor-aids-count`
- `POST /api/increment-visitor-orphans-count`
- `POST /api/increment-visitor-teachers-count`

### Notes
- Validation messages are localized (Arabic) for domain fields.
- Export endpoints return downloadable Excel files.
- Dashboard endpoints return aggregated counts for charting.

### License
MIT
