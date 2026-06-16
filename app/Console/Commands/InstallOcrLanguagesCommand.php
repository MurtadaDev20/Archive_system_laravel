<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class InstallOcrLanguagesCommand extends Command
{
    protected $signature = 'ocr:install-languages {--langs=ara,eng : Comma-separated Tesseract language codes}';

    protected $description = 'Download Tesseract language packs into storage/app/tesseract/tessdata';

    public function handle(): int
    {
        $langs = collect(explode(',', $this->option('langs')))
            ->map(fn ($l) => trim($l))
            ->filter();

        $dir = storage_path('app/tesseract/tessdata');
        File::ensureDirectoryExists($dir);

        foreach ($langs as $lang) {
            $target = $dir.DIRECTORY_SEPARATOR.$lang.'.traineddata';

            if (File::exists($target)) {
                $this->line("Already present: {$lang}");
                continue;
            }

            $url = "https://github.com/tesseract-ocr/tessdata/raw/main/{$lang}.traineddata";
            $this->info("Downloading {$lang}...");

            $response = Http::timeout(120)->get($url);

            if (! $response->successful()) {
                $this->error("Failed to download {$lang} from tessdata repository.");

                return self::FAILURE;
            }

            File::put($target, $response->body());
            $this->line("Saved: {$target}");
        }

        $this->newLine();
        $this->info('Language packs installed at: '.$dir);
        $this->line('TESSDATA_PREFIX will resolve to this folder automatically.');

        return self::SUCCESS;
    }
}
