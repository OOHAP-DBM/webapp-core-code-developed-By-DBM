<div>
    <h3 class="text-sm font-semibold mb-3">Gazeflow</h3>

    <div class="flex gap-12 text-sm">
        <div>
            <p class="text-gray-400">Expected Eyeball</p>
            <p class="font-medium">{{ number_format($hoarding->expected_eyeball ?? 0) }}</p>
        </div>

        <div>
            <p class="text-gray-400">Expected Footfall</p>
            <p class="font-medium">{{ number_format($hoarding->expected_footfall ?? 0) }}</p>
        </div>
    </div>
</div>

<hr class="my-6 border-gray-300">
