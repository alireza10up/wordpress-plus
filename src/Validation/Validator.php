<?php

namespace Alireza10up\WordpressPlus\Validation;

use Illuminate\Validation\Factory;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;

class Validator {
    /**
     * Validation Errors
     * @var array
     */
    private $errors = [];
    
    /**
     * Validation Messages
     * @var array
     */
    private $messages = [
        'accepted' => 'فیلد :attribute باید پذیرفته شود.',
        'active_url' => 'فیلد :attribute یک آدرس معتبر نیست.',
        'after' => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
        'after_or_equal' => 'فیلد :attribute باید تاریخی برابر یا بعد از :date باشد.',
        'alpha' => 'فیلد :attribute باید فقط شامل حروف باشد.',
        'alpha_dash' => 'فیلد :attribute باید فقط شامل حروف، اعداد، خط فاصله و زیرخط باشد.',
        'alpha_num' => 'فیلد :attribute باید فقط شامل حروف و اعداد باشد.',
        'array' => 'فیلد :attribute باید یک آرایه باشد.',
        'before' => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
        'before_or_equal' => 'فیلد :attribute باید تاریخی برابر یا قبل از :date باشد.',
        'between' => [
            'numeric' => 'فیلد :attribute باید بین :min و :max باشد.',
            'file' => 'فیلد :attribute باید بین :min و :max کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید بین :min و :max کاراکتر باشد.',
            'array' => 'فیلد :attribute باید بین :min و :max آیتم داشته باشد.',
        ],
        'boolean' => 'فیلد :attribute باید درست یا غلط باشد.',
        'confirmed' => 'تأییدیه :attribute مطابقت ندارد.',
        'date' => 'فیلد :attribute یک تاریخ معتبر نیست.',
        'date_equals' => 'فیلد :attribute باید تاریخی برابر با :date باشد.',
        'date_format' => 'فیلد :attribute با قالب :format مطابقت ندارد.',
        'different' => 'فیلد :attribute و :other باید متفاوت باشند.',
        'digits' => 'فیلد :attribute باید :digits رقم باشد.',
        'digits_between' => 'فیلد :attribute باید بین :min و :max رقم باشد.',
        'dimensions' => 'فیلد :attribute دارای ابعاد تصویر نامعتبر است.',
        'distinct' => 'فیلد :attribute دارای مقدار تکراری است.',
        'email' => 'فیلد :attribute باید یک ایمیل معتبر باشد.',
        'exists' => 'فیلد :attribute انتخاب شده معتبر نیست.',
        'file' => 'فیلد :attribute باید یک فایل باشد.',
        'filled' => 'فیلد :attribute باید مقدار داشته باشد.',
        'gt' => [
            'numeric' => 'فیلد :attribute باید بزرگ‌تر از :value باشد.',
            'file' => 'فیلد :attribute باید بزرگ‌تر از :value کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید بزرگ‌تر از :value کاراکتر باشد.',
            'array' => 'فیلد :attribute باید بیشتر از :value آیتم داشته باشد.',
        ],
        'gte' => [
            'numeric' => 'فیلد :attribute باید بزرگ‌تر یا برابر با :value باشد.',
            'file' => 'فیلد :attribute باید بزرگ‌تر یا برابر با :value کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید بزرگ‌تر یا برابر با :value کاراکتر باشد.',
            'array' => 'فیلد :attribute باید :value آیتم یا بیشتر داشته باشد.',
        ],
        'image' => 'فیلد :attribute باید یک تصویر باشد.',
        'in' => 'فیلد :attribute انتخاب شده معتبر نیست.',
        'in_array' => 'فیلد :attribute در :other وجود ندارد.',
        'integer' => 'فیلد :attribute باید عدد صحیح باشد.',
        'ip' => 'فیلد :attribute باید یک آدرس IP معتبر باشد.',
        'ipv4' => 'فیلد :attribute باید یک آدرس IPv4 معتبر باشد.',
        'ipv6' => 'فیلد :attribute باید یک آدرس IPv6 معتبر باشد.',
        'json' => 'فیلد :attribute باید یک رشته JSON معتبر باشد.',
        'lt' => [
            'numeric' => 'فیلد :attribute باید کمتر از :value باشد.',
            'file' => 'فیلد :attribute باید کمتر از :value کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید کمتر از :value کاراکتر باشد.',
            'array' => 'فیلد :attribute باید کمتر از :value آیتم داشته باشد.',
        ],
        'lte' => [
            'numeric' => 'فیلد :attribute باید کمتر یا برابر با :value باشد.',
            'file' => 'فیلد :attribute باید کمتر یا برابر با :value کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید کمتر یا برابر با :value کاراکتر باشد.',
            'array' => 'فیلد :attribute نباید بیشتر از :value آیتم داشته باشد.',
        ],
        'max' => [
            'numeric' => 'فیلد :attribute نباید بزرگ‌تر از :max باشد.',
            'file' => 'فیلد :attribute نباید بزرگ‌تر از :max کیلوبایت باشد.',
            'string' => 'فیلد :attribute نباید بزرگ‌تر از :max کاراکتر باشد.',
            'array' => 'فیلد :attribute نباید بیشتر از :max آیتم داشته باشد.',
        ],
        'mimes' => 'فیلد :attribute باید یکی از انواع فایل‌های زیر باشد: :values.',
        'min' => [
            'numeric' => 'فیلد :attribute باید حداقل :min باشد.',
            'file' => 'فیلد :attribute باید حداقل :min کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید حداقل :min کاراکتر باشد.',
            'array' => 'فیلد :attribute باید حداقل :min آیتم داشته باشد.',
        ],
        'numeric' => 'فیلد :attribute باید عدد باشد.',
        'regex' => 'فرمت فیلد :attribute معتبر نیست.',
        'required' => 'فیلد :attribute الزامی است.',
        'same' => 'فیلد :attribute و :other باید یکسان باشند.',
        'size' => [
            'numeric' => 'فیلد :attribute باید :size باشد.',
            'file' => 'فیلد :attribute باید :size کیلوبایت باشد.',
            'string' => 'فیلد :attribute باید :size کاراکتر باشد.',
            'array' => 'فیلد :attribute باید شامل :size آیتم باشد.',
        ],
    ];    

    /**
     * Validate Data
     * @param mixed $data
     * @param mixed $rules
     * @return bool
     */
    public function validate($data, $rules) {
        $translator = new Translator(new ArrayLoader(), 'fa');
        $factory = new Factory($translator);

        $validator = $factory->make($data, $rules, $this->messages);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->all();
            return false;
        }

        return true;
    }

    /**
     * Check Validation
     * @return array|bool
     */
    public function check() {
        return empty($this->errors) ? true : $this->errors;
    }
}