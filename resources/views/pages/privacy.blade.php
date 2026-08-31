@php
    $brand    = $settings['company_name'] ?? config('app.name');
    $legal    = $settings['company_legal_name'] ?: $brand;
    $email    = $settings['company_support_email'] ?: $settings['company_email'];
    $phone    = $settings['company_phone'] ?? '';
    $address  = \App\Models\SiteSetting::fullAddress();
    $officer  = $settings['legal_grievance_officer'] ?? 'Grievance Officer';

    $sections = [
        'scope'        => '1. Scope of this policy',
        'who-we-are'   => '2. Who we are',
        'what-we-collect' => '3. Information we collect',
        'why'          => '4. Why we use your information',
        'notifications' => '5. Notifications and push messages',
        'location'     => '6. Location information',
        'cookies'      => '7. Cookies and similar technologies',
        'sharing'      => '8. Who we share information with',
        'businesses'   => '9. What businesses can see',
        'retention'    => '10. How long we keep information',
        'security'     => '11. How we protect information',
        'rights'       => '12. Your rights and choices',
        'children'     => '13. Children',
        'transfers'    => '14. Storage and international transfers',
        'links'        => '15. Third-party links',
        'changes'      => '16. Changes to this policy',
        'grievance'    => '17. Grievance officer and contact',
    ];
@endphp

