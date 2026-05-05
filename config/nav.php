<?php

return [
    'main' => [
        ['label' => 'Home',     'url' => '/'],
        ['label' => 'Rooms',    'url' => '/rooms'],
        ['label' => 'Services', 'url' => '/services'],
        ['label' => 'FAQs',     'url' => '/faqs'],
        ['label' => 'Contact',  'url' => '/contact'],
    ],

    'footer' => [
        [
            'title' => 'Explore',
            'links' => [
                ['label' => 'About',    'url' => '/about'],
                ['label' => 'Rooms',    'url' => '/rooms'],
                ['label' => 'FAQs',     'url' => '/faqs'],
                ['label' => 'Contact',  'url' => '/contact'],
            ],
        ],
        [
            'title' => 'Support',
            'links' => [
                ['label' => 'Privacy Policy', 'url' => '/privacy'],
                ['label' => 'Terms of Service','url' => '/terms'],
            ],
        ],
    ],

    'social' => [
        [
            'label' => 'Facebook',
            'url'   => 'https://facebook.com/',
            'icon'  => '<svg class="w-4 h-4 fill-current text-gray-400" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.5 9.9v-7h-2.3v-2.9h2.3V9.7c0-2.3 1.4-3.6 3.5-3.6 1 0 2 .1 2 .1v2.2h-1.1c-1.1 0-1.4.7-1.4 1.4v1.7h2.4l-.4 2.9h-2v7A10 10 0 0022 12z"/></svg>',
        ],
        [
            'label' => 'Instagram',
            'url'   => 'https://instagram.com/',
            'icon'  => '<svg class="w-4 h-4 fill-current text-gray-400" viewBox="0 0 24 24"><path d="M7 2C4.8 2 3 3.8 3 6v12c0 2.2 1.8 4 4 4h10c2.2 0 4-1.8 4-4V6c0-2.2-1.8-4-4-4H7zm10 2c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H7c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h10zm-5 3.5A5.5 5.5 0 1017.5 13 5.5 5.5 0 0012 7.5zm0 9A3.5 3.5 0 1115.5 13 3.5 3.5 0 0112 16.5zm5.7-9.9a1.3 1.3 0 11-1.3 1.3 1.3 1.3 0 011.3-1.3z"/></svg>',
        ],
    ],
];
