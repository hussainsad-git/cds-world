<?php

return [
    'encoding' => 'UTF-8',
    'finalize' => true,
    'ignoreNonStrings' => false,
    'cachePath' => storage_path('app/purifier'),
    'cacheFileMode' => 0755,
    'settings' => [
        // Rich-text from the admin editors: structural + text formatting only. No scripts, iframes or inline styles.
        'default' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'h2[id],h3[id],h4,p,br,hr,strong,b,em,i,u,s,sub,sup,blockquote,ul,ol,li,a[href|title|target|rel],img[src|alt|title|width|height],table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],pre,code',
            'HTML.TargetBlank' => true,
            'HTML.TargetNoopener' => true,
            'CSS.AllowedProperties' => '',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true],
        ],
        // Admin-authored popup HTML: same rules.
        'popup' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'h2,h3,p,br,strong,b,em,i,u,ul,ol,li,a[href|title|target|rel],img[src|alt|width|height]',
            'HTML.TargetBlank' => true,
            'HTML.TargetNoopener' => true,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true],
        ],
    ],
    'custom_definition' => null,
    'custom_attributes' => [],
    'custom_elements' => [],
];
