-- =============================================================
-- 011 — Media library folders
--
-- Photos were all landing in one flat storage/uploads/YYYY/MM/ bucket, so a
-- library of a few hundred images was an undifferentiated wall of thumbnails
-- with no way to tell a service photo from a blog cover.
--
-- Each content module that carries an image now owns one folder. A photo is
-- uploaded into "unsorted" and files itself the moment a record attaches it —
-- first use wins, so a photo shared by a service and a promotion has one home
-- and one stable URL. The slug set lives in app/Services/MediaFolders.php;
-- this column stores it, and the same slug is the directory name on disk.
--
-- The UPDATEs below only stamp the column. Moving the files themselves is a
-- filesystem job, not a SQL one — MediaFiler::reorganize() does that, driven
-- by the "Reorganize files" button in the media library. Until it runs, the
-- rows keep pointing at where the files actually still are, so nothing breaks
-- in the meantime.
--
-- ORDERING: the ALTER runs first because everything after it needs the
-- column. It is also the one statement that cannot be replayed — a corrected
-- re-run after a mid-file failure would hit "duplicate column" — but the
-- UPDATEs are all guarded on folder = 'unsorted', so they are individually
-- idempotent and safe to re-run by hand if that ever happens.
-- =============================================================

ALTER TABLE media
  ADD COLUMN folder VARCHAR(40) NOT NULL DEFAULT 'unsorted' AFTER title,
  ADD KEY idx_media_folder (folder, deleted_at);

-- --- Backfill: photos already attached to something ------------------
-- Every statement is guarded on folder = 'unsorted', so the first module to
-- claim a photo keeps it. That is the same first-use-wins rule MediaFiler
-- applies from here on, applied once to the existing library. Statement order
-- is therefore the tie-break for a photo used in more than one place.

UPDATE media m
  JOIN services s ON s.media_id = m.id AND s.deleted_at IS NULL
   SET m.folder = 'services'
 WHERE m.folder = 'unsorted';

-- Gallery images belong to the service that shows them, same as the cover.
UPDATE media m
  JOIN service_images si ON si.media_id = m.id
  JOIN services s ON s.id = si.service_id AND s.deleted_at IS NULL
   SET m.folder = 'services'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN service_categories c ON c.media_id = m.id AND c.deleted_at IS NULL
   SET m.folder = 'categories'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN promotions p ON p.media_id = m.id AND p.deleted_at IS NULL
   SET m.folder = 'promotions'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN specials sp ON sp.media_id = m.id AND sp.deleted_at IS NULL
   SET m.folder = 'specials'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN blog_posts b ON b.media_id = m.id AND b.deleted_at IS NULL
   SET m.folder = 'blog'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN product_brands pb ON pb.media_id = m.id AND pb.deleted_at IS NULL
   SET m.folder = 'brands'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN gift_cards g ON g.media_id = m.id AND g.deleted_at IS NULL
   SET m.folder = 'gift-cards'
 WHERE m.folder = 'unsorted';

UPDATE media m
  JOIN products pr ON pr.media_id = m.id AND pr.deleted_at IS NULL
   SET m.folder = 'products'
 WHERE m.folder = 'unsorted';
