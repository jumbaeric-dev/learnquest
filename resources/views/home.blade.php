@php
$url = fn (string $name, string $fallback) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : url($fallback);

$childLogin = $url('child.login', '/child/login');
$parentLogin = $url('parent.login', '/parent/login');

// Parent signup: first matching named route wins, otherwise the fallback path
$parentSignup = url('/parent/register'); // <-- set your real parent signup path here
foreach (['parent.register', 'parent.signup', 'register.parent', 'register'] as $name) {
    if (\Illuminate\Support\Facades\Route::has($name)) {
        $parentSignup = route($name);
        break;
    }
}

$price = config('learnquest.marketing.premium_price', '$4.99');
$period = config('learnquest.marketing.premium_period', 'per month');
$email = config('learnquest.support_email', 'support@learnquest.app');

$title = 'LearnQuest – Fun Learning Adventures for Kids | Games, Missions & Progress Tracking';
$desc = 'LearnQuest turns learning into an adventure. Kids explore worlds, complete missions, earn XP and badges, while parents track real progress. Start free today.';

$faqs = [
['Is LearnQuest really free to start?', 'Yes. The Free plan lets your child start learning straight away with no credit card needed. You can upgrade to Premium whenever you want more worlds, more profiles and deeper progress reports.'],
['What ages is LearnQuest for?', 'LearnQuest is designed for primary-school-aged children. Lessons and activities are organised into subjects, courses and short lessons so children can learn at their own level.'],
['How does my child log in?', 'Children log in with a simple username and PIN created by their parent, so no email address or password is needed. Parents manage everything from their own secure account.'],
['How can I see how my child is doing?', 'The parent dashboard shows lessons completed, skills practised, streaks and progress over time, so you always know where your child is shining and where they need a hand.'],
['Which payment methods are accepted?', 'Premium can be paid for with card, PayPal or M-Pesa, so families can choose the option that suits them.'],
['Can I cancel Premium at any time?', 'Yes. You can cancel from the Billing page in your parent account. Your child keeps access until the end of the period you paid for, then moves back to the Free plan.'],
];

$plans = [
[
'name' => 'Free', 'price' => '$0', 'period' => 'forever', 'featured' => false,
'blurb' => 'Perfect for trying LearnQuest and building a daily learning habit.',
'cta' => 'Start for free',
'features' => ['Starter worlds and missions', '1 child profile', 'XP, levels and daily streaks', 'Basic progress overview'],
],
[
'name' => 'Premium', 'price' => $price, 'period' => $period, 'featured' => true,
'blurb' => 'Everything your child needs to keep growing, with full parent insights.',
'cta' => 'Go Premium',
'features' => ['All worlds, courses and missions', 'Multiple child profiles', 'Badges, achievements and leaderboards', 'Detailed parent progress reports', 'Pay by card, PayPal or M-Pesa', 'Priority support'],
],
];

