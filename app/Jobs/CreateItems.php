<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $items;

    /**
     * CreateItems constructor.
     * @param array $items
     */
    public function __construct(Array $items)
    {
        $this->items = $items;
    }

    /**
     * @param Item $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Item $item)
    {
        try {
            DB::beginTransaction();
            $item->bulkInsertOrUpdate($this->items);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
        }
    }
}
