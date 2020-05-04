<?php

namespace App\Http\Controllers\Admin;

use App\Item;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SplFileObject;
use App\Jobs\CreateItems;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{

    //現在から過去に遡って表示したい月の数
    const NUM_MONTHS = 4;

    /**
     * HomeController constructor.
     * @param Item $item
     * @param Sale $sale
     */
    public function __construct(Item $item, Sale $sale)
    {
        $this->middleware('auth:admin');
        $this->item = $item;
        $this->sale = $sale;
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

    public function sale(Request $request)
    {
        if($request->has('targetMonth')) {
            $target_month = $request->targetMonth;
        }else {
            $target_month = Carbon::now()->format('Y-m');
        }

        $sales = $this->getTargetMonthSales($target_month);
        $diplayed_months = $this->displayedMonths();

        return view('admin.sale')
                ->with('sales', $sales)
                ->with('displayed_months', $diplayed_months)
                ->with('target_month', $target_month);
    }

    public  function importCsv(Request $request)
    {
        if (!$request->hasFile('csv_file')) {
            $error = ['エラー' => 'csvファイルが選択されていません。'];
            return redirect()->back()->withErrors($error);
        }

        $this->validate($request, [
            'csv_file' => [
                'required',
                'max:1024',
                'mimes:csv,txt',
            ]
        ]);

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

    public function exportCsv(Request $request)
    {
        if($request->has('targetMonth')) {
            $target_month = $request->targetMonth;
        }else {
            $target_month = Carbon::now()->format('Y-m');
        }

        $data = $this->getTargetMonthSales($target_month);

        $response = new StreamedResponse(function () use ($data) {

            // ファイルの書き出しはfopen()
            $stream = fopen('php://output', 'w');
            // ヘッダの設定
            $head = [
                '商品名',
                '購入数',
                '単価',
                '売上',
                '購入者',
                '購入日時',
            ];
            // 宣言したストリームに対してヘッダを書き出し
            mb_convert_variables('SJIS-win', 'UTF-8', $head);
            fputcsv($stream, $head);
            
            if($data)
            {
                foreach ($data as $line)
                {
                    // ストリームに対して1行ごと書き出し
                    mb_convert_variables('SJIS-win', 'UTF-8', $line);
                    fputcsv($stream, [
                        $line->item->name,
                        $line->quantity,
                        $line->amount,
                        $line->quantity * $line->amount,
                        $line->user->name,
                        $line->created_at
                    ]);
                }
            }
            fclose($stream);
        },
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=sale.csv',
            ]
        );

      return $response;
    }

    private function getTargetMonthSales(String $target_month)
    {
        $target_date_time = new Carbon($target_month);
        $from_date_time = $target_date_time->startOfMonth()->format('Y-m-d');
        $to_date_time = $target_date_time->endOfMonth()->format('Y-m-d');

        $sales = $this->sale->with(['user', 'item'])->whereBetween('created_at', [$from_date_time, $to_date_time])->get();

        return $sales;
    }

    /**
     * 
     * 
     */

    private function displayedMonths(): array
    {
        $displayed_months = [];
        $now = Carbon::now();

        for($i = 0; $i < self::NUM_MONTHS; $i++) {
            array_push($displayed_months, $now->format('Y-m'));
            $now->subMonth();
        }

        return $displayed_months;
    }

}
