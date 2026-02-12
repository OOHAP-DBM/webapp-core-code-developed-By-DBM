<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OOHAPP — Something Big Is Coming</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont;
        }

        /* subtle grain */
        .noise {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="min-h-screen relative overflow-hidden bg-[#0b1f1d] text-white">

<!-- Animated Gradient Background -->
<div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,#00baa8_0%,transparent_35%),radial-gradient(circle_at_80%_80%,#009e8e_0%,transparent_40%)] opacity-40"></div>
<div class="absolute inset-0 noise pointer-events-none"></div>

<!-- Main -->
<div class="relative z-10 min-h-screen flex items-center justify-center px-6">
    <div class="max-w-4xl w-full text-center">

        <!-- Brand -->
        <div class="flex justify-center mb-10">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#00baa8] to-[#009e8e] flex items-center justify-center shadow-2xl">
                <span class="text-3xl font-black tracking-tight">OO</span>
            </div>
        </div>

        <!-- Headline -->
        <h1 class="text-5xl md:text-7xl font-black leading-tight tracking-tight mb-6">
            The Future of
            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-[#00baa8] to-[#5fffe7]">
                Outdoor Advertising
            </span>
        </h1>

        <p class="text-gray-300 text-lg md:text-xl max-w-2xl mx-auto mb-12">
            We’re crafting a next-generation platform that will redefine how brands
            discover, book, and scale outdoor advertising.
        </p>

        <!-- Countdown -->
        <div class="flex justify-center gap-4 md:gap-6 mb-14">
            @foreach(['Days','Hours','Minutes','Seconds'] as $label)
                <div class="w-20 md:w-24 h-24 md:h-28 rounded-2xl bg-white/10 backdrop-blur-lg border border-white/20 flex flex-col justify-center shadow-xl">
                    <div id="{{ strtolower($label) }}" class="text-3xl md:text-4xl font-bold text-[#00baa8]">00</div>
                    <div class="text-xs uppercase tracking-widest text-gray-400 mt-1">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        <!-- CTA -->
       <div class="flex justify-center mb-10">
            <a href="{{ route('home') }}"
            class="group relative flex items-center gap-2 px-5 py-2.5 rounded-xl 
                    bg-white/5 backdrop-blur-md border border-white/10
                    transition-all duration-300 ease-out
                    hover:bg-emerald-400/10 hover:border-emerald-400/40
                    hover:shadow-[0_0_25px_rgba(16,185,129,0.35)]
                    hover:-translate-y-1">

                {{-- Glow effect --}}
                <span class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100
                            transition duration-300
                            bg-gradient-to-r from-emerald-400/20 via-teal-400/20 to-emerald-400/20 blur-xl">
                </span>

                {{-- Logo --}}
                <h1 class="relative flex items-center text-white text-xl font-semibold tracking-wide
                        transition duration-300 group-hover:text-emerald-300">

                    <svg width="21" height="21" class="mt-1 mx-0.5 transition duration-300 group-hover:scale-110"
                        viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">

                        <path d="M1.19337 8.43476C1.06653 9.1025 1 9.79309 1 10.5C1 16.299 5.47715 21 11 21C16.5228 21 21 16.299 21 10.5C21 4.70101 16.5228 0 11 0C10.0948 0 9.21772 0.126281 8.38383 0.362979V5.73332C9.15366 5.26649 10.0471 4.99936 11 4.99936C13.8933 4.99936 16.2387 7.46208 16.2387 10.5C16.2387 13.5379 13.8933 16.0006 11 16.0006C8.10674 16.0006 5.7613 13.5379 5.7613 10.5C5.7613 9.76959 5.89689 9.07243 6.14307 8.43476H1.19337Z"
                            fill="black"/>

                        <path d="M0 0.7875L7.25 2.05941V7.35L0 6.07809V0.7875Z"
                            class="fill-emerald-400 group-hover:fill-emerald-300 transition duration-300"/>
                    </svg>

                    <span class="ml-1">ohApp</span>
                </h1>
            </a>
        </div>

        <!-- Secondary CTA -->
        <div class="flex justify-center gap-6 text-sm text-gray-400">
            <span>Launching Soon</span>
            <span>•</span>
            <span>Made in India 🇮🇳</span>
            <span>•</span>
            <span>OOHAPP</span>
        </div>

        <!-- Footer -->
        <p class="mt-16 text-xs text-gray-500">
            © {{ date('Y') }} OOHAPP. All rights reserved.
        </p>
    </div>
</div>

<!-- Countdown Script -->
<script>
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + 10);

    function updateCountdown() {
        const now = new Date().getTime();
        const d = targetDate - now;

        if (d <= 0) return;

        days.innerText = Math.floor(d / (1000 * 60 * 60 * 24));
        hours.innerText = Math.floor((d / (1000 * 60 * 60)) % 24);
        minutes.innerText = Math.floor((d / (1000 * 60)) % 60);
        seconds.innerText = Math.floor((d / 1000) % 60);
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>

</body>
</html>
