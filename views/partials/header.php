<?php
$themeHref = '/' . ltrim($currentThemeData['css_file'], '/');
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= h($meta['baseTitle'] ?? 'HohnePeople') ?></title>
    <meta name="description" content="<?= h($meta['description'] ?? '') ?>">
    <meta property="og:title" content="<?= h($meta['baseTitle'] ?? 'HohnePeople') ?>">
    <meta property="og:description" content="<?= h($meta['description'] ?? '') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= h(getBaseOrigin() . ($meta['url'] ?? '')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?= h(getBaseOrigin() . ($meta['url'] ?? '')) ?>">
    <link rel="stylesheet" href="/themes/base.css">
    <link rel="stylesheet" href="<?= h($themeHref) ?>">
</head>
<body class="theme-<?= h($currentTheme) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<div class="site-shell">
    <header class="site-header">
        <div class="inner">
            <a class="brand" href="<?= h(buildUrl('home')) ?>">
                <img src="/assets/logo.svg" alt="HohnePeople" width="36" height="36" loading="lazy">
                <div>
                    <h1><?= h(text('site.name', 'HohnePeople')) ?></h1>
                    <span><?= h(text('site.slogan', 'All styles, one attitude - Welcome to the mix!')) ?></span>
                </div>
            </a>
            <nav class="nav-links" aria-label="Primary">
                <?php foreach ($headerNav as $item): ?>
                    <?php
                    $navSlug = $item['slug'];
                    $href = $item['path'];
                    $isActive = false;
                    switch ($navSlug) {
                        case 'home':
                            $href = buildUrl('home');
                            $isActive = $route === 'home';
                            break;
                        case 'videos':
                            $href = buildUrl('videos');
                            $isActive = $route === 'videos' || $route === 'video';
                            break;
                        case 'reviews':
                            $href = buildUrl('reviews');
                            $isActive = $route === 'reviews' || $route === 'review';
                            break;
                        case 'about':
                            $href = buildUrl('page', ['slug' => 'about']);
                            $isActive = $route === 'page' && ($_GET['slug'] ?? '') === 'about';
                            break;
                        default:
                            $isActive = false;
                            break;
                    }
                    ?>
                    <a class="<?= !empty($isActive) ? 'active' : '' ?>" href="<?= h($href) ?>"><?= h($item['label']) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="nav-meta">
                <div class="meta-switchers" aria-label="Switch language">
                    <?php foreach ($supportedLangs as $code): ?>
                        <a class="pill <?= $lang === $code ? 'active' : '' ?>" href="<?= h(buildSwitcherUrl(['lang' => $code])) ?>"><?= strtoupper($code) ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="meta-switchers" aria-label="Switch theme">
                    <?php foreach ($themes as $slug => $theme): ?>
                        <a class="pill <?= $currentTheme === $slug ? 'active' : '' ?>" href="<?= h(buildSwitcherUrl(['theme' => $slug])) ?>"><?= h($theme['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>
    <main id="main">
