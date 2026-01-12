<?php

namespace App\Traits;

trait UppercaseFillable
{
    /**
     * Método que deve ser implementado pela classe que usa o trait
     * para definir quais atributos serão convertidos para uppercase.
     *
     * @return array
     */
    abstract protected function getUppercaseFields(): array;

    /**
     * Boot do trait.
     * Este método é chamado automaticamente pelo Laravel quando o modelo é inicializado.
     */
    protected static function bootUppercaseFillable(): void
    {
        static::saving(function ($model) {
            $model->applyUppercaseToAttributes();
        });
    }

    /**
     * Aplica uppercase aos atributos configurados.
     */
    protected function applyUppercaseToAttributes(): void
    {
        foreach ($this->getUppercaseFields() as $attribute) {
            if (isset($this->attributes[$attribute]) &&
                is_string($this->attributes[$attribute]) &&
                !is_null($this->attributes[$attribute])) {
                $this->attributes[$attribute] = strtoupper($this->attributes[$attribute]);
            }
        }
    }

    /**
     * Intercepta a atribuição de atributos e aplica o uppercase automaticamente.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value): mixed
    {
        if (in_array($key, $this->getUppercaseFields()) &&
            is_string($value)) {
            $value = strtoupper($value);
        }

        return parent::setAttribute($key, $value);
    }
}
