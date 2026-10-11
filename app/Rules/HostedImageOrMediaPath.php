<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Remplace le « optionalImage » Joi du frontend (plus de dataURL côté API) :
 * accepte une URL http(s) ou un chemin /storage/... retourné par POST /media.
 */
class HostedImageOrMediaPath implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // nullable
        }

        if (! is_string($value)) {
            $fail('L\'image doit être un média hébergé ou une URL valide (http/https)');
        }

        if (preg_match('#^https?://.+#', $value) === 1) {
            return;
        }

        if (str_starts_with($value, '/storage/')) {
            return;
        }

        $fail('L\'image doit être un média hébergé ou une URL valide (http/https)');
    }
}
