<?php

declare(strict_types=1);

return [
    'label' => 'Page',
    'plural_label' => 'Pages',
    'navigation_group' => 'Content',
    'subheading' => 'Manage static content pages such as About Us, Contact, and FAQ.',

    'heading' => 'Heading',
    'heading_hint' => 'The page title shown to visitors, e.g. "About Us".',
    'slug' => 'Slug',
    'slug_hint' => 'Auto-generated from the heading. Used as the URL path, e.g. /about-us.',
    'content' => 'Content',
    'content_hint' => 'The main body content of the page, edited with the rich text editor.',
    'title' => 'SEO Title',
    'title_hint' => 'SEO page title shown in the browser tab and search results. Leave blank to use the heading.',
    'description' => 'Meta Description',
    'description_hint' => 'Meta description shown in search engine results. Aim for 150-160 characters.',
    'no_index' => 'No Index',
    'no_index_hint' => 'When enabled, search engines are instructed not to index this page.',
    'canonical' => 'Canonical URL',
    'canonical_hint' => 'Canonical URL to prevent duplicate content issues. Leave blank unless this page should point to another URL.',
    'status' => 'Status',
    'status_hint' => 'Published: visible on the frontend. Draft: hidden. Scheduled: published on the date set below. Deleted: removed from listings.',
    'published_at' => 'Publish At',
    'published_at_hint' => 'The date and time this page will be made public. Only applies when status is Scheduled.',
    'image' => 'Image',
    'path' => 'Image File',
    'alt_text' => 'Alt Text',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'status_deleted' => 'Deleted',
    'status_published' => 'Published',
    'status_draft' => 'Draft',
    'status_scheduled' => 'Scheduled',
];
