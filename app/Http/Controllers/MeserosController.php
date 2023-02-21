<?php

namespace App\Http\Controllers;

use App\Product;
use App\Category;
use App\User;
use App\State;
use Illuminate\Http\Request;

class MeserosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meseros = User::latest()->get();
        return view('user.index',compact('meseros'));
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
            'email' => 'required|string|email|max:255|unique:users,email',
        ]);

        $data = $request->all();
        $data['name'] = $request->input('name');
        $data['email'] = $request->input('email');

        if (User::create($data)) 
        {
            return redirect()->route('index.user')->with('success','Mesero creado.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|max:255',
        ]);

        $value = User::find($id);

        if($value->email != $request->input('email'))
        {
            $count = User::where('email', $request->input('email'))->count();
            if($count > 0)
                return redirect()->route('index.user')->with('error', 'El correo ya existe.');
        }


        User::find($id)->update($request->all());

        return redirect()->route('index.user')->with('success','Mesero modificado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(User::find($id)->delete())
            return redirect()->route('index.user')->with('success','Mesero borrado.');
    }
}
