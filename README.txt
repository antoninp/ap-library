=== AP Library ===
Contributors: Antonin Puleo
Donate link: https://antoninpuleo.com/
Tags: photography, media, uploads, custom post type, taxonomy, exif, gallery, archive, dates, keywords
Requires at least: 6.5
Tested up to: 6.8.3
Requires PHP: 7.4
Stable tag: 1.2.1
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
    - `aplb_genre` (hierarchical) for your own logical groupings (optional)
    - `aplb_keyword` (flat) for IPTC/EXIF photo keywords automatically extracted from featured images
- EXIF integration to extract taken dates and IPTC keywords from featured images (creates matching taxonomy terms automatically)
- Admin UI enhancements (meta box, quick edit integration, sortable columns)
- Backfill tools to sync existing content
- Query customization so archives order by `aplb_published_date` by default

== Archive Query Settings ==

Since 1.2.x you can configure how taxonomy / post type / author / date archives build their main query instead of relying on hard‑coded logic.

Navigate to: Photos → Archive Settings.

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
2. Activate “AP Library” in the Plugins screen.
3. (Optional) Run the Backfill tool to populate taxonomy terms from existing meta values and generate keywords.

== Frequently Asked Questions ==

= Where do taken and published dates live? =
Two custom meta keys are used on `aplb_photo` posts: `aplb_taken_date` and `aplb_published_date`.

= How are dates turned into taxonomy archives? =
Dates are synchronized one-way from meta to taxonomy. Taken dates create a Year → Month → Day hierarchy in `aplb_taken_date`. Published date uses a flat term in `aplb_published_date`.

= Does it read EXIF automatically? =
Yes. On save and during upload processing the plugin attempts to read DateTimeOriginal from the featured image. If missing, you can still set dates manually.

= How are keywords extracted and assigned? =
Keywords are automatically extracted from IPTC metadata (field 2#025) embedded in featured images. The plugin uses case-insensitive matching, so "Australia" and "australia" map to the same term with a consistent title-case display name. Keywords are assigned during upload and can be backfilled for existing content.

== Screenshots ==

1. Photos list with date columns and quick edit.
2. Taken date taxonomy archive (Year → Month → Day).

== Changelog ==

= Unreleased - Consolidated to Single Photo CPT (breaking) =
- Removed legacy `aplb_library` custom post type; all functionality now centers on `aplb_uploads`.
- Detached taxonomies `aplb_library_pdate` and `aplb_uploads_genre` from the old CPT; they now only attach to `aplb_uploads`.
- Removed admin actions and helper classes related to creating/updating library posts.
- Updated archive query settings UI to eliminate library post type contexts.
- Simplified uninstall routine (only removes `aplb_uploads` posts plus related taxonomies).
- Renamed CPT from `aplb_uploads` to `aplb_photo` with archive base `photos`.
- Renamed taxonomies to `aplb_taken_date`, `aplb_published_date`, `aplb_genre`, and `aplb_keyword` with updated rewrite bases.
- Updated admin UI (menus, columns, bulk actions, meta box) and public query logic to the new slugs.
- Uninstall/deactivation updated to clean up the new CPT/taxonomies.
- Documentation updated to reflect single CPT architecture.
- Breaking change: existing content under the old `aplb_uploads` CPT and taxonomies will not appear until migrated. Use the Backfill tools to re-sync date/keyword terms from meta, and consider migrating post_type from `aplb_uploads` to `aplb_photo` if you have existing data. After upgrading, visit Settings → Permalinks and click Save to flush rewrite rules.

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

= 1.2.1 =
No action required. This release improves internationalization and uninstall cleanup. Uninstall now fully removes AP Library posts and taxonomy terms and flushes rewrites.

= 1.2.0 =
This release adds automatic keyword extraction from IPTC metadata and separate backfill operations for taken/published dates. Run the backfill tools (Taken Date, Published Date, and Keywords) to populate taxonomy terms for existing content.

= 1.1.0 =
This release adds hierarchical taken date archives, EXIF-based date extraction, and improved meta→taxonomy synchronization. Run the Backfill tool to synchronize existing content.
