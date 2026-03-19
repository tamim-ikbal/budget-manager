## Budget Manager Master Plan (Action/DTO + Git-Tracked Tasks + Yarn Gates)

### Summary
Deliver the full product in strict, sequential tasks with hard quality gates.  
Each task is implemented on its own branch from `dev`, merged manually by you, then next task starts from fresh `dev`.

### Execution Workflow (Required for Every Task)
1. `git checkout dev`
2. `git pull origin dev`
3. `git checkout -b codex/task-XX-slug`
4. Implement only the approved task scope.
5. Run all task gates (tests + quality checks).
6. Commit and share task completion report.
7. Stop and ask you to manually merge into `dev`.
8. Resume next task only after your merge confirmation.

### Architecture Standards (Global)
- Controller -> Action structure for all business flows.
- DTOs are constructor-based classes (no DTO package), stored in `app/DTOs/{Domain}`.
- Actions are invokable classes (`__invoke`) and reusable by future API controllers.
- Form Requests handle validation/authorization input boundaries.
- Resources handle output shaping.
- Inertia pages are wrappers; UI logic lives in `resources/js/components/...`.
- Shadcn components only from generated `resources/js/components/ui`.
- Workspace route binding uses UID: `{workspace:uid}`.

### Locked Product Decisions
- Roles: `SUPER_ADMIN`, `ADMIN`, `USER`.
- Workspace roles: `Owner`, `Member`.
- Registration auto-creates workspace + owner membership in a transaction.
- Default registration currency: active default -> first active; if none active, registration blocked.
- Admin scope for system entities: `ADMIN` + `SUPER_ADMIN`.
- No admin bypass for workspace Owner/Member policies.
- Invite flow supports unregistered users via token + signup bridge.
- Invite expiry: 7 days, with revoke/resend.
- Money fields standardized to `decimal(12,2)` for budget/expense/borrow.
- Borrow meaning: workspace borrowed from member (`workspace_member_id` is lender).
- Delivery surface in this phase: Inertia web.

### Task List (Hard-Gated Order)
1. Admin Currency + Registration Bootstrap  
2. Workspace Core  
3. Workspace Invite  
4. Budget  
5. Category  
6. Expense  
7. Borrow  
8. Admin Users (list + role update + delete)  
9. Cross-Module Integration Regression

### Mandatory Gate for Every Task
- Targeted Pest tests for that task pass.
- `vendor/bin/pint --dirty --format agent` passes.
- `yarn lint:check` passes.
- `yarn format:check` passes.
- `yarn types:check` passes.
- `php artisan wayfinder:generate --with-form --no-interaction` completed.
- If task touches build-critical frontend behavior: `yarn build` passes.

### Task Completion Report Format (Every Task)
- Branch name.
- Scope implemented.
- Files/modules changed (high-level).
- Test and gate command results.
- Explicit request for your manual merge into `dev`.

### Branch Naming Convention
- `codex/task-XX-slug`  
- Example: `codex/task-01-admin-currency-registration`
