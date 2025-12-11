=== AP Library ===
Contributors: Antonin Puleo
Donate link: https://antoninpuleo.com/
Tags: photography, media, uploads, custom post type, taxonomy, exif, gallery, archive, dates, keywords
Requires at least: 6.5
Tested up to: 6.8.3
Requires PHP: 7.4
Stable tag: 1.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Photo Library system for WordPress built around a custom post type for photos, date metadata, hierarchical taken date archives, EXIF extraction, keyword extraction, and admin tools.

== Description ==

AP Library provides a robust foundation to manage a photography library as first-class posts. It defines:

- Custom Post Type: `aplb_photo` for photos
- Meta fields: `aplb_published_date`, `aplb_taken_date` (ISO 8601: `YYYY-MM-DD`)
- Taxonomies:
    - `aplb_published_date` (flat) for published date groupings
    - `aplb_taken_date` (hierarchical) for taken date — Year → Month → Day — enabling clean archives like `/photo-taken/2023/november/15/`
    - `aplb_location` (hierarchical) for location — Country → State/Province → City — extracted from photo IPTC metadata
    - `aplb_genre` (hierarchical) for your own logical groupings (optional)
    - `aplb_keyword` (flat) for IPTC/EXIF photo keywords automatically extracted from featured images
- EXIF integration to extract taken dates, locations, and IPTC keywords from featured images (creates matching taxonomy terms automatically)
- Admin UI enhancements (meta box, quick edit integration, sortable columns)
- Backfill tools to sync existing content
- Query customization so archives order by `aplb_published_date` by default

== Archive Rules ==

Since 1.2.x you can configure how taxonomy / post type / author / date archives build their main query instead of relying on hard‑coded logic.

Navigate to: Photos → Archive Rules.

For each archive context you can:
* Enable or disable the rule (when disabled, WordPress default behavior applies)
* Set Post Types included (currently only `aplb_photo`)
* Choose Order By: Meta Value, Post Date, Title, or Menu Order
* Specify Meta Key (used only when ordering by Meta Value; defaults to `aplb_published_date`)
* Set Order direction (ASC / DESC)
* Configure Posts Per Page (leave empty for WP default, use -1 for all posts)

Managed contexts:
* Genre taxonomy: `aplb_genre`
* Taken date taxonomy: `aplb_taken_date`
* Published date taxonomy: `aplb_published_date`
* Location taxonomy: `aplb_location`
* Keyword taxonomy: `aplb_keyword`
* Photos post type archive
* Author archives
* Date archives (year / month / day)
* Search results
* Front page (when set to show posts)

The rules are applied via `pre_get_posts` to the main query. Any Query Loop block set to inherit the URL query will automatically reflect updates. Leaving Meta Key empty while using Meta Value ordering falls back to the default published date meta key.

Changing included post types may hide posts not assigned to the selected types within a taxonomy term—ensure term assignments are consistent if you expect mixed results.

== Installation ==

1. Upload the `ap-library` folder to `/wp-content/plugins/`.
2. Activate "AP Library" in the Plugins screen.
3. (Optional) Flush permalinks (Settings → Permalinks → Save) to enable location taxonomy archives.
4. (Optional) Run the Backfill tool to populate taxonomy terms from existing meta values, generate keywords, and extract location data from IPTC metadata.

== Frequently Asked Questions ==

= Where do taken and published dates live? =
Two custom meta keys are used on `aplb_photo` posts: `aplb_taken_date` and `aplb_published_date`.

= How are dates turned into taxonomy archives? =
Dates are synchronized one-way from meta to taxonomy. Taken dates create a Year → Month → Day hierarchy in `aplb_taken_date`. Published date uses a flat term in `aplb_published_date`.

= Does it read EXIF automatically? =
Yes. On save and during upload processing the plugin attempts to read DateTimeOriginal from the featured image. If missing, you can still set dates manually.