$schema = [
'@context' => 'https://schema.org',
'@graph' => [
[
'@type' => 'Organization',
'@id' => url('/') . '#org',
'name' => 'LearnQuest',
'url' => url('/'),
'logo' => asset('images/logo.png'),
'email' => $email,
],
[
'@type' => 'WebSite',
'url' => url('/'),
'name' => 'LearnQuest',
'publisher' => ['@id' => url('/') . '#org'],
],
[
'@type' => 'WebApplication',
'name' => 'LearnQuest',
'applicationCategory' => 'EducationalApplication',
'operatingSystem' => 'Web',
'audience' => ['@type' => 'PeopleAudience', 'suggestedMinAge' => 5, 'suggestedMaxAge' => 13],
'description' => $desc,
'offers' => [
['@type' => 'Offer', 'name' => 'Free', 'price' => '0', 'priceCurrency' => 'USD'],
['@type' => 'Offer', 'name' => 'Premium', 'price' => preg_replace('/[^0-9.]/', '', $price), 'priceCurrency' => 'USD'],
],
],
[
'@type' => 'FAQPage',
'mainEntity' => array_map(fn ($f) => [
'@type' => 'Question',
'name' => $f[0],
'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
], $faqs),
],
],
];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $desc }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#5b3df5">
    <link rel="canonical" href="{{ url('/') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="LearnQuest">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/og-cover.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $desc }}">
    <meta name="twitter:image" content="{{ asset('images/og-cover.png') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script type="application/ld+json">
        {
            !!json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!
        }
    </script>

    <style>
        :root {
            --ink: #1c1840;
            --muted: #625f86;
            --bg: #fbfaff;
            --card: #ffffff;
            --violet: #5b3df5;
            --violet-2: #8a5cff;
            --pink: #ff5fa2;
            --amber: #ffc83d;
            --mint: #2fd6a3;
            --sky: #38b6ff;
            --line: #ebe8fb;
            --radius: 22px;
            --shadow: 0 20px 50px -20px rgba(91, 61, 245, .35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 84px
        }

        body {
            font-family: 'Nunito', system-ui, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Baloo 2', 'Nunito', sans-serif;
            line-height: 1.15;
            letter-spacing: -.01em
        }

        a {
            color: inherit;
            text-decoration: none
        }

        img,
        svg {
            max-width: 100%;
            display: block
        }

        .container {
            width: min(1160px, 92%);
            margin-inline: auto
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0)
        }

        :focus-visible {
            outline: 3px solid var(--amber);
            outline-offset: 3px;
            border-radius: 8px
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            font-weight: 800;
            border-radius: 999px;
            padding: .85rem 1.6rem;
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 1rem;
            font-family: inherit;
            transition: transform .2s, box-shadow .2s, background .2s
        }

        .btn:hover {
            transform: translateY(-2px)
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--violet), var(--violet-2));
            color: #fff;
            box-shadow: 0 12px 28px -10px rgba(91, 61, 245, .7)
        }

        .btn-accent {
            background: var(--amber);
            color: #3b2a00;
            box-shadow: 0 12px 28px -10px rgba(255, 200, 61, .9)
        }

        .btn-ghost {
            background: #fff;
            border-color: var(--line);
            color: var(--violet)
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem
        }

        /* Nav */
        header.nav {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 50;
            background: rgba(251, 250, 255, .82);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--line)
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-family: 'Baloo 2';
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--violet)
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.8rem;
            font-weight: 700
        }

        .nav-links a:not(.btn) {
            color: var(--muted);
            transition: color .2s
        }

        .nav-links a:not(.btn):hover {
            color: var(--violet)
        }

        .nav-cta {
            display: flex;
            gap: .6rem;
            align-items: center
        }

        .menu-btn {
            display: none;
            background: none;
            border: 0;
            cursor: pointer;
            padding: .5rem
        }

        .menu-btn span {
            display: block;
            width: 26px;
            height: 3px;
            background: var(--ink);
            border-radius: 3px;
            margin: 5px 0;
            transition: .3s
        }

        /* Hero */
        .hero {
            position: relative;
            padding: 150px 0 90px;
            background:
                radial-gradient(900px 500px at 85% -10%, rgba(138, 92, 255, .25), transparent 60%),
                radial-gradient(700px 500px at -10% 30%, rgba(255, 95, 162, .16), transparent 60%),
                radial-gradient(600px 400px at 60% 100%, rgba(56, 182, 255, .15), transparent 60%)
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 3rem;
            align-items: center
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #fff;
            border: 1px solid var(--line);
            padding: .4rem .9rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: .9rem;
            color: var(--violet);
            box-shadow: 0 6px 18px -10px rgba(91, 61, 245, .5)
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5.6vw, 4.4rem);
            margin: 1.2rem 0 1rem;
            font-weight: 800
        }

        .grad {
            background: linear-gradient(120deg, var(--violet), var(--pink) 60%, var(--amber));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent
        }

        .hero p.lead {
            font-size: 1.2rem;
            color: var(--muted);
            max-width: 34rem
        }

        .hero-ctas {
            display: flex;
            flex-wrap: wrap;
            gap: .9rem;
            margin: 2rem 0 1.4rem
        }

        .hero-note {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2rem;
            color: var(--muted);
            font-weight: 700;
            font-size: .95rem
        }

        .hero-note span::before {
            content: "✓";
            color: var(--mint);
            font-weight: 900;
            margin-right: .35rem
        }

        /* Hero art */
        .art {
            position: relative;
            min-height: 470px
        }

        .screen {
            position: absolute;
            inset: 20px 10px 20px 30px;
            background: linear-gradient(160deg, #6a4bff, #9a6bff 55%, #ff7ab8);
            border-radius: 36px;
            box-shadow: var(--shadow);
            padding: 1.6rem;
            color: #fff;
            overflow: hidden
        }

        .screen::after {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            right: -90px;
            bottom: -110px
        }

        .screen h3 {
            font-size: 1.5rem
        }

        .screen small {
            opacity: .85;
            font-weight: 700
        }

        .xpbar {
            height: 14px;
            background: rgba(255, 255, 255, .28);
            border-radius: 99px;
            margin: .8rem 0 1.2rem;
            overflow: hidden
        }

        .xpbar i {
            display: block;
            height: 100%;
            width: 68%;
            background: var(--amber);
            border-radius: 99px;
            animation: grow 2.4s ease-out both
        }

        @keyframes grow {
            from {
                width: 0
            }
        }

        .worlds {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .8rem;
            position: relative;
            z-index: 1
        }

        .world {
            background: rgba(255, 255, 255, .2);
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 20px;
            padding: 1rem;
            font-weight: 800
        }

        .world b {
            display: block;
            font-size: 2rem;
            line-height: 1.1;
            margin-bottom: .2rem
        }

        .float {
            position: absolute;
            background: #fff;
            color: var(--ink);
            border-radius: 18px;
            padding: .7rem 1rem;
            font-weight: 800;
            box-shadow: 0 16px 36px -14px rgba(28, 24, 64, .4);
            display: flex;
            align-items: center;
            gap: .6rem;
            animation: bob 5s ease-in-out infinite;
            z-index: 2
        }

        .float .ic {
            font-size: 1.6rem
        }

        .f1 {
            top: 0;
            right: 0
        }

        .f2 {
            bottom: 60px;
            left: -10px;
            animation-delay: -1.5s
        }

        .f3 {
            bottom: -6px;
            right: 20px;
            animation-delay: -3s
        }

        @keyframes bob {
            50% {
                transform: translateY(-12px)
            }
        }

        /* Sections */
        section {
            padding: 96px 0
        }

        .eyebrow {
            color: var(--violet);
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .85rem
        }

        .sec-head {
            text-align: center;
            max-width: 44rem;
            margin: 0 auto 3.4rem
        }

        .sec-head h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            margin: .5rem 0 .8rem;
            font-weight: 800
        }

        .sec-head p {
            color: var(--muted);
            font-size: 1.1rem
        }

        /* Features */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.4rem
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.8rem;
            transition: transform .25s, box-shadow .25s
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow)
        }

        .ico {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 1.7rem;
            margin-bottom: 1rem
        }

        .card h3 {
            font-size: 1.3rem;
            margin-bottom: .4rem
        }

        .card p {
            color: var(--muted)
        }

        .c1 .ico {
            background: #efe9ff
        }

        .c2 .ico {
            background: #ffe6f1
        }

        .c3 .ico {
            background: #fff3d0
        }

        .c4 .ico {
            background: #d9f7ec
        }

        .c5 .ico {
            background: #dff2ff
        }

        .c6 .ico {
            background: #ffe9dc
        }

        /* Steps */
        .steps {
            background: linear-gradient(180deg, #f3efff, #fbfaff)
        }

        .step-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.6rem;
            counter-reset: s
        }

        .step {
            position: relative;
            text-align: center;
            padding: 2rem 1.4rem
        }

        .step::before {
            counter-increment: s;
            content: counter(s);
            display: grid;
            place-items: center;
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--violet), var(--pink));
            color: #fff;
            font: 800 1.7rem 'Baloo 2';
            box-shadow: 0 14px 26px -10px rgba(91, 61, 245, .6)
        }

        .step h3 {
            font-size: 1.3rem;
            margin-bottom: .4rem
        }

        .step p {
            color: var(--muted)
        }

        /* Parents */
        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3.5rem;
            align-items: center
        }

        .split h2 {
            font-size: clamp(2rem, 3.6vw, 2.8rem);
            margin: .5rem 0 1rem;
            font-weight: 800
        }

        .split p.lead {
            color: var(--muted);
            font-size: 1.1rem;
            margin-bottom: 1.4rem
        }

        .ticks {
            list-style: none;
            display: grid;
            gap: .7rem;
            margin-bottom: 1.8rem
        }

        .ticks li {
            display: flex;
            gap: .7rem;
            font-weight: 700
        }

        .ticks li::before {
            content: "✓";
            flex: none;
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--mint);
            color: #fff;
            font-size: .85rem;
            font-weight: 900
        }

        .dash {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 1.6rem;
            box-shadow: var(--shadow)
        }

        .dash-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            font-weight: 800
        }

        .tag {
            background: #d9f7ec;
            color: #0a7a58;
            border-radius: 99px;
            padding: .2rem .7rem;
            font-size: .8rem
        }

        .stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .8rem;
            margin-bottom: 1.3rem
        }

        .stat {
            background: var(--bg);
            border-radius: 16px;
            padding: .9rem;
            text-align: center
        }

        .stat b {
            display: block;
            font: 800 1.6rem 'Baloo 2';
            color: var(--violet)
        }

        .stat span {
            font-size: .8rem;
            color: var(--muted);
            font-weight: 700
        }

        .bar {
            margin-bottom: .9rem
        }

        .bar div {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            font-size: .9rem;
            margin-bottom: .3rem
        }

        .bar i {
            display: block;
            height: 10px;
            border-radius: 99px;
            background: var(--line);
            overflow: hidden
        }

        .bar i::after {
            content: "";
            display: block;
            height: 100%;
            width: var(--w);
            border-radius: 99px;
            background: linear-gradient(90deg, var(--violet), var(--pink))
        }

        /* Pricing */
        .pricing {
            background: linear-gradient(180deg, #fbfaff, #f3efff)
        }

        .price-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.6rem;
            max-width: 860px;
            margin: 0 auto
        }

        .plan {
            position: relative;
            background: #fff;
            border: 2px solid var(--line);
            border-radius: 28px;
            padding: 2.2rem;
            display: flex;
            flex-direction: column
        }

        .plan.featured {
            border-color: transparent;
            background: linear-gradient(#fff, #fff) padding-box, linear-gradient(135deg, var(--violet), var(--pink), var(--amber)) border-box;
            box-shadow: var(--shadow);
            transform: translateY(-8px)
        }

        .badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--amber);
            color: #3b2a00;
            font-weight: 800;
            font-size: .8rem;
            padding: .35rem 1rem;
            border-radius: 99px;
            white-space: nowrap
        }

        .plan h3 {
            font-size: 1.6rem
        }

        .plan .blurb {
            color: var(--muted);
            margin: .3rem 0 1rem
        }

        .amount {
            font: 800 3rem 'Baloo 2';
            line-height: 1
        }

        .amount small {
            font: 700 1rem 'Nunito';
            color: var(--muted)
        }

        .plan ul {
            list-style: none;
            margin: 1.4rem 0 1.8rem;
            display: grid;
            gap: .65rem;
            flex: 1
        }

        .plan li {
            display: flex;
            gap: .6rem;
            font-weight: 600
        }

        .plan li::before {
            content: "✓";
            color: var(--mint);
            font-weight: 900
        }

        .pay-note {
            text-align: center;
            color: var(--muted);
            margin-top: 2rem;
            font-weight: 700
        }

        /* FAQ */
        .faq {
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            gap: .9rem
        }

        details {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.1rem 1.4rem
        }

        details[open] {
            box-shadow: var(--shadow)
        }

        summary {
            cursor: pointer;
            font: 700 1.15rem 'Baloo 2';
            list-style: none;
            display: flex;
            justify-content: space-between;
            gap: 1rem
        }

        summary::-webkit-details-marker {
            display: none
        }

        summary::after {
            content: "+";
            color: var(--violet);
            font-size: 1.5rem;
            line-height: 1;
            transition: transform .2s
        }

        details[open] summary::after {
            transform: rotate(45deg)
        }

        details p {
            color: var(--muted);
            margin-top: .7rem
        }

        /* CTA */
        .cta-band {
            padding: 0 0 96px
        }

        .cta {
            background: linear-gradient(135deg, #4a2fe0, #8a5cff 55%, #ff5fa2);
            border-radius: 40px;
            color: #fff;
            text-align: center;
            padding: 4.2rem 1.5rem;
            position: relative;
            overflow: hidden
        }

        .cta::before,
        .cta::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12)
        }

        .cta::before {
            width: 300px;
            height: 300px;
            left: -80px;
            top: -120px
        }

        .cta::after {
            width: 240px;
            height: 240px;
            right: -60px;
            bottom: -100px
        }

        .cta h2 {
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 800;
            position: relative
        }

        .cta p {
            max-width: 36rem;
            margin: .8rem auto 2rem;
            opacity: .92;
            font-size: 1.15rem;
            position: relative
        }

        .cta .btn {
            position: relative
        }

        /* Contact */
        .contact-grid {
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            gap: 3rem;
            align-items: start
        }

        .contact-info h2 {
            font-size: clamp(2rem, 3.6vw, 2.8rem);
            margin: .5rem 0 1rem;
            font-weight: 800
        }

        .contact-info p {
            color: var(--muted);
            font-size: 1.08rem;
            margin-bottom: 1.5rem
        }

        .info-item {
            display: flex;
            gap: .9rem;
            align-items: center;
            margin-bottom: 1rem;
            font-weight: 700
        }

        .info-item .ico {
            margin: 0;
            width: 48px;
            height: 48px;
            font-size: 1.3rem;
            background: #efe9ff
        }

        form.contact {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 2rem;
            box-shadow: var(--shadow);
            display: grid;
            gap: 1.1rem
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1rem
        }

        label {
            display: block;
            font-weight: 800;
            font-size: .92rem;
            margin-bottom: .35rem
        }

        input,
        select,
        textarea {
            width: 100%;
            font: inherit;
            padding: .8rem 1rem;
            border: 2px solid var(--line);
            border-radius: 14px;
            background: var(--bg);
            color: var(--ink);
            transition: border .2s, box-shadow .2s
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: 0;
            border-color: var(--violet);
            box-shadow: 0 0 0 4px rgba(91, 61, 245, .15)
        }

        textarea {
            min-height: 140px;
            resize: vertical
        }

        .hp {
            position: absolute;
            left: -9999px
        }

        .alert {
            border-radius: 14px;
            padding: .9rem 1.1rem;
            font-weight: 700
        }

        .alert.ok {
            background: #d9f7ec;
            color: #0a6b4d
        }

        .alert.err {
            background: #ffe3e8;
            color: #a3173a
        }

        .alert ul {
            margin-left: 1.1rem
        }

        /* Footer */
        footer {
            background: #17133a;
            color: #c9c5ee;
            padding: 64px 0 28px
        }

        .foot-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 2rem
        }

        footer .logo {
            color: #fff;
            margin-bottom: .8rem
        }

        footer h4 {
            color: #fff;
            margin-bottom: .8rem;
            font-size: 1.1rem
        }

        footer ul {
            list-style: none;
            display: grid;
            gap: .5rem
        }

        footer a:hover {
            color: var(--amber)
        }

        .copy {
            border-top: 1px solid rgba(255, 255, 255, .12);
            margin-top: 2.5rem;
            padding-top: 1.4rem;
            text-align: center;
            font-size: .9rem
        }

        /* Reveal */
        .reveal {
            opacity: 0;
            transform: translateY(26px);
            transition: opacity .7s ease, transform .7s ease
        }

        .reveal.in {
            opacity: 1;
            transform: none
        }

        /* Responsive */
        @media (max-width:960px) {

            .hero-grid,
            .split,
            .contact-grid {
                grid-template-columns: 1fr
            }

            .art {
                min-height: 420px;
                max-width: 520px;
                margin-inline: auto;
                width: 100%
            }

            .grid-3,
            .step-grid {
                grid-template-columns: 1fr 1fr
            }

            .foot-grid {
                grid-template-columns: 1fr 1fr
            }

            .nav-links {
                position: absolute;
                top: 72px;
                left: 0;
                right: 0;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                background: #fff;
                padding: 1rem 4%;
                border-bottom: 1px solid var(--line);
                display: none
            }

            .nav-links.open {
                display: flex
            }

            .nav-links a {
                padding: .8rem 0
            }

            .nav-cta {
                flex-direction: column;
                align-items: stretch;
                padding-top: .6rem
            }

            .menu-btn {
                display: block
            }
        }

        @media (max-width:620px) {
            section {
                padding: 72px 0
            }

            .hero {
                padding-top: 120px
            }

            .grid-3,
            .step-grid,
            .price-grid,
            .row,
            .foot-grid {
                grid-template-columns: 1fr
            }

            .plan.featured {
                transform: none
            }

            .f2 {
                left: 0
            }
        }

        @media (prefers-reduced-motion:reduce) {
            html {
                scroll-behavior: auto
            }

            * {
                animation: none !important;
                transition: none !important
            }

            .reveal {
                opacity: 1;
                transform: none
            }
        }
    </style>
