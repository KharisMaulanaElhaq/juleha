<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseFinishedEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:close-finished-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tandai status kegiatan yang sudah melewati waktu berakhir efektif menjadi done (selesai)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now('Asia/Jakarta');
        $events = Event::where('status', '!=', 'done')->get();
        $updatedCount = 0;

        foreach ($events as $event) {
            $effectiveEnd = $event->effectiveEndsAt();
            if ($effectiveEnd && $now->greaterThan($effectiveEnd)) {
                $event->update(['status' => 'done']);
                $updatedCount++;
            }
        }

        $this->info("Berhasil memperbarui {$updatedCount} kegiatan yang telah berakhir menjadi status 'done'.");

        return self::SUCCESS;
    }
}
