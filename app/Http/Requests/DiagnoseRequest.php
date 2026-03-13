<?php

namespace App\Http\Requests;

use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;

class DiagnoseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,jpe,png,webp',
                'max:5120',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        $fail('The uploaded file is invalid.');

                        return;
                    }

                    $imageSize = @getimagesize($value->getPathname());

                    if ($imageSize === false || ($imageSize[0] ?? 0) < 1 || ($imageSize[1] ?? 0) < 1) {
                        $fail('The image must be a genuine image file.');
                    }
                },
            ],
            'plant_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