</head>

<body>
    <a href="#main" class="sr-only">Skip to content</a>

    <header class="nav">
        <div class="container nav-inner">
            <a href="#top" class="logo" aria-label="LearnQuest home">
                <svg width="38" height="38" viewBox="0 0 40 40" aria-hidden="true">
                    <defs>
                        <linearGradient id="lg" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#5b3df5" />
                            <stop offset="1" stop-color="#ff5fa2" />
                        </linearGradient>
                    </defs>
                    <rect width="40" height="40" rx="12" fill="url(#lg)" />
                    <path d="M20 8l3.4 7.2 7.8 1-5.7 5.5 1.5 7.8L20 25.600 13 29.500l1.500-7.800L8.800 16.200l7.800-1z" fill="#ffc83d" />
                </svg>
                LearnQuest
            </a>
            <button class="menu-btn" aria-label="Toggle menu" aria-expanded="false" aria-controls="navlinks"><span></span><span></span><span></span></button>
            <nav class="nav-links" id="navlinks" aria-label="Main">
                <a href="#features">Features</a>
                <a href="#how">How it works</a>
                <a href="#parents">For parents</a>
                <a href="#pricing">Pricing</a>
                <a href="#faq">FAQ</a>
                <a href="#contact">Contact</a>
                <div class="nav-cta">
                    <a href="{{ $childLogin }}" class="btn btn-ghost">Child login</a>
                    <a href="{{ $parentLogin }}" class="btn btn-primary">Parent login</a>
                </div>
            </nav>
        </div>
    </header>

    <main id="main">
        {{-- HERO --}}
        <div class="hero" id="top">
            <div class="container hero-grid">
                <div class="reveal">
                    <span class="pill">🚀 Learning that feels like play</span>
                    <h1>Turn every lesson into a <span class="grad">learning adventure</span></h1>
                    <p class="lead">LearnQuest is the kids' learning app where children explore magical worlds, complete missions, earn XP and badges, and build real skills, while parents follow their progress every step of the way.</p>
                    <div class="hero-ctas">
                        <a href="{{ $parentSignup }}" class="btn btn-accent btn-lg">Start free today</a>
                        <a href="{{ $childLogin }}" class="btn btn-ghost btn-lg">I'm a student</a>
                    </div>
                    <div class="hero-note">
                        <span>No credit card needed</span>
                        <span>Safe for kids</span>
                        <span>Cancel anytime</span>
                    </div>
                </div>

                <div class="art reveal" aria-hidden="true">
                    <div class="screen">
                        <small>Welcome back, explorer!</small>
                        <h3>Level 7 · Star Adventurer</h3>
                        <div class="xpbar"><i></i></div>
                        <div class="worlds">
                            <div class="world"><b>🔢</b>Number Jungle</div>
                            <div class="world"><b>📖</b>Story Island</div>
                            <div class="world"><b>🔬</b>Science Space</div>
                            <div class="world"><b>🌍</b>World Explorer</div>
                        </div>
                    </div>
                    <div class="float f1"><span class="ic">🔥</span> 5-day streak!</div>
                    <div class="float f2"><span class="ic">🏅</span> New badge unlocked</div>
                    <div class="float f3"><span class="ic">⭐</span> +50 XP</div>
                </div>
            </div>
        </div>

        {{-- FEATURES --}}
        <section id="features">
            <div class="container">
                <div class="sec-head reveal">
                    <span class="eyebrow">Why kids love it</span>
                    <h2>Everything a curious child needs to grow</h2>
                    <p>Playful on the outside, carefully structured on the inside. LearnQuest keeps kids motivated while building skills that last.</p>
                </div>
                <div class="grid-3">
                    <article class="card c1 reveal">
                        <div class="ico" aria-hidden="true">🗺️</div>
                        <h3>Worlds to explore</h3>
                        <p>Subjects become themed worlds with courses, modules and short lessons, so learning always feels like a journey and never a chore.</p>
                    </article>
                    <article class="card c2 reveal">
                        <div class="ico" aria-hidden="true">🎯</div>
                        <h3>Daily missions</h3>
                        <p>Bite-sized missions give children a clear goal each day and build a healthy learning habit in just a few minutes.</p>
                    </article>
                    <article class="card c3 reveal">
                        <div class="ico" aria-hidden="true">⭐</div>
                        <h3>XP, levels and badges</h3>
                        <p>Every activity earns rewards. Levels, achievements and streaks celebrate effort and keep motivation high.</p>
                    </article>
                    <article class="card c4 reveal">
                        <div class="ico" aria-hidden="true">🧩</div>
                        <h3>Interactive activities</h3>
                        <p>Hands-on activities practise real skills, from numbers and reading to science and beyond, instead of passive screen time.</p>
                    </article>
                    <article class="card c5 reveal">
                        <div class="ico" aria-hidden="true">📈</div>
                        <h3>Skill-based progress</h3>
                        <p>Progress rolls up from activities to lessons, courses and skills, giving a clear picture of what your child has truly mastered.</p>
                    </article>
                    <article class="card c6 reveal">
                        <div class="ico" aria-hidden="true">🛡️</div>
                        <h3>Safe and simple login</h3>
                        <p>Kids sign in with a username and PIN set by parents. No emails, no open chat, no distractions.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- HOW IT WORKS --}}
        <section id="how" class="steps">
            <div class="container">
                <div class="sec-head reveal">
                    <span class="eyebrow">How it works</span>
                    <h2>Up and learning in three easy steps</h2>
                    <p>Set up in minutes. Your child can be on their first mission today.</p>
                </div>
                <div class="step-grid">
                    <div class="step reveal">
                        <h3>Create a parent account</h3>
                        <p>Sign up free and add a profile for your child with a fun username and a secret PIN.</p>
                    </div>
                    <div class="step reveal">
                        <h3>Your child starts questing</h3>
                        <p>They log in, pick a world and begin missions, collecting XP, badges and streaks as they go.</p>
                    </div>
                    <div class="step reveal">
                        <h3>Watch them grow</h3>
                        <p>Follow progress from your parent dashboard and upgrade whenever you want to unlock more.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- PARENTS --}}
        <section id="parents">
            <div class="container split">
                <div class="reveal">
                    <span class="eyebrow">For parents</span>
                    <h2>Know exactly how your child is doing</h2>
                    <p class="lead">Skip the guesswork. Your parent dashboard turns learning into clear, friendly insights you can act on.</p>
                    <ul class="ticks">
                        <li>See lessons completed, streaks and skills practised</li>
                        <li>Manage child profiles and PINs in one place</li>
                        <li>Switch between children in a single tap</li>
                        <li>Upgrade, pay and manage billing securely</li>
                    </ul>
                    <a href="{{ $parentLogin }}" class="btn btn-primary">Open parent dashboard</a>
                </div>
                <div class="dash reveal" aria-hidden="true">
                    <div class="dash-top"><span>This week</span><span class="tag">On track</span></div>
                    <div class="stat-row">
                        <div class="stat"><b>12</b><span>Lessons</span></div>
                        <div class="stat"><b>5</b><span>Day streak</span></div>
                        <div class="stat"><b>840</b><span>XP earned</span></div>
                    </div>
                    <div class="bar">
                        <div><span>Numbers</span><span>82%</span></div><i style="--w:82%"></i>
                    </div>
                    <div class="bar">
                        <div><span>Reading</span><span>64%</span></div><i style="--w:64%"></i>
                    </div>
                    <div class="bar">
                        <div><span>Science</span><span>47%</span></div><i style="--w:47%"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- PRICING --}}
        <section id="pricing" class="pricing">
            <div class="container">
                <div class="sec-head reveal">
                    <span class="eyebrow">Pricing</span>
                    <h2>Simple pricing for every family</h2>
                    <p>Start free and upgrade when you are ready. No hidden fees, cancel anytime.</p>
                </div>
                <div class="price-grid">
                    @foreach ($plans as $plan)
                    <article class="plan reveal {{ $plan['featured'] ? 'featured' : '' }}">
                        @if ($plan['featured'])<span class="badge">⭐ Most popular</span>@endif
                        <h3>{{ $plan['name'] }}</h3>
                        <p class="blurb">{{ $plan['blurb'] }}</p>
                        <div class="amount">{{ $plan['price'] }} <small>/ {{ $plan['period'] }}</small></div>
                        <ul>
                            @foreach ($plan['features'] as $f)<li>{{ $f }}</li>@endforeach
                        </ul>
                        <a href="{{ $parentSignup }}" class="btn {{ $plan['featured'] ? 'btn-primary' : 'btn-ghost' }} btn-lg">{{ $plan['cta'] }}</a>
                    </article>
                    @endforeach
                </div>
                <p class="pay-note">💳 Pay with card · PayPal · M-Pesa</p>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq">
            <div class="container">
                <div class="sec-head reveal">
                    <span class="eyebrow">FAQ</span>
                    <h2>Questions parents often ask</h2>
                </div>
                <div class="faq">
                    @foreach ($faqs as [$q, $a])
                    <details class="reveal">
                        <summary>{{ $q }}</summary>
                        <p>{{ $a }}</p>
                    </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <div class="cta-band">
            <div class="container">
                <div class="cta reveal">
                    <h2>Ready to start your child's quest?</h2>
                    <p>Join LearnQuest free and give your child a reason to say "just one more lesson!"</p>
                    <a href="{{ $parentSignup }}" class="btn btn-accent btn-lg">Create free account</a>
                </div>
            </div>
        </div>

        {{-- CONTACT --}}
        <section id="contact" style="padding-top:0">
            <div class="container contact-grid">
                <div class="contact-info reveal">
                    <span class="eyebrow">Contact support</span>
                    <h2>We're here to help</h2>
                    <p>Questions about plans, billing or your child's account? Send us a message and our team will get back to you, usually within one business day.</p>
                    <div class="info-item"><span class="ico" aria-hidden="true">✉️</span><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                    <div class="info-item"><span class="ico" aria-hidden="true">⏱️</span><span>Replies within 1 business day</span></div>
                    <div class="info-item"><span class="ico" aria-hidden="true">🏫</span><span>Schools and teachers welcome</span></div>
                </div>

                <form class="contact reveal" method="POST" action="{{ route('contact.send') }}#contact" novalidate>
                    @csrf
                    @if (session('contact_success'))
                    <div class="alert ok" role="status">{{ session('contact_success') }}</div>
                    @endif
                    @if ($errors->any())
                    <div class="alert err" role="alert">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <div class="row">
                        <div><label for="name">Your name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
                        <div><label for="cemail">Email</label><input id="cemail" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
                    </div>
                    <div>
                        <label for="topic">Topic</label>
                        <select id="topic" name="topic" required>
                            @foreach (['general' => 'General question', 'billing' => 'Billing and payments', 'technical' => 'Technical help', 'school' => 'Schools and teachers'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('topic')===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="message">How can we help?</label>
                        <textarea id="message" name="message" required minlength="10">{{ old('message') }}</textarea>
                    </div>
                    <div class="hp" aria-hidden="true"><label>Leave empty<input name="website" tabindex="-1" autocomplete="off"></label></div>
                    <button class="btn btn-primary btn-lg" type="submit">Send message</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="foot-grid">
                <div>
                    <div class="logo">LearnQuest</div>
                    <p>Fun, safe and effective learning adventures for kids, with real insight for parents.</p>
                </div>
                <div>
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how">How it works</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Account</h4>
                    <ul>
                        <li><a href="{{ $childLogin }}">Child login</a></li>
                        <li><a href="{{ $parentLogin }}">Parent login</a></li>
                        <li><a href="{{ $parentSignup }}">Sign up free</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#contact">Contact us</a></li>
                        <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                    </ul>
                </div>
            </div>
            <p class="copy">© {{ date('Y') }} LearnQuest. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu
        const btn = document.querySelector('.menu-btn'),
            links = document.getElementById('navlinks');
        btn.addEventListener('click', () => {
            const open = links.classList.toggle('open');
            btn.setAttribute('aria-expanded', open);
        });
        links.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
            links.classList.remove('open');
            btn.setAttribute('aria-expanded', false);
        }));

        // Scroll reveal
        const els = document.querySelectorAll('.reveal');
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver(entries => entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    io.unobserve(e.target);
                }
            }), {
                threshold: .12
            });
            els.forEach((el, i) => {
                el.style.transitionDelay = (i % 3) * 90 + 'ms';
                io.observe(el);
            });
        } else {
            els.forEach(el => el.classList.add('in'));
        }
    </script>
</body>

</html>