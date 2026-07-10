# AGENTS.md
# Chapung Art AI Development Agent Rules

Version: 1.0
Project: Chapung Art
Framework: Laravel 13 + Filament 4 + Blade + Tailwind CSS

---

## Purpose

AGENTS.md is the main document that controls how AI coding agents work in this project.

The AI must read all documentation in `docs/` before analysis, implementation, refactoring, testing, or deployment.

Documents in `docs/` are the Single Source of Truth (SSOT). Do not make assumptions when information is already available in the documentation.

---

## Document Execution Order

Before changing any code, read these documents in order:

1. `docs/PRD.md`
2. `docs/IMPLEMENTATION_PLAN.md`
3. `docs/UI_GUIDELINES.md`
4. `docs/DATABASE_SCHEMA.md`
5. `docs/API_SPECIFICATION.md`
6. `docs/CODING_STANDARDS.md`
7. `docs/DEPLOYMENT.md`
8. `docs/TESTING.md`
9. `docs/ROADMAP.md`
10. Source code

---

## Document Responsibility

### 1. PRD.md

Use PRD to understand:

- Business goal
- Product vision
- User requirements
- Functional requirements
- Non-functional requirements
- Acceptance criteria

Do not create features that conflict with the PRD.

### 2. IMPLEMENTATION_PLAN.md

Use the implementation plan to determine:

- Active phase
- Active task
- Dependency
- Implementation priority
- Phase output

Work on only one phase. Do not skip phases.

### 3. UI_GUIDELINES.md

All UI must follow the guideline, including:

- Layout
- Typography
- Color
- Button
- Form
- Card
- Sidebar
- Dashboard
- Responsive behavior
- Animation
- Accessibility

Do not create designs that conflict with this document.

### 4. DATABASE_SCHEMA.md

All database changes must follow the schema.

Check:

- Table
- Column
- Foreign key
- Index
- Relationship
- Naming convention

Do not invent database structure when the schema already defines it.

### 5. API_SPECIFICATION.md

Use this document when developing:

- REST API
- Controller
- Endpoint
- JSON response
- Validation
- Authentication

Ensure all endpoints follow the specification.

### 6. CODING_STANDARDS.md

All code must follow project standards, including:

- PSR-12
- Laravel convention
- SOLID
- Clean architecture
- DRY
- KISS

Do not introduce unrelated coding style.

### 7. DEPLOYMENT.md

Use this document for:

- Production build
- Hosting
- cPanel
- Composer
- Storage
- Queue
- Cron
- Optimization

Deployment must follow the documented flow.

### 8. TESTING.md

Use this document as the QA checklist.

Required checks include:

- CRUD test
- Validation test
- Upload test
- Search test
- Filter test
- Authorization test
- Responsive test

### 9. ROADMAP.md

Use this document to understand:

- Project version
- Future features
- Long-term priority

Do not implement roadmap features before they enter the active phase.

---

## Implementation Flow

Every task must follow this flow:

1. Read PRD.
2. Read Implementation Plan.
3. Identify active phase.
4. Read UI Guidelines.
5. Read Database Schema.
6. Read API Specification.
7. Read Coding Standards.
8. Audit existing source code.
9. Implement feature.
10. Testing.
11. Verification.
12. Report.

---

## Phase Rule

The AI may work on only:

- One phase
- One module
- One feature
- One logical commit unit

Do not combine multiple phases.

---

## Database Rules

All database changes must use migrations.

Do not:

- Edit old migrations
- Drop existing tables
- Rename existing tables
- Remove columns without a new migration

Use:

- New migration
- Seeder
- Factory when needed

---

## Model Rules

Models must have:

- Fillable
- Casts
- Relationship
- Scope when needed
- Helper method when needed

Use Eloquent relationships.

---

## Filament Rules

All resources must have:

- Navigation
- Form
- Table
- Validation
- Search
- Sort
- Filter
- Bulk action
- Empty state
- Helper text
- Placeholder
- Image preview
- Responsive grid

---

## UI Rules

All UI must be:

- Modern
- Premium
- Clean
- Minimal
- Responsive
- Dark mode friendly
- Accessible
- Fast

Do not create generic UI.

---

## Performance Rule

Use:

- Pagination
- Lazy loading
- Eager loading
- Cache
- Queue
- Optimized query

---

## Security Rule

Do not:

- Commit `.env`
- Commit API keys
- Commit secrets
- Hardcode credentials

Use:

- Policy
- Middleware
- Validation
- Authorization
- Storage

---

## File Upload Rule

Uploads must use:

- Laravel Storage
- Validation
- Unique filename
- Image optimization
- Preview
- Delete old file handling

---

## Testing Rule

Before a phase is completed, run:

```powershell
php artisan migrate
php artisan optimize
php artisan test
```

When using Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan optimize
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```

---

## Verification Checklist

- Migration
- Model
- Relation
- Seeder
- Factory
- CRUD
- Validation
- Upload
- Search
- Filter
- Pagination
- Authorization
- Responsive
- Console error
- PHP error
- JavaScript error
- Broken link

---

## Output Format

Every completed phase must use this report format:

```text
================================================
PHASE COMPLETED
================================================
Phase:
Objective:
Summary:
Files Created:
Files Updated:
Migration:
Seeder:
Models:
Resources:
Routes:
Commands Executed:
Testing Result:
Manual Verification:
Known Issues:
Recommendation:
Next Phase:
================================================
```

---

## When Requirement Is Missing

If a requirement is missing:

1. Search PRD.
2. Search Implementation Plan.
3. Search UI Guidelines.
4. Search Database Schema.
5. Search API Specification.
6. Use Laravel best practice.

Do not make unsupported assumptions.

---

## Prohibited

The AI must not:

- Remove old features
- Remove migrations
- Change project structure without clear reason
- Change design without guideline
- Change routes without reason
- Change models without reason
- Remove relationships
- Change database directly
- Generate code that fails linting
- Add packages without justification

---

## Success Criteria

A task is complete only when:

- It matches PRD.
- It matches Implementation Plan.
- It matches UI Guidelines.
- It matches Database Schema.
- It matches API Specification.
- It matches Coding Standards.
- Testing passes.
- Verification passes.
- Existing features are not broken.
- Output is production ready.

---

## Final Instruction

For every new task, the AI must:

1. Read all `docs/` files in priority order.
2. Determine the active phase from `IMPLEMENTATION_PLAN.md`.
3. Audit related code.
4. Implement only within the active phase scope.
5. Run tests according to `TESTING.md`.
6. Verify the result against `UI_GUIDELINES.md` and `CODING_STANDARDS.md`.
7. Verify before declaring completion.
8. Report using the output format above.

The `docs/` folder is the main project reference. If code and documentation differ, follow documentation first, then propose a safe refactor to realign the code with the documentation.
