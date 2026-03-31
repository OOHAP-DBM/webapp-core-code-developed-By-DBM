<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class OptimizedImage extends Component
{
    public $src;
    public $alt;
    public $class;
    public $width;
    public $height;
    public $fallback;
    public $loading;
    public $decoding;
    public $attributes;
    public $disableLazy;

    /**
     * Create a new component instance.
     *
     * @param string|null $src
     * @param string|null $alt
     * @param string|null $class
     * @param int|null $width
     * @param int|null $height
     * @param string|null $fallback
     * @param bool $disableLazy
     * @return void
     */
    public function __construct($src = null, $alt = '', $class = '', $width = null, $height = null, $fallback = '/assets/images/no-image.png', $disableLazy = false)
    {
        $this->src = $this->resolveSrc($src, $fallback);
        $this->alt = $alt;
        $this->class = $class;
        $this->width = $width;
        $this->height = $height;
        $this->fallback = $fallback;
        $this->loading = $disableLazy ? null : 'lazy';
        $this->decoding = 'async';
        $this->disableLazy = $disableLazy;
    }

    /**
     * Resolve the image source, fallback if missing or not found.
     */
    protected function resolveSrc($src, $fallback)
    {
        if (empty($src)) {
            return asset($fallback);
        }
        // Storage path
        if (str_starts_with($src, 'public/') || str_starts_with($src, 'avatars/') || str_starts_with($src, 'images/')) {
            if (Storage::exists($src)) {
                return Storage::url($src);
            } else {
                return asset($fallback);
            }
        }
        // Full URL
        if (filter_var($src, FILTER_VALIDATE_URL)) {
            return $src;
        }
        // Asset path
        if (file_exists(public_path($src))) {
            return asset($src);
        }
        // Fallback
        return asset($fallback);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.optimized-image');
    }
}
