@php
    $brand   = $settings['company_name'] ?? config('app.name');
    $legal   = $settings['company_legal_name'] ?: $brand;
    $email   = $settings['company_support_email'] ?: $settings['company_email'];
    $phone   = $settings['company_phone'] ?? '';
    $address = \App\Models\SiteSetting::fullAddress();
    $city    = $settings['legal_governing_city'] ?? '';
    $state   = $settings['legal_governing_state'] ?? '';

    $sections = [
        'acceptance'    => '1. Acceptance of these terms',
        'definitions'   => '2. Definitions',
        'eligibility'   => '3. Who may use the platform',
        'accounts'      => '4. Accounts and guest bookings',
        'bookings'      => '5. Bookings, tokens and queue positions',
        'cancellations' => '6. Cancellations, no-shows and rescheduling',
        'fees'          => '7. Fees, payments and refunds',
        'our-role'      => '8. Our role: we are the platform, not the service provider',
        'businesses'    => '9. Additional terms for listed businesses',
        'reviews'       => '10. Reviews and user content',
        'conduct'       => '11. Acceptable use',
        'communications' => '12. Notifications and communications',
        'ip'            => '13. Intellectual property',
        'third-parties' => '14. Third-party services',
        'availability'  => '15. Availability and changes to the service',
        'liability'     => '16. Disclaimers and limitation of liability',
        'indemnity'     => '17. Indemnity',
        'termination'   => '18. Suspension and termination',
        'changes'       => '19. Changes to these terms',
        'law'           => '20. Governing law and disputes',
        'contact'       => '21. How to reach us',
    ];
@endphp

