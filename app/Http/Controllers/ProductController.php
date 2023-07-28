<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request){
        $keyword = $request->get('search');
        $perPage =5;

        if(!empty($keyword)){
            $products = Product::where('name', 'LIKE', "%$keyword%")
                      ->orWhere('category', 'LIKE', "%$keyword")
                      ->latest()->paginate($perPage);
        }else{
            $products = Product::latest()->paginate($perPage);
        }
        return view('index', ['products' => $products])->with('i',(request()->input('page',1) -1) *5);
    }

    public function create(){
        return view('create');
    }

    public function store(Request $request){

        $request->validate([
            'name' => 'required',
            'image' =>'required|image|mimes:jpg,png,jpef,gif,svg|max:2028'
        ]);

        $file_name = time() . '.' . request()->image->getClientOriginalExtension();
        request()->image->move(public_path('image'), $file_name);

        $product = new Product;

        /*$product->name = $request->name;
        $product->description = $request->description;
        $product->image = $file_name;
        $product->category = $request->category;
        $product->quantity = $request->quantity;
        $product->price = $request->price;*/

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'image' => $file_name
        ]);

        $product->save();
        return redirect()->route('products.index')->with('success', 'Product Have Been Added Successfully');

    }

    public function edit($id) {
        $product = Product::findOrFail($id);
        return view('edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product){
        $request->validate([
            'name' => 'required'
        ]);

        $file_name = $request->hidden_product_image;

        if ($request->image != '') {
            $file_name = time() . '.' . request()->image->getClientOriginalExtension();
            request()->image->move(public_path('image'), $file_name);
        }

        $product = Product::find($request->hidden_id);

        
        $product ->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'image' => $file_name
        ]);

        return redirect()->route('products.index')->with('success', 'Product Updated');
    }

    public function destroy($id){
        $product = Product::findOrFail($id);
        $image_path = public_path()."/image/";
        $image = $image_path. $product->image;
        if (file_exists($image)) {
            @unlink($image);
        }
        $product->delete();
        return redirect('products')->with('success', 'Product Deleted !!!');

    }
}
