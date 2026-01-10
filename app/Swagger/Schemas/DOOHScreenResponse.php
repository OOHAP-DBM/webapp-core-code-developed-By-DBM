<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="DOOHScreenResponse",
 * type="object",
 * title="DOOH Screen Response",
 * description="The response object returned after a successful screen update"
 * )
 */
class DOOHScreenResponse
{
    /**
     * @OA\Property(property="success", type="boolean", example=true)
     */
    public $success;

    /**
     * @OA\Property(property="message", type="string", example="Screen updated successfully")
     */
    public $message;

    /**
     * @OA\Property(
     * property="data",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="price_per_slot", type="number", format="float", example=150.00),
     * @OA\Property(property="video_length", type="integer", example=15),
     * @OA\Property(property="base_monthly_price", type="number", format="float", example=250000.00),
     * @OA\Property(property="enable_weekly_booking", type="boolean", example=true),
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
     * @OA\Property(
     * property="offers",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="name", type="string", example="Festival"),
     * @OA\Property(property="discount", type="integer", example=10)
     * )
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-09T10:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-09T12:30:00Z")
     * )
     */
    public $data;
}
