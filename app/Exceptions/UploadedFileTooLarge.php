<?php

namespace App\Exceptions;

use Exception;

/**
 * Fichier uploadé trop volumineux (limite : 3 Mo) — rendu en HTTP 413.
 */
class UploadedFileTooLarge extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Le fichier dépasse la taille maximale autorisée (3 Mo).',
        ], 413);
    }
}
