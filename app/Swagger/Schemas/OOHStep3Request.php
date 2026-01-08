<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="OOHStep3Request",
 *   type="object",
 *   required={"offers_json"},
 *   @OA\Property(
 *     property="offers_json",
 *     type="string",
 *     description="JSON-encoded array of campaign packages. Each package object should have: name, min_booking_duration, duration_unit, discount, end_date, services.",
 *     example="[{name: 'Annual Bulk', min_booking_duration: 12, duration_unit: 'months', discount: 10, end_date: '2026-12-31', services: ['printing','mounting']}]"
 *   ),
 *   @OA\Property(property="enable_weekly_booking", type="boolean", example=true),
 *   @OA\Property(property="weekly_price_1", type="number", format="float", example=345),
 *   @OA\Property(property="weekly_price_2", type="number", format="float", example=543),
 *   @OA\Property(property="weekly_price_3", type="number", format="float", example=45),
 *   @OA\Property(property="graphics_included", type="boolean", example=false),
 *   @OA\Property(property="graphics_charge", type="number", format="float", example=354),
 *   @OA\Property(property="printing_included", type="boolean", example=false),
 *   @OA\Property(property="printing_charge", type="number", format="float", example=5435),
 *   @OA\Property(property="printing_material_type", type="string", example="flex"),
 *   @OA\Property(property="mounting_included", type="boolean", example=false),
 *   @OA\Property(property="mounting_charge", type="number", format="float", example=34),
 *   @OA\Property(property="lighting_included", type="boolean", example=false),
 *   @OA\Property(property="lighting_charge", type="number", format="float", example=43),
 *   @OA\Property(property="lighting_type", type="string", example="front-lit"),
 *   @OA\Property(property="remounting_charge", type="number", format="float", example=345),
 *   @OA\Property(property="survey_charge", type="number", format="float", example=34)
 * )
 */
class OOHStep3Request {}
