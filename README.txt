=== AP Library ===
Contributors: Antonin Puleo
Donate link: https://antoninpuleo.com/
Tags: photography, media, uploads, custom post type, taxonomy, exif, gallery, archive, dates, keywords
Requires at least: 6.5
Tested up to: 6.8.3
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Photo Library system for WordPress built around a custom post type for uploads, date metadata, hierarchical taken date archives, EXIF extraction, and admin tools.

== Description ==

AP Library provides a robust foundation to manage a photography library as first-class posts. It defines:

- Custom Post Type: `aplb_uploads` for uploaded photos
- Meta fields: `aplb_published_date`, `aplb_taken_date` (ISO 8601: `YYYY-MM-DD`)
- Taxonomies:
    - `aplb_library_pdate` (flat) for published date groupings
    - `aplb_uploads_tdate` (hierarchical) for taken date — Year → Month → Day — enabling clean archives like `/uploads-tdate/2023/november/15/`
    - `aplb_uploads_keyword` (flat) for IPTC/EXIF photo keywords automatically extracted from the featured image
- EXIF integration to extract taken date from featured images and IPTC keywords (creates matching taxonomy terms automatically)
- Admin UI enhancements (meta box, quick edit integration, sortable columns)
- Backfill tools to sync existing content
- Query customization so archives order by `aplb_published_date` by default

== Installation ==

1. Upload the `ap-library` folder to `/wp-content/plugins/`.
2. Activate “AP Library” in the Plugins screen.
3. (Optional) Run the Backfill tool to populate taxonomy terms from existing meta values and generate keywords.

== Frequently Asked Questions ==

= Where do taken and published dates live? =
Two custom meta keys are used on `aplb_uploads` posts: `aplb_taken_date` and `aplb_published_date`.

= How are dates turned into taxonomy archives? =
Dates are synchronized one-way from meta to taxonomy. Taken dates create a Year → Month → Day hierarchy in `aplb_uploads_tdate`. Published date uses a flat term in `aplb_library_pdate`.

= Does it read EXIF automatically? =
Yes. On save and during upload processing the plugin attempts to read DateTimeOriginal from the featured image. If missing, you can still set dates manually.

== Screenshots ==

1. Uploads list with date columns and quick edit.
2. Taken date taxonomy archive (Year → Month → Day).

== Changelog ==

= Unreleased - IPTC Keyword Taxonomy (pending release) =
- Introduced non-hierarchical keyword taxonomy `aplb_uploads_keyword` (auto-populated from IPTC keywords on upload).
- Added EXIF/IPTC keyword extraction to post creation flow.
- Backfill screen now offers separate date and keyword operations under a single submenu.

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

= Unreleased =
Keyword taxonomy & extraction are available but not yet part of a tagged release. Wait for next stable version before deploying to production sites.

= 1.1.0 =
This release adds hierarchical taken date archives, EXIF-based date extraction, and improved meta→taxonomy synchronization. Run the Backfill tool to synchronize existing content.
