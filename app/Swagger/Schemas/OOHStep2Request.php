<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OOHStep2Request",
 *     type="object",
 *
 *     @OA\Property(
 *         property="nagar_nigam_approved",
 *         type="boolean",
 *         example=true
 *     ),
 *
 *     @OA\Property(
 *         property="permit_number",
 *         type="string",
 *         nullable=true,
 *         example="NGM-2024-4567"
 *     ),
 *
 *     @OA\Property(
 *         property="permit_valid_till",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         example="2026-12-31"
 *     ),
 *
 *     @OA\Property(
 *         property="expected_footfall",
 *         type="integer",
 *         example=50000
 *     ),
 *
 *     @OA\Property(
 *         property="expected_eyeball",
 *         type="integer",
 *         example=150000
 *     ),
 *
 *     @OA\Property(
 *         property="audience_types",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"daily commuters","office crowd"}
 *     ),
 *
 *     @OA\Property(
 *         property="blocked_dates_json",
 *         type="string",
 *         nullable=true,
 *         example={"2026-01-10","2026-01-15"}
 *     ),
 *
 *     @OA\Property(
 *         property="needs_grace_period",
 *         type="boolean",
 *         example=true
 *     ),
 *
 *     @OA\Property(
 *         property="grace_period_days",
 *         type="integer",
 *         example=7
 *     ),
 *
 *     @OA\Property(
 *         property="visibility_type",
 *         type="string",
 *         example="one_way"
 *     ),
 *
 *     @OA\Property(
 *         property="visibility_start",
 *         type="string",
 *         nullable=true,
 *         example="GT Road"
 *     ),
 *
 *     @OA\Property(
 *         property="visibility_end",
 *         type="string",
 *         nullable=true,
 *         example="Central Mall"
 *     ),
 *
 *     @OA\Property(
 *         property="facing_direction",
 *         type="string",
 *         example="north"
 *     ),
 *
 *     @OA\Property(
 *         property="road_type",
 *         type="string",
 *         example="highway"
 *     ),
 *
 *     @OA\Property(
 *         property="traffic_type",
 *         type="string",
 *         example="vehicular"
 *     ),
 *
 *     @OA\Property(
 *         property="visible_from",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"metro station","signal"}
 *     ),
 *
 *     @OA\Property(
 *         property="located_at",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"road divider","junction"}
 *     ),
 *
 *     @OA\Property(
 *         property="brand_logos",
 *         type="array",
 *         @OA\Items(type="string", format="binary")
 *     )
 * )
 */
class OOHStep2Request {}
