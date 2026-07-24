// Build a schema.org BreadcrumbList from breadcrumb items. Each item that has a
// url gets an absolute `item` (origin + url); the trailing crumb usually has no
// url, which Google allows for the current page.
export function breadcrumbJsonLd(items, origin = '') {
    return {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: items.map((item, index) => {
            const entry = {
                '@type': 'ListItem',
                position: index + 1,
                name: item.heading,
            };

            if (item.url) {
                entry.item = `${origin}${item.url}`;
            }

            return entry;
        }),
    };
}
