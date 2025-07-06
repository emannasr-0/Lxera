<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Translation\CategoryTranslation;
use Illuminate\Http\Request;

use App\CategoryRequirement;
use App\Models\Api\Organization;

class CategoryController extends Controller
{
    public function index()
    {
        removeContentLocale();

        $this->authorize('admin_categories_list');

        $categories = Category::with([
            'subCategories'
        ])
            ->orderBy('id', 'desc')
            ->get();

        $data = [
            'categories' => $categories
        ];

        return response()->json([
            'success' => true,
            'message' => $data
        ], 200);
    }

    public function create()
    {
        $this->authorize('admin_categories_create');


        $data = [
            'pageTitle' => trans('admin/main.category_new_page_title'),
        ];

        return view('admin.categories.create', $data);
    }

    public function store($url_name, Request $request)
    {
        $this->authorize('admin_categories_create');

        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->validate($request, [
            'title' => 'required|min:3|max:128',
            'slug' => 'nullable|max:255|unique:categories,slug',
            'icon' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        $data = $request->all();
        $locale = mb_strtolower($data['locale'] ?? app()->getLocale());

        $order = $data['order'] ?? Category::whereNull('parent_id')->count() + 1;

        $category = Category::create([
            'slug' => $data['slug'] ?? Category::makeSlug($data['title']),
            'icon' => $data['icon'],
            'order' => $order,
            'status' => $data['status']
        ]);

        CategoryTranslation::updateOrCreate([
            'category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $data['title'],
        ]);

        // Handle subcategories
        $hasSubCategories = $request->boolean('has_sub');
        $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $locale);

        // Handle requirements
        $hasRequirements = $request->filled('requirements');
        $this->setRequirements($category, $request->get('requirements'), $hasRequirements, $locale);

        cache()->forget(Category::$cacheKey);
        removeContentLocale();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $category->id
        ], 201);
    }


    public function update($url_name, Request $request, $id)
    {
        $this->authorize('admin_categories_edit');

        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $category = Category::findOrFail($id);

        $this->validate($request, [
            'title' => 'sometimes|min:3|max:255',
            'slug' => 'nullable|max:255|unique:categories,slug,' . $category->id,
            'icon' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive'
        ]);

        $data = $request->all();

        $category->update([
            'icon' => $request->has('icon') ? $request->input('icon') : $category->icon,
            'slug' => $request->filled('slug') ? $request->input('slug') : Category::makeSlug($data['title'] ?? $category->title),
            'order' => $data['order'] ?? $category->order,
            'status' => $data['status'] ?? $category->status,
        ]);

        CategoryTranslation::updateOrCreate([
            'category_id' => $category->id,
            'locale' => mb_strtolower($data['locale'] ?? app()->getLocale()),
        ], [
            'title' => $data['title'] ?? $category->title,
        ]);

        // for categories
        $hasSubCategories = ($request->get('has_sub') === 'on');
        $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale'] ?? app()->getLocale());

        // for requirements
        $hasRequirements = $request->filled('requirements');
        $this->setRequirements($category, $request->get('requirements'), $hasRequirements, $data['locale'] ?? app()->getLocale());

        cache()->forget(Category::$cacheKey);

        removeContentLocale();

        return response()->json([
            'status' => 'success',
            'message' => 'Category Updated Successfully',
        ]);
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
        $order = 1;
        $oldIds = [];

        if ($hasSubCategories and !empty($subCategories) and count($subCategories)) {
            foreach ($subCategories as $key => $subCategory) {
                $check = Category::where('id', $key)->first();

                if (is_numeric($key)) {
                    $oldIds[] = $key;
                }

                if (!empty($subCategory['title'])) {
                    $checkSlug = 0;
                    if (!empty($subCategory['slug'])) {
                        $checkSlug = Category::query()->where('slug', $subCategory['slug'])->count();
                    }

                    $slug = (!empty($subCategory['slug']) and ($checkSlug == 0 or ($checkSlug == 1 and $check->slug == $subCategory['slug']))) ? $subCategory['slug'] : Category::makeSlug($subCategory['title']);

                    if (!empty($check)) {
                        $check->update([
                            'order' => $order,
                            'icon' => $subCategory['icon'] ?? null,
                            'slug' => $slug,
                        ]);

                        CategoryTranslation::updateOrCreate([
                            'category_id' => $check->id,
                            'locale' => mb_strtolower($locale),
                        ], [
                            'title' => $subCategory['title'],
                        ]);
                    } else {

                        $new = Category::create([
                            'parent_id' => $category->id,
                            'slug' => $slug,
                            'icon' => $subCategory['icon'] ?? null,
                            'order' => $order,
                        ]);

                        CategoryTranslation::updateOrCreate([
                            'category_id' => $new->id,
                            'locale' => mb_strtolower($locale),
                        ], [
                            'title' => $subCategory['title'],
                        ]);

                        $oldIds[] = $new->id;
                    }

                    $order += 1;
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
        $order = 1;
        $oldIds = [];

        if ($hasRequirements and !empty($requirements) and count($requirements)) {
            foreach ($requirements as $key => $requirement) {
                $check = CategoryRequirement::where('id', $key)->first();
                if (!empty($requirement['title']) and !empty($requirement['description'])) {
                    $requirement['category_id'] = $category->id;
                    if ($check) {
                        $check->update($requirement);
                    } else {
                        CategoryRequirement::create($requirement);
                    }
                }
            }
        }
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
