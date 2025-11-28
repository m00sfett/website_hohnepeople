<?php
declare(strict_types=1);

require_once __DIR__ . '/sanitizer.php';

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    exit('Please copy config.php.sample to config.php and fill in your database credentials.');
}
$config = require $configPath;

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config['db']['host'],
    $config['db']['port'],
    $config['db']['name'],
    $config['db']['charset']
);
try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed.');
}

$defaultLang = $config['site']['default_lang'] ?? 'de';
$supportedLangs = $config['site']['supported_langs'] ?? ['de', 'en'];
$lang = $defaultLang;
$cookieDays = (int)($config['site']['cookie_ttl_days'] ?? 180);
$cookieLifetime = time() + ($cookieDays * 86400);

if (!empty($_GET['lang']) && in_array($_GET['lang'], $supportedLangs, true)) {
    $lang = $_GET['lang'];
    setcookie('site_lang', $lang, $cookieLifetime, '/');
} elseif (!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $supportedLangs, true)) {
    $lang = $_COOKIE['site_lang'];
}

$themes = loadThemes($pdo);
$defaultTheme = $config['site']['default_theme'] ?? array_key_first($themes);
$currentTheme = $defaultTheme;
if (!empty($_GET['theme']) && isset($themes[$_GET['theme']])) {
    $currentTheme = $_GET['theme'];
    setcookie('site_theme', $currentTheme, $cookieLifetime, '/');
} elseif (!empty($_COOKIE['site_theme']) && isset($themes[$_COOKIE['site_theme']])) {
    $currentTheme = $_COOKIE['site_theme'];
} elseif (!empty($themes)) {
    foreach ($themes as $slug => $data) {
        if (!empty($data['is_default'])) {
            $currentTheme = $slug;
            break;
        }
    }
}
$currentThemeData = $themes[$currentTheme] ?? reset($themes) ?: ['css_file' => 'themes/neo.css', 'name' => 'Neo'];

$fallbackLang = $defaultLang;
$siteTexts = loadSiteTexts($pdo, $lang, $fallbackLang);
$headerNav = loadNavigation($pdo, $lang, 'header');
$footerNav = loadNavigation($pdo, $lang, 'footer');
$socialLinks = loadSocialLinks($pdo);

$route = $_GET['page'] ?? 'home';
$slug = $_GET['slug'] ?? null;
$view = 'home';
$viewData = [];

switch ($route) {
    case 'videos':
        $pageNumber = max(1, (int)($_GET['p'] ?? 1));
        $perPage = 6;
        $offset = ($pageNumber - 1) * $perPage;
        $videos = loadVideos($pdo, $lang, $fallbackLang, $perPage, $offset);
        $totalVideos = countVideos($pdo);
        $totalPages = (int)ceil($totalVideos / $perPage);
        $view = 'videos';
        $viewData = compact('videos', 'pageNumber', 'totalPages');
        break;
    case 'video':
        if ($slug) {
            $video = loadVideoBySlug($pdo, $slug, $lang, $fallbackLang);
            if ($video) {
                $videoLinks = loadVideoLinks($pdo, (int)$video['id']);
                $relatedVideos = loadRelatedVideos($pdo, (int)$video['id'], $lang, $fallbackLang);
                $linkedReviews = loadReviewsForVideo($pdo, (int)$video['id'], $lang, $fallbackLang);
                $view = 'video';
                $viewData = compact('video', 'videoLinks', 'relatedVideos', 'linkedReviews');
                break;
            }
        }
        http_response_code(404);
        $view = 'page';
        $viewData = ['page' => ['title' => '404', 'body' => '<p>Content not found.</p>']];
        break;
    case 'reviews':
        $reviews = loadReviews($pdo, $lang, $fallbackLang);
        $view = 'reviews';
        $viewData = compact('reviews');
        break;
    case 'review':
        if ($slug) {
            $review = loadReviewBySlug($pdo, $slug, $lang, $fallbackLang);
            if ($review) {
                $reviewVideos = loadVideosForReview($pdo, (int)$review['id'], $lang, $fallbackLang);
                $view = 'review';
                $viewData = compact('review', 'reviewVideos');
                break;
            }
        }
        http_response_code(404);
        $view = 'page';
        $viewData = ['page' => ['title' => '404', 'body' => '<p>Review not found.</p>']];
        break;
    case 'page':
        if ($slug) {
            $page = loadPage($pdo, $slug, $lang, $fallbackLang);
            if ($page) {
                $view = 'page';
                $viewData = compact('page');
                break;
            }
        }
        http_response_code(404);
        $viewData = ['page' => ['title' => '404', 'body' => '<p>Page not found.</p>']];
        break;
    case 'links':
        $view = 'links';
        break;
    case 'sitemap':
        header('Content-Type: application/xml; charset=utf-8');
        echo buildSitemap($pdo);
        exit;
    case 'home':
    default:
        $latest = loadLatestVideo($pdo, $lang, $fallbackLang);
        $recentVideos = loadVideos($pdo, $lang, $fallbackLang, 6, 0);
        $recentReviews = loadReviews($pdo, $lang, $fallbackLang, 2);
        $view = 'home';
        $viewData = compact('latest', 'recentVideos', 'recentReviews');
        break;
}

