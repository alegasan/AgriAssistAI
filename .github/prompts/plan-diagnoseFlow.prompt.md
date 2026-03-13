## Plan: Diagnose Flow with Service Layer

Implement a full end-to-end synchronous diagnose flow at /client/diagnose using a thin controller + dedicated service architecture. Keep HTTP concerns (validation, response, redirects) in controller/request classes, and move upload + AI + persistence logic into a service to keep the controller maintainable.

**Steps**
1. Phase 1 - Domain and persistence foundation
2. Add Diagnosis Eloquent model with guarded/fillable, casts for raw_ai_response, and user() relation.
3. Add diagnoses() relation on User model so user-scoped querying is first-class. depends on 2
4. Confirm migration field contract and naming stays as-is (image_path, plant_name, disease_name, confidence_score, symptoms, treatment, raw_ai_response). parallel with 2
5. Phase 2 - Request validation and backend flow
6. Create DiagnoseRequest for POST validation: image required, image mime type/size bounds, extension whitelist (jpeg/jpg/png/webp), optional plant_name string with max length.
7. Add genuine image verification beyond MIME checks (decode + inspect dimensions with Laravel image validation rules and/or getimagesize/Intervention Image) to reduce spoofed payload risk. depends on 6
8. Add DiagnoseService that handles: secure image storage path strategy, AI request payload mapping, AI response normalization, diagnosis record creation, and structured error propagation. depends on 7
9. Add/open AI configuration keys in config/services and env contract for model + API key, then consume from DiagnoseService via Http client or official SDK wrapper used by project. depends on 8
10. Update Client DiagnoseController with store() action using injected service and DiagnoseRequest; keep method thin (call service + return inertia/redirect payload). depends on 8
11. Add POST route for /client/diagnose in existing client middleware group while keeping current GET route. depends on 10
12. Add rate limiting for diagnose submission endpoint (per-user and IP fallback) to mitigate abuse and control external AI API spend. depends on 11
13. Add per-user storage quota checks before persisting uploads (reject with clear validation/business error when limit exceeded). depends on 8
14. Ensure uploaded files are stored on a non-public disk/private path, or if served, only via controlled responses with safe Content-Type and Content-Disposition headers. depends on 8
15. Phase 3 - Frontend upload and result rendering
15. Build Diagnose page form using Inertia useForm with multipart submit, image preview, loading state, validation error rendering, and success transition.
16. Decide response UX for synchronous mode: either render result in same page section or redirect to a result page route if that route exists; implement based on current route map with minimal new surface area. depends on 15
17. Add resilient frontend states: empty, processing, success, and API failure fallback messaging.
18. Phase 4 - Consistency and documentation
18. Align README route mapping with real route prefix (/client/*), specifically Diagnose row and routing map entries. parallel with 15
19. If route helper generation is used in this repo, regenerate route artifacts after route changes.
20. Phase 5 - Test coverage and verification
20. Add Pest feature tests for diagnose submit happy path (valid image), validation failures (missing/invalid image), extension/genuine-image rejection, and authorization/middleware behavior for non-client users.
21. Add feature/service tests for rate limiting behavior and quota enforcement branches.
22. Add service-level test (or feature-level mocked HTTP test) for AI provider success and failure mapping to persisted diagnosis fields.
23. Run targeted tests first, then full relevant suite, and confirm storage path and DB writes are correct.

**Relevant files**
- /routes/web.php - add POST diagnose route inside existing client middleware group.
- /app/Http/Controllers/Client/DiagnoseController.php - keep index() and add thin store().
- /app/Models/User.php - add diagnoses() relation.
- /database/migrations/2026_03_13_105735_create_diagnoses_table.php - reference schema contract for mapping/validation.
- /config/services.php - add AI provider config entries used by service.
- /bootstrap/app.php and/or route middleware config - register throttle policy for diagnose submissions.
- /resources/js/pages/Client/DiagnoseTab/Index.vue - implement upload UI + result states.
- /readme.md - update route docs to match /client prefix.
- New file: /app/Models/Diagnosis.php - diagnosis model and casts/relations.
- New file: /app/Http/Requests/DiagnoseRequest.php - centralized validation.
- New file: /app/Services/DiagnoseService.php - upload + AI + persistence orchestration.
- Potential new file: /app/Services/UploadQuotaService.php (or equivalent policy/helper) - quota guard logic.
- New test file(s): /tests/Feature/Client/DiagnoseTest.php and/or focused service test.

**Verification**
1. Run targeted Pest test file for diagnose flow.
2. Run php artisan test --compact --filter=Diagnose for quick regression check.
3. Manually submit an image via /client/diagnose and verify DB row in diagnoses with non-null image_path and parsed AI fields.
4. Validate stored image exists on configured disk and path format is user-scoped.
5. Confirm non-client/non-auth users are blocked by middleware for POST diagnose endpoint.
6. Confirm README table and routing map now match actual implemented routes.
7. Confirm spoofed/non-genuine images are rejected even if MIME appears valid.
8. Confirm throttling returns expected status after threshold and does not break normal usage.
9. Confirm quota limit blocks additional uploads and surfaces clear user-facing error.
10. Confirm uploaded files are not directly web-accessible unless intentionally served through a controlled endpoint.

**Decisions**
- Route prefix stays /client/diagnose for now.
- Processing mode is synchronous for this phase.
- Scope includes full end-to-end: frontend upload + backend processing + persisted result response.
- Controller remains thin; service owns business logic.

**Further Considerations**
1. Recommendation: design DiagnoseService return DTO/array contract now so queue-based async processing can be added later without changing controller signature.
2. Recommendation: use Http::fake in tests for AI provider calls to avoid external API dependency in CI.
3. Recommendation: optionally add a confidence badge component once core flow is stable (post-MVP polish).
4. Recommendation: log rate-limit/quota rejections for abuse monitoring and cost observability.
