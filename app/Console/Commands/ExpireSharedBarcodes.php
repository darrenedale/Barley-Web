<?php

namespace App\Console\Commands;

use App\Models\SharedBarcode;
use DateTime;

/**
 * Soft-delete shared barcodes that expired more than 28 days ago.
 */
class ExpireSharedBarcodes extends Command
{
    /**
     * The number of days' grace after the expiry date for which to keep shared barcodes.
     */
    public const GraceDays = 28;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = "expire:barley-shard-barcodes";

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Delete shared barcodes that expired more than " . self::GraceDays . " ago.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        assert(0 <= self::GraceDays);
        /** @noinspection PhpUnhandledExceptionInspection Assertion guarantees DateTime constructor will not throw */
        $res = SharedBarcode::where("expires_at", "<", new DateTime(self::GraceDays . " days ago"))
            ->delete();
        $this->line("soft-deleted {$res} shared barcode(s).");
        return self::ExitOk;
    }
}
