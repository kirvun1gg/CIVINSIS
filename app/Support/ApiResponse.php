<?php

namespace App\Support;

trait ApiResponse
{
    /**
     * Respuesta JSON compatible con el frontend legacy:
     * { success: bool, message: string, ...extra }
     */
    protected function json(bool $success, string $message = '', array $extra = [])
    {
        return response()->json(array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra));
    }
}
