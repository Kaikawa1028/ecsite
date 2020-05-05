<?php

namespace App\Http\Controllers;

use App\CartItem;
use App\Events\ItemSold;
use App\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\Buy;
use Illuminate\Support\Facades\Mail;

class BuyController extends Controller
{
    public function index()
    {
        $cartitems = CartItem::select('cart_items.*', 'items.name', 'items.amount')
            ->where('user_id', Auth::id())
            ->join('items', 'items.id','=','cart_items.item_id')
            ->get();
        $subtotal = 0;
        foreach($cartitems as $cartitem){
            $subtotal += $cartitem->amount * $cartitem->quantity;
        }
        return view('buy/index', ['cartitems' => $cartitems, 'subtotal' => $subtotal]);
    }

    public function store(Request $request)
    {
        if( $request->has('post') ){
            event(new ItemSold(Auth::user()));

            $cart_items = CartItem::with('item')->where('user_id', Auth::id())->get();
            try {
                \DB::beginTransaction();
                foreach ($cart_items as $cart_item) {

                    $sale = new Sale();
                    $sale->user_id = $cart_item->user_id;
                    $sale->item_id = $cart_item->item_id;
                    $sale->quantity = $cart_item->quantity;
                    $sale->amount = $cart_item->item->amount;
                    $sale->save();

                    $cart_item->delete();
                }
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error($e->getMessage());
                //ホントはここでエラー画面に返すべきだけどね；；
            }
            return view('buy/complete');
        }
        $request->flash();
        return $this->index();
    }
}