<x-legal-page
    title="Terms &"
    accent="Conditions"
    badge="Legal"
    intro="The agreement between you and us when you use this platform — to find a business, book a token, or run a business of your own on it."
    :effective-date="$settings['terms_effective_date']">

    <x-slot name="toc">
        @foreach($sections as $id => $label)
            <a href="#{{ $id }}">{{ $label }}</a>
        @endforeach
        @if(!empty($settings['terms_extra']))
            <a href="#additional">22. Additional terms</a>
        @endif
    </x-slot>

    <section id="acceptance">
        <h2>1. Acceptance of these terms</h2>
        <p>These Terms and Conditions ("Terms") govern your access to and use of the {{ $brand }} website, progressive web app and related services (together, the "Platform"), operated by {{ $legal }} ("we", "us", "our").</p>
        <p>By browsing the Platform, creating a booking, listing a business or otherwise using any part of the service, you agree to these Terms and to our <a href="{{ route('privacy') }}">Privacy Policy</a>. If you do not agree with any part of them, please do not use the Platform.</p>
        <div class="callout">
            <p><strong>In short:</strong> we run a marketplace that connects you with independent local businesses and manages their appointment queues. The haircut, consultation, repair or treatment itself is delivered by that business — not by us.</p>
        </div>
    </section>

    <section id="definitions">
        <h2>2. Definitions</h2>
        <ul>
            <li><strong>Business</strong> (also "Provider" or "Vendor") — an independent salon, clinic, consultant, service centre, studio or other establishment that lists itself on the Platform.</li>
            <li><strong>Specialist</strong> (also "Employee" or "Staff") — an individual working for a Business who has their own queue, availability and login on the Platform.</li>
            <li><strong>Customer</strong> — any person who uses the Platform to find a Business or make a Booking, whether or not they hold a registered account.</li>
            <li><strong>Booking</strong> — a token or an appointment slot reserved through the Platform, identified by a token number and the phone number used to book it.</li>
            <li><strong>Queue</strong> — the live, ordered list of Bookings a Business or Specialist is working through on a given day.</li>
            <li><strong>Content</strong> — anything submitted to the Platform, including reviews, photographs, business descriptions, prices and messages.</li>
        </ul>
    </section>

    <section id="eligibility">
        <h2>3. Who may use the platform</h2>
        <p>You must be at least 18 years old to register an account, list a Business, or make a payment on the Platform. A Booking may be made on behalf of a minor by their parent or legal guardian, who remains responsible for that Booking and for the minor's conduct at the Business.</p>
        <p>You must provide accurate information about yourself and, where you act for a Business, confirm that you are authorised to bind that Business to these Terms.</p>
    </section>

    <section id="accounts">
        <h2>4. Accounts and guest bookings</h2>
        <h3>4.1 Guest bookings</h3>
        <p>Most Customers use the Platform without creating an account. When you book as a guest, your identity is your <strong>phone number</strong>: the number you enter is stored on your device and is what proves that a Booking is yours when you view or cancel it. If you clear your browser data or switch devices, you may lose the ability to manage that Booking from the Platform — contact the Business directly in that case.</p>
        <p>Because the phone number is the only handle we have, entering a number that does not belong to you is a misuse of the Platform: it sends someone else's notifications to a stranger, and gives them control over the Booking.</p>

        <h3>4.2 Registered accounts</h3>
        <p>Business owners, Specialists and administrators access the Platform through a registered account. You are responsible for keeping your password confidential and for all activity carried out under your login. Tell us immediately if you believe your account has been compromised.</p>

        <h3>4.3 Verification</h3>
        <p>We may verify a phone number or email address by sending a one-time password (OTP), and may limit the number of verification attempts and resends to protect the service from abuse. We may also require a Business to be reviewed and approved by our team before its listing goes live, and may decline or revoke that approval at our discretion.</p>
    </section>

    <section id="bookings">
        <h2>5. Bookings, tokens and queue positions</h2>
        <h3>5.1 What a booking is</h3>
        <p>A confirmed Booking reserves your place in a Business's queue, or a slot in its schedule. It is an arrangement between you and that Business. We pass on the request, hold the position, and keep both sides informed.</p>

        <h3>5.2 Wait times are estimates</h3>
        <p>Every waiting time, "your turn in" estimate and expected start time shown on the Platform is a <strong>projection based on the Business's own average service duration and its live queue</strong>. Real life interferes: an appointment can run long, a Specialist can take a break, an emergency can be prioritised. Estimates are given in good faith and are not a guarantee.</p>

        <h3>5.3 The business controls its own queue</h3>
        <p>A Business or Specialist may, in the ordinary course of running their day: call the next token, mark a visit complete, skip or postpone a Booking they cannot serve, pause new bookings when they are full, or close early. Where they do so, we notify you at the contact details attached to the Booking.</p>

        <h3>5.4 Arriving for your booking</h3>
        <p>Please arrive in time for your token to be called. A Business is entitled to move on to the next Customer if you are not present when your turn arrives, and to apply its own published policy on late arrivals.</p>

        <h3>5.5 Details you provide</h3>
        <p>Some Businesses require your details before confirming a Booking — your name and phone number, and an email address if you choose to add one. Others take the Booking without asking for anything. Any details you give are shared with that Business only, and are used to prepare for your visit.</p>
    </section>

    <section id="cancellations">
        <h2>6. Cancellations, no-shows and rescheduling</h2>
        <ul>
            <li><strong>You may cancel</strong> a Booking from the "My Bookings" screen at any time before it is served. The queue closes the gap automatically and the Customers behind you move up.</li>
            <li><strong>A Business may cancel</strong> a Booking where it cannot be served — for instance if the Specialist is unavailable, the shop closes unexpectedly, or the requested service cannot be provided.</li>
            <li><strong>Repeated no-shows</strong> or abusive booking behaviour (including placing bookings you do not intend to keep, or booking on someone else's number) may result in restrictions on your ability to use the Platform.</li>
            <li><strong>Rescheduling</strong> is done by cancelling and booking again, subject to availability.</li>
        </ul>
        <p>Any cancellation charge, deposit policy or late fee is set and applied by the Business, not by us, and must be published by that Business before you book.</p>
    </section>

    <section id="fees">
        <h2>7. Fees, payments and refunds</h2>
        <h3>7.1 What you may be charged</h3>
        <ul>
            <li><strong>The service price</strong> — set by the Business and usually paid to it directly at the premises.</li>
            <li><strong>A booking or token amount</strong> — where a Business chooses to collect a small amount online to confirm a token.</li>
            <li><strong>A service fee or priority/emergency fee</strong> — where a Business offers and you select such an option.</li>
            <li><strong>Subscription fees</strong> — payable by Businesses for their plan on the Platform, as described in clause 9.</li>
        </ul>
        <p>All applicable amounts and taxes are displayed before you confirm a payment. Prices shown on a Business's profile are maintained by that Business; where a displayed price and the price charged at the premises differ, please raise it with the Business, and tell us so we can follow up on the listing.</p>

        <h3>7.2 Payment processing</h3>
        <p>Online payments are processed by our third-party payment gateway. We do not store your card number, UPI PIN, CVV or net-banking credentials — those are handled entirely by the gateway under its own terms and security standards.</p>

        <h3>7.3 Refunds</h3>
        <p>Where an amount was collected online for a Booking that the Business could not honour, or that you cancelled within the Business's stated cancellation window, the refund is initiated to the original payment method. Refunds typically settle within 5–10 business days depending on your bank or payment provider. Disputes about the quality or delivery of the service itself must be raised with the Business first; we will assist where we reasonably can.</p>

        <h3>7.4 Chargebacks</h3>
        <p>Please contact us or the Business before raising a chargeback — most disputes are settled faster that way. Nothing in these Terms limits your right to dispute a payment with your card issuer, bank or payment provider. We may suspend access where we reasonably suspect a dispute is fraudulent or abusive, not for exercising that right.</p>
    </section>

    <section id="our-role">
        <h2>8. Our role: we are the platform, not the service provider</h2>
        <p>We provide discovery, booking, queue management and communication tools. We do not provide, supervise, employ, endorse or control the Businesses or Specialists listed, and we are not a party to the service contract between you and a Business.</p>
        <ul>
            <li>We do not guarantee the quality, safety, legality, licensing or outcome of any service booked through the Platform.</li>
            <li>We do not guarantee that a Business will be open, available, or able to honour a Booking.</li>
            <li>Listing details — hours, prices, photographs, staff and descriptions — are supplied by the Business. We review submissions before approval but cannot verify everything continuously.</li>
            <li>Badges, marks and labels shown on a listing reflect that Business's status and plan on the Platform. They are not a statement about its professional competence, licensing, safety, or the quality or outcome of the services it provides.</li>
        </ul>
        <p>If something goes wrong during a visit, raise it with the Business. If the Business does not resolve it, or the issue concerns safety, fraud or misrepresentation on a listing, please <a href="{{ route('contact') }}">tell us</a> — we take listing integrity seriously and can suspend or remove a Business from the Platform.</p>
    </section>

    <section id="businesses">
        <h2>9. Additional terms for listed businesses</h2>
        <p>These clauses apply in addition to the rest of these Terms where you register or operate a Business on the Platform.</p>

        <h3>9.1 Your listing</h3>
        <p>You are responsible for keeping your business name, address, category, working hours, service prices, fees, staff list and availability accurate and current. You must hold every registration, licence and permission that the law requires for the services you offer, and you must honour Bookings made through the Platform on the terms displayed on your profile.</p>

        <h3>9.2 Approval and status</h3>
        <p>A new Business is reviewed by our team before it appears publicly. We may approve, reject, suspend, reinstate or remove a listing — for example where information is false, where required licences are missing, where Customers repeatedly report unhonoured Bookings, or where these Terms are breached.</p>

        <h3>9.3 Subscription plans</h3>
        <p>Access to the Business dashboard and its features depends on your subscription plan, which may include a free trial. Plans renew only when you choose to pay for a further term; features beyond your plan's limits (including reporting and export tools available on selected plans) may be restricted or withheld. Subscription fees already paid are not refundable for a term that has begun, except where the law requires otherwise.</p>

        <h3>9.4 Staff accounts</h3>
        <p>Where you create logins for your Specialists, you are responsible for those accounts and for the personal data of the staff you enter. You must remove access promptly when someone leaves your business.</p>

        <h3>9.5 Amounts collected on your behalf and settlements</h3>
        <p>Where the Platform collects money from Customers in connection with your Bookings, those amounts are settled to you according to the settlement schedule we operate and notify to you, net of any platform fees, gateway charges, taxes and adjustments (including refunds and chargebacks). You must keep your payout details, including your UPI ID or bank details, accurate; we are not responsible for funds sent to payout details you supplied incorrectly.</p>

        <h3>9.6 Referral rewards</h3>
        <p>Where a referral programme is offered, rewards are credited only for genuine, approved and active Businesses referred by you. Self-referrals, duplicate registrations and any attempt to game the programme void the reward and may result in suspension.</p>

        <h3>9.7 Customer data</h3>
        <p>Customer details visible to you through the Platform — names, phone numbers, booking notes and history — are provided strictly so that you can serve those Bookings. You must not use them for unsolicited marketing, sell or share them, or retain them beyond what your own legal obligations require. You are an independent controller of the customer records you keep in your own systems, and are responsible for handling them lawfully.</p>
    </section>

    <section id="reviews">
        <h2>10. Reviews and user content</h2>
        <p>Reviews exist so that the next Customer can make an informed choice. When you post a review, rating, photograph or other Content, you confirm that it reflects a genuine experience, that it is yours to post, and that it does not contain anything unlawful, defamatory, obscene or private about another person.</p>
        <ul>
            <li>You keep ownership of your Content, and grant us a non-exclusive, royalty-free, worldwide licence to host, display, reproduce and distribute it on and in connection with the Platform.</li>
            <li>Fake, incentivised, paid or retaliatory reviews are prohibited — whether posted by a Customer, a Business, or anyone acting on a Business's behalf.</li>
            <li>A Business may report a review it believes breaches these Terms. Our team reviews reports and may remove the review, clear the report, or take further action against the account involved.</li>
            <li>We may remove Content that breaches these Terms without prior notice, and may withhold or delete Content that is the subject of a credible legal complaint.</li>
        </ul>
    </section>

    <section id="conduct">
        <h2>11. Acceptable use</h2>
        <p>You agree not to:</p>
        <ul>
            <li>make Bookings you do not intend to honour, or place bookings in bulk to block a Business's queue;</li>
            <li>use another person's phone number, identity or payment method;</li>
            <li>scrape, crawl, harvest or bulk-download listings, reviews, contact numbers or any other data from the Platform;</li>
            <li>use bots, scripts or automated tools to interact with the Platform, or attempt to bypass rate limits, verification or security controls;</li>
            <li>probe, scan, overload or attempt to gain unauthorised access to the Platform, its infrastructure, or another user's account;</li>
            <li>upload malware, or post spam, unlawful, hateful, harassing or sexually explicit material;</li>
            <li>impersonate any person or business, or misrepresent your affiliation with one;</li>
            <li>use the Platform for any purpose that breaches applicable law.</li>
        </ul>
    </section>

    <section id="communications">
        <h2>12. Notifications and communications</h2>
        <p>By making a Booking or registering, you agree to receive <strong>transactional messages</strong> relating to your use of the Platform — booking confirmations, queue and turn alerts, cancellations, OTPs, payment receipts, subscription notices and service announcements — by browser or app push notification and email, and, where we have enabled those channels, by SMS or WhatsApp.</p>
        <p>These messages are part of the service and cannot be fully switched off while you hold an active Booking or account, though you may disable browser push notifications from your device settings at any time. Promotional messages, where sent, are separate and can always be opted out of.</p>
    </section>

    <section id="ip">
        <h2>13. Intellectual property</h2>
        <p>The Platform, including its software, design, text, graphics, logos and the {{ $brand }} name, is owned by {{ $legal }} or its licensors and is protected by intellectual property law. You may use the Platform for its intended purpose; you may not copy, modify, distribute, reverse-engineer, resell, frame or create derivative works from it without our written permission.</p>
        <p>Business names, logos and photographs uploaded by Businesses remain the property of those Businesses, which grant us a licence to display them on the Platform.</p>
    </section>

    <section id="third-parties">
        <h2>14. Third-party services</h2>
        <p>The Platform relies on third-party services to function — including a payment gateway for online payments, a messaging and push-notification provider for alerts, mapping and location services for discovery, and identity providers used to verify certain reviews. Your use of those features is also subject to the relevant provider's terms. We are not responsible for the availability, accuracy or acts of third-party services, including any map data or distance estimate they supply.</p>
    </section>

    <section id="availability">
        <h2>15. Availability and changes to the service</h2>
        <p>We work to keep the Platform available and accurate, but we provide it on an "as is" and "as available" basis. Maintenance, upgrades, network failures and events outside our control may interrupt it. We may add, change, restrict or withdraw features, including features previously offered free of charge, and may set or adjust limits (such as rate limits or plan entitlements) that apply to your use.</p>
    </section>

    <section id="liability">
        <h2>16. Disclaimers and limitation of liability</h2>
        <p>To the fullest extent permitted by law:</p>
        <ul>
            <li>we disclaim all implied warranties, including merchantability, fitness for a particular purpose and non-infringement;</li>
            <li>we are not liable for the acts, omissions, service quality, conduct, pricing, timing or safety practices of any Business or Specialist;</li>
            <li>we are not liable for indirect, incidental, special, punitive or consequential losses, or for loss of profits, revenue, goodwill, data or opportunity, however arising;</li>
            <li>we are not liable for losses caused by a missed notification, an inaccurate wait-time estimate, a queue change made by a Business, or a Booking a Business did not honour;</li>
            <li>our total aggregate liability arising out of or in connection with the Platform is limited to the total amount you paid to us (excluding amounts payable to a Business) in the three months immediately before the event giving rise to the claim, or ₹1,000, whichever is greater.</li>
        </ul>
        <p>Nothing in these Terms excludes liability that cannot lawfully be excluded, including liability for fraud or for death or personal injury caused by our negligence.</p>
    </section>

    <section id="indemnity">
        <h2>17. Indemnity</h2>
        <p>You agree to indemnify and hold harmless {{ $legal }}, its directors, employees and agents against any claim, demand, loss, liability or expense (including reasonable legal fees) arising from your breach of these Terms, your Content, your misuse of the Platform, or — where you operate a Business — the services you deliver to Customers.</p>
    </section>

    <section id="termination">
        <h2>18. Suspension and termination</h2>
        <p>You may stop using the Platform at any time, and may ask us to close a registered account. We may suspend or terminate your access, remove a listing, or cancel pending Bookings where we reasonably believe you have breached these Terms, where required by law, or where continued access poses a risk to other users or to the Platform. Where it is reasonable to do so, we will tell you why.</p>
        <p>Termination does not affect Bookings already served, amounts already due, or any clause that by its nature should survive — including clauses 10, 13, 16, 17 and 20.</p>
    </section>

    <section id="changes">
        <h2>19. Changes to these terms</h2>
        <p>We may update these Terms as the Platform develops or the law changes. The effective date at the top of this page always reflects the current version. Where a change materially affects your rights, we will give reasonable notice through the Platform or by email. Continuing to use the Platform after a change takes effect means you accept the updated Terms.</p>
    </section>

    <section id="law">
        <h2>20. Governing law and disputes</h2>
        <p>These Terms are governed by the laws of India. Subject to the paragraph below, the courts at {{ $city ?: 'our registered office location' }}{{ $state ? ', ' . $state : '' }} have exclusive jurisdiction over any dispute arising out of or in connection with them.</p>
        <p>Before starting formal proceedings, please contact us so we can try to resolve the matter directly — most issues are settled far faster that way.</p>
    </section>

    <section id="contact">
        <h2>21. How to reach us</h2>
        <p>Questions about these Terms, or a complaint about something on the Platform:</p>
        <div class="callout">
            <p>
                <strong>{{ $legal }}</strong><br>
                @if($address){{ $address }}<br>@endif
                @if($email)Email: <a href="mailto:{{ $email }}">{{ $email }}</a><br>@endif
                @if($phone)Phone: <a href="tel:{{ $phone }}">{{ $phone }}</a><br>@endif
                Or use the <a href="{{ route('contact') }}">contact form</a>.
            </p>
        </div>
    </section>

    @if(!empty($settings['terms_extra']))
        <section id="additional">
            <h2>22. Additional terms</h2>
            @foreach(preg_split('/\n{2,}/', trim($settings['terms_extra'])) as $paragraph)
                <p>{{ trim($paragraph) }}</p>
            @endforeach
        </section>
    @endif
</x-legal-page>
