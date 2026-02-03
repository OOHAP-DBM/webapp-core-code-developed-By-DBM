<?php

namespace App\Swagger\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CartResponse",
 *     type="object",
 *     required={"success","status"},
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="status", type="string", example="updated"),
 *     @OA\Property(property="message", type="string", example="Cart updated successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="in_cart", type="boolean", example=true),
 *         @OA\Property(property="final_price", type="number", example=345),
 *         @OA\Property(property="cart_count", type="integer", example=1)
 *     )
 * )
 */
class CartResponse {}
