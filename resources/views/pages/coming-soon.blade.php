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
        <div class="flex flex-col sm:flex-row justify-center gap-4 mb-10">
            <form class="flex w-full sm:w-auto gap-3">
                <input
                    type="email"
                    placeholder="Enter your email for early access"
                    class="px-5 py-4 rounded-xl bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#00baa8] w-72"
                >
                <button
                    type="submit"
                    class="px-8 py-4 rounded-xl bg-gradient-to-r from-[#00baa8] to-[#009e8e] font-bold text-white shadow-2xl hover:scale-105 transition-transform">
                    Notify Me
                </button>
            </form>
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
