<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OOHStep1Request",
 *     type="object",
 *     required={
 *         "category",
 *         "width",
 *         "height",
 *         "measurement_unit",
 *         "address",
 *         "pincode",
 *         "locality",
 *         "state",
 *         "base_monthly_price",
 *         "lat",
 *         "lng"
 *     },
 *
 *     @OA\Property(
 *         property="category",
 *         type="string",
 *         example="Billboard"
 *     ),
 *
 *     @OA\Property(
 *         property="width",
 *         type="number",
 *         format="float",
 *         example=40
 *     ),
 *
 *     @OA\Property(
 *         property="height",
 *         type="number",
 *         format="float",
 *         example=20
 *     ),
 *
 *     @OA\Property(
 *         property="measurement_unit",
 *         type="string",
 *         enum={"sqft","sqm"},
 *         example="sqft"
 *     ),
 *
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         example="MG Road, Near Metro Station"
 *     ),
 *
 *     @OA\Property(
 *         property="pincode",
 *         type="string",
 *         example="560001"
 *     ),
 *
 *     @OA\Property(
 *         property="locality",
 *         type="string",
 *         example="MG Road"
 *     ),
 *
 *     @OA\Property(
 *         property="city",
 *         type="string",
 *         nullable=true,
 *         example="Bengaluru"
 *     ),
 *
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         example="Karnataka"
 *     ),
 *
 *     @OA\Property(
 *         property="lat",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         example=12.9716
 *     ),
 *
 *     @OA\Property(
 *         property="lng",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         example=77.5946
 *     ),
 *
 *     @OA\Property(
 *         property="base_monthly_price",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         example=250000
 *     ),
 *
 *     @OA\Property(
 *         property="monthly_price",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         example=24000
 *     ),
 *
 *      @OA\Property(
 *                 property="media",
 *                 type="array",
 *                 @OA\Items(type="string", format="binary")
 *             )
 * )
 */
class OOHStep1Request {}
