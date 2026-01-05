<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="OOHApp API",
 *     version="1.0.0",
 *     description="Authentication & Platform APIs for OOHApp",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter: Bearer {your access token}"
 * )
 * @OA\Parameter(
 *     parameter="AcceptHeader",
 *     name="Accept",
 *     in="header",
 *     required=true,
 *     description="Accept header. Always set to application/json for API requests.",
 *     @OA\Schema(type="string", default="application/json")
 * )

 * @OA\OpenApi(
 *     @OA\Components(
 *         @OA\Parameter(ref="#/components/parameters/AcceptHeader")
 *     )
 * )
 */
class OpenApi
{
    // This class is just a placeholder for the annotations
}
