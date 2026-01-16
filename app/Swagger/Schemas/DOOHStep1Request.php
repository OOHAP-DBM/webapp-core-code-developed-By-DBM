<?php
namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="DOOHStep1Request",
 * type="object",
 * required={
 * "category","screen_type","measurement_unit",
 * "width","height",
 * "resolution_type",
 * "address","locality","pincode",
 * "lat","lng",
 * "price_per_10_sec_slot","price_per_30_sec_slot",
 * "media"
 * },
 *
 * @OA\Property(property="category", type="string"),
 * @OA\Property(property="screen_type", type="string", enum={"LED","LCD"}),
 *
 * @OA\Property(property="measurement_unit", type="string", enum={"sqft","sqm"}),
 * @OA\Property(property="width", type="number", example=500),
 * @OA\Property(property="height", type="number", example=300),
 *
 * @OA\Property(property="resolution_type", type="string", example="Full HD (1920 × 1080)"),
 * @OA\Property(property="resolution_width", type="integer", nullable=true, example=1920),
 * @OA\Property(property="resolution_height", type="integer", nullable=true, example=1080),
 *
 * @OA\Property(property="address", type="string"),
 * @OA\Property(property="locality", type="string"),
 * @OA\Property(property="city", type="string", nullable=true),
 * @OA\Property(property="state", type="string", nullable=true),
 * @OA\Property(property="pincode", type="string"),
 *
 * @OA\Property(
 * property="landmarks",
 * type="array",
 * @OA\Items(type="string")
 * ),
 *
 * @OA\Property(property="lat", type="number", format="double"),
 * @OA\Property(property="lng", type="number", format="double"),
 * @OA\Property(property="geotag", type="string", format="uri", nullable=true),
 *
 * @OA\Property(property="price_per_10_sec_slot", type="number"),
 * @OA\Property(property="price_per_30_sec_slot", type="number"),
 *
 * @OA\Property(
 * property="media",
 * type="array",
 * @OA\Items(type="string", format="binary")
 * )
 * )
 */
class DOOHStep1Request {}


// public function rules(): array
// {
//     return [
//         'category' => 'required|string',
//         'screen_type' => 'required|in:LED,LCD',

//         'measurement_unit' => 'required|in:sqft,sqm',
//         'width' => 'required|numeric|min:1',
//         'height' => 'required|numeric|min:1',

//         'resolution_type' => 'required|string',
//         'resolution_width' => 'required_if:resolution_type,custom|integer',
//         'resolution_height' => 'required_if:resolution_type,custom|integer',

//         'address' => 'required|string',
//         'locality' => 'required|string',
//         'city' => 'nullable|string',
//         'state' => 'nullable|string',
//         'pincode' => 'required|string',

//         'landmarks' => 'nullable|array',
//         'landmarks.*' => 'string',

//         'lat' => 'required|numeric|between:-90,90',
//         'lng' => 'required|numeric|between:-180,180',
//         'geotag' => 'nullable|url',

//         'price_per_10_sec_slot' => 'required|numeric|min:1',
//         'price_per_30_sec_slot' => 'required|numeric|min:1',

//         'media' => 'required|array|min:1',
//         'media.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
//     ];
// }
