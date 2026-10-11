<?php

namespace App\Exceptions;

use Exception;

/**
 * Type MIME non autorisé (whitelist : jpeg, png, webp, gif, svg) — rendu en HTTP 415.
 */
class UnsupportedMediaType extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Type de fichier non autorisé (jpeg, png, webp, gif ou svg attendu).',
        ], 415);
    }
}
