<?php
$apiBase = rtrim(getenv('BLOG_API_BASE_URL') ?: 'https://lending-institution-exception-welcome.trycloudflare.com', '/'); // TEMPORARY tunnel to the blog backend -- replace with the permanent API URL
$currentHost = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
$siteHost = 'www.mmwmachine.com'; // the website this blog page belongs to
if (!in_array(strtolower($currentHost), ['localhost', '127.0.0.1', '::1'], true)) $currentHost = $siteHost;
$domain = preg_replace('/^www\./i', '', strtolower($currentHost));
$websiteId = preg_replace('/[^a-f0-9]/i', '', $_GET['websiteId'] ?? '');
$slug = preg_replace('/[^a-z0-9-]/i', '', $_GET['slug'] ?? '');
// Local dev: the blog API only knows the registered production domain.
if (in_array($domain, ['localhost', '127.0.0.1', '::1'], true)) {
    $domain = getenv('BLOG_LOCAL_DOMAIN') ?: 'localhost/mmw';
    $apiBase = rtrim(getenv('BLOG_LOCAL_API_BASE_URL') ?: 'http://localhost:3000/', '/');
}
// Site may live in a subfolder locally (e.g. /mmw/); empty in production.
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if (!$slug) {
    header('Location: ' . $basePath . '/blog/', true, 302);
    exit;
}
function api_get($url)
{
    if (function_exists('curl_init')) {
        $c = curl_init($url);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12, CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        $b = curl_exec($c);
        $s = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        return $s >= 200 && $s < 300 ? json_decode($b, true) : null;
    }
    $b = @file_get_contents($url);
    return $b === false ? null : json_decode($b, true);
}
function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function clean_domain($value)
{
    return preg_replace('/^www\./i', '', strtolower(trim(preg_replace('#^https?://#i', '', rtrim((string)$value, '/')))));
}
$detail = $slug ? api_get($apiBase . '/api/blogs/public/' . rawurlencode(clean_domain($domain)) . '/' . rawurlencode($slug)) : null;
$site = is_array($detail) ? ($detail['website'] ?? null) : null;
$websiteId = $site['_id'] ?? $websiteId;
$blog = is_array($detail) ? ($detail['blog'] ?? null) : null;
$recentFeed = api_get($apiBase . '/api/blogs/public/' . rawurlencode(clean_domain($domain)) . '?page=1&limit=5');
$recentBlogs = is_array($recentFeed) ? ($recentFeed['blogs'] ?? []) : [];
if (!$blog) {
    http_response_code(404);
}
$title = $blog['metaTitle'] ?? $blog['title'] ?? 'Blog not found';
$description = $blog['metaDescription'] ?? $blog['excerpt'] ?? '';
$image = $blog['ogImage'] ?? $blog['featuredImage'] ?? ($blog['images'][0] ?? '');
$canonical = $blog['canonicalUrl'] ?? ('https://' . strtolower($currentHost) . '/blog/' . rawurlencode($slug) . '/');
$robots = ($blog['robotsIndex'] ?? 'index') . ',' . ($blog['robotsFollow'] ?? 'follow');
$faqSchema = [];
foreach (($blog['faqs'] ?? []) as $faq) {
    if (!empty($faq['question']) && !empty($faq['answer'])) $faqSchema[] = ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]];
}
// Page values read by the site's header.php (it prints the doctype, <head>, menu and <body>).
$pageTitle = $title;
$pageDescription = $description;
$canonicalUrl = $canonical;
?>
<?php if (file_exists(__DIR__ . '/header.php')) include_once __DIR__ . '/header.php'; ?>
<?php if ($faqSchema): ?><script type="application/ld+json">
        <?= json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqSchema], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script><?php endif; ?>