= How are keywords extracted and assigned? =
Keywords are automatically extracted from IPTC metadata (field 2#025) embedded in featured images. The plugin uses case-insensitive matching, so "Australia" and "australia" map to the same term with a consistent title-case display name. Keywords are assigned during upload and can be backfilled for existing content.

= How is location data extracted? =
Location information is extracted from IPTC metadata fields embedded in photos: City/Sublocation (2#092, 2#090), Province/State (2#095), and Country/Primary Location (2#101). The plugin creates a hierarchical taxonomy (Country → State → City) and can backfill existing photos. Location terms are optional; photos without location metadata are unaffected.

== Screenshots ==

1. Photos list with date columns and quick edit.
2. Taken date taxonomy archive (Year → Month → Day).

== Navigation ==

After activation you will find plugin tools under the Photos post type menu:
- Photos → Library Overview (quick actions, status, general settings)
- Photos → Backfill (regenerate taken/published dates, keywords, and location)
- Photos → Archive Rules (configure ordering & enable/disable archive contexts)

== Bulk Genre Assignment ==

On the Photos list screen (`edit.php?post_type=aplb_photo`) a "Bulk Genres" toolbar lets you assign one or more Genre taxonomy terms (`aplb_genre`) to multiple photo posts without page reloads.

Workflow:
1. Tick the checkboxes of the photos you want to update (thumbnails are visible in the list for visual confirmation).
2. Select one or more genres in the multi‑select box.
3. (Optional) Toggle "Replace existing genres" if you want to overwrite instead of add.
4. Click "Apply Genres to Selected".

Modes:
* Add (default): Selected genres are merged with any existing genres on each photo (duplicates suppressed).
* Replace: Existing genres for each selected photo are discarded and replaced with only the selected genres.

The update runs through an internal REST endpoint (`/wp-json/ap-library/v1/assign-genres`). A success message appears and each affected row's Genre column is updated immediately—no full page refresh required.

Notes:
* You must have capability to edit the selected posts (standard `edit_post`).
* The toolbar only appears on the `aplb_photo` list screen.
* At least one post and one genre must be selected for the Apply button to enable.
* If a request fails, an inline error message is shown; retry after checking connectivity or nonce validity.

This feature significantly reduces repetitive Quick Edit steps when tagging batches of newly created photo posts prior to publishing.

== Bulk Date Tools ==

On the Photos list screen (`edit.php?post_type=aplb_photo`) unified date toolbars let you batch update photo dates without page reloads:

**Bulk Post Date Toolbar**: Update WordPress post dates (published and modified) for multiple photos.
**Bulk Published Date Toolbar**: Update the custom `aplb_published_date` meta field and synchronize to the `aplb_published_date` taxonomy.
**Bulk Taken Date Toolbar**: Update the custom `aplb_taken_date` meta field and synchronize to the hierarchical `aplb_taken_date` taxonomy.

Workflow:
1. Tick the checkboxes of the photos you want to update.
2. Select a new date using the date picker.
3. Click "Apply [Date Type]" to update all selected photos.
4. A success message confirms the update and date columns refresh immediately.

Features:
* Post date updates (publish/modify) synchronize with taxonomy date fields automatically.
* Visual indicator (clock icon with timestamp) shows when dates were last updated.
* Bulk updates respect user capabilities (`edit_post`).
* All updates run through the REST endpoint (`/wp-json/ap-library/v1/update-dates`).
* Taxonomy terms are created automatically if they do not exist.

== Bulk Portfolio Assignment ==

On the Photos list screen, a "Bulk Portfolios" toolbar lets you assign one or more Portfolio taxonomy terms (`aplb_portfolio`) to multiple photo posts.

Workflow:
1. Tick the checkboxes of the photos you want to update.
2. Select one or more portfolios in the multi‑select box.
3. (Optional) Toggle "Replace existing portfolios" if you want to overwrite instead of add.
4. Click "Apply Portfolios to Selected".

Modes:
* Add (default): Selected portfolios are merged with any existing portfolios on each photo (duplicates suppressed).
* Replace: Existing portfolios for each selected photo are discarded and replaced with only the selected portfolios.

The update runs through an internal REST endpoint (`/wp-json/ap-library/v1/assign-portfolios`). A success message appears and each affected row's Portfolio column is updated immediately.

== Filtering by Location ==

On the Photos list screen, a dropdown filter allows you to filter photos by location taxonomy terms (`aplb_location`). Select any location (Country, State, or City) to display only photos assigned to that location.

This is helpful when:
* Reviewing photos from a specific region or destination.
* Organizing photo collections by geography.
* Verifying location assignments extracted from IPTC metadata.

== Photo Post Creation Filters ==

To prevent non-photograph images (logos, icons, banners, UI graphics) from being converted to photo posts, the "Create Missing Photo Posts" action applies intelligent filtering.

Configurable filters (Photos → Library Overview → Photo Post Creation Filters):

**Filename Exclusions**: Images with these keywords in their filename are skipped. Default keywords include: `logo`, `banner`, `icon`, `avatar`, `profile`, `thumbnail`, `thumb`, `background`, `header`, `footer`, `placeholder`, `default`, `button`, `badge`, `sprite`, `ui`, `favicon`, `symbol`, `graphic`, `decoration`.

**Minimum Dimensions**: Images smaller than the specified width or height (in pixels) are excluded. Default: 400×400px. Set to 0 to disable.

**Minimum File Size**: Images smaller than the specified file size (in KB) are excluded. Default: 50KB. Set to 0 to disable. Small file sizes often indicate logos or icons rather than photographs.

**Extension Exclusions**: SVG and GIF files are always excluded regardless of other settings.

All filters are applied when running the "Create Missing Photo Posts" quick action. Filters do not affect manual photo post creation or auto-creation on upload. You can customize all thresholds and keywords through the settings interface.

Typical exclusion scenarios:
* Logo files: Caught by filename keyword "logo"
* Small icons: Caught by dimension filter (< 400px)
* Tiny graphics: Caught by file size filter (< 50KB)
* UI elements: Caught by keywords like "button", "badge", "ui"
* Social media graphics: Often caught by dimension ratios or keywords like "banner", "header"

== Changelog ==

= 1.3.2 - Location Taxonomy, Bulk Date Tools, Date Sync Fix =
- Added: Location taxonomy (`aplb_location`) with hierarchical structure (Country → State/Province → City) extracted from photo IPTC metadata.
- Added: Automatic location term extraction and assignment from featured image IPTC fields (Country, State, Sublocation, City).
- Added: Bulk post date toolbar for batch updating post dates (publish/modified dates) on Photos list screen.
- Added: Unified bulk date toolbar combining post date, published date, and taken date updates via REST endpoint `/ap-library/v1/update-dates`.
- Added: Visual indicator (clock icon with timestamp) showing when photo dates were last updated.
- Added: Filter Photos list by taxonomy terms via dropdowns.
- Added: Archive Rules context for location taxonomy (`tax:aplb_location`) with auto-configuration.
- Updated: Location taxonomy included in Archive Rules configuration UI.
- Updated: Backfill tool now supports location term generation from IPTC metadata.
- Fixed: Meta key and taxonomy date terms not synchronizing when dates updated via bulk date toolbar.
- Developer Notes: New EXIF methods `get_location()` and `get_location_from_post()` (@since 1.3.2) extract location from photo metadata; new REST endpoint handles bulk date updates; location backfill method `process_location_backfill()` (@since 1.3.2) available on Backfill page.

= 1.3.1 - Portfolio Support, Unified Bulk Tools, Date Format Setting =
- Added: Portfolio taxonomy (`aplb_portfolio`) for curated photo collections (hierarchical & REST-enabled).
- Added: Portfolio cover image term meta with media uploader on add/edit screens.
- Added: Bulk portfolio assignment toolbar (Add / Replace) via REST endpoint `/ap-library/v1/assign-portfolios`.
- Added: Unified bulk assignment script `ap-library-bulk-assign.js` for genres & portfolios (replaces genre-only script).
- Added: Auto-clearing of selected photo checkboxes and replace checkbox after successful bulk assignment.
- Added: Global date format setting (Photos → Library Overview) applied to list columns and newly created date taxonomy terms.
- Added: Custom column order (thumbnail, title, genre, portfolio, keyword, taken date tax, published date tax, author, post date, taken meta, published meta).
- Updated: Archive Rules now include portfolio taxonomy context (`tax:aplb_portfolio`).
- Updated: Bulk assignment UI order (Genre toolbar appears left of Portfolio for faster access).
- Updated: Date taxonomy term creation respects chosen date format for day-level terms.
- Updated: Settings label clarifies global impact of date format selection.
- Updated: REST localization consolidated into unified config objects for both taxonomies.
- Removed: Legacy script `ap-library-bulk-genres.js` replaced by unified `ap-library-bulk-assign.js`.
- Removed: Automatic default "All" genre assignment on photo creation for author control.
- Fixed: Missing portfolio archive rule context registration.
- Fixed: Genre/Portfolio columns not refreshing immediately after bulk operations.
- Fixed: Replace-mode checkbox persisting checked state post-operation.
- Fixed: Inconsistent date display (full month names) ignoring abbreviated preference.
- Developer Notes: New class `Ap_Library_Portfolio` (@since 1.3.1) manages portfolio term meta; date format option `ap_library_date_format` (default `M j, Y`) does not retroactively rename existing terms.

= 1.3.0 - Consolidated to Single Photo CPT (breaking changes) =
- Added: Bulk genre assignment toolbar on Photos list screen with Add/Replace modes and REST API endpoint `/ap-library/v1/assign-genres`.
- Added: Configurable photo post creation filters to exclude non-photographs (logos, icons, banners) based on filename keywords, dimensions (min width/height, default 400px), and file size (min KB, default 50KB).
- Added: Smart filtering in "Create Missing Photo Posts" action for logos and UI graphics.
- Added: Reset to Defaults button on Archive Rules page.
- Added: Prominent warning on Backfill page about irreversible overwrite operations.
- Updated: Renamed CPT from `aplb_uploads` → `aplb_photo` with archive base `photos`.
- Updated: Renamed taxonomies to `aplb_taken_date`, `aplb_published_date`, `aplb_genre`, and `aplb_keyword` with updated rewrite bases.
- Updated: Replaced "AP Library" submenu with "Library Overview" (slug: `aplb-overview`) as central hub for actions, status, and unified settings.
- Updated: Normalized submenu slugs for consistency: `aplb-overview`, `aplb-backfill`, `aplb-archive-rules`.
- Updated: Archive Rules UI reorganized with explanatory text above table.
- Updated: Overview page settings combined into single Save button with enhanced Related Tools links.
- Updated: Admin UI (menus, columns, bulk actions, meta box) and public query logic to new slugs.
- Updated: All taxonomy associations now target `aplb_photo` only.
- Updated: Simplified uninstall routine (only removes `aplb_photo` posts plus related taxonomies).
- Updated: Documentation updated to reflect single CPT architecture.
- Removed: Legacy `aplb_library` custom post type; all functionality now centers on the `aplb_photo` post type.
- Removed: Admin actions and helper classes related to creating/updating library posts.
- Removed: Deprecated Upload* stub classes/files after migration.
- Breaking change: Existing content under the old `aplb_uploads` CPT and taxonomies will not appear until migrated. Use the Backfill tools to re-sync date/keyword terms from meta, and consider migrating post_type from `aplb_uploads` to `aplb_photo` if you have existing data. After upgrading, visit Settings → Permalinks and click Save to flush rewrite rules.

= 1.2.1 - i18n and cleanup =
- Updated: Normalized translation text domain to `ap-library` across the plugin for consistent i18n.
- Updated: Regenerated POT file and added `X-Domain: ap-library` header.
- Fixed: CPT labels to use the unified `ap-library` text domain.
- Fixed: Corrected CPT ↔ taxonomy associations to use actual registered slugs.
- Fixed: Uninstall routine now removes posts and terms for all AP Library CPTs/taxonomies and flushes rewrite rules.
- Misc: Minor label consistency and internal cleanup.

= 1.2.0 - Photo Keywords & Enhanced Date Backfill =
- Added `aplb_uploads_keyword` taxonomy with automatic IPTC keyword extraction from featured images
- Keywords are now automatically extracted and assigned during upload post creation
- Implemented case-insensitive keyword matching with normalized slugs and title-case display names
- Enhanced backfill UI: unified submenu with three separate operations (Taken Date, Published Date, Keywords)
- Split date backfill into independent operations for taken dates and published dates
- Improved hierarchical date term names for better human readability (e.g., "May 2023" instead of "May", "May 15, 2023" instead of "15")
- Added EXIF keyword extraction methods: `get_keywords()` and `get_keywords_from_post()`
- Keyword taxonomy hidden from Quick Edit to maintain consistency
- All keyword operations respect case-insensitive matching ("Australia" and "australia" map to the same term)

= 1.1.0 - Hierarchical taken date archives, EXIF, and sync improvements =
- Introduced hierarchical taken date taxonomy `aplb_uploads_tdate` (Year → Month → Day) with clean archive URLs.
- One-way synchronization from `aplb_taken_date` meta to hierarchical terms; `aplb_library_pdate` remains flat.
- Backfill tool updated to generate and sync hierarchical date terms for existing uploads.
- Admin columns and quick edit updated to edit dates and re-sync terms accordingly.
- EXIF extraction prioritized to populate `aplb_taken_date` from featured image metadata when available.
- Upload post creation streamlined: avoid duplicate term creation; set meta then trigger synchronization.
- Archive query adjustments: ensure taxonomy archives use `aplb_uploads` post type and order by `aplb_published_date` (DESC).
- UI refinement: date taxonomies hidden from Quick Edit to prevent conflicts with meta-driven sync.
- General reliability fixes and internal logging during development (removed in release).

= 1.0.0 - Initial Release =
- Custom post type, date meta, base taxonomies, admin UI, and public hooks skeleton.

== Upgrade Notice ==

= 1.3.2 =
This release adds a new Location taxonomy with IPTC metadata extraction and unified bulk date tools. After upgrading:
1. Flush permalinks (Settings → Permalinks → Save) to ensure location taxonomy archives resolve.
2. (Optional) Run the Backfill tool (Photos → Backfill → Location) to extract location data from existing photo IPTC metadata.
3. Use the new unified bulk date toolbar to batch update post dates, published dates, or taken dates.
4. (Optional) Add the Location filter dropdown to your Photos list via custom columns if desired.
No database migration is required; existing posts and taxonomies remain intact. The location taxonomy is optional and backfill only affects photos with location data in their featured image IPTC fields.

= 1.3.1 =
This release adds the Portfolio taxonomy, unified bulk assignment (genres + portfolios), and a global date format setting. After upgrading:
1. Flush permalinks (Settings → Permalinks → Save) to ensure portfolio archives resolve.
2. Visit Photos → Library Overview to choose a date format (default short) before creating new date terms.
3. (Optional) Add portfolio cover images via the Portfolio taxonomy term edit screens.
4. Use the new Bulk Portfolios toolbar to batch assign portfolios; verify column order updated.
No database migration is required; existing posts remain intact. Previously created date terms keep their original names.

= 1.3.0 =
**BREAKING CHANGES:** This release consolidates to a single photo CPT (`aplb_photo`) and renames post types/taxonomies. Existing content will not appear until migrated. After upgrading, visit Settings → Permalinks and click Save. Use Backfill tools to re-sync content. Review the changelog for full migration details.

= 1.2.1 =
No action required. This release improves internationalization and uninstall cleanup. Uninstall now fully removes AP Library posts and taxonomy terms and flushes rewrites.

= 1.2.0 =
This release adds automatic keyword extraction from IPTC metadata and separate backfill operations for taken/published dates. Run the backfill tools (Taken Date, Published Date, and Keywords) to populate taxonomy terms for existing content.

= 1.1.0 =
This release adds hierarchical taken date archives, EXIF-based date extraction, and improved meta→taxonomy synchronization. Run the Backfill tool to synchronize existing content.
