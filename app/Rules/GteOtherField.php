<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\DataAwareRule; // Untuk akses data lain
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class GteOtherField implements InvokableRule, DataAwareRule
{
    protected string $otherField;
    protected array $data = []; // Untuk menyimpan semua data baris

    public function __construct(string $otherField)
    {
        $this->otherField = $otherField;
    }

    /**
     * Setel data yang sedang divalidasi.
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function __invoke($attribute, $value, $fail): void
    {
        $otherValue = $this->data[$this->otherField] ?? null;

        // Logging untuk debugging
        \Illuminate\Support\Facades\Log::info("Custom GTE Rule Check:", [
            'attribute' => $attribute, // nama field yg divalidasi (jarak_km_max)
            'value' => $value,          // nilai jarak_km_max
            'otherField' => $this->otherField, // nama field pembanding (jarak_km_min)
            'otherValue' => $otherValue, // nilai jarak_km_min
            'value_type' => gettype($value),
            'otherValue_type' => gettype($otherValue)
        ]);

        if ($otherValue === null || $value === null) {
            // Jika salah satu nullable dan kosong, biarkan aturan nullable yang menangani
            return;
        }

        // Pastikan keduanya numerik sebelum perbandingan
        if (!is_numeric($value) || !is_numeric($otherValue)) {
            // Ini seharusnya sudah ditangani oleh aturan 'numeric' sebelumnya, tapi sebagai failsafe
            $fail("Nilai untuk :attribute dan {$this->otherField} harus numerik.");
            return;
        }

        if (floatval($value) < floatval($otherValue)) {
            $fail("Nilai untuk :attribute harus lebih besar atau sama dengan nilai pada kolom {$this->otherField}.");
        }
    }
}