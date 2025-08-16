<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = ['key','value','type','group','description','is_public'];

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['type'] = 'array';
        } elseif (is_bool($value)) {
            $this->attributes['value'] = $value ? '1' : '0';
            $this->attributes['type'] = 'boolean';
        } elseif (is_int($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'integer';
        } elseif (is_float($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'float';
        } else {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'string';
        }
    }
}
