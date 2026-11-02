<?php

namespace Database\Seeders;

/** Draft legal/editorial copy. Owner review (and legal review for privacy/terms) is recommended before launch. */
class PolicyContent
{
    private static function page(string $slug, string $title, string $html, array $seo, string $excerpt): array
    {
        return [
            'slug' => $slug, 'title' => $title, 'template' => 'legal', 'excerpt' => $excerpt, 'seo' => $seo,
            'sections' => [['richtext', null, ['html' => $html]]],
        ];
    }

    public static function all(): array
    {
        return [
            self::page('privacy-policy', 'Privacy Policy', <<<'H'
<p>This policy explains what personal information CDS World collects through this website, why we collect it, and the choices you have.</p>
<h2>Information we collect</h2>
<h3>Newsletter</h3><p>If you subscribe, we collect your email address, optionally your name, the time you gave consent, and a one-way hashed form of your IP address to help prevent abuse. We email a confirmation link before activating your subscription.</p>
<h3>Contact form</h3><p>When you write to us we collect your name, email address, optional phone number and your message, so that we can reply.</p>
<h3>Event registration</h3><p>If an event offers registration on this site, we collect the details you submit (such as name, email and organisation) to manage attendance.</p>
<h3>Cookies and technical data</h3><p>The site uses essential cookies for security and session handling. We do not use advertising cookies. Servers may keep standard technical logs for security and troubleshooting.</p>
<h2>How we use information</h2>
<p>We use personal information only to send the communications you requested, respond to enquiries, run events and keep the website secure. We do not sell personal information.</p>
<h2>Sharing</h2>
<p>We share information only with service providers who help operate the website or email service, when required by law, or with your consent.</p>
<h2>Retention and security</h2>
<p>We keep information only as long as needed for the purposes above and protect it with reasonable technical and organisational measures.</p>
<h2>Your choices</h2>
<p>You can unsubscribe from the newsletter at any time using the link in every email. To access, correct or delete your information, contact us at <a href="mailto:info@cdsworld.org">info@cdsworld.org</a>.</p>
<h2>External sites</h2><p>Our pages may link to or embed content from third parties such as YouTube and Facebook, which have their own privacy practices.</p>
<h2>Changes</h2><p>We may update this policy and will post the current version on this page.</p>
H, ['Privacy Policy | CDS World', 'How CDS World collects, uses and protects personal information submitted through its website, including newsletter, contact and event forms.', 'CDS World privacy policy'], 'How CDS World handles personal information.'),

            self::page('terms-conditions', 'Terms & Conditions', <<<'H'
<p>By using this website you agree to these terms. If you do not agree, please do not use the site.</p>
<h2>Use of the website</h2><p>You may use the site for lawful purposes. You must not attempt to disrupt or gain unauthorised access to the site, or misuse its forms.</p>
<h2>Content and intellectual property</h2><p>Unless stated otherwise, content published by CDS World is protected by copyright. You may quote short excerpts with clear attribution and a link to the original page. Reproduction beyond this requires written permission.</p>
<h2>Views expressed</h2><p>Articles reflect the views of their authors and do not necessarily represent the position of CDS World. See our <a href="/disclaimer">disclaimer</a>.</p>
<h2>Third-party links</h2><p>We are not responsible for the content or practices of external websites that we link to or embed.</p>
<h2>Limitation of liability</h2><p>Content is provided for information and policy discussion. To the fullest extent permitted by law, CDS World is not liable for losses arising from use of the site or reliance on its content.</p>
<h2>Changes and governing law</h2><p>We may update these terms at any time. These terms are governed by the laws of Pakistan.</p>
<h2>Contact</h2><p>Questions about these terms: <a href="mailto:info@cdsworld.org">info@cdsworld.org</a>.</p>
H, ['Terms & Conditions | CDS World', 'The terms that apply to your use of the CDS World website, including content use, third-party links and limitation of liability.', 'CDS World terms and conditions'], 'Terms governing use of this website.'),

            self::page('disclaimer', 'Disclaimer', <<<'H'
<p>The information on this website is provided for general information and public policy discussion only.</p>
<h2>Authors&rsquo; views</h2><p>Opinions in articles, videos and event contributions are those of the individual authors or speakers and do not necessarily reflect the views of CDS World.</p>
<h2>No professional advice</h2><p>Nothing here is legal, financial, investment or other professional advice.</p>
<h2>Accuracy</h2><p>We aim for accurate and current content, but cannot guarantee completeness. If you spot an error, please <a href="/contact">tell us</a> and we will review it under our <a href="/editorial-policy">editorial policy</a>.</p>
H, ['Disclaimer | CDS World', 'CDS World disclaimer: views expressed by authors are their own, content is for information only and is not professional advice.', 'CDS World disclaimer'], 'Limits of responsibility for published content.'),

            self::page('editorial-policy', 'Editorial Policy', <<<'H'
<p>CDS World publishes research and commentary intended to inform policy debate. This policy describes how content is prepared and published.</p>
<h2>Independence</h2><p>Content is selected on its merit and relevance to our focus areas. Authors are expected to disclose relevant interests.</p>
<h2>Review before publication</h2><p>Submitted articles are saved as drafts, submitted for review, and assessed by an editor. Only approved articles are published, and publication can be scheduled.</p>
<h2>Sourcing and attribution</h2><p>Claims should be supported by credible sources, listed in the article references. Quotations and data must be attributed. Plagiarism is not accepted.</p>
<h2>Corrections and updates</h2><p>Significant errors are corrected promptly and articles show their last-updated date. Report concerns through the <a href="/contact">contact page</a>.</p>
<h2>Opinion and research</h2><p>Opinion pieces and commentary are labelled as such and represent the author&rsquo;s view.</p>
H, ['Editorial Policy | CDS World', 'How CDS World selects, reviews, publishes and corrects research articles and commentary, including independence, sourcing and corrections.', 'editorial policy think tank'], 'How we select, review and correct published content.'),

            self::page('research-methodology', 'Research Methodology', <<<'H'
<p>CDS World research aims to be transparent, evidence-based and useful to decision-makers.</p>
<h2>Framing the question</h2><p>Each study begins with a clearly stated question and scope tied to our focus areas.</p>
<h2>Evidence</h2><p>We draw on official documents, published data, academic literature and credible reporting, and cite sources so readers can verify them.</p>
<h2>Analysis</h2><p>Methods, assumptions and limitations are stated where they affect conclusions. We distinguish evidence from interpretation.</p>
<h2>Review and dialogue</h2><p>Work is reviewed editorially before release, and our events and dialogues help test ideas with practitioners and young people.</p>
<h2>Updates</h2><p>Research may be updated as new evidence emerges; changes are reflected in the article&rsquo;s updated date.</p>
<p>See also our <a href="/editorial-policy">editorial policy</a>.</p>
H, ['Research Methodology | CDS World', 'The principles behind CDS World research: framing questions, using verifiable evidence, transparent analysis and editorial review.', 'research methodology think tank'], 'The principles behind our research.'),
        ];
    }
}
