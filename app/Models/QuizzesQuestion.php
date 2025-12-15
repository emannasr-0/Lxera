<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Support\Facades\URL;

class QuizzesQuestion extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'quizzes_questions';
    public $timestamps = false;
    protected $guarded = ['id'];

    static $multiple = 'multiple';
    static $descriptive = 'descriptive';

    public $translatedAttributes = ['title', 'correct'];
    
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

    public function getCorrectAttribute()
    {
        return getTranslateAttributeValue($this, 'correct');
    }


    public function quizzesQuestionsAnswers()
    {
        return $this->hasMany('App\Models\QuizzesQuestionsAnswer', 'question_id', 'id')->with('translations');
    }

    public function canAccessToEdit($user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $result = false;

        if (!empty($user)) {
            $quiz = Quiz::find($this->quiz_id);

            $webinar = null;
            if (!empty($quiz->webinar_id)) {
                $webinar = Webinar::query()->find($quiz->webinar_id);
            }

            if ($quiz->creator_id != $user->id and (!empty($webinar) and $webinar->canAccess($user))) {
                $quiz = null;
            }


            if (!empty($quiz)) {
                $result = true;
            }
        }

        return $result;
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }
}
