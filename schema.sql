--
-- HohnePeople base schema and demo data
--
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS review_video_map;
DROP TABLE IF EXISTS review_translations;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS video_links;
DROP TABLE IF EXISTS video_translations;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS page_translations;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS social_links;
DROP TABLE IF EXISTS navigation_translations;
DROP TABLE IF EXISTS navigation;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS site_texts;

CREATE TABLE site_texts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  text_key VARCHAR(64) NOT NULL,
  lang CHAR(2) NOT NULL,
  content TEXT NOT NULL,
  UNIQUE KEY uniq_text (text_key, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE themes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(32) NOT NULL,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  css_file VARCHAR(120) NOT NULL,
  is_default TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  UNIQUE KEY uniq_theme_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE navigation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(32) NOT NULL,
  path VARCHAR(160) NOT NULL,
  location ENUM('header','footer') NOT NULL DEFAULT 'header',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_nav_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE navigation_translations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  navigation_id INT NOT NULL,
  lang CHAR(2) NOT NULL,
  label VARCHAR(120) NOT NULL,
  UNIQUE KEY uniq_nav_lang (navigation_id, lang),
  CONSTRAINT fk_nav_trans FOREIGN KEY (navigation_id) REFERENCES navigation (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(32) NOT NULL,
  label VARCHAR(80) NOT NULL,
  url VARCHAR(255) NOT NULL,
  icon_key VARCHAR(32) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(64) NOT NULL,
  template VARCHAR(32) NOT NULL DEFAULT 'page',
  is_indexed TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_page_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE page_translations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_id INT NOT NULL,
  lang CHAR(2) NOT NULL,
  title VARCHAR(160) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  UNIQUE KEY uniq_page_lang (page_id, lang),
  CONSTRAINT fk_page_trans FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL,
  platform VARCHAR(32) NOT NULL,
  platform_ref VARCHAR(128) NOT NULL,
  primary_url VARCHAR(255) NOT NULL,
  publish_date DATE NOT NULL,
  thumbnail_url VARCHAR(255) DEFAULT NULL,
  hero_priority TINYINT(1) DEFAULT 0,
  UNIQUE KEY uniq_video_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE video_translations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id INT NOT NULL,
  lang CHAR(2) NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  lyrics MEDIUMTEXT,
  tags VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uniq_video_lang (video_id, lang),
  CONSTRAINT fk_video_trans FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE video_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id INT NOT NULL,
  kind VARCHAR(32) NOT NULL,
  label VARCHAR(80) NOT NULL,
  url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_video_link FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL,
  publish_date DATE NOT NULL,
  UNIQUE KEY uniq_review_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_translations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  review_id INT NOT NULL,
  lang CHAR(2) NOT NULL,
  title VARCHAR(160) NOT NULL,
  excerpt TEXT NOT NULL,
  body MEDIUMTEXT NOT NULL,
  UNIQUE KEY uniq_review_lang (review_id, lang),
  CONSTRAINT fk_review_trans FOREIGN KEY (review_id) REFERENCES reviews (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_video_map (
  review_id INT NOT NULL,
  video_id INT NOT NULL,
  PRIMARY KEY (review_id, video_id),
  CONSTRAINT fk_rvm_review FOREIGN KEY (review_id) REFERENCES reviews (id) ON DELETE CASCADE,
  CONSTRAINT fk_rvm_video FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed data
INSERT INTO site_texts (text_key, lang, content) VALUES
('site.name', 'de', 'HohnePeople'),
('site.name', 'en', 'HohnePeople'),
('site.slogan', 'de', '"Alle Styles, eine Haltung - Willkommen im Mix!"'),
('site.slogan', 'en', 'All styles, one attitude - Welcome to the mix!'),
('site.tagline', 'de', 'HohnePeople ist ein wandelndes Kollektiv aus Beats, Farben und Haltungen.'),
('site.tagline', 'en', 'HohnePeople is a shifting collective of beats, colors, and statements.'),
('footer.imprint', 'de', 'Impressum'),
('footer.imprint', 'en', 'Imprint'),
('footer.privacy', 'de', 'Datenschutz'),
('footer.privacy', 'en', 'Privacy'),
('home.latest', 'de', 'Neuestes Video'),
('home.latest', 'en', 'Latest Video'),
('home.moreVideos', 'de', 'Weitere Videos'),
('home.moreVideos', 'en', 'More Videos'),
('home.reviewsTeaser', 'de', 'Frische Stimmen über HohnePeople'),
('home.reviewsTeaser', 'en', 'Fresh voices about HohnePeople');

INSERT INTO themes (slug, name, description, css_file, is_default) VALUES
('neo', 'Neo Chrome', 'Bold gradients with punchy contrast.', 'themes/neo.css', 1),
('mono', 'Mono Air', 'Calm monochrome palette.', 'themes/mono.css', 0);

INSERT INTO navigation (slug, path, location, sort_order) VALUES
('home', '/', 'header', 1),
('videos', '/videos', 'header', 2),
('reviews', '/reviews', 'header', 3),
('about', '/about', 'header', 4),
('imprint', '/imprint', 'footer', 1),
('privacy', '/privacy', 'footer', 2);

INSERT INTO navigation_translations (navigation_id, lang, label)
SELECT id, 'de', CASE slug
  WHEN 'home' THEN 'Start'
  WHEN 'videos' THEN 'Videos'
  WHEN 'reviews' THEN 'Reviews'
  WHEN 'about' THEN 'Über'
  WHEN 'imprint' THEN 'Impressum'
  WHEN 'privacy' THEN 'Datenschutz'
END
FROM navigation;

INSERT INTO navigation_translations (navigation_id, lang, label)
SELECT id, 'en', CASE slug
  WHEN 'home' THEN 'Home'
  WHEN 'videos' THEN 'Videos'
  WHEN 'reviews' THEN 'Reviews'
  WHEN 'about' THEN 'About'
  WHEN 'imprint' THEN 'Imprint'
  WHEN 'privacy' THEN 'Privacy'
END
FROM navigation;

INSERT INTO social_links (slug, label, url, icon_key, sort_order) VALUES
('youtube', 'YouTube', 'https://youtube.com/@hohnepeople', 'youtube', 1),
('suno', 'Suno', 'https://suno.com/@hohnepeople', 'suno', 2),
('soundcloud', 'SoundCloud', 'https://soundcloud.com/hohnepeople', 'soundcloud', 3),
('instagram', 'Instagram', 'https://instagram.com/hohnepeople', 'instagram', 4),
('tiktok', 'TikTok', 'https://tiktok.com/@hohnepeople', 'tiktok', 5);

INSERT INTO pages (slug, template) VALUES
('about', 'page'),
('imprint', 'page'),
('privacy', 'page');

INSERT INTO page_translations (page_id, lang, title, body) VALUES
((SELECT id FROM pages WHERE slug = 'about'), 'de', 'Über HohnePeople', '<p>HohnePeople bewegt sich zwischen Klangkunst und Street Couture. Wir mischen analoges Echo mit digitalen Kanten und feiern Kollaborationen über Genres hinweg.</p><p>Jedes Release ist ein Kapitel, jede Bühne ein Spielplatz. Wir lieben minimale Mittel und klare Aussagen.</p>'),
((SELECT id FROM pages WHERE slug = 'about'), 'en', 'About HohnePeople', '<p>HohnePeople lives between sound art and street couture. We merge analog echoes with digital edges and celebrate cross-genre collaborations.</p><p>Each release is a chapter, every stage a playground. Minimal tools, clear statements.</p>'),
((SELECT id FROM pages WHERE slug = 'imprint'), 'de', 'Impressum', '<p>HohnePeople Studio<br>Beispielstraße 12<br>10999 Berlin</p><p>Kontakt: studio@hohnepeople.com</p>'),
((SELECT id FROM pages WHERE slug = 'imprint'), 'en', 'Imprint', '<p>HohnePeople Studio<br>Sample Street 12<br>10999 Berlin</p><p>Contact: studio@hohnepeople.com</p>'),
((SELECT id FROM pages WHERE slug = 'privacy'), 'de', 'Datenschutz', '<p>Wir speichern lediglich technisch notwendige Cookies für Sprache und Theme.</p><p>Es findet keinerlei Tracking oder Profiling statt.</p>'),
((SELECT id FROM pages WHERE slug = 'privacy'), 'en', 'Privacy', '<p>We only store strictly necessary cookies for language and theme.</p><p>No tracking, no profiling.</p>');

INSERT INTO videos (slug, platform, platform_ref, primary_url, publish_date, thumbnail_url, hero_priority) VALUES
('chromatic-pulse', 'youtube', 'dQw4w9WgXcQ', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2024-09-18', NULL, 1),
('static-poem', 'youtube', '5NV6Rdv1a3I', 'https://www.youtube.com/watch?v=5NV6Rdv1a3I', '2024-06-02', NULL, 0),
('futuresweat', 'youtube', 'OPf0YbXqDm0', 'https://www.youtube.com/watch?v=OPf0YbXqDm0', '2024-03-25', NULL, 0);

INSERT INTO video_translations (video_id, lang, title, description, lyrics, tags) VALUES
((SELECT id FROM videos WHERE slug = 'chromatic-pulse'), 'de', 'Chromatic Pulse', 'Neon-Beats, gefilterte Stimmen und ein Groove für späte Nächte.', 'Hook:
Chromatische Pulse, wir atmen im Delay,
Groove im Rückspiegel, alles steht im Spray.', 'electronic,night,berlin'),
((SELECT id FROM videos WHERE slug = 'chromatic-pulse'), 'en', 'Chromatic Pulse', 'Neon beats, filtered vocals, a groove for late city drifts.', 'Hook:
Chromatic pulses, we breathe inside delay,
Grooves in the rearview, everything in spray.', 'electronic,night,berlin'),
((SELECT id FROM videos WHERE slug = 'static-poem'), 'de', 'Static Poem', 'Ein Sprechgesang über modulare Synth-Fahnen.', 'Verse:
Wir falten Funker in Texturen,
Statik tanzt auf allen Spuren.', 'spokenword,minimal'),
((SELECT id FROM videos WHERE slug = 'static-poem'), 'en', 'Static Poem', 'Spoken surge over modular synth banners.', 'Verse:
We fold transmitters into textures,
Static dances over every trace.', 'spokenword,minimal'),
((SELECT id FROM videos WHERE slug = 'futuresweat'), 'de', 'Future Sweat', 'Clubhymne zwischen Jersey und Industrial.', 'Refrain:
Schweiß der Zukunft, Tropfen voll Neon,
Wir halten die Frequenz, bleiben nie schon.', 'club,jersey'),
((SELECT id FROM videos WHERE slug = 'futuresweat'), 'en', 'Future Sweat', 'Club anthem suspended between Jersey and industrial.', 'Chorus:
Sweat from the future, dripping neon true,
We hold the frequency, never déjà vu.', 'club,jersey');

INSERT INTO video_links (video_id, kind, label, url, sort_order) VALUES
((SELECT id FROM videos WHERE slug = 'chromatic-pulse'), 'youtube', 'Watch on YouTube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 1),
((SELECT id FROM videos WHERE slug = 'chromatic-pulse'), 'suno', 'Hear on Suno', 'https://suno.com/@hohnepeople/chromatic-pulse', 2),
((SELECT id FROM videos WHERE slug = 'chromatic-pulse'), 'soundcloud', 'SoundCloud', 'https://soundcloud.com/hohnepeople/chromatic-pulse', 3),
((SELECT id FROM videos WHERE slug = 'static-poem'), 'youtube', 'Watch on YouTube', 'https://www.youtube.com/watch?v=5NV6Rdv1a3I', 1),
((SELECT id FROM videos WHERE slug = 'static-poem'), 'suno', 'Hear on Suno', 'https://suno.com/@hohnepeople/static-poem', 2),
((SELECT id FROM videos WHERE slug = 'futuresweat'), 'youtube', 'Watch on YouTube', 'https://www.youtube.com/watch?v=OPf0YbXqDm0', 1),
((SELECT id FROM videos WHERE slug = 'futuresweat'), 'soundcloud', 'SoundCloud', 'https://soundcloud.com/hohnepeople/futuresweat', 2);

INSERT INTO reviews (slug, publish_date) VALUES
('radio-berlin-pulse', '2024-09-25'),
('grid-mag-static', '2024-06-10');

INSERT INTO review_translations (review_id, lang, title, excerpt, body) VALUES
((SELECT id FROM reviews WHERE slug = 'radio-berlin-pulse'), 'de', 'Radio Berlin über Chromatic Pulse', '"Chromatic Pulse" ist ein Clubsignal für Geduldige.', '<p>"Chromatic Pulse" schlägt wie ein Herz aus Glasfasern. Radio Berlin lobt den Track für seine Ruhe vor dem Drop und die kontrollierte Eskalation.</p><p>Die Lyrics wirken wie flüsternde Graffiti, die Synths glänzen metallisch.</p>'),
((SELECT id FROM reviews WHERE slug = 'radio-berlin-pulse'), 'en', 'Radio Berlin on Chromatic Pulse', '"Chromatic Pulse" is a club signal for the patient.', '<p>"Chromatic Pulse" beats like fiber-optic hearts. Radio Berlin praises its slow-burn build and controlled escalation.</p><p>The lyrics read like whispering graffiti while the synths shimmer metallic.</p>'),
((SELECT id FROM reviews WHERE slug = 'grid-mag-static'), 'de', 'GRID Magazine zu Static Poem', 'GRID nennt es "ein poetisches Modem-Solo".', '<p>GRID Magazine feiert "Static Poem" als Moment, in dem Spoken Word auf Signalrauschen trifft.</p><p>Die Performance bleibt roh, doch jede Silbe sitzt.</p>'),
((SELECT id FROM reviews WHERE slug = 'grid-mag-static'), 'en', 'GRID Magazine on Static Poem', 'GRID calls it "a poetic modem solo."', '<p>GRID Magazine applauds "Static Poem" where spoken-word collides with signal noise.</p><p>The delivery stays raw while every syllable lands with intent.</p>');

INSERT INTO review_video_map (review_id, video_id) VALUES
((SELECT id FROM reviews WHERE slug = 'radio-berlin-pulse'), (SELECT id FROM videos WHERE slug = 'chromatic-pulse')),
((SELECT id FROM reviews WHERE slug = 'grid-mag-static'), (SELECT id FROM videos WHERE slug = 'static-poem'));
