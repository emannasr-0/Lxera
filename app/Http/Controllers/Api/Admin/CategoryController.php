<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Translation\CategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CategoryRequirement;
use App\Models\Api\Organization;

class CategoryController extends Controller
{
    public function index()
    {
        removeContentLocale();

        $this->authorize('admin_categories_list');

        $categories = Category::with(['subCategories'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $categories->getCollection()->transform(function ($category) {
            return [
                'id' => $category->id,
                'icon' => $category->icon,
                'slug' => $category->slug, 
                'order' => $category->order,
                'title' => $category->title,
                'subCategories' => $category->subCategories->count(),
                'courses_count' => count($category->getCategoryCourses()),
                'teachers_count' => count($category->getCategoryInstructorsIdsHasMeeting()),
                'status' => $category->status,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => [
                'categories' => $categories
            ]
        ], 200);
    }

    public function categories()
    {
        removeContentLocale();

        $this->authorize('admin_categories_list');

        $categories = Category::whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $categories->getCollection()->transform(function ($category) {
            return [
                'id' => $category->id,
                'icon' => asset('store/' . $category->icon),
                'order' => $category->order,
                 'slug' => $category->slug,
                'title' => $category->title,
                'subCategoriesCount' => $category->subCategories->count(),
                'courses_count' => count($category->getCategoryCourses()),
                'teachers_count' => count($category->getCategoryInstructorsIdsHasMeeting()),
                'status' => $category->status,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => [
                'categories' => $categories
            ]
        ], 200);
    }

    public function subCategories($url_name, $categoryId)
    {
        removeContentLocale();

        $this->authorize('admin_categories_list');

        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }


        $subCategories = Category::where('parent_id', $categoryId)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $subCategories->getCollection()->transform(function ($sub) {
            return [
                'id' => $sub->id,
                 'icon' => asset('store/' . $sub->icon),
                'order' => $sub->order,
                'title' => $sub->title,
                'slug' => $sub->slug,
                'courses_count' => count($sub->getCategoryCourses()),
                'teachers_count' => count($sub->getCategoryInstructorsIdsHasMeeting()),
                'status' => $sub->status,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => [
                'subCategories' => $subCategories
            ]
        ], 200);
    }

    public function show($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $category = Category::with(['subCategories.translations', 'categoryRequirements', 'translations'])->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->has_sub = ($category->subCategories && $category->subCategories->count() > 0) ? 1 : 0;

        $data = [
            'id' => $category->id,
            'slug' => $category->slug,
            'parent_id' => $category->parent_id,
            'education' => $category->education,
            'icon' => asset('store/' . $category->icon),
            'order' => $category->order,
            'status' => $category->status,
            'has_sub' => $category->has_sub,
            'title' => optional($category->translations->first())->title, // take title from first translation
            'sub_categories' => $category->subCategories->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'slug' => $sub->slug,
                    'parent_id' => $sub->parent_id,
                    'education' => $sub->education,
                    'icon' => $sub->icon,
                    'order' => $sub->order,
                    'status' => $sub->status,
                    'title' => optional($sub->translations->first())->title, // direct title
                ];
            }),
            'category_requirements' => $category->categoryRequirements,
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

public function store(Request $request)
{
    try {
        $this->authorize('admin_categories_create');

        // -------------------------
        // 🔍 1) Validation
        // -------------------------
        $validated = $request->validate([
            'title' => 'required|min:3|max:128',
            'slug' => 'nullable|max:255|unique:categories,slug',
            'icon' => 'required', 
            'status' => 'required|in:active,inactive',
            'locale' => 'required|string|min:2|max:5',
            'sub_categories' => 'nullable|array',
            'requirements' => 'nullable|array',
        ]);

        $data = $request->all();

        // -------------------------
        // 📌 2) معالجة رفع الصورة (الأساسية)
        // -------------------------
        $path = $this->uploadImage($request->icon);

        // -------------------------
        // 📌 3) إنشاء التصنيف
        // -------------------------
        $order = $data['order'] ?? Category::whereNull('parent_id')->count() + 1;

        $category = Category::create([
            'slug' => $data['slug'] ?? Category::makeSlug($data['title']),
            'icon' => $path,
            'order' => $order,
            'status' => $data['status']
        ]);

        CategoryTranslation::updateOrCreate([
            'category_id' => $category->id,
            'locale' => $data['locale'],
        ], [
            'title' => $data['title'],
        ]);

        // -------------------------
        // 📌 4) إنشاء Sub Categories
        // -------------------------
        $subCategories = [];
        if (!empty($data['sub_categories'])) {
            foreach ($data['sub_categories'] as $sub) {

                $subIcon = !empty($sub['icon']) ? $this->uploadImage($sub['icon']) : null;

                $subCat = Category::create([
                    'parent_id' => $category->id,
                    'slug' => $sub['slug'] ?? Category::makeSlug($sub['title']),
                    'icon' => $subIcon,
                    'order' => 1,
                    'status' => 'active'
                ]);

                CategoryTranslation::updateOrCreate([
                    'category_id' => $subCat->id,
                    'locale' => $data['locale'],
                ], [
                    'title' => $sub['title'],
                ]);

                $subCategories[] = [
                    'id' => $subCat->id,
                    'title' => $sub['title'],
                    'slug' => $subCat->slug,
                    'icon' => $subIcon ? asset('store/' . $subIcon) : null,
                    'order' => $subCat->order,
                    'status' => $subCat->status
                ];
            }
        }

        // -------------------------
        // 📌 5) إضافة Requirements
        // -------------------------
     

        // -------------------------
        // 📌 6) Clean cache
        // -------------------------
        cache()->forget(Category::$cacheKey);
        removeContentLocale();

        // -------------------------
        // 📌 7) Return JSON
        // -------------------------
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => [
                'id' => $category->id,
                'slug' => $category->slug,
                'title' => $data['title'],
                'icon' => asset('store/' . $category->icon),
                'order' => $category->order,
                'status' => $category->status,
                'locale' => $data['locale'],
                'sub_categories' => $subCategories
                
            ]
        ], 201);


    } catch (\Illuminate\Validation\ValidationException $e) {

        // 🔥 نفس نظام رسائل الخطأ بلغتين
        $formattedErrors = [];
        $currentLocale = app()->getLocale();

        foreach ($e->errors() as $field => $messages) {
            $data = $e->validator->getData();
            $rules = [$field => $e->validator->getRules()[$field]];

            app()->setLocale('ar');
            $arValidator = Validator::make($data, $rules);
            $arMessage = $arValidator->errors()->first($field);

            app()->setLocale('en');
            $enValidator = Validator::make($data, $rules);
            $enMessage = $enValidator->errors()->first($field);

            $formattedErrors[$field] = [
                'ar' => $arMessage,
                'en' => $enMessage
            ];
        }

        app()->setLocale($currentLocale);

        return response()->json([
            'message' => 'Validation failed',
            'errors' => $formattedErrors
        ], 422);
    }
}
private function uploadImage($image)
{
    // ملف مرفوع
    if ($image instanceof \Illuminate\Http\UploadedFile) {
        return $image->store('categories', 'public');
    }

    // Base64
    if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {

        $imageData = substr($image, strpos($image, ',') + 1);
        $imageData = base64_decode($imageData);
        $extension = $type[1];

        $fileName = 'categories/' . uniqid() . "." . $extension;
        Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }

    return null;
}


public function update(Request $request, $url_name, $id)
{
    try {
        $this->authorize('admin_categories_edit');

        // -----------------------------------
        // 🔍 1) تأكيد وجود المؤسسة
        // -----------------------------------
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // -----------------------------------
        // 🔍 2) تحميل التصنيف
        // -----------------------------------
        $category = Category::findOrFail($id);

        // -----------------------------------
        // 🔍 3) Validate
        // -----------------------------------
        $request->validate([
            'title' => 'sometimes|min:3|max:128',
            'slug' => 'sometimes|max:255|unique:categories,slug,' . $category->id,
            'icon' => 'sometimes',
            'status' => 'sometimes|in:active,inactive',
            'sub_categories' => 'nullable|array',
           
        ]);

        $data = $request->all();
        $locale = $data['locale'] ?? 'ar';

        // -----------------------------------
        // 🖼 4) رفع الصورة الجديدة (إن وجدت)
        // -----------------------------------
        if (!empty($data['icon'])) {

            $newIcon = $this->uploadImage($data['icon']);

            // حذف القديم لو موجود
            if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                Storage::disk('public')->delete($category->icon);
            }

            $category->icon = $newIcon;
        }

        // -----------------------------------
        // 📌 5) تحديث بيانات التصنيف
        // -----------------------------------
        $category->slug = $data['slug'] ?? $category->slug;
        $category->order = $data['order'] ?? $category->order;
        $category->status = $data['status'] ?? $category->status;
        $category->save();

        // -----------------------------------
        // 🔤 6) تحديث الترجمة
        // -----------------------------------
        if (!empty($data['title'])) {
            CategoryTranslation::updateOrCreate([
                'category_id' => $category->id,
                'locale' => $locale,
            ], [
                'title' => $data['title'],
            ]);
        }

        // -----------------------------------
        // 📌 7) تحديث Sub Categories
        // -----------------------------------
        $subCategories = [];
        if (!empty($data['sub_categories'])) {

            foreach ($data['sub_categories'] as $sub) {

                // تحديث أو إنشاء Sub category
                $subCat = Category::updateOrCreate(
                    [
                        'slug' => $sub['slug'] ?? Category::makeSlug($sub['title']),
                        'parent_id' => $category->id,
                    ],
                    [
                        'status' => 'active'
                    ]
                );

                // رفع صورة جديدة
                if (!empty($sub['icon'])) {

                    $subIcon = $this->uploadImage($sub['icon']);

                    if ($subCat->icon && Storage::disk('public')->exists($subCat->icon)) {
                        Storage::disk('public')->delete($subCat->icon);
                    }

                    $subCat->icon = $subIcon;
                    $subCat->save();
                }

                // تحديث الترجمة
                CategoryTranslation::updateOrCreate([
                    'category_id' => $subCat->id,
                    'locale' => $locale
                ], [
                    'title' => $sub['title']
                ]);

                // إعداد بيانات العودة
                $subCategories[] = [
                    'id' => $subCat->id,
                    'title' => $sub['title'],
                    'slug' => $subCat->slug,
                    'icon' => $subCat->icon ? asset('store/' . $subCat->icon) : null,
                    'order' => $subCat->order,
                    'status' => $subCat->status
                ];
            }
        }

        // -----------------------------------
        // 📌 8) تحديث Requirements
        // -----------------------------------
    

        // -----------------------------------
        // 🧹 9) Cache clear
        // -----------------------------------
        cache()->forget(Category::$cacheKey);
        removeContentLocale();

        // -----------------------------------
        // 📌 10) Return response
        // -----------------------------------
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => [
                'id' => $category->id,
                'slug' => $category->slug,
                'title' => $data['title'] ?? null,
                'icon' => asset('store/' . $category->icon),
                'order' => $category->order,
                'status' => $category->status,
                'locale' => $locale,
                'sub_categories' => $subCategories,
               
            ]
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {

        // نفس فورمات الأخطاء السابق
        $formattedErrors = [];
        $currentLocale = app()->getLocale();

        foreach ($e->errors() as $field => $messages) {
            $data = $e->validator->getData();
            $rules = [$field => $e->validator->getRules()[$field]];

            app()->setLocale('ar');
            $arValidator = Validator::make($data, $rules);
            $arMessage = $arValidator->errors()->first($field);

            app()->setLocale('en');
            $enValidator = Validator::make($data, $rules);
            $enMessage = $enValidator->errors()->first($field);

            $formattedErrors[$field] = [
                'ar' => $arMessage,
                'en' => $enMessage
            ];
        }

        app()->setLocale($currentLocale);

        return response()->json([
            'message' => 'Validation failed',
            'errors' => $formattedErrors
        ], 422);
    }
}



    public function destroy($url_name, $id)
    {
        $this->authorize('admin_categories_delete');

        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $category = Category::where('id', $id)->first();

        if (!empty($category)) {
            Category::where('parent_id', $category->id)->delete();
            CategoryRequirement::where('category_id', $id)->delete();
            $category->delete();
        }

        cache()->forget(Category::$cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Category Deleted Successfully'
        ]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Category::select('id')
            ->whereTranslationLike('title', "%$term%");

        /*if (!empty($option)) {

        }*/

        $categories = $query->get();

        return response()->json($categories, 200);
    }

    public function setSubCategory(Category $category, $subCategories, $hasSubCategories, $locale)
    {
        $oldIds = [];

        if ($hasSubCategories && !empty($subCategories) && is_array($subCategories)) {
            foreach ($subCategories as $index => $subCategory) {
                $order = $index + 1;

                $check = !empty($subCategory['id']) ? Category::find($subCategory['id']) : null;

                if (!empty($subCategory['title'])) {
                    $slug = !empty($subCategory['slug'])
                        ? $subCategory['slug']
                        : Category::makeSlug($subCategory['title']);

                    if ($check) {
                        $check->update([
                            'order' => $order,
                            'icon' => $subCategory['icon'] ?? null,
                            'slug' => $slug,
                        ]);

                        CategoryTranslation::updateOrCreate(
                            ['category_id' => $check->id, 'locale' => mb_strtolower($locale)],
                            ['title' => $subCategory['title']]
                        );

                        $oldIds[] = $check->id;
                    } else {
                        $new = Category::create([
                            'parent_id' => $category->id,
                            'slug' => $slug,
                            'icon' => $subCategory['icon'] ?? null,
                            'order' => $order,
                        ]);

                        CategoryTranslation::updateOrCreate(
                            ['category_id' => $new->id, 'locale' => mb_strtolower($locale)],
                            ['title' => $subCategory['title']]
                        );

                        $oldIds[] = $new->id;
                    }
                }
            }
        }

        Category::where('parent_id', $category->id)
            ->whereNotIn('id', $oldIds)
            ->delete();

        return true;
    }

    public function setRequirements(Category $category, $requirements, $hasRequirements, $locale)
    {
        $oldIds = [];

        if ($hasRequirements && !empty($requirements) && is_array($requirements)) {
            foreach ($requirements as $index => $requirement) {

                if (!empty($requirement['title']) && !empty($requirement['description'])) {
                    $requirement['category_id'] = $category->id;

                    if (!empty($requirement['id'])) {
                        $check = CategoryRequirement::find($requirement['id']);
                        if ($check) {
                            $check->update($requirement);
                            $oldIds[] = $check->id;
                        }
                    } else {
                        $new = CategoryRequirement::create($requirement);
                        $oldIds[] = $new->id;
                    }
                }
            }
        }

        CategoryRequirement::where('category_id', $category->id)
            ->whereNotIn('id', $oldIds)
            ->delete();

        return true;
    }

    public function deleteRequirement(Request $request, $id, $reqId)
    {
        $requirement = CategoryRequirement::where(['id' => $reqId, 'category_id' => $id])->first();
        $requirement->delete();

        cache()->forget(Category::$cacheKey);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => !empty($parent) ? trans('update.sub_category_successfully_deleted') : trans('requirement successfully deleted'),
            'status' => 'success'
        ];

        return  back()->with(['toast' => $toastData]);
    }
}
