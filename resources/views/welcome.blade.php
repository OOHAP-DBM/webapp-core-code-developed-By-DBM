<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OOHAPP') }} - Seamless Hoarding Booking For Maximum Visibility</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1a202c;
            background: #f7fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
        }

        .nav {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s;
        }

        .nav a:hover {
            color: #2563eb;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-hero-primary {
            background: white;
            color: #667eea;
        }

        .btn-hero-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Sections */
        .section {
            padding: 60px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 10px;
        }

        .section-subtitle {
            font-size: 16px;
            color: #718096;
        }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #e2e8f0;
        }

        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-description {
            font-size: 14px;
            color: #718096;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #a0aec0;
            margin-bottom: 12px;
        }

        .card-price {
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 12px;
        }

        .card-price small {
            font-size: 14px;
            font-weight: 400;
            color: #718096;
        }

        .btn-card {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-card:hover {
            background: #1d4ed8;
        }

        /* City Card */
        .city-card {
            position: relative;
            height: 180px;
            border-radius: 12px;
            overflow: hidden;
        }

        .city-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.7) 100%);
            z-index: 1;
        }

        .city-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .city-card-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            z-index: 2;
            color: white;
        }

        .city-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .city-count {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #1a202c;
            color: #a0aec0;
            padding: 60px 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #a0aec0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #2d3748;
            padding-top: 30px;
            text-align: center;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">OOHAPP</div>
                <nav class="nav">
                    <a href="#best-hoardings">Hoardings</a>
                    <a href="#top-dooh">DOOH</a>
                    <a href="#cities">Cities</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register.role-selection') }}" class="btn-primary">Get Started</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Seamless Hoarding Booking<br>For Maximum Visibility</h1>
                <p>Connect with the best outdoor advertising spaces across India. Book hoardings and digital screens in minutes.</p>
                <div class="hero-actions">
                    <a href="{{ route('hoardings.index') }}" class="btn-hero btn-hero-primary">Explore Hoardings</a>
                    <a href="{{ route('dooh.index') }}" class="btn-hero btn-hero-secondary">Browse DOOH</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Hoardings Section -->
    <section class="section" id="best-hoardings">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Best Hoardings</h2>
                <p class="section-subtitle">Premium outdoor advertising spaces</p>
            </div>
            <div class="grid">
                @forelse($bestHoardings as $hoarding)
                    <div class="card" onclick="window.location.href='{{ route('hoardings.show', $hoarding->slug ?? $hoarding->id) }}'">
                        @if($hoarding->hasMedia('images'))
                            <img src="{{ $hoarding->getFirstMediaUrl('images') }}" alt="{{ $hoarding->title }}" class="card-image">
                        @else
                            <div class="card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                        @endif
                        <div class="card-content">
                            <h3 class="card-title">{{ $hoarding->title }}</h3>
                            <p class="card-description">{{ Str::limit($hoarding->description ?? $hoarding->address, 80) }}</p>
                            <div class="card-meta">
                                <span>📍 {{ Str::limit($hoarding->address, 30) }}</span>
                                <span>{{ ucfirst($hoarding->type) }}</span>
                            </div>
                            <div class="card-price">
                                ₹{{ number_format($hoarding->monthly_price, 0) }} <small>/month</small>
                            </div>
                            <button class="btn-card">View Details</button>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #718096;">
                        <p>No hoardings available at the moment. Check back soon!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Top DOOH Section -->
    <section class="section" id="top-dooh" style="background: white;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Top DOOHs</h2>
                <p class="section-subtitle">Digital out-of-home advertising screens</p>
            </div>
            <div class="grid">
                @forelse($topDOOHs as $dooh)
                    <div class="card" onclick="window.location.href='{{ route('dooh.show', $dooh->id) }}'">
                        <div class="card-image" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                        <div class="card-content">
                            <h3 class="card-title">{{ $dooh->name }}</h3>
                            <p class="card-description">{{ Str::limit($dooh->description ?? $dooh->address, 80) }}</p>
                            <div class="card-meta">
                                <span>📍 {{ $dooh->city }}, {{ $dooh->state }}</span>
                                <span>{{ ucfirst($dooh->screen_type) }}</span>
                            </div>
                            <div class="card-price">
                                ₹{{ number_format($dooh->price_per_slot, 0) }} <small>/slot</small>
                            </div>
                            <button class="btn-card">View Details</button>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #718096;">
                        <p>No DOOH screens available at the moment. Check back soon!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
  @dump($city);
    <!-- Top Cities Section -->
    <section class="section" id="cities">
              

        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Top DOAs</h2>
                <p class="section-subtitle">Explore advertising opportunities in major cities</p>
            </div>
            <div class="grid">
                @foreach($topCities as $city)
                    <div class="city-card">
                        <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}">
                        <div class="city-card-content">
                            <div class="city-name">{{ $city['name'] }}</div>
                            <div class="city-count">{{ $city['count'] }} Hoardings</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>OOHAPP</h3>
                    <p style="font-size: 14px; line-height: 1.6;">Your one-stop platform for booking outdoor advertising spaces across India.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('hoardings.index') }}">Hoardings</a></li>
                        <li><a href="{{ route('dooh.index') }}">DOOH Screens</a></li>
                        <li><a href="{{ route('search') }}">Search</a></li>
                        <li><a href="{{ route('register.role-selection') }}">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>For Vendors</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('login') }}">Vendor Login</a></li>
                        <li><a href="#">List Your Hoarding</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} OOHAPP. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
