# WP Schema JSON-LD

A tiny, dependency-free PHP class that generates valid [schema.org](https://schema.org) JSON-LD structured data for WordPress — Article, FAQPage, BreadcrumbList, Product, Organization, WebSite. No SEO plugin bloat required.

## Why

Rich results (FAQ snippets, breadcrumbs, product info) in Google search come from structured data. Most SEO plugins ship megabytes of code to do this; this class does it in one file.

## Usage

```php
require_once 'src/SchemaJsonLd.php';

$schema = new SchemaJsonLd();
$schema
    ->addOrganization('Acme Co', 'https://example.com', 'https://example.com/logo.png')
    ->addArticle(
        'Hello world',
        'https://example.com/hello',
        '2026-10-04',          // datePublished (ISO 8601)
        'Azan Umer',           // author
        'https://example.com/hello.jpg',
        'A short description'
    )
    ->addFaq([
        ['question' => 'What is JSON-LD?', 'answer' => 'A way to embed structured data in HTML.'],
        ['question' => 'Is it required?',  'answer' => 'No, but it enables rich search results.'],
    ])
    ->addBreadcrumb([
        ['name' => 'Home', 'url' => 'https://example.com/'],
        ['name' => 'Blog', 'url' => 'https://example.com/blog/'],
        ['name' => 'Hello world'],
    ]);

echo $schema->render(); // <script type="application/ld+json">…</script>
```

See `examples/wordpress-example.php` for a ready `wp_head` hook that outputs Article + Breadcrumb schema on single posts.

## API

| Method | Schema type |
|---|---|
| `addOrganization($name, $url, $logo)` | Organization |
| `addWebSite($name, $url, $searchUrlTemplate)` | WebSite (+ SearchAction sitelinks box) |
| `addArticle($headline, $url, $datePublished, $author, $image, $description, $dateModified)` | Article |
| `addFaq([['question'=>..,'answer'=>..], …])` | FAQPage |
| `addBreadcrumb([['name'=>..,'url'=>..], …])` | BreadcrumbList |
| `addProduct($name, $image, $description, $price, $currency, $availability, $rating, $reviews)` | Product |
| `raw($entity)` | Any schema.org type, as an array |
| `toJson()` / `render()` | JSON string / full `<script>` tag |

Multiple entities are combined into a single `@graph` automatically.

Validate your output with Google's [Rich Results Test](https://search.google.com/test/rich-results).

MIT licensed.
