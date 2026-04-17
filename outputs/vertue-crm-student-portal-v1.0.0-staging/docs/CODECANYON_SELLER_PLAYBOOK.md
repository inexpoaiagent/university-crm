# CodeCanyon Seller Playbook

This is the practical launch plan for publishing Vertue CRM successfully.

## Phase 1 - Product Readiness
1. Run the full QA checklist in `docs/CODECANYON_RELEASE_PACKAGE.md`.
2. Confirm fresh install works on:
   - local (XAMPP/Laragon)
   - cPanel shared hosting
3. Verify demo credentials work exactly as documented.

## Phase 2 - Packaging
1. Keep only deployable files in release ZIP:
   - include source, docs, SQL
   - exclude `.git`, local caches, screenshots not used by listing
2. Ensure `storage/logs` has no sensitive data.
3. Ensure `.env` is not included. Include `.env.example` only.

## Phase 3 - Listing Assets
Prepare these before upload:
- Thumbnail 80x80
- Main preview image
- 5-10 product screenshots (CRM dashboard, students, applications, portal, documents)
- Optional short preview video (60-120 seconds)
- Sales copy from `docs/CODECANYON_ITEM_DESCRIPTION.md`

## Phase 4 - Submission
1. Create/verify Envato Author account.
2. Go to CodeCanyon upload page.
3. Select proper category and subcategory.
4. Paste prepared title/description/features.
5. Upload ZIP + preview assets.
6. Submit for review.

## Phase 5 - Review Handling
If reviewer requests changes:
1. Reply politely with exact fix notes.
2. Patch only requested items plus related stability fixes.
3. Re-test login, install flow, and major CRUD modules.
4. Re-upload updated ZIP and changelog.

## Phase 6 - Post-Approval Growth
1. Publish docs online (public URL).
2. Respond to buyer questions fast in first 2 weeks.
3. Ship small stability updates quickly (v1.0.1, v1.0.2).
4. Add release notes on each update.

## Recommended Commercial Setup
- Price: **$59** regular license
- Initial support response target: <24 hours business days
- Update cadence: at least monthly
- Refund risk reduction: clear install docs + clear support boundaries

## Suggested First 3 Milestones
1. v1.0.1 - Stability and UX refinements from first buyer feedback.
2. v1.1.0 - Expanded reporting and analytics cards.
3. v1.2.0 - Optional API token and webhook integrations.
