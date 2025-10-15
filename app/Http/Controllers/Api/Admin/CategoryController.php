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
                'icon' => $category->icon,
                'order' => $category->order,
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
                'icon' => $sub->icon,
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
            'icon' => $category->icon,
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

            $validated = $request->validate([
                'title' => 'required|min:3|max:128',
                'slug' => 'nullable|max:255|unique:categories,slug',
                'icon' => 'required',
                'status' => 'required|in:active,inactive',
                'locale' => 'required|string|min:2|max:5'
            ]);

            $data = $request->all();
            // $locale = 'ar';

            $order = $data['order'] ?? Category::whereNull('parent_id')->count() + 1;

            $category = Category::create([
                'slug' => $data['slug'] ?? Category::makeSlug($data['title']),
                'icon' => $data['icon'],
                'order' => $order,
                'status' => $data['status']
            ]);

            CategoryTranslation::updateOrCreate([
                'category_id' => $category->id,
                'locale' => $data['locale'],
            ], [
                'title' => $data['title'],
            ]);

            $hasSubCategories = (!empty($request->get('sub_categories')));
            $subCategories = $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale']);

            $hasRequirements = (!empty($request->get('requirements')));
            $requirements = $this->setRequirements($category, $request->get('requirements'), $hasRequirements, $data['locale']);

            cache()->forget(Category::$cacheKey);
            removeContentLocale();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'title' => $data['title'],
                    'icon' => $category->icon,
                    'order' => $category->order,
                    'status' => $category->status,
                    'locale' => $data['locale'],
                    'sub_categories' => $subCategories,
                    'requirements' => $requirements
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
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

    public function update(Request $request, $url_name,  $id)
    {
        try {
            $this->authorize('admin_categories_edit');

            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $category = Category::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|min:3|max:128',
                'slug' => 'sometimes|max:255|unique:categories,slug,' . $category->id,
                'icon' => 'sometimes',
                'status' => 'sometimes|in:active,inactive',
            ]);

            $data = $request->all();
            $locale = $data['locale'] ?? 'ar';

            $category->update([
                'slug' => $data['slug'] ?? Category::makeSlug($data['title']),
                'icon' => $data['icon'],
                'order' => $data['order'] ?? $category->order,
                'status' => $data['status']
            ]);

            CategoryTranslation::updateOrCreate([
                'category_id' => $category->id,
                'locale' => $locale,
            ], [
                'title' => $data['title'],
            ]);

            $hasSubCategories = (!empty($request->get('sub_categories')));
            $subCategories = $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $locale);

            $hasRequirements = (!empty($request->get('requirements')));
            $requirements = $this->setRequirements($category, $request->get('requirements'), $hasRequirements, $locale);

            cache()->forget(Category::$cacheKey);
            removeContentLocale();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'title' => $data['title'],
                    'icon' => $category->icon,
                    'order' => $category->order,
                    'status' => $category->status,
                    'locale' => $locale,
                    'sub_categories' => $subCategories,
                    'requirements' => $requirements
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
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