<x-legal-page
    title="Privacy"
    accent="Policy"
    badge="Legal"
    intro="What we collect when you use this platform, why we collect it, who sees it, and what you can ask us to do about it."
    :effective-date="$settings['privacy_effective_date']">

    <x-slot name="toc">
        @foreach($sections as $id => $label)
            <a href="#{{ $id }}">{{ $label }}</a>
        @endforeach
        @if(!empty($settings['privacy_extra']))
            <a href="#additional">18. Additional disclosures</a>
        @endif
    </x-slot>

    <section id="scope">
        <h2>1. Scope of this policy</h2>
        <p>This Privacy Policy explains how {{ $legal }} ("we", "us", "our") handles personal information when you use the {{ $brand }} website, progressive web app and related services (the "Platform") — whether you book as a guest, hold a registered account, work as a Specialist, or run a Business listed with us.</p>
        <div class="callout">
            <p><strong>The short version:</strong> we collect what we reasonably need to hold a booking, tell you when it is your turn, and keep the Platform working and safe. Your phone number and booking details go to the business you booked with, because they cannot serve you otherwise. We do not sell your data, and we do not run advertising trackers on this platform.</p>
        </div>
    </section>

    <section id="who-we-are">
        <h2>2. Who we are</h2>
        <p>{{ $legal }} is the controller of the personal information described in this policy.</p>
        <p>@if($address){{ $address }}<br>@endif @if($email)Email: <a href="mailto:{{ $email }}">{{ $email }}</a>@endif</p>
        <p>Note that each listed Business is a separate, independent controller of the customer records it maintains in its own systems and premises. This policy covers what <em>we</em> do; a Business's own practices are its responsibility.</p>
    </section>

    <section id="what-we-collect">
        <h2>3. Information we collect</h2>

        <h3>3.1 Information you give us</h3>
        <ul>
            <li><strong>To make a booking:</strong> your name and phone number where the Business asks for them, and your email address if you choose to add one. Some Businesses do not ask for any details at all, in which case the booking is held against your device alone.</li>
            <li><strong>To hold an account:</strong> your name, email address, phone number and password (stored only as a cryptographic hash).</li>
            <li><strong>To list a business:</strong> the owner's name, business name, category, address and coordinates, contact number, working hours, service prices and fees, photographs, staff details, and payout details such as a UPI ID.</li>
            <li><strong>Reviews and photographs</strong> you choose to publish about a Business, together with the name and, where you enter one, the phone number you attach to the review. A review may be posted anonymously; a review verified through a third-party sign-in also carries the name and email address on that account.</li>
            <li><strong>Messages you send us</strong> through the contact form or by email, including anything you include in them.</li>
        </ul>

        <h3>3.2 Information collected automatically</h3>
        <ul>
            <li><strong>Device and log data:</strong> IP address, browser and device type, operating system, referring page, pages viewed, and the date and time of your requests.</li>
            <li><strong>Session and security data:</strong> session identifiers, CSRF tokens and rate-limiting counters used to keep the Platform working and to block abuse.</li>
            <li><strong>Push notification tokens</strong> issued by your browser or device when you allow notifications, so we can alert you when your turn approaches.</li>
            <li><strong>Location information</strong> — see section 6.</li>
        </ul>

        <h3>3.3 Information from third parties</h3>
        <ul>
            <li><strong>Payment status</strong> from our payment gateway — whether a payment succeeded, failed or was refunded, together with a transaction reference. We never receive your full card number, CVV, UPI PIN or banking password.</li>
            <li><strong>Verified identity for reviews:</strong> if you choose to sign in with a third-party identity provider to leave a verified review, we receive the name and email address associated with that account.</li>
            <li><strong>Place names</strong> from mapping services when converting coordinates into a readable locality.</li>
        </ul>
    </section>

    <section id="why">
        <h2>4. Why we use your information</h2>
        <ul>
            <li><strong>To create and manage bookings</strong> — issue your token, hold your position, show you the queue, and let you cancel. This is the core of the service and cannot be provided without your name and phone number.</li>
            <li><strong>To keep you informed</strong> — booking confirmations, turn alerts, skips, cancellations and shop closures.</li>
            <li><strong>To verify identity</strong> — one-time passwords sent to your phone or email, and checks against duplicate or fraudulent bookings.</li>
            <li><strong>To take and settle payments</strong> — process online payments, issue refunds, and settle amounts collected on a Business's behalf.</li>
            <li><strong>To support you</strong> — investigate and answer your enquiries and complaints.</li>
            <li><strong>To keep the Platform safe</strong> — detect and prevent spam bookings, fake reviews, scraping, abuse and fraud, and enforce our <a href="{{ route('terms') }}">Terms and Conditions</a>.</li>
            <li><strong>To improve the service</strong> — understand which features are used and where the experience breaks, using aggregated and, where practical, de-identified data.</li>
            <li><strong>To provide business reporting</strong> — give each Business the booking records and exports it needs to run and account for its own operations.</li>
            <li><strong>To meet legal obligations</strong> — tax, accounting, and responding to lawful requests from authorities.</li>
        </ul>
        <p>We rely on the performance of our contract with you, your consent (for location and push notifications), our legitimate interests in operating a safe and functioning platform, and compliance with legal obligations, as the basis for these uses.</p>
    </section>

    <section id="notifications">
        <h2>5. Notifications and push messages</h2>
        <p>When you allow notifications, your browser or device issues us a token that lets us deliver a message to that specific installation. We store the token against your device or account and use it only to send booking-related alerts. Revoking notification permission in your browser or device settings stops these immediately; you may also continue to receive booking updates by SMS or email where those channels are configured.</p>
    </section>

    <section id="location">
        <h2>6. Location information</h2>
        <p>Location is used for one purpose: showing you businesses near you and ordering them by distance.</p>
        <ul>
            <li><strong>Precise location</strong> (GPS coordinates) is read only after you explicitly allow it in the browser prompt. It is stored in cookies on your own device and sent with your requests so results can be sorted by distance. Your browser also sends those coordinates directly to the third-party geocoding services we use, so the area name can be shown back to you — see section 8.</li>
            <li><strong>Approximate location</strong> may be inferred from a city or area you select manually.</li>
            <li>We do not build a location history, track your movements between sessions, or share your coordinates with Businesses.</li>
        </ul>
        <p>You can withdraw location access at any time through your browser's site settings, and clear the stored preference by clearing this site's cookies.</p>
    </section>

    <section id="cookies">
        <h2>7. Cookies and similar technologies</h2>
        <p>We use cookies and local storage for:</p>
        <ul>
            <li><strong>Essential functions</strong> — your session, sign-in state and CSRF protection. The Platform cannot work without these.</li>
            <li><strong>Booking identity</strong> — remembering the phone number a booking was made with, together with a random identifier we generate for the device itself, so "My Bookings" works without an account and still works where the Business asks for no details at all.</li>
            <li><strong>Preferences</strong> — your chosen location, and whether you have already answered the location or notification prompts, so you are not asked repeatedly.</li>
        </ul>
        <p>We do not use advertising or cross-site tracking cookies on the Platform. Blocking essential cookies will prevent bookings from working.</p>
    </section>

    <section id="sharing">
        <h2>8. Who we share information with</h2>
        <ul>
            <li><strong>The business you booked with</strong> — your name, phone number, token details, and any additional details that Business asked for. This is necessary for it to serve you, and is limited to the Business you actually booked with, together with the Specialist assigned to you.</li>
            <li><strong>Service providers</strong> that operate parts of the Platform under contract — hosting and infrastructure, the payment gateway, messaging and push-notification delivery, and email delivery. They may process personal information only on our instructions.</li>
            <li><strong>Geocoding and mapping services</strong> — where you allow location, your browser sends your coordinates directly to the mapping providers we use so they can return the name of the area you are in. Those providers receive that request from your device under their own privacy policies rather than on our instructions, and we do not send them your name, phone number or booking details.</li>
            <li><strong>Professional advisers</strong> — auditors, accountants and lawyers, where necessary and under a duty of confidentiality.</li>
            <li><strong>Authorities</strong> — where we are legally required to disclose information, or where disclosure is necessary to establish, exercise or defend legal claims, or to protect the safety of any person.</li>
            <li><strong>A successor entity</strong> — if the business is reorganised, merged or acquired, information may transfer as part of that transaction, subject to this policy.</li>
        </ul>
        <p><strong>We do not sell your personal information, and we do not share it with advertisers or data brokers.</strong></p>
    </section>

    <section id="businesses">
        <h2>9. What businesses can see</h2>
        <p>A Business's dashboard shows it the bookings placed with that Business: the customer's name and phone number, the email address where one was given, token and timing details, the outcome of the visit, and its own historical reports — which it can download as a spreadsheet where its plan allows. A Business does not see your bookings with any other Business, your location coordinates, or your account password.</p>
        <p>Businesses are contractually required to use these details only to serve bookings and to comply with applicable data protection law. If you believe a Business has misused your details — for example by sending you marketing you never asked for — please <a href="{{ route('contact') }}">report it to us</a>.</p>
    </section>

    <section id="retention">
        <h2>10. How long we keep information</h2>
        <ul>
            <li><strong>Booking records</strong> — kept while the booking is live and afterwards as part of the Business's operational and reporting history, and to resolve disputes.</li>
            <li><strong>Account information</strong> — kept while your account is active, and for a reasonable period afterwards.</li>
            <li><strong>Payment and settlement records</strong> — kept as long as tax and accounting law requires.</li>
            <li><strong>Reviews</strong> — kept while published; removed reviews are deleted from public view.</li>
            <li><strong>Contact enquiries</strong> — kept for as long as needed to handle your query and to keep a record of the outcome.</li>
            <li><strong>Logs and security data</strong> — kept for a short period for troubleshooting and abuse prevention.</li>
        </ul>
        <p>When information is no longer needed, we delete it or de-identify it so it can no longer be linked to you.</p>
    </section>

    <section id="security">
        <h2>11. How we protect information</h2>
        <p>We use industry-standard measures appropriate to the sensitivity of the data, including encrypted transport (HTTPS), password hashing, protection against cross-site request forgery, rate limiting and throttling on sensitive endpoints such as login and OTP verification, role-based access controls that restrict each account to its own data, and restricted administrative access.</p>
        <p>No system is perfectly secure. Please help by keeping your password private and unique, and by telling us promptly at <a href="mailto:{{ $email }}">{{ $email }}</a> if you suspect unauthorised access. Where a breach is likely to affect you, we will notify you and the relevant authority as the law requires.</p>
    </section>

    <section id="rights">
        <h2>12. Your rights and choices</h2>
        <p>Subject to applicable law, you may ask us to:</p>
        <ul>
            <li><strong>Access</strong> the personal information we hold about you;</li>
            <li><strong>Correct</strong> information that is inaccurate or out of date;</li>
            <li><strong>Delete</strong> information we no longer have a lawful reason to keep;</li>
            <li><strong>Restrict or object</strong> to certain processing;</li>
            <li><strong>Withdraw consent</strong> you previously gave — for example for location or push notifications — without affecting processing already carried out;</li>
            <li><strong>Receive a copy</strong> of information you provided to us, in a portable format.</li>
        </ul>
        <p>To exercise any of these, write to <a href="mailto:{{ $email }}">{{ $email }}</a> or use the <a href="{{ route('contact') }}">contact form</a>. We may need to verify your identity — usually by confirming control of the phone number or email address on the record — before we act, and we will respond within the period the law allows.</p>
        <p>Some information cannot be deleted on request where we must keep it: for example, transaction and tax records, and details of a booking that is still live or is subject to a dispute.</p>
    </section>

    <section id="children">
        <h2>13. Children</h2>
        <p>The Platform is not directed at children under 18, and we do not knowingly collect their personal information for account registration. A parent or guardian may book an appointment for a child, in which case the details provided are treated as the guardian's submission. If you believe a child has provided us information directly, contact us and we will delete it.</p>
    </section>

    <section id="transfers">
        <h2>14. Storage and international transfers</h2>
        <p>Information is stored on servers operated by our hosting and infrastructure providers. Some of our service providers may process data outside your country. Where that happens, we take reasonable steps to ensure a comparable level of protection through contractual safeguards with those providers.</p>
    </section>

    <section id="links">
        <h2>15. Third-party links</h2>
        <p>The Platform may link to external sites — a Business's own website, a social media profile, or a payment page. Those destinations have their own privacy policies, and we are not responsible for their practices. Please read their policies before sharing information with them.</p>
    </section>

    <section id="changes">
        <h2>16. Changes to this policy</h2>
        <p>We update this policy as the Platform develops or the law changes. The effective date at the top of this page always reflects the current version, and material changes will be signposted on the Platform or notified by email where we hold one for you.</p>
    </section>

    <section id="grievance">
        <h2>17. Grievance officer and contact</h2>
        <p>For any privacy question, request or complaint — including a grievance under applicable Indian data protection and intermediary rules — contact:</p>
        <div class="callout">
            <p>
                <strong>{{ $officer }}</strong><br>
                {{ $legal }}<br>
                @if($address){{ $address }}<br>@endif
                @if($email)Email: <a href="mailto:{{ $email }}">{{ $email }}</a><br>@endif
                @if($phone)Phone: <a href="tel:{{ $phone }}">{{ $phone }}</a><br>@endif
                Or use the <a href="{{ route('contact') }}">contact form</a>.
            </p>
        </div>
        <p>If you are not satisfied with our response, you may escalate the matter to the data protection authority with jurisdiction over you.</p>
    </section>

    @if(!empty($settings['privacy_extra']))
        <section id="additional">
            <h2>18. Additional disclosures</h2>
            @foreach(preg_split('/\n{2,}/', trim($settings['privacy_extra'])) as $paragraph)
                <p>{{ trim($paragraph) }}</p>
            @endforeach
        </section>
    @endif
</x-legal-page>
