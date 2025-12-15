<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class QuizzesQuestionsAnswer extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'quizzes_questions_answers';
    public $timestamps = false;
    protected $guarded = ['id'];

    // 👈 مهم جداً مع الـ Translatable
    public $translatedAttributes = ['title'];

  protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // الدومين + /store
        $baseUrl = rtrim('https://api.lxera.net/store', '/');

        // مثال: uploads/questions/images/Screenshot 2025-11-17 095323.png
        $relativePath = ltrim($this->image, '/');

        $dir  = dirname($relativePath);              // uploads/questions/images
        $file = basename($relativePath);             // Screenshot 2025-11-17 095323.png
        $file = rawurlencode($file);                 // Screenshot%202025-11-17%20095323.png

        $path = $dir . '/' . $file;

        return $baseUrl . '/' . $path;
    }

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }
}