$meta = buildMeta($route, $viewData, $config);

include __DIR__ . '/views/partials/header.php';
include __DIR__ . '/views/' . $view . '.php';
include __DIR__ . '/views/partials/footer.php';

function loadSiteTexts(PDO $pdo, string $lang, string $fallback): array
{
    $stmt = $pdo->prepare('SELECT text_key, content FROM site_texts WHERE lang = :lang');
    $stmt->execute(['lang' => $lang]);
    $texts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    if ($lang !== $fallback) {
        $fallbackStmt = $pdo->prepare('SELECT text_key, content FROM site_texts WHERE lang = :lang');
        $fallbackStmt->execute(['lang' => $fallback]);
        foreach ($fallbackStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $key => $content) {
            if (!isset($texts[$key])) {
                $texts[$key] = $content;
            }
        }
    }
    return $texts;
}

function loadNavigation(PDO $pdo, string $lang, string $location): array
{
    $sql = 'SELECT n.slug, n.path, nt.label FROM navigation n
        INNER JOIN navigation_translations nt ON nt.navigation_id = n.id AND nt.lang = :lang
        WHERE n.location = :location AND n.is_active = 1
        ORDER BY n.sort_order ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['lang' => $lang, 'location' => $location]);
    return $stmt->fetchAll();
}

function loadSocialLinks(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT slug, label, url, icon_key FROM social_links WHERE is_active = 1 ORDER BY sort_order ASC');
    return $stmt->fetchAll();
}

function loadThemes(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT slug, name, description, css_file, is_default FROM themes WHERE is_active = 1 ORDER BY is_default DESC, name ASC');
    $rows = $stmt->fetchAll();
    $themes = [];
    foreach ($rows as $row) {
        $themes[$row['slug']] = $row;
    }
    return $themes;
}

