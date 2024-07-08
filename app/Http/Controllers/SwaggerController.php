<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Pet Shop API",
 *         version="1.0",
 *         description="API documentation for the Pet Shop application"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="BearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT"
 *         )
 *     )
 * )
 */
class SwaggerController extends Controller
{
    //
}
