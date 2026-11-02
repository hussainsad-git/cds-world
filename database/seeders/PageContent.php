<?php

namespace Database\Seeders;

/**
 * Initial CMS copy. Editable in the admin panel; the seeder never overwrites existing pages.
 * Facts come only from the live cdsworld.org site (see CLAUDE.md). Policy/legal text needs owner review.
 */
class PageContent
{
    public static function all(): array
    {
        return array_merge(self::home(), self::about(), self::listings(), self::utility(), self::policies());
    }

    private static function rt(string $html): array
    {
        return ['html' => $html];
    }

    private static function home(): array
    {
        $focus = [
            ['Geopolitics', 'Analysis of shifting power dynamics and their implications for Pakistan and the wider region.'],
            ['Economy & Energy', 'Research on economic development, energy security and the policy choices that shape growth.'],
            ['Regional Security', 'Perspectives on security challenges, peace and stability across South Asia and neighbouring regions.'],
            ['CPEC', 'Studies of the China-Pakistan Economic Corridor and regional connectivity.'],
            ['Climate Security', 'How climate change affects resources, livelihoods and national security.'],
            ['International Relations', "Commentary on Pakistan's foreign policy and its relations with regional and global partners."],
            ['Development & Governance', 'Work on institutional development, public policy and governance for sustainable development.'],
        ];

        return [[
            'slug' => 'home', 'title' => 'Home', 'template' => 'home',
            'seo' => ['CDS World | Think Tank for Pakistan Policy & Development', "CDS World is an independent think tank advancing research, national dialogue and policy ideas on Pakistan's stability, security, economy, energy and climate.", 'think tank Pakistan'],
            'sections' => [
                ['ticker', 'Top News', ['limit' => 6]],
                ['hero', 'Advancing Stability Through Developmental Research', [
                    'description' => 'A think tank that turns independent research, national dialogue and policy ideas into hope for a more stable and prosperous Pakistan.',
                    'cta_primary' => ['label' => 'Explore research', 'url' => '/articles'],
                    'cta_secondary' => ['label' => 'Upcoming events', 'url' => '/events'],
                    'show_stats' => true, 'background_type' => 'image',
                ]],
                ['research_focus', 'Research focus areas', [
                    'description' => 'Our work brings evidence and dialogue to the questions that matter most for Pakistan and its region.',
                    'items' => array_map(fn ($f) => ['title' => $f[0], 'description' => $f[1], 'url' => '/articles?category='.\Illuminate\Support\Str::slug($f[0])], $focus),
                ]],
                ['latest_articles', 'Latest research & analysis', ['description' => 'Recent publications from CDS World researchers and contributors.', 'limit' => 6, 'featured_first' => true]],
                ['events', 'Events', ['description' => 'Conferences, seminars and dialogues that bring policymakers, researchers and young people together.', 'limit' => 4]],
                ['videos', 'Interviews & video articles', ['description' => 'Interviews, discussions and recorded sessions.', 'limit' => 3]],
                ['media_coverage', 'CDS World in the media', ['description' => 'How our work and experts are covered by news outlets.', 'limit' => 4]],
                ['gallery', 'Photo gallery', ['description' => 'Moments from our events and activities.', 'limit' => 5]],
                ['about', 'About CDS World', [
                    'text' => 'CDS World is a non-governmental organization dedicated to youth engagement, national dialogue, and policy development.',
                    'link' => ['label' => 'Learn more about us', 'url' => '/about'],
                ]],
                ['team', 'People', ['description' => 'The people behind our research and dialogue.', 'groups' => ['board', 'leadership'], 'limit' => 5, 'home_only' => true]],
                ['newsletter', 'Stay informed', ['description' => 'Subscribe to receive new research, event announcements and updates from CDS World.']],
            ],
        ]];
    }

