<?php

namespace App\Http\Requests;

use App\Models\MoodEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PersonalModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && config('pms.modules.'.$this->route('module')) !== null;
    }

    public function rules(): array
    {
        $module = config('pms.modules.'.$this->route('module'));

        if ($module === null) {
            return [];
        }

        $rules = [];

        foreach ($module['fields'] as $field => $definition) {
            $rules[$field] = $definition['rules'];

            if (isset($definition['relationship'])) {
                $table = match ($definition['relationship']) {
                    'journalEntries' => 'journal_entries',
                    'todos' => 'todos',
                    'books' => 'books',
                };

                $rules[$field][] = Rule::exists($table, 'id')
                    ->where('user_id', $this->user()->getAuthIdentifier());
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->route('module') === 'mood' && ! $validator->errors()->has('entry_date')) {
                    $existingMood = MoodEntry::query()
                        ->where('user_id', $this->user()->getAuthIdentifier())
                        ->whereDate('entry_date', $this->input('entry_date'))
                        ->when($this->route('record') !== null, fn ($query) => $query->where('id', '!=', $this->route('record')))
                        ->exists();

                    if ($existingMood) {
                        $validator->errors()->add('entry_date', 'You have already recorded a mood for this day.');
                    }
                }

                if ($this->route('module') !== 'books' || $validator->errors()->hasAny(['current_page', 'total_pages'])) {
                    return;
                }

                $totalPages = $this->input('total_pages');

                if ($totalPages !== null && (int) $this->input('current_page') > (int) $totalPages) {
                    $validator->errors()->add('current_page', 'The current page cannot exceed the total page count.');
                }
            },
        ];
    }
}
