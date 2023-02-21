<?php

namespace App\Http\Controllers;

use App\Order;
use App\State;
use App\Product;
use App\Category;
use App\Ticket;
use App\User;
use Carbon\Carbon;
Carbon::setLocale('es');
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'table' => 'required',
            'user_id' => 'required',
            'notes' => 'max:500'
        ]);

        $count = State::where('table', $request->input('table'))->where(function ($q)
        {
            $q->where('status', '=', 'ESPERA');
        })->count();

        if($count > 0)
            return redirect()->route('index')->with('error', 'La mesa que seleccionó ya posee una órden.');

        $product_list_array = json_decode($request->input('cart_list'));

        //print_r($product_list_array[0]);

        $data1 = $request->all();
        $data1['user_id'] = $request->input('user_id');
        $data1['table'] = $request->input('table');
        $data1['notes'] = $request->input('notes');
        $data1['status'] = 'ESPERA';
        State::create($data1);

        $last = State::latest()->first();

        foreach ($product_list_array as $key => $object) 
        {
            $aux = $object->unique_key;
            if($aux == $object->unique_key)
            {
                $data = $request->all();
                $data['product_id'] = $object->product_id;
                $data['state_id'] = $last->id;
                $data['num'] = $object->product_quantity;
                Order::create($data);
                //echo $object->unique_key;
            }
        }
        return redirect()->route('index')->with('success','Orden creada.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $state = State::find($id);
        $order = Order::where('state_id', $id)->latest()->get();
        $products = Product::latest()->get();
        $categories = Category::latest()->get();
        $names = User::pluck('name','id');
        return view('order.show',compact('order', 'id', 'products', 'categories', 'names', 'state'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        State::where('id',$id)->update(['STATUS' => 'REALIZADA']);

        $order = Order::where('state_id', $id)->latest()->get();

        $ac = 0;
        $subtotal = 0;
        $propina = 0;
        $total = 0;

        foreach($order as $o)
        {
            $value = Product::find($o->product_id);

            $ac = $value->price * $o->num;

            $subtotal = $subtotal + $ac;
        }

        $propina = (($subtotal * 10)/100);

        $total = $subtotal + $propina;

        $data = $request->all();
        $data['state_id'] = $id;
        $data['tip'] = $propina;
        $data['subtotal'] = $subtotal;
        $data['total'] = $total;

        Ticket::create($data);

        return redirect()->route('index')->with('success','Orden realizada.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'table' => 'required',
            'user_id' => 'required',
            'notes' => 'max:500'
        ]);

        $state = State::find($id);

        if($state->table != $request->input('table'))
        {
            $count = State::where('table', $request->input('table'))->where(function ($q)
            {
                $q->where('status', '=', 'ESPERA');
            })->count();

            if($count > 0)
                return redirect()->route('show.order', $id)->with('error', 'La mesa que seleccionó ya posee una órden.');
        }

        State::where('id', $id)->update([
            'user_id' => $request->input('user_id'),
            'table' => $request->input('table'),
            'notes' => $request->input('notes'),
        ]);

        $product_list_array = json_decode($request->input('cart_list'));

        //print_r($product_list_array[0]);

        Order::where('state_id', $id)->delete();

        foreach ($product_list_array as $key => $object) 
        {
            $aux = $object->unique_key;
            if($aux == $object->unique_key)
            {
                $data = $request->all();
                $data['product_id'] = $object->product_id;
                $data['state_id'] = $id;
                $data['num'] = $object->product_quantity;
                Order::create($data);
                //echo $object->unique_key;
            }
        }
        return redirect()->route('show.order', $id)->with('success','Orden modificada.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(State::find($id)->delete())
            return redirect()->route('index')->with('success','Orden borrada.');
    }

    public function print($id)
    {
        $order = Order::where('state_id', $id)->latest()->get();
        $now = State::where('id',$id)->first();
        $owner = User::find($now->user_id)->name;
        return view('order.print',compact('order', 'id', 'now', 'owner'));
    }
    public function printer($id)
    {
        $order = Order::where('state_id', $id)->latest()->get();

        $ac = 0;
        $subtotal = 0;
        $propina = 0;
        $total = 0;

        foreach($order as $o)
        {
            $value = Product::find($o->product_id);

            $ac = $value->price * $o->num;

            $subtotal = $subtotal + $ac;
        }

        $propina = (($subtotal * 10)/100);

        $total = $subtotal + $propina;

        $now = State::find($id);
        $owner = User::find($now->user_id)->name;

        return view('order.printer',compact('order', 'id', 'now', 'total', 'propina', 'subtotal', 'owner'));
    }

    public function total($id)
    {
        $order = Order::where('state_id', $id)->latest()->get();

        $ac = 0;
        $subtotal = 0;
        $propina = 0;
        $total = 0;

        foreach($order as $o)
        {
            $value = Product::find($o->product_id);

            $ac = $value->price * $o->num;

            $subtotal = $subtotal + $ac;
        }

        $propina = (($subtotal * 10)/100);

        $total = $subtotal + $propina;

        return view('order.total',compact('order', 'id', 'total', 'propina', 'subtotal'));
    }
    public function result()
    {
        $dt = Carbon::parse(Carbon::now());
        $year = $dt->year;
        $results = Ticket::whereYear('created_at', $year)->latest()->get();
        $now = Carbon::now();
        $start = $now->startOfWeek();
        $end = $now->endOfWeek();

        return view('order.result',compact('results','year', 'now'));
    }

    public function searchDates(Request $request)
    {
        request()->validate([
            'datefilter' => 'required',
            'start' => 'date_format:Y-m-d|required',
            'end' => 'date_format:Y-m-d|required'
        ]);

        $start = $request->start;
        $end = $request->end;

        $results = Ticket::whereBetween('created_at', [$start, $end])->latest()->get();

        return view('order.search',compact('results', 'start', 'end'));
    }

    public function searchDate(Request $request)
    {
        request()->validate([
            'start_alternative' => 'date_format:Y-m-d|required',
            'end_alternative' => 'date_format:Y-m-d|required'
        ]);

        $start = $request->start_alternative;
        $end = $request->end_alternative;

        if($start == $end)
        {
            $results = Ticket::whereDate('created_at', $start)->latest()->get();
        }
        else
        {
            $results = Ticket::whereBetween('created_at', [$start, $end])->latest()->get();
        }

        return view('order.search',compact('results', 'start', 'end'));
    }
}
