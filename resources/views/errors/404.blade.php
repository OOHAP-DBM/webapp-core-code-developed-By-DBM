@extends('layouts.app')

@section('title', '404 - Page Not Found | OOHAPP')

@section('content')
    <section class="min-h-[70vh] flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <div class="text-center">
                <p class="text-sm font-semibold tracking-wide text-blue-600">OOHAPP</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">Oops! Page not found</h1>
                <p class="mt-3 text-sm text-slate-600 sm:text-base">
                    The page you are looking for may have been moved, deleted, or the URL might be incorrect.
                </p>
            </div>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ url('/') }}"
                   class="inline-flex w-full items-center justify-center rounded-lg bg-black px-5 py-3 text-sm font-medium text-white transition hover:opacity-90 sm:w-auto">
                    Go to Home
                </a>

                <a href="{{ url('/search') }}"
                   class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                    Explore Hoardings
                </a>
            </div>

            <form action="{{ url('/search') }}" method="GET" class="mt-8">
                <label for="city" class="mb-2 block text-sm font-medium text-slate-700">
                    Find hoardings by city
                </label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        id="city"
                        name="city"
                        type="text"
                        placeholder="e.g. Delhi, Mumbai, Bengaluru"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none ring-blue-500 focus:ring-2"
                    >
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-700">
                        Search
                    </button>
                </div>
            </form>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <p class="text-sm font-medium text-slate-700">Popular pages</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ url('/') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200">Home</a>
                    <!-- <a href="{{ url('/hoardings') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200">Hoardings</a> -->
                    <a href="{{ url('/search') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200">Search</a>
                    <!-- <a href="{{ url('/contact') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200">Contact</a> -->
                </div>
            </div>
        </div>
    </section>
@endsection