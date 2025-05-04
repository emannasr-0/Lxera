<?php

namespace App\Http\Livewire;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Webinar;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class RegisterBundles extends Component
{
    
    public function render()
    {
        $seoSettings = getSeoMetas('register');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.register_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.register_page_title');
        $pageRobot = getPageRobot('register');

        $referralSettings = getReferralSettings();

        $referralCode = Cookie::get('referral_code');

        $categories = Category::whereNull('parent_id')->where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('activeBundles')
                    ->orWhereHas('activeSubCategories', function ($query) {
                        $query->whereHas('activeBundles');
                    });
            })->get();

        $courses = Webinar::where('unattached', 1)->where('status', 'active')->get();
        
        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'referralCode' => $referralCode,
            'referralSettings' => $referralSettings,
            'categories'  => $categories,
            'courses'  => $courses,
            
        ];

        return view('livewire.register-bundles',['data'=>$data,'courses'=>$courses]);
    }
}
