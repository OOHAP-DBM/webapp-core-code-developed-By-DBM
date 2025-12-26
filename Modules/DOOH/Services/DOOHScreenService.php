<?php
// Modules/DOOH/Services/DOOHScreenService.php

namespace Modules\DOOH\Services;

use Modules\DOOH\Repositories\DOOHScreenRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DOOHScreenService
{
    protected $repo;

    public function __construct(DOOHScreenRepository $repo)
    {
        $this->repo = $repo;
    }

    // public function storeStep1($vendor, $data, $mediaFiles)
    // {
    //     // dd($data)  ;
    //     $validator = Validator::make($data, [
    //         'category'         => 'required|string|max:100',
    //         'screen_type'      => 'required|string|max:50',
    //         'width'            => 'required|numeric|min:0.1',
    //         'height'           => 'required|numeric|min:0.1',
    //         'measurement_unit'=> 'required|in:sqft,sqm',
    //         'address'          => 'required|string|max:255',
    //         'pincode'          => 'required|string|max:20',
    //         'locality'         => 'required|string|max:100',
    //         'price_per_slot'   => 'required|numeric|min:1',
    //         'media'            => 'required',
    //     ], [
    //         'media.required'   => 'At least one media file is required.',
    //     ]);

    //     if ($validator->fails() || count($mediaFiles) < 1) {
    //         $errors = $validator->errors()->toArray();
    //         if (count($mediaFiles) < 1) {
    //             $errors['media'][] = 'At least one media file is required.';
    //         }
    //         return ['success' => false, 'errors' => $errors];
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $screen = $this->repo->createStep1($vendor, $data);
    //         // $mediaPaths = $this->repo->storeMedia($vendor->id, $screen->id, $mediaFiles);
    //         $mediaRecords = $this->repo->storeMedia($screen->id, $mediaFiles);

    //         $screen->media = json_encode($mediaPaths);
    //         $screen->save();
    //         DB::commit();
    //         return ['success' => true, 'screen' => $screen];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return ['success' => false, 'errors' => ['media' => ['Media upload failed. Please try again.']]];
    //     }
    // }
    public function storeStep1($vendor, $data, $mediaFiles)
    {
        $validator = Validator::make($data, [
            'category'          => 'required|string|max:100',
            'screen_type'       => 'required|string|max:50',
            'width'             => 'required|numeric|min:0.1',
            'height'            => 'required|numeric|min:0.1',
            'measurement_unit'  => 'required|in:sqft,sqm',
            'address'           => 'required|string|max:255',
            'pincode'           => 'required|string|max:20',
            'locality'          => 'required|string|max:100',
            'price_per_slot'    => 'required|numeric|min:1',
        ]);

        // if ($validator->fails() || empty($mediaFiles)) {
        //     $errors = $validator->errors()->toArray();

        //     if (empty($mediaFiles)) {
        //         $errors['media'][] = 'At least one media file is required.';
        //     }

        //     return ['success' => false, 'errors' => $errors];
        // }

        // DB::beginTransaction();

        // try {
        //     // Step 1: Create screen
        //     $screen = $this->repo->createStep1($vendor, $data);

        //     // Step 2: Store media
        //     $this->repo->storeMedia($screen->id, $mediaFiles);

        //     DB::commit();

        //     return [
        //         'success' => true,
        //         'screen'  => $screen->fresh('media'),
        //     ];
        // } catch (\Throwable $e) {
        //     DB::rollBack();

        //     logger()->error('DOOH media upload failed', [
        //         'error' => $e->getMessage(),
        //     ]);

        //     return [
        //         'success' => false,
        //         'errors'  => [
        //             'media' => ['Media upload failed. Please try again.'],
        //         ],
        //     ];
        // }
        if ($validator->fails() || empty($mediaFiles)) {
            $errors = $validator->errors()->toArray();
            if (empty($mediaFiles)) {
                $errors['media'][] = 'At least one media file is required.';
            }
            throw new \Illuminate\Validation\ValidationException($validator, response()->json(['errors' => $errors], 422));
        }

        return DB::transaction(function () use ($vendor, $data, $mediaFiles) {
            $screen = $this->repo->createStep1($vendor, $data);
            $this->repo->storeMedia($screen->id, $mediaFiles);
            return ['success' => true, 'screen' => $screen->fresh('media')];
        });
    }
}
