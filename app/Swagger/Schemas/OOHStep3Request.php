<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OOHStep3Request",
 *     type="object",
 *
 *     @OA\Property(
 *         property="offer_name",
 *         type="string",
 *         nullable=true,
 *         example="Republic Day Special"
 *     ),
 *
 *     @OA\Property(
 *         property="discount_type",
 *         type="string",
 *         enum={"percentage","flat"},
 *         nullable=true,
 *         example="percentage"
 *     ),
 *
 *     @OA\Property(
 *         property="discount_value",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         example=15
 *     ),
 *
 *     @OA\Property(
 *         property="min_booking_duration",
 *         type="integer",
 *         example=1
 *     ),
 *
 *     @OA\Property(
 *         property="duration_unit",
 *         type="string",
 *         example="months"
 *     ),
 *
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         example="2026-02-01"
 *     ),
 *
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         example="2026-04-30"
 *     ),
 *
 *     @OA\Property(
 *         property="auto_apply",
 *         type="boolean",
 *         example=false
 *     )
 * )
 */
class OOHStep3Request {}
