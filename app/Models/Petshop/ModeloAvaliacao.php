<?php

declare(strict_types=1);

namespace App\Models\Petshop;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModeloAvaliacao extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ativo';

    public const STATUS_INACTIVE = 'inativo';

    protected $table = 'petshop_vet_modelos_avaliacao';

    protected $fillable = [
        'empresa_id',
        'title',
        'category',
        'notes',
        'fields',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'fields' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function atualizador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
        ];
    }

    public static function categories(): array
    {
        return [
            'anamnese' => 'Anamnese',
            'clinico' => 'Clínico',
            'pre_operatorio' => 'Pré-operatório',
            'pos-operatorio' => 'Pós-operatório',
            'pos_operatorio' => 'Pós-operatório',
            'retorno' => 'Retorno',
            'triagem' => 'Triagem',
            'personalizado' => 'Personalizado',
        ];
    }

    public static function categoryLabel(?string $category): ?string
    {
        $category = is_string($category) ? trim($category) : null;

        if (! $category) {
            return null;
        }

        $categories = self::categories();

        if (array_key_exists($category, $categories)) {
            return $categories[$category];
        }

        return Str::of($category)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }

    public static function fieldTypes(): array
    {
        return array_keys(self::fieldTypeOptions());
    }

    public static function fieldTypeOptions(): array
    {
        return [
            'text' => 'Texto curto',
            'textarea' => 'Texto longo',
            'number' => 'Número (decimal)',
            'integer' => 'Número inteiro',
            'date' => 'Data',
            'time' => 'Hora',
            'datetime' => 'Data e hora',
            'select' => 'Lista de opções',
            'multi_select' => 'Lista de opções (múltipla)',
            'checkbox' => 'Caixa de seleção (Sim/Não)',
            'checkbox_group' => 'Grupo de checkboxes',
            'radio_group' => 'Grupo de radio buttons',
            'email' => 'E-mail',
            'phone' => 'Telefone',
            'file' => 'Upload de arquivo',
            'rich_text' => 'Editor de texto',
        ];
    }

    public static function fieldTypeLabel(?string $type): ?string
    {
        $type = is_string($type) ? trim($type) : null;

        if (! $type) {
            return null;
        }

        return self::fieldTypeOptions()[$type] ?? null;
    }

    public static function configKeysForType(string $type): array
    {
        return match ($type) {
            'text' => ['placeholder'],
            'textarea' => ['textarea_placeholder'],
            'number' => ['number_min', 'number_max'],
            'integer' => ['integer_min', 'integer_max'],
            'date' => ['date_hint'],
            'time' => ['time_hint'],
            'datetime' => ['datetime_hint'],
            'select' => ['select_options'],
            'multi_select' => ['multi_select_options'],
            'checkbox' => ['checkbox_label_checked', 'checkbox_label_unchecked', 'checkbox_default'],
            'checkbox_group' => ['checkbox_group_options'],
            'radio_group' => ['radio_group_options', 'radio_group_default'],
            'email' => ['email_placeholder'],
            'phone' => ['phone_placeholder'],
            'file' => ['file_types', 'file_max_size'],
            'rich_text' => ['rich_text_default'],
            default => [],
        };
    }

    public static function configLabel(string $key, ?string $type = null): string
    {
        return match ($key) {
            'placeholder' => 'Placeholder',
            'textarea_placeholder' => 'Placeholder',
            'number_min' => 'Mínimo',
            'number_max' => 'Máximo',
            'integer_min' => 'Mínimo',
            'integer_max' => 'Máximo',
            'date_hint' => 'Dica/descrição',
            'time_hint' => 'Dica/descrição',
            'datetime_hint' => 'Dica/descrição',
            'select_options' => 'Opções',
            'multi_select_options' => 'Opções',
            'checkbox_group_options' => 'Opções',
            'radio_group_options' => 'Opções',
            'radio_group_default' => 'Opção padrão',
            'checkbox_label_checked' => 'Texto (marcado)',
            'checkbox_label_unchecked' => 'Texto (desmarcado)',
            'checkbox_default' => 'Padrão',
            'email_placeholder' => 'Exemplo/placeholder',
            'phone_placeholder' => 'Exemplo/placeholder',
            'file_types' => 'Tipos permitidos',
            'file_max_size' => 'Tamanho máximo (MB)',
            'rich_text_default' => 'Texto padrão',
            default => $type ? ($key . ' (' . $type . ')') : $key,
        };
    }
}
