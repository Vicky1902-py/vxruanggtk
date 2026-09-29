{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{{ url('/') }}</loc>
    <lastmod>{{ now()->toAtomString() }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>{{ route('login') }}</loc>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
  @foreach ($pages as $page)
    <url>
      <loc>{{ route('page.show', $page->slug) }}</loc>
      <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.7</priority>
    </url>
  @endforeach
  @foreach ($schools as $school)
    <url>
      <loc>{{ url('/') }}/?school={{ $school->subdomain }}</loc>
      <lastmod>{{ $school->updated_at->toAtomString() }}</lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.6</priority>
    </url>
  @endforeach
</urlset>
