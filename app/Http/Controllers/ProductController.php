<?php

namespace App\Http\Controllers;

use App\Product;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as Image;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::latest()->get();
        $categories = Category::pluck('name','id');
        return view('product.index',compact('products', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required',
            'description' => 'max:500',
            'price' => 'required|numeric'
        ]);

        $file  = $request->photo->getRealPath();
        $path  = '/' . $request->photo->hashName();
        $image = Image::make($file);
        $image->fit(400, 400, function ($constraint)
        {
            $constraint->aspectRatio();
        });
        $photos = Storage::disk('photo')->put($path, (string) $image->encode('jpg', 90));

        $data = $request->all();
        $data['name'] = $request->input('name');
        $data['path'] = $path;
        $data['category_id'] = $request->input('category_id');
        $data['description'] = $request->input('description');
        $data['price'] = $request->input('price');

        if (Product::create($data)) 
        {
            return redirect()->route('index.product')->with('success','Producto creado.');
        }
    }
    public function update(Request $request, $id)
    {
        request()->validate([
            'name' => 'required|string',
            'category_id' => 'required',
            'description' => 'max:500',
            'price' => 'required|numeric'
        ]);
        Product::find($id)->update($request->all());

        return redirect()->route('index.product')->with('success','Producto modificado.');
    }

    public function changeImage(Request $request, $id)
    {
        request()->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $column   = Product::find($id);
        Storage::disk('photo')->delete($column->path);

        $file  = $request->image->getRealPath();
        $path  = '/' . $request->image->hashName();
        $image = Image::make($file);
        $image->fit(400, 400, function ($constraint)
        {
            $constraint->aspectRatio();
        });
        $photos = Storage::disk('photo')->put($path, (string) $image->encode('jpg', 90));

        Product::where('id', $id)->update([
            'path' => $path
        ]);


        return redirect()->route('index.product')->with('success','Producto modificado.');

    }

    public function destroy($id)
    {
        $column   = Product::find($id);
        Storage::disk('photo')->delete($column->path);
        $column->delete();
        return redirect()->route('index.product')->with('success', 'Producto borrado satisfactoriamente.');
    }
}