function loadVideos(PDO $pdo, string $lang, string $fallback, int $limit, int $offset): array
{
    $sql = 'SELECT v.*, COALESCE(vt.title, vtf.title) AS title,
            COALESCE(vt.description, vtf.description) AS description,
            COALESCE(vt.tags, vtf.tags) AS tags
        FROM videos v
        LEFT JOIN video_translations vt ON vt.video_id = v.id AND vt.lang = :lang
        LEFT JOIN video_translations vtf ON vtf.video_id = v.id AND vtf.lang = :fallback
        ORDER BY v.publish_date DESC
        LIMIT :limit OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lang', $lang, PDO::PARAM_STR);
    $stmt->bindValue(':fallback', $fallback, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return array_map('sanitizeVideoRecord', $stmt->fetchAll());
}

function countVideos(PDO $pdo): int
{
    return (int)$pdo->query('SELECT COUNT(*) FROM videos')->fetchColumn();
}

function loadLatestVideo(PDO $pdo, string $lang, string $fallback): ?array
{
    $sql = 'SELECT v.*, COALESCE(vt.title, vtf.title) AS title,
            COALESCE(vt.description, vtf.description) AS description,
            COALESCE(vt.lyrics, vtf.lyrics) AS lyrics,
            COALESCE(vt.tags, vtf.tags) AS tags
        FROM videos v
        LEFT JOIN video_translations vt ON vt.video_id = v.id AND vt.lang = :lang
        LEFT JOIN video_translations vtf ON vtf.video_id = v.id AND vtf.lang = :fallback
        ORDER BY v.hero_priority DESC, v.publish_date DESC
        LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['lang' => $lang, 'fallback' => $fallback]);
    $video = $stmt->fetch();

    return $video ? sanitizeVideoRecord($video) : null;
}

function loadVideoBySlug(PDO $pdo, string $slug, string $lang, string $fallback): ?array
{
    $sql = 'SELECT v.*, COALESCE(vt.title, vtf.title) AS title,
            COALESCE(vt.description, vtf.description) AS description,
            COALESCE(vt.lyrics, vtf.lyrics) AS lyrics,
            COALESCE(vt.tags, vtf.tags) AS tags
        FROM videos v
        LEFT JOIN video_translations vt ON vt.video_id = v.id AND vt.lang = :lang
        LEFT JOIN video_translations vtf ON vtf.video_id = v.id AND vtf.lang = :fallback
        WHERE v.slug = :slug';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['slug' => $slug, 'lang' => $lang, 'fallback' => $fallback]);
    $video = $stmt->fetch();
    return $video ? sanitizeVideoRecord($video) : null;
}

function loadVideoLinks(PDO $pdo, int $videoId): array
{
    $stmt = $pdo->prepare('SELECT kind, label, url FROM video_links WHERE video_id = :video ORDER BY sort_order ASC');
    $stmt->execute(['video' => $videoId]);
    return array_map('sanitizeVideoLink', $stmt->fetchAll());
}

function loadRelatedVideos(PDO $pdo, int $videoId, string $lang, string $fallback): array
{
    $sql = 'SELECT v.*, COALESCE(vt.title, vtf.title) AS title
        FROM videos v
        LEFT JOIN video_translations vt ON vt.video_id = v.id AND vt.lang = :lang
        LEFT JOIN video_translations vtf ON vtf.video_id = v.id AND vtf.lang = :fallback
        WHERE v.id != :video
        ORDER BY v.publish_date DESC
        LIMIT 3';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['video' => $videoId, 'lang' => $lang, 'fallback' => $fallback]);
    return array_map('sanitizeVideoRecord', $stmt->fetchAll());
}

