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
 *         example="NN-DEL-2024-8891"
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
 *         property="block_dates",
 *         type="boolean",
 *         example=false
 *     ),
 *
 *     @OA\Property(
 *         property="blocked_dates_json",
 *         type="array",
 *         nullable=true,
 *         @OA\Items(type="string", example="2026-01-15")
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
 *         nullable=true,
 *         example=5
 *     ),
 *
 *     @OA\Property(
 *         property="expected_footfall",
 *         type="integer",
 *         nullable=true,
 *         example=1000
 *     ),
 *
 *     @OA\Property(
 *         property="expected_eyeball",
 *         type="integer",
 *         nullable=true,
 *         example=5000
 *     ),
 *
 *     @OA\Property(
 *         property="audience_type",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             example="Students"
 *         )
 *     ),
 *
 *     @OA\Property(
 *         property="visible_from",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             example="From Flyover"
 *         )
 *     ),
 *
 *     @OA\Property(
 *         property="located_at",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             example="Shopping Mall"
 *         )
 *     ),
 *
 *     @OA\Property(
 *         property="visibility_type",
 *         type="string",
 *         enum={"one_way","both_side"},
 *         example="one_way"
 *     ),
 *
 *     @OA\Property(
 *         property="visibility_start",
 *         type="string",
 *         nullable=true,
 *         example="Santacruz"
 *     ),
 *
 *     @OA\Property(
 *         property="visibility_end",
 *         type="string",
 *         nullable=true,
 *         example="Fun Mall"
 *     ),
 *
 *     @OA\Property(
 *         property="brand_logos",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             format="binary"
 *         )
 *     )
 * )
 */
class OOHStep2Request {}
