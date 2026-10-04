<?php
/**
 * SchemaJsonLd — dependency-free JSON-LD (schema.org) builder for WordPress.
 *
 * Generates valid schema.org structured data (Article, FAQPage, BreadcrumbList,
 * Product, Organization, WebSite) without a heavyweight SEO plugin.
 *
 * Usage:
 *   $schema = new SchemaJsonLd();
 *   $schema->addOrganization('Acme', 'https://example.com', 'https://example.com/logo.png')
 *           ->addArticle('Hello world', 'https://example.com/hello', '2026-10-04', 'Azan Umer');
 *   echo $schema->render(); // prints <script type="application/ld+json">…</script>
 *
 * Requires PHP 7.2+. No dependencies.
 */
declare(strict_types=1);

class SchemaJsonLd
{
    /** @var array<int, array<string, mixed>> */
    private $entities = array();

    /**
     * Add any raw schema.org entity (escape hatch for types not covered below).
     *
     * @param array<string, mixed> $entity Must include '@type'.
     */
    public function raw(array $entity)
    {
        $this->entities[] = $entity;
        return $this;
    }

    public function addOrganization($name, $url, $logo = '')
    {
        $org = array(
            '@type' => 'Organization',
            'name'  => $name,
            'url'   => $url,
        );
        if ($logo !== '') {
            $org['logo'] = $logo;
        }
        $this->entities[] = $org;
        return $this;
    }

    public function addWebSite($name, $url, $searchUrlTemplate = '')
    {
        $site = array(
            '@type' => 'WebSite',
            'name'  => $name,
            'url'   => $url,
        );
        if ($searchUrlTemplate !== '') {
            $site['potentialAction'] = array(
                '@type'       => 'SearchAction',
                'target'      => $searchUrlTemplate, // e.g. https://example.com/?s={search_term_string}
                'query-input' => 'required name=search_term_string',
            );
        }
        $this->entities[] = $site;
        return $this;
    }

    public function addArticle($headline, $url, $datePublished, $author = '', $image = '', $description = '', $dateModified = '')
    {
        $article = array(
            '@type'         => 'Article',
            'headline'      => $headline,
            'url'           => $url,
            'datePublished' => $datePublished,
        );
        if ($dateModified !== '') {
            $article['dateModified'] = $dateModified;
        }
        if ($author !== '') {
            $article['author'] = array('@type' => 'Person', 'name' => $author);
        }
        if ($image !== '') {
            $article['image'] = $image;
        }
        if ($description !== '') {
            $article['description'] = $description;
        }
        $this->entities[] = $article;
        return $this;
    }

    /**
     * @param array<int, array{question: string, answer: string}> $pairs
     */
    public function addFaq(array $pairs)
    {
        $questions = array();
        foreach ($pairs as $pair) {
            if (empty($pair['question']) || empty($pair['answer'])) {
                continue;
            }
            $questions[] = array(
                '@type'          => 'Question',
                'name'           => $pair['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $pair['answer'],
                ),
            );
        }
        if (!empty($questions)) {
            $this->entities[] = array(
                '@type'           => 'FAQPage',
                'mainEntity'      => $questions,
            );
        }
        return $this;
    }

    /**
     * @param array<int, array{name: string, url?: string}> $items Ordered crumb list.
     */
    public function addBreadcrumb(array $items)
    {
        $elements = array();
        $position = 1;
        foreach ($items as $item) {
            if (empty($item['name'])) {
                continue;
            }
            $crumb = array(
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $item['name'],
            );
            if (!empty($item['url'])) {
                $crumb['item'] = $item['url'];
            }
            $elements[] = $crumb;
            $position++;
        }
        if (!empty($elements)) {
            $this->entities[] = array(
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $elements,
            );
        }
        return $this;
    }

    public function addProduct($name, $image, $description, $price, $currency, $availability = 'InStock', $ratingValue = null, $reviewCount = null)
    {
        $product = array(
            '@type'       => 'Product',
            'name'        => $name,
            'image'       => $image,
            'description' => $description,
            'offers'      => array(
                '@type'         => 'Offer',
                'price'         => $price,
                'priceCurrency' => $currency,
                'availability'  => 'https://schema.org/' . $availability,
            ),
        );
        if ($ratingValue !== null && $reviewCount !== null) {
            $product['aggregateRating'] = array(
                '@type'       => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'reviewCount' => $reviewCount,
            );
        }
        $this->entities[] = $product;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        if (count($this->entities) === 1) {
            return array_merge(array('@context' => 'https://schema.org'), $this->entities[0]);
        }
        return array(
            '@context' => 'https://schema.org',
            '@graph'   => $this->entities,
        );
    }

    public function toJson()
    {
        $json = json_encode(
            $this->toArray(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        return $json === false ? '{}' : $json;
    }

    /**
     * Returns the full <script> tag, safe to echo in wp_head.
     */
    public function render()
    {
        return '<script type="application/ld+json">' . "\n"
            . $this->toJson() . "\n"
            . '</script>';
    }
}
