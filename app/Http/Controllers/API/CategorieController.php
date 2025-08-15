<?php

namespace App\Http\Controllers\API;

use App\Models\Categorie;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CategorieController extends Controller
{
    use GlobalTraits;

    public function categories()
    {
        $categories = Categorie::with('parent')->get();

        if ($categories) {
            return $this->SendResponse($categories, 'Sucess , this is all categories', 200);
        }
        return $this->SendResponse(null, 'Error , tha categories is undifaind', 401);
    }

    public function index(Request $request)
    {
        $categories = Categorie::all();
        if ($categories) {
            return $this->SendResponse($categories, 'Sucess , this is all categories', 200);
        }
        return $this->SendResponse(null, 'Error , tha categories is undifaind', 401);
    }

    public function subCategory()
    {
        $subCategory = Categorie::with(
            'users.roles',
            'children.users.roles',
            'children.children.users.roles',
            'children.children.children.users.roles',
            'children.children.children.children.users.roles',
            'children.children.children.children.children.users.roles'
        )->whereNull('parent_id')->get();

        if ($subCategory->isNotEmpty()) {
            return $this->SendResponse($subCategory, 'Success, these are all categories with associated children and users', 200);
        }

        return $this->SendResponse(null, 'Error, no categories found', 401);
    }

    public function storeSubCategories(Request $request)
    {
        $validator = Validator($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'info' => 'required|string|max:855',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse([], $validator->errors(), 401);
        }
        // create group folder in public
        $this->CreateMultieFolders();
        $userId = Auth::id();
        if ($request->hasFile("cover")) {
            $file = $request->file("cover");
            $imageName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path("cover/"), $imageName);
            $Categories = new Categorie;
            $Categories->path = $request->name;
            if ($request->parent_id) {
                $Categories->parent_id = $request->parent_id;
                $json_string = Categorie::where("id", $request->parent_id)->get("path");
                $json_array = json_decode($json_string, true);
                $value = $json_array[0]['path'];
                $Categories->path = $value . " - " . $request->name;
            }
            $Categories->name = $request->name;
            $Categories->info = $request->info;
            $Categories->cover = $imageName;
            $Categories->save();
            $Categories->users()->attach($userId);
        } elseif (!$request->hasFile("cover")) {
            $Categories = new Categorie;
            $Categories->path = $request->name;
            if ($request->parent_id) {
                $Categories->parent_id = $request->parent_id;
                $json_string = Categorie::where("id", $request->parent_id)->get("path");
                $json_array = json_decode($json_string, true);
                $value = $json_array[0]['path'];
                $Categories->path = $value . " - " . $request->name;
            }
            $Categories->name = $request->name;
            $Categories->info = $request->info;
            $Categories->save();
            $Categories->users()->attach($userId);
        }
        if ($Categories) {

            return $this->SendResponse($Categories, 'success', 200);
        }
        return $this->SendResponse([], 'Error', 401);
    }

    public function categorie($id)
    {
        $categorie = Categorie::find($id);
        if ($categorie) {
            return $this->SendResponse($categorie, 'success', 200);
        }
        return $this->SendResponse(null, 'error tha categorie not found', 400);
    }

    public function subCategorie($id)
    {
        $categorie = Categorie::where('parent_id', $id)->get();
        if ($categorie) {
            return $this->SendResponse($categorie, 'success', 200);
        }
        return $this->SendResponse(null, 'error tha categorie not found', 400);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator($request->all(), [
            'name' => 'required|string|max:255',
            'info' => 'required|string|max:855',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse([], $validator->errors(), 401);
        }
        $userId = Auth::id();
        $this->CreateMultieFolders();

        $Categories = Categorie::findOrFail($id);
        if ($request->hasFile("cover")) {


            $coverPath = public_path('cover/' . $Categories->cover);
            if (file_exists($coverPath)) {
                unlink($coverPath);
                //echo 'delete cover file';
            }

            $file = $request->file("cover");
            $imageName = time() . "_" . $file->getClientOriginalName();
            $file->move(public_path("cover/"), $imageName);
            $Categories->name = $request->name;
            $Categories->info = $request->info;
            // $Categories->name_folder = $request->name_folder;
            if ($request->parent_id) {
                $Categories->parent_id = $request->parent_id;
            }
            $Categories->cover = $imageName;
            //$Categories->auther = $request->auther;
            $Categories->save();
            $Categories->users()->attach($userId);
            // if ($request->brands) {
            //     $inputString = $request->brands;
            //     $group = json_decode($inputString);
            //     $brandsIds = []; // Create an array to store the tag IDs
            //     foreach ($group as $item) {
            //         $brand = Brands::where('id', $item)->first();
            //         if ($brand) {
            //             $brandsIds[] = $brand->id; // Store the tag ID instead of the tag object
            //         }
            //     }
            //     $Categories->Brands()->sync($brandsIds);
            // }
        }
        if (!$request->hasFile("cover")) {
            $Categories->name = $request->name;
            $Categories->info = $request->info;
            if ($request->parent_id) {
                $Categories->parent_id = $request->parent_id;
            }
            $Categories->save();
            $Categories->users()->attach($userId);
        }

        if ($Categories) {
            return $this->SendResponse($Categories, "Success update Categories", 200);
        }
        return $this->SendResponse(null, "Error not update Categories", 200);
    }


    public function destroy($id)
    {
        $Categorie = Categorie::find($id);
        if ($Categorie) {
            $coverPath = public_path('cover/' . $Categorie->cover);
            Log::info('Cover path: ' . $coverPath); // Debugging line
            if (file_exists($coverPath) && !is_dir($coverPath)) {
                unlink($coverPath);
            }
            $Categorie->delete();
            return $this->SendResponse($Categorie, "success deleted is Categorie", 200);
        }
        return $this->SendResponse(null, "Error that category not deleted", 400);
    }


    public function multeDestroy(Request $request)
    {

        //return $request;

        // Assuming the incoming request is a JSON array
        $group = $request->input(); // Get the entire request payload

        // Check if the input is an array
        if (!is_array($group)) {
            return response()->json(['error' => 'Invalid input data'], 400);
        }

        // Proceed with deletion
        foreach ($group as $item) {
            Categorie::where('id', $item)->delete();

            $coverPath = public_path('cover/' . $item->cover);
            if (file_exists($coverPath)) {
                unlink($coverPath);
                //echo 'delete cover file';
            }
        }

        return $this->SendResponse($request, "success deleted is Categorie", 200);
    }

    public function creteFoldersInPublic()
    {
        $path = public_path();
        $folders = array('images', 'photo', 'cover', 'avatars');
        foreach ($folders as $folder) {
            if (!is_dir($path . '/' . $folder)) {
                mkdir($path . '/' . $folder, 0777);
                return 'created folder success';
            } else {
                return 'ths folder alredy exist ...';
            }
        }
    }
}