<style>
    :root {
        --red: #bd001b;
        --dark: #320008;
        --soft: #fff1f3;
        --muted: #706769
    }

    * {
        box-sizing: border-box
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        color: #292326;
        background: #fff
    }

    a {
        color: var(--red)
    }

    .nav {
        height: 72px;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1120px;
        margin: auto
    }

    .logo {
        font-size: 26px;
        font-weight: 900;
        text-decoration: none;
        color: var(--dark)
    }

    .logo span {
        color: var(--red)
    }

    .hero {
        background: linear-gradient(115deg, #fff0f2, #ffd7dc);
        padding: 72px 24px 110px;
        text-align: center
    }

    .hero .category {
        color: var(--red);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase
    }

    .hero h1 {
        max-width: 920px;
        margin: 16px auto;
        font: 600 clamp(40px, 7vw, 72px)/1.06 Georgia
    }

    .byline {
        color: var(--muted);
        font-size: 13px
    }

    .article {
        max-width: 860px;
        margin: -62px auto 70px;
        padding: 0 24px
    }

    .featured {
        width: 100%;
        max-height: 520px;
        object-fit: cover;
        border-radius: 17px;
        box-shadow: 0 15px 45px #40000818
    }

    .content {
        font: 17px/1.85 Georgia, serif;
        padding: 42px 28px
    }

    .content h2,
    .content h3 {
        color: var(--dark);
        line-height: 1.2
    }

    .content h2 {
        font-size: 34px;
        margin-top: 42px
    }

    .content img {
        max-width: 100%;
        height: auto;
        border-radius: 11px
    }

    .content table {
        width: 100%;
        border-collapse: collapse
    }

    .content td,
    .content th {
        border: 1px solid #ddd;
        padding: 10px
    }

    .video {
        position: relative;
        padding-top: 56.25%;
        margin: 30px 0
    }

    .video iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0
    }

    .faq,
    .cta,
    .related {
        max-width: 860px;
        margin: 35px auto;
        padding: 0 24px
    }

    .faq h2,
    .related h2 {
        font: 600 34px Georgia
    }

    .faq details {
        border-top: 1px solid #eadbdd;
        padding: 17px 0
    }

    .faq summary {
        font-weight: 700;
        cursor: pointer
    }

    .faq p {
        color: var(--muted);
        line-height: 1.7
    }

    .cta {
        padding: 38px;
        border-radius: 15px;
        background: linear-gradient(110deg, #70000d, #cc001d);
        color: #fff;
        text-align: center
    }

    .cta h2 {
        font: 600 34px Georgia;
        margin: 0 0 18px
    }

    .cta a {
        display: inline-block;
        background: #fff;
        color: var(--red);
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 800
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px
    }

    .related-grid a {
        padding: 17px;
        border: 1px solid #eadbdd;
        border-radius: 10px;
        text-decoration: none;
        color: #302529;
        font-weight: 700
    }

    @media(max-width:600px) {
        .hero {
            padding-top: 45px
        }

        .content {
            padding: 30px 5px
        }

        .related-grid {
            grid-template-columns: 1fr
        }
    }

    /* Premium MMW article layout */
    body {
        background: #fbfaf9;
        color: #14283b
    }

    .nav {
        height: 78px;
        position: relative
    }

    .nav:before {
        content: "MMW INSIGHTS";
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        letter-spacing: .16em;
        font-weight: 800;
        color: #8c7377
    }

    .hero {
        position: relative;
        background: linear-gradient(120deg, #fff1f3, #fff 55%, #f5d8dc);
        padding: 75px 24px 125px;
        border-top: 1px solid #f0dce0
    }

    .hero:after {
        content: "";
        position: absolute;
        width: 250px;
        height: 250px;
        border-radius: 50%;
        background: #c5001b0b;
        right: 8%;
        top: 25px
    }

    .hero h1 {
        position: relative;
        z-index: 1;
        color: #062c50;
        max-width: 980px;
        font-size: clamp(40px, 6vw, 70px)
    }

    .hero-excerpt {
        position: relative;
        z-index: 1;
        max-width: 720px;
        margin: 22px auto 0;
        color: #526779;
        font-size: 16px;
        line-height: 1.7
    }

    .article {
        max-width: 960px;
        margin-top: -72px
    }

    .featured {
        display: block;
        max-height: 560px;
        padding: 14px;
        background: #fff;
        object-fit: contain;
        border: 1px solid #e5d9db;
        border-radius: 20px
    }

    .content {
        margin-top: 24px;
        padding: 50px 65px;
        background: #fff;
        border: 1px solid #eee4e5;
        border-radius: 18px;
        box-shadow: 0 15px 38px #42000808;
        color: #263b4b;
        font: 17px/1.9 Arial, sans-serif
    }

    .content>p:first-child {
        font-size: 19px;
        color: #1a3347
    }

    .content h2 {
        font: 700 34px/1.2 Georgia;
        color: #072e52;
        padding-top: 10px
    }

    .content h3 {
        font: 700 25px/1.25 Georgia;
        color: #8f0013
    }

    .content blockquote {
        margin: 32px 0;
        padding: 22px 26px;
        border-left: 5px solid #c4001b;
        background: #fff2f4;
        color: #5c3339
    }

    .content a {
        font-weight: 700
    }

    .content ul,
    .content ol {
        padding-left: 24px
    }

    .content li {
        margin: 8px 0
    }

    .content img {
        display: block;
        margin: 30px auto;
        border: 1px solid #eee;
        padding: 6px
    }

    .faq,
    .related {
        max-width: 960px;
        padding: 30px 45px;
        border: 1px solid #eadfe1;
        border-radius: 17px;
        background: #fff
    }

    .faq details:first-of-type {
        border-top: 0
    }

    .faq summary {
        color: #092f52;
        font-size: 15px
    }

    .cta {
        max-width: 912px;
        margin-top: 40px;
        padding: 48px;
        background: linear-gradient(115deg, #75000e, #cf001e);
        box-shadow: 0 16px 40px #76001122
    }

    .related-grid a {
        background: #fff7f8;
        transition: .2s
    }

    .related-grid a:hover {
        border-color: #bd001b;
        transform: translateY(-2px)
    }

    @media(max-width:650px) {
        .nav:before {
            display: none
        }

        .hero {
            padding: 48px 18px 95px
        }

        .article {
            padding: 0 14px
        }

        .featured {
            padding: 7px
        }

        .content {
            padding: 30px 20px
        }

        .content h2 {
            font-size: 28px
        }

        .faq,
        .related {
            margin-left: 14px;
            margin-right: 14px;
            padding: 24px 18px
        }

        .cta {
            margin-left: 14px;
            margin-right: 14px
        }
    }

    /* Compact, readable article page */
    .hero {
        max-width: none;
        width: 100%;
        margin: 0;
        padding: 42px 24px 48px;
        background: linear-gradient(110deg, #fff1f3, #fff 52%, #f5d8dc);
        border-bottom: 1px solid #efdadd
    }

    .hero:after {
        display: none
    }

    .hero h1 {
        max-width: 760px;
        font: 700 clamp(28px, 4vw, 40px)/1.2 Arial, sans-serif;
        margin: 10px auto
    }

    .hero-excerpt {
        max-width: 680px;
        margin-top: 14px;
        font-size: 14px;
        line-height: 1.55
    }

    .article {
        width: calc(100% - 40px);
        max-width: 980px;
        margin: 34px auto 0
    }

    .featured {
        width: 100%;
        max-height: 520px;
        border-radius: 3px;
        padding: 0;
        object-fit: cover
    }

    .content {
        margin-top: 20px;
        padding: 26px 34px;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1.75;
        box-shadow: none
    }

    .content>p:first-child {
        font-size: 17px
    }

    .content h2 {
        font-size: 29px
    }

    .content h3 {
        font-size: 22px
    }

    @media(max-width:650px) {
        .hero {
            padding: 32px 18px 55px
        }

        .hero h1 {
            font-size: 34px
        }

        .article {
            margin-top: -24px
        }

        .content {
            padding: 26px 18px
        }
    }

    .article-layout {
        max-width: 1200px;
        margin: 0 auto;
        padding: 42px 20px 70px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 32px;
        align-items: start
    }

    .article-main .hero {
        width: 100%;
        margin: 0 0 24px;
        padding: 0 0 22px;
        text-align: left;
        background: transparent;
        border-bottom: 1px solid #eadde0
    }

    .article-main .hero h1 {
        margin: 8px 0 10px;
        max-width: none;
        font-size: clamp(30px, 4vw, 45px);
        text-align: left
    }

    .article-main .hero-excerpt {
        max-width: none;
        margin: 12px 0 0;
        text-align: left
    }

    .article-main .byline {
        text-align: left
    }

    .article-main .article {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0
    }

    .article-main .featured {
        width: 100%;
        max-height: 540px;
        object-fit: cover
    }

    .article-main .content {
        width: 100%;
        margin-top: 22px
    }

    .side-panel {
        position: sticky;
        top: 20px;
        padding: 22px;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        box-shadow: 0 8px 26px #0000000b
    }

    .side-panel h3 {
        margin: 0 0 14px;
        padding-bottom: 10px;
        border-bottom: 2px solid #bd001b;
        color: #172f43;
        font-size: 13px;
        text-transform: uppercase
    }

    .side-search {
        display: flex;
        margin-bottom: 26px
    }

    .side-search input {
        min-width: 0;
        flex: 1;
        height: 40px;
        padding: 0 11px;
        border: 1px solid #ddd;
        border-right: 0
    }

    .side-search button {
        width: 42px;
        border: 0;
        background: #bd001b;
        color: #fff;
        font-size: 17px
    }

    .recent-post {
        display: grid;
        grid-template-columns: 84px minmax(0, 1fr);
        gap: 11px;
        align-items: center;
        padding: 11px 0;
        border-bottom: 1px solid #eee;
        color: #1c3042;
        text-decoration: none
    }

    .recent-post img {
        width: 84px;
        height: 58px;
        object-fit: cover
    }

    .recent-post strong {
        display: block;
        font-size: 12px;
        line-height: 1.35
    }

    .quote-box {
        margin-top: 26px;
        padding: 18px;
        background: #fff3f5;
        border-left: 4px solid #bd001b
    }

    .quote-box p {
        font-size: 13px;
        line-height: 1.5
    }

    .quote-box a {
        display: block;
        padding: 11px;
        background: #bd001b;
        color: #fff;
        text-align: center;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase
    }

    @media(max-width:900px) {
        .article-layout {
            grid-template-columns: 1fr
        }

        .side-panel {
            position: static
        }
    }

    @media(max-width:640px) {
        .article-layout {
            padding: 25px 14px 50px
        }

        .article-main .hero h1 {
            font-size: 31px
        }
    }
</style>
<?php if (!$blog): ?><main class="hero">
        <h1>Blog not found</h1>
        <p>Check the URL or return to all blogs.</p>
    </main><?php else: ?><section class="page-header">
        <div class="page-header__bg" style="background-image: url(assets/img/banner/about-page-banner.webp);"></div>
        <div class="container">
            <div class="page-header__inner">
                <h3>Blog</h3>
                <div class="thm-breadcrumb__inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="index.php">Home</a></li>
                        <li><span class="icon-arrow-left"></span></li>
                        <li><a href="blog/">Blog</a></li>
                        <li><span class="icon-arrow-left"></span></li>
                        <li><?= e(mb_strimwidth($blog['title'], 0, 40, '...')) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <div class="article-layout">
        <main class="article-main">
            <header class="hero"><span class="category"><?= e($blog['category']['name'] ?? 'Industrial Insights') ?></span>
                <h1><?= e($blog['title']) ?></h1>
                <div class="byline">By <?= e($blog['authorName'] ?? 'MMW Team') ?> · <?= e(date('d F Y', strtotime($blog['publishedAt'] ?? $blog['createdAt']))) ?></div>
                <?php if (!empty($blog['excerpt'])): ?><p class="hero-excerpt"><?= e($blog['excerpt']) ?></p><?php endif; ?>
            </header>
            <article class="article"><?php if ($blog['featuredImage'] ?? ''): ?><img class="featured" src="<?= e($blog['featuredImage']) ?>" alt="<?= e($blog['featuredImageAlt'] ?? $blog['title']) ?>"><?php endif; ?><div class="content"><?= $blog['content'] ?></div><?php if ($blog['youtubeUrl'] ?? ''): $vid = preg_replace('~.*(?:youtu.be/|v=|embed/)([^?&/]+).*~', '$1', $blog['youtubeUrl']); ?><div class="video"><iframe src="https://www.youtube.com/embed/<?= e($vid) ?>" allowfullscreen></iframe></div><?php endif; ?></article>
            <?php if (!empty($blog['faqs'])): ?><section class="faq">
                    <h2>Frequently Asked Questions</h2><?php foreach ($blog['faqs'] as $faq): ?><details>
                            <summary><?= e($faq['question']) ?></summary>
                            <p><?= nl2br(e($faq['answer'])) ?></p>
                        </details><?php endforeach; ?>
                </section><?php endif; ?>
            <?php if (!empty($blog['relatedProducts']) || !empty($blog['relatedBlogs'])): ?><section class="related">
                    <h2>Related Resources</h2>
                    <div class="related-grid"><?php foreach (($blog['relatedProducts'] ?? []) as $item): ?><a href="#"><?= e($item['name'] ?? 'Product') ?></a><?php endforeach; ?><?php foreach (($blog['relatedBlogs'] ?? []) as $item): ?><a href="<?= e($basePath) ?>/blog/<?= e(rawurlencode($item['slug'] ?? '')) ?>/"><?= e($item['title'] ?? 'Blog') ?></a><?php endforeach; ?></div>
                </section><?php endif; ?>
            <?php if (!empty($blog['cta']['label'])): ?><section class="cta">
                    <h2>Ready to discuss your requirement?</h2><a href="<?= e($blog['cta']['url'] ?? '/contact-us.php') ?>"><?= e($blog['cta']['label']) ?></a>
                </section><?php endif; ?>
        </main>
        <aside class="side-panel">
            <h3>Search</h3>
            <form class="side-search" action="<?= e($basePath) ?>/blog/" method="get"><input type="search" name="q" placeholder="Search posts"><button aria-label="Search">⌕</button></form>
            <h3>Recent Posts</h3><?php foreach ($recentBlogs as $recent): if (($recent['slug'] ?? '') === $slug) continue;
                                        $ri = $recent['featuredImage'] ?? ($recent['images'][0] ?? ''); ?><a class="recent-post" href="<?= e($basePath) ?>/blog/<?= e(rawurlencode($recent['slug'] ?? '')) ?>/"><?php if ($ri): ?><img src="<?= e($ri) ?>" alt=""><?php endif; ?><strong><?= e($recent['title'] ?? 'Blog') ?></strong></a><?php endforeach; ?><div class="quote-box">
                <h3>Request a Quote</h3>
                <p>Discuss your printing and converting machine requirement with our team.</p><a href="contact.php">Contact Us</a>
            </div>
        </aside>
    </div><?php endif; ?>
<?php if (file_exists(__DIR__ . '/footer.php')) include_once __DIR__ . '/footer.php'; ?>