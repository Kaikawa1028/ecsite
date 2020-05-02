<?php

namespace App\Http\Controllers\Admin;

use App\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SplFileObject;
use App\Jobs\CreateItems;

class HomeController extends Controller
{
    /**
     * HomeController constructor.
     * @param Item $item
     */
    public function __construct(Item $item)
    {
        $this->middleware('auth:admin');
        $this->item = $item;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.home');
    }

    public  function importCsv(Request $request)
    {
        if (!$request->hasFile('csv_file')) {
            $error = ['エラー' => 'csvファイルが選択されていません。'];
            return redirect()->back()->withErrors($error);
        }

        $custom_error_msg = [
          "required" => "ファイルが選択されていません。",
          "max:1024" => "ファイルのサイズが1024kbを超えています。",
          "mimes:csv,txt" => "ファイルの形式が間違っています。",
        ];

        $this->validate($request, [
            'csv_file' => [
                'required',
                'max:1024',
                'mimes:csv,txt',
            ]
        ], $custom_error_msg);

        if (!$request->file('csv_file')->isValid()) {
            return redirect()->back();
        }

        $file = $request->file('csv_file');

        $file = new SplFileObject($file->path());

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE
        );

        $items = [];
        try{
            foreach ($file as $line) {
                $now = Carbon::now()->format('Y-m-d H:i:s');
                $item = [
                  'name' => $line[0],
                  'amount' => $line[1],
                  'created_at' => $now,
                  'updated_at' => $now,
                ];
                array_push($items, $item);
            }
        }catch (Exception $e) {
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }

        CreateItems::dispatch($items);

        return redirect()->back()->with('flash_message', '登録処理を行っています。');
    }
}