function loadReviews(PDO $pdo, string $lang, string $fallback, ?int $limit = null): array
{
    $sql = 'SELECT r.*, COALESCE(rt.title, rtf.title) AS title,
            COALESCE(rt.excerpt, rtf.excerpt) AS excerpt
        FROM reviews r
        LEFT JOIN review_translations rt ON rt.review_id = r.id AND rt.lang = :lang
        LEFT JOIN review_translations rtf ON rtf.review_id = r.id AND rtf.lang = :fallback
        ORDER BY r.publish_date DESC';
    if ($limit !== null) {
        $sql .= ' LIMIT :limit';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lang', $lang, PDO::PARAM_STR);
    $stmt->bindValue(':fallback', $fallback, PDO::PARAM_STR);
    if ($limit !== null) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function loadReviewBySlug(PDO $pdo, string $slug, string $lang, string $fallback): ?array
{
    $sql = 'SELECT r.*, COALESCE(rt.title, rtf.title) AS title,
            COALESCE(rt.body, rtf.body) AS body
        FROM reviews r
        LEFT JOIN review_translations rt ON rt.review_id = r.id AND rt.lang = :lang
        LEFT JOIN review_translations rtf ON rtf.review_id = r.id AND rtf.lang = :fallback
        WHERE r.slug = :slug';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['slug' => $slug, 'lang' => $lang, 'fallback' => $fallback]);
    $review = $stmt->fetch();
    return $review ?: null;
}

function loadReviewsForVideo(PDO $pdo, int $videoId, string $lang, string $fallback): array
{
    $sql = 'SELECT r.slug, COALESCE(rt.title, rtf.title) AS title
        FROM review_video_map m
        INNER JOIN reviews r ON r.id = m.review_id
        LEFT JOIN review_translations rt ON rt.review_id = r.id AND rt.lang = :lang
        LEFT JOIN review_translations rtf ON rtf.review_id = r.id AND rtf.lang = :fallback
        WHERE m.video_id = :video';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['video' => $videoId, 'lang' => $lang, 'fallback' => $fallback]);
    return $stmt->fetchAll();
}

function loadVideosForReview(PDO $pdo, int $reviewId, string $lang, string $fallback): array
{
    $sql = 'SELECT v.slug, COALESCE(vt.title, vtf.title) AS title
        FROM review_video_map m
        INNER JOIN videos v ON v.id = m.video_id
        LEFT JOIN video_translations vt ON vt.video_id = v.id AND vt.lang = :lang
        LEFT JOIN video_translations vtf ON vtf.video_id = v.id AND vtf.lang = :fallback
        WHERE m.review_id = :review';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['review' => $reviewId, 'lang' => $lang, 'fallback' => $fallback]);
    return $stmt->fetchAll();
}

function loadPage(PDO $pdo, string $slug, string $lang, string $fallback): ?array
{
    $sql = 'SELECT p.slug, COALESCE(pt.title, ptf.title) AS title,
            COALESCE(pt.body, ptf.body) AS body
        FROM pages p
        LEFT JOIN page_translations pt ON pt.page_id = p.id AND pt.lang = :lang
        LEFT JOIN page_translations ptf ON ptf.page_id = p.id AND ptf.lang = :fallback
        WHERE p.slug = :slug';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['slug' => $slug, 'lang' => $lang, 'fallback' => $fallback]);
    $page = $stmt->fetch();
    return $page ?: null;
}

function buildUrl(string $route, array $params = []): string
{
    global $config;
    $pretty = !empty($config['site']['use_pretty_urls']);
    $query = http_build_query(array_merge(['page' => $route], $params));
    switch ($route) {
        case 'home':
            return $pretty ? '/' : '/index.php';
        case 'videos':
            return $pretty ? '/videos' : '/index.php?page=videos';
        case 'reviews':
            return $pretty ? '/reviews' : '/index.php?page=reviews';
        case 'video':
            if (!empty($params['slug'])) {
                return $pretty ? '/video/' . urlencode($params['slug']) : '/index.php?page=video&slug=' . urlencode($params['slug']);
            }
            return $pretty ? '/videos' : '/index.php?page=videos';
        case 'review':
            if (!empty($params['slug'])) {
                return $pretty ? '/review/' . urlencode($params['slug']) : '/index.php?page=review&slug=' . urlencode($params['slug']);
            }
            return $pretty ? '/reviews' : '/index.php?page=reviews';
        case 'page':
            if (!empty($params['slug'])) {
                if ($pretty && in_array($params['slug'], ['about', 'imprint', 'privacy'], true)) {
                    return '/' . $params['slug'];
                }
                return '/index.php?page=page&slug=' . urlencode($params['slug']);
            }
            return '/';
        case 'sitemap':
            return $pretty ? '/sitemap.xml' : '/index.php?page=sitemap';
        default:
            return '/index.php?' . $query;
    }
}

function formatDate(string $date): string
{
    $dt = new DateTime($date);
    return $dt->format('d M Y');
}

function sanitizeUrl(?string $url): ?string
{
    if ($url === null) {
        return null;
    }

    $clean = trim($url);
    if ($clean === '') {
        return null;
    }

    // Strip control characters and whitespace that may precede a protocol.
    $clean = preg_replace('/^[\p{C}\s]+/u', '', $clean);

    $parts = parse_url($clean);
    if ($parts === false || empty($parts['scheme'])) {
        return null;
    }

    $scheme = strtolower((string)$parts['scheme']);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return null;
    }

    return filter_var($clean, FILTER_VALIDATE_URL) ? $clean : null;
}

function sanitizeVideoRecord(array $video): array
{
    $video['primary_url'] = sanitizeUrl($video['primary_url'] ?? null);
    $video['thumbnail_url'] = sanitizeUrl($video['thumbnail_url'] ?? null);

    return $video;
}

function sanitizeVideoLink(array $link): array
{
    $link['url'] = sanitizeUrl($link['url'] ?? null);

    return $link;
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function buildMeta(string $route, array $viewData, array $config): array
{
    $baseTitle = 'HohnePeople';
    $description = 'Minimal artist site for HohnePeople.';
    $url = buildUrl('home');
    if ($route === 'video' && !empty($viewData['video'])) {
        $video = $viewData['video'];
        $baseTitle = $video['title'] . ' · HohnePeople';
        $description = strip_tags((string)$video['description']);
        $url = buildUrl('video', ['slug' => $video['slug']]);
    } elseif ($route === 'review' && !empty($viewData['review'])) {
        $review = $viewData['review'];
        $baseTitle = $review['title'] . ' · HohnePeople';
        $description = substr(strip_tags((string)$review['body']), 0, 140);
        $url = buildUrl('review', ['slug' => $review['slug']]);
    }
    return compact('baseTitle', 'description', 'url');
}

function renderIcon(string $key): string
{
    $icons = [
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23.5 6.5s-.2-1.6-.8-2.3c-.8-.8-1.7-.8-2.1-.9C17.1 3 12 3 12 3h-.1S6.9 3 3.4 3.3c-.5.1-1.3.1-2.1.9C.8 4.9.7 6.5.7 6.5S.5 8.3.5 10.1v1.7c0 1.8.2 3.6.2 3.6s.2 1.6.8 2.3c.8.8 1.9.8 2.4.9 1.8.2 7.6.3 7.6.3s5.1 0 8.6-.3c.4-.1 1.3-.1 2.1-.9.6-.7.8-2.3.8-2.3s.2-1.8.2-3.6v-1.7c0-1.8-.2-3.6-.2-3.6zM9.7 13.6V7.8l5.8 2.9-5.8 2.9z"/></svg>',
        'suno' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/><path fill="currentColor" d="M7 12a5 5 0 0 1 10 0 5 5 0 0 1-10 0zm2 0a3 3 0 1 0 6 0 3 3 0 0 0-6 0z"/></svg>',
        'soundcloud' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.5 10c-.3 0-.6 0-.9.1a4 4 0 0 0-7.8 1.4v.1a2.7 2.7 0 0 0-1.1-.2 2.7 2.7 0 0 0 0 5.3h9.8a3.3 3.3 0 0 0 0-6.7z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" ry="5" stroke="currentColor" fill="none"/><circle cx="12" cy="12" r="3.5" stroke="currentColor" fill="none"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 7.2a4.6 4.6 0 0 1-3-1.4v7.5a5.4 5.4 0 1 1-5.4-5.4c.2 0 .4 0 .6.1V4h3.7c.2 1.4 1.4 2.6 3 2.7z"/></svg>',
    ];
    return $icons[$key] ?? '';
}

function buildSitemap(PDO $pdo): string
{
    $urls = [buildUrl('home'), buildUrl('videos'), buildUrl('reviews'), buildUrl('page', ['slug' => 'about']), buildUrl('page', ['slug' => 'imprint']), buildUrl('page', ['slug' => 'privacy'])];
    $videoStmt = $pdo->query('SELECT slug FROM videos');
    foreach ($videoStmt->fetchAll(PDO::FETCH_COLUMN) as $slug) {
        $urls[] = buildUrl('video', ['slug' => $slug]);
    }
    $reviewStmt = $pdo->query('SELECT slug FROM reviews');
    foreach ($reviewStmt->fetchAll(PDO::FETCH_COLUMN) as $slug) {
        $urls[] = buildUrl('review', ['slug' => $slug]);
    }
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
    foreach ($urls as $loc) {
        $url = $xml->addChild('url');
        $url->addChild('loc', htmlspecialchars('' . getBaseOrigin() . $loc));
    }
    return $xml->asXML();
}

function getBaseOrigin(): string
{
    static $cached;
    if ($cached) {
        return $cached;
    }

    global $config;
    $configured = $config['site']['base_url'] ?? null;
    $normalizedConfigBase = normalizeBaseUrl($configured);
    if ($normalizedConfigBase !== null) {
        $cached = $normalizedConfigBase;
        return $cached;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $hostHeader = (string)($_SERVER['HTTP_HOST'] ?? '');
    $sanitizedHost = sanitizeHost($hostHeader);

    if ($sanitizedHost === null) {
        $serverName = (string)($_SERVER['SERVER_NAME'] ?? '');
        $sanitizedHost = sanitizeHost($serverName);
    }

    if ($sanitizedHost === null) {
        $sanitizedHost = 'localhost';
        $serverPort = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null;
        if ($serverPort && !in_array($serverPort, [80, 443], true)) {
            $sanitizedHost .= ':' . $serverPort;
        }
    }

    $cached = $scheme . '://' . $sanitizedHost;
    return $cached;
}

function normalizeBaseUrl(?string $url): ?string
{
    if ($url === null) {
        return null;
    }

    $trimmed = trim($url);
    if ($trimmed === '') {
        return null;
    }

    $parts = parse_url($trimmed);
    if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
        return null;
    }

    $scheme = strtolower($parts['scheme']);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return null;
    }

    $hostPort = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    $sanitizedHost = sanitizeHost($hostPort);
    if ($sanitizedHost === null) {
        return null;
    }

    $normalized = $scheme . '://' . $sanitizedHost;
    if (!empty($parts['path']) && $parts['path'] !== '/') {
        $normalized .= rtrim($parts['path'], '/');
    }
    return $normalized;
}

function sanitizeHost(string $host): ?string
{
    $clean = trim($host);
    if ($clean === '' || strpbrk($clean, "\r\n") !== false) {
        return null;
    }

    if (str_contains($clean, '://')) {
        return null;
    }

    $parsed = parse_url('//' . $clean);
    if ($parsed === false || empty($parsed['host'])) {
        return null;
    }

    $hostname = $parsed['host'];
    $port = $parsed['port'] ?? null;

    $isValidDomain = filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false || $hostname === 'localhost';
    $isValidIp = filter_var($hostname, FILTER_VALIDATE_IP) !== false;
    if (!$isValidDomain && !$isValidIp) {
        return null;
    }

    $normalizedHost = $hostname;
    if ($isValidIp && str_contains($hostname, ':')) {
        $normalizedHost = '[' . $hostname . ']';
    }

    if ($port !== null) {
        if ($port < 1 || $port > 65535) {
            return null;
        }
        return $normalizedHost . ':' . $port;
    }

    return $normalizedHost;
}

function buildSwitcherUrl(array $params): string
{
    $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    $query = $_GET;
    foreach ($params as $key => $value) {
        $query[$key] = $value;
    }
    $queryString = http_build_query($query);
    return $currentPath . ($queryString ? '?' . $queryString : '');
}

function text(string $key, string $fallback = ''): string
{
    global $siteTexts;
    return $siteTexts[$key] ?? ($fallback ?: $key);
}