    private static function about(): array
    {
        $about = self::rt('<h2>Who we are</h2><p>CDS World is a non-governmental organization dedicated to youth engagement, national dialogue, and policy development. As a think tank, we turn independent research, national dialogue and policy ideas into a contribution toward a more stable and prosperous Pakistan.</p>');
        $mv = self::rt('<h2>Our mission</h2><p>To turn independent research, national dialogue and policy ideas into practical contributions toward a more stable and prosperous Pakistan.</p><h2>Our vision</h2><p>A stable, peaceful and prosperous Pakistan, shaped by evidence-based policy and an informed, engaged young generation.</p>');
        $obj = self::rt('<h2>Objectives</h2><ul><li>Produce independent, evidence-based research on Pakistan and regional and international issues.</li><li>Create space for national dialogue among policymakers, experts, academics and civil society.</li><li>Engage young people in public debate and policy thinking.</li><li>Support policy development with clear, practical analysis.</li><li>Share knowledge openly through articles, events, videos and media engagement.</li></ul>');
        $values = self::rt('<h2>Core values</h2><ul><li><strong>Independence</strong>: analysis led by evidence rather than interests.</li><li><strong>Integrity</strong>: honest sourcing, attribution and correction of errors.</li><li><strong>Inclusiveness</strong>: respect for diverse voices and perspectives.</li><li><strong>Public interest</strong>: research that serves stability and development.</li></ul>');
        $approach = self::rt('<h2>Research approach</h2><p>Our work follows the standards set out in our <a href="/research-methodology">research methodology</a> and <a href="/editorial-policy">editorial policy</a>: clear sourcing, transparent attribution and editorial review before publication.</p><h2>Areas of focus</h2><p>Geopolitics, economy and energy, regional security, CPEC and regional connectivity, climate security, international relations, and development and governance. Browse our <a href="/articles">research articles</a>.</p>');

        return [[
            'slug' => 'about', 'title' => 'About CDS World', 'template' => 'about',
            'excerpt' => 'CDS World is a think tank dedicated to youth engagement, national dialogue and policy development.',
            'seo' => ['About CDS World | Independent Think Tank in Pakistan', 'Learn about CDS World, its mission, vision, objectives and values: independent research, national dialogue and youth engagement for a stable Pakistan.', 'about CDS World'],
            'sections' => [['richtext', null, $about], ['richtext', null, $mv], ['richtext', null, $obj], ['richtext', null, $values], ['richtext', null, $approach]],
        ], [
            'slug' => 'who-we-are', 'title' => 'Who We Are', 'template' => 'team',
            'excerpt' => 'Leadership, researchers and contributors at CDS World.',
            'seo' => ['Who We Are | CDS World Leadership & Research Team', 'Meet the leadership, research team and contributors behind CDS World and its work on policy, security and development.', 'CDS World team'],
            'sections' => [['richtext', null, self::rt('<p>CDS World brings together leaders, researchers and contributors committed to evidence-based dialogue on Pakistan and its region.</p>')], ['team', 'Leadership & management', ['groups' => ['leadership']]], ['team', 'Research team', ['groups' => ['research']]], ['team', 'Contributors', ['groups' => ['contributor']]]],
        ], [
            'slug' => 'board-of-directors', 'title' => 'Board of Directors', 'template' => 'team',
            'excerpt' => 'The Board of Directors of CDS World.',
            'seo' => ['Board of Directors | CDS World', 'Meet the Board of Directors guiding the strategy and independence of CDS World.', 'CDS World board of directors'],
            'sections' => [['team', null, ['groups' => ['board']]]],
        ]];
    }

    private static function listings(): array
    {
        $mk = fn ($slug, $title, $h1, $intro, $seoT, $seoD, $kw) => [
            'slug' => $slug, 'title' => $title, 'template' => 'listing',
            'seo' => [$seoT, $seoD, $kw],
            'sections' => [['listing_intro', $h1, self::rt($intro)]],
        ];

        return [
            $mk('articles', 'Articles', 'Research & Publications', '<p>Read independent analysis from CDS World on Pakistan policy, geopolitics, regional security, economy and energy, CPEC, climate security and governance. Search by topic, author or date.</p>', 'Research Articles & Policy Analysis | CDS World', 'Read independent research and policy analysis from CDS World on geopolitics, regional security, economy, energy, CPEC, climate security and governance.', 'Pakistan policy research'),
            $mk('events', 'Events', 'Events', '<p>CDS World hosts conferences, seminars, roundtables and youth dialogues that bring policymakers, researchers and citizens together. Find upcoming sessions and explore past events.</p>', 'Events, Seminars & Dialogues | CDS World', 'Upcoming and past CDS World events: conferences, seminars and national dialogues on policy, security, development and youth engagement.', 'policy events Pakistan'),
            $mk('media-coverage', 'Media Coverage', 'Media Coverage', '<p>See how CDS World research and experts are featured in news outlets, interviews and commentary.</p>', 'Media Coverage | CDS World in the News', 'News features, interviews and commentary featuring CDS World research and experts across media outlets.', 'CDS World media coverage'),
            $mk('gallery', 'Gallery', 'Photo & Video Gallery', '<p>Browse photographs and videos from CDS World events, conferences and activities, organised by album.</p>', 'Photo & Video Gallery | CDS World', 'Photo and video albums from CDS World conferences, seminars and dialogues.', 'CDS World gallery'),
            $mk('videos', 'Videos', 'Videos & Interviews', '<p>Watch interviews, panel discussions, webinars and documentaries from CDS World on peace, stability and development.</p>', 'Videos, Interviews & Webinars | CDS World', 'Watch CDS World interviews, panel discussions, recorded webinars and documentaries on policy, security and development.', 'policy interviews webinars'),
        ];
    }

    private static function utility(): array
    {
        return [[
            'slug' => 'contact', 'title' => 'Contact', 'template' => 'contact',
            'seo' => ['Contact CDS World | Get in Touch', 'Contact CDS World for research enquiries, event information, media requests and partnerships.', 'contact CDS World'],
            'sections' => [['richtext', 'Contact CDS World', self::rt('<p>We welcome questions about our research, events, media requests and collaboration. Use the form or contact us directly and we will respond as soon as we can.</p>')], ['contact_info', null, []], ['contact_form', 'Send us a message', []]],
        ], [
            'slug' => 'newsletter', 'title' => 'Newsletter', 'template' => 'default',
            'seo' => ['Newsletter | CDS World Updates by Email', 'Subscribe to the CDS World newsletter for new research, event announcements and updates.', 'CDS World newsletter'],
            'sections' => [['newsletter', 'Subscribe to our newsletter', ['description' => 'New research, event announcements and updates from CDS World, delivered by email. You can unsubscribe at any time.']]],
        ]];
    }

    private static function policies(): array
    {
        return PolicyContent::all();
    }
}
