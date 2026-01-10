<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="DOOHStep3Request",
 * type="object",
 *
 * @OA\Property(
 * property="price_per_slot",
 * type="number",
 * format="float",
 * example=150
 * ),
 *
 * @OA\Property(
 * property="video_length",
 * type="integer",
 * enum={15,30},
 * example=15
 * ),
 *
 * @OA\Property(
 * property="base_monthly_price",
 * type="number",
 * format="float",
 * example=250000
 * ),
 *
 * @OA\Property(
 * property="monthly_offered_price",
 * type="number",
 * format="float",
 * nullable=true,
 * example=230000
 * ),
 *
 * @OA\Property(
 * property="enable_weekly_booking",
 * type="boolean",
 * example=true
 * ),
 *
 * @OA\Property(
 * property="weekly_price_1",
 * type="number",
 * format="float",
 * nullable=true,
 * example=80000
 * ),
 *
 * @OA\Property(
 * property="weekly_price_2",
 * type="number",
 * format="float",
 * nullable=true,
 * example=150000
 * ),
 *
 * @OA\Property(
 * property="weekly_price_3",
 * type="number",
 * format="float",
 * nullable=true,
 * example=210000
 * ),
 *
 * @OA\Property(
 * property="slots",
 * type="array",
 * @OA\Items(
 * type="object",
 * @OA\Property(property="name", type="string", example="Morning"),
 * @OA\Property(property="start_time", type="string", example="8:00 AM"),
 * @OA\Property(property="end_time", type="string", example="12:00 PM"),
 * @OA\Property(property="active", type="boolean", example=true)
 * )
 * ),
 *
 *   @OA\Property(
 *     property="offers_json",
 *     type="string",
 *     description="JSON-encoded array of campaign packages. Each package object should have: name, min_booking_duration, duration_unit, discount, end_date, services.",
 *     example="[{name: 'Annual Bulk', min_booking_duration: 12, duration_unit: 'months', discount: 10, end_date: '2026-12-31', services: ['printing','mounting']}]"
 *   ),
 *
 * @OA\Property(
 * property="graphics_included",
 * type="boolean",
 * example=true
 * ),
 *
 * @OA\Property(
 * property="graphics_charge",
 * type="number",
 * format="float",
 * nullable=true,
 * example=5000
 * )
 * )
 */
class DOOHStep3Request {}
