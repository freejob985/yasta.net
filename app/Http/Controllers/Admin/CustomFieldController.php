<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\CustomField;
use App\Http\Controllers\Controller;
use App\Setting;
use Artesaos\SEOTools\Facades\SEOMeta;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        /**
         * Start SEO
         */
        $settings = Setting::find(1);
        //SEOMeta::setTitle('Dashboard - Custom Fields - ' . (empty($settings->setting_site_name) ? config('app.name', 'Laravel') : $settings->setting_site_name));
        SEOMeta::setTitle(__('seo.backend.admin.custom-field.custom-fields', ['site_name' => empty($settings->setting_site_name) ? config('app.name', 'Laravel') : $settings->setting_site_name]));
        SEOMeta::setDescription('');
        SEOMeta::setCanonical(URL::current());
        SEOMeta::addKeyword($settings->setting_site_seo_home_keywords);
        /**
         * End SEO
         */

        $category_id = $request->category;

        if($category_id)
        {
            $category = Category::findOrFail($category_id);

            $all_custom_fields = $category->customFields()->orderBy('custom_field_order')->get();
        }
        else
        {
            $all_custom_fields = CustomField::all();
        }

        $all_categories = Category::orderBy('category_name')->get();

        return response()->view('backend.admin.custom-field.index',
            compact('all_categories', 'category_id', 'all_custom_fields'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        /**
         * Start SEO
         */
        $settings = Setting::find(1);
        //SEOMeta::setTitle('Dashboard - Create Custom Field - ' . (empty($settings->setting_site_name) ? config('app.name', 'Laravel') : $settings->setting_site_name));
        SEOMeta::setTitle(__('seo.backend.admin.custom-field.create-custom-field', ['site_name' => empty($settings->setting_site_name) ? config('app.name', 'Laravel') : $settings->setting_site_name]));
        SEOMeta::setDescription('');
        SEOMeta::setCanonical(URL::current());
        SEOMeta::addKeyword($settings->setting_site_seo_home_keywords);
        /**
         * End SEO
         */

        $all_categories = Category::all();

        return response()->view('backend.admin.custom-field.create', compact('all_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'category.*' => 'numeric',
            'custom_field_name' => 'required|max:255',
            'custom_field_type' => 'required|numeric',
        ]);

        $custom_field_type = $request->custom_field_type;

        if($custom_field_type == CustomField::TYPE_SELECT || $custom_field_type == CustomField::TYPE_MULTI_SELECT)
        {
            if(empty($request->custom_field_seed_value))
            {
                throw ValidationException::withMessages(
                    [
                        'custom_field_seed_value' => 'Seed value required',
                    ]);
            }
        }

        $categories = $request->category;

        foreach($categories as $key => $category_id)
        {
            $category = Category::find($category_id);

            if($category)
            {
                $custom_field_name = ucwords(strtolower($request->custom_field_name));
                $custom_field_seed_value = $request->custom_field_seed_value;
                $custom_field_order = $request->custom_field_order;

                $new_custom_field = new CustomField(array(
                    'custom_field_name' => $custom_field_name,
                    'custom_field_type' => $custom_field_type,
                    'custom_field_seed_value' => $custom_field_seed_value,
                    'custom_field_order' => $custom_field_order,
                ));

                $created_custom_field = $category->customFields()->save($new_custom_field);

            }
        }

        \Session::flash('flash_message', __('alert.custom-field-created'));
        \Session::flash('flash_type', 'success');

        return redirect()->route('admin.custom-fields.index');
    }

    /**
     * Display the specified resource.
     *
     * @param CustomField $customField
     * @return RedirectResponse
     */
    public function show(CustomField $customField)
    {
        return redirect()->route('admin.custom-fields.edit', $customField);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CustomField $customField
     * @return Response
     */
    public function edit(CustomField $customField)
    {
        /**
         * Start SEO
         */
        $settings = Setting::find(1);
        //SEOMeta::setTitle('Dashboard - Edit Custom Field - ' . (empty($settings->setting_site_name) ? config('app.name', 'Laravel') : $settings->setting_site_name));
        SEOMeta::setTitle(__('seo.backend.admin.custom-field.edit-custom-field', ['site_name' => empty($settings->setting_site_name) ? config('app.name', 'Laravel') : $settings->setting_site_name]));
        SEOMeta::setDescription('');
        SEOMeta::setCanonical(URL::current());
        SEOMeta::addKeyword($settings->setting_site_seo_home_keywords);
        /**
         * End SEO
         */

        return response()->view('backend.admin.custom-field.edit', compact('customField'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param CustomField $customField
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, CustomField $customField)
    {
        $request->validate([
            'custom_field_name' => 'required|max:255',
            'custom_field_type' => 'required|numeric',
        ]);

        $custom_field_type = $request->custom_field_type;

        if($custom_field_type == CustomField::TYPE_SELECT || $custom_field_type == CustomField::TYPE_MULTI_SELECT)
        {
            if(empty($request->custom_field_seed_value))
            {
                throw ValidationException::withMessages(
                    [
                        'custom_field_seed_value' => 'Seed value required',
                    ]);
            }
        }

        $customField->custom_field_name = ucwords(strtolower($request->custom_field_name));
        $customField->custom_field_type = $request->custom_field_type;
        $customField->custom_field_seed_value = $request->custom_field_seed_value;
        $customField->custom_field_order = $request->custom_field_order;
        $customField->save();

        \Session::flash('flash_message', __('alert.custom-field-updated'));
        \Session::flash('flash_type', 'success');

        return redirect()->route('admin.custom-fields.edit', $customField);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CustomField $customField
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(CustomField $customField)
    {
        // check model relations before delete
        if($customField->itemFeatures()->get()->count() > 0)
        {
            \Session::flash('flash_message', __('alert.custom-field-delete-error-listing'));
            \Session::flash('flash_type', 'danger');

            return redirect()->route('admin.custom-fields.edit', $customField);
        }
        else
        {
            $customField->delete();

            \Session::flash('flash_message', __('alert.custom-field-deleted'));
            \Session::flash('flash_type', 'success');

            return redirect()->route('admin.custom-fields.index');
        }
    }
}
