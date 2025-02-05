<?php

declare(strict_types=1);

namespace App\Console\Commands\PostInstall;

use Illuminate\Console\Command;
use LaravelLang\Locales\Facades\Locales;
use Statamic\Console\RunsInPlease;
use JsonException;

class CollectAvailableLangLocales extends Command
{
    use RunsInPlease;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'statamic:peak:collect-available-lang-locales 
                          {--format=json : Output format (json|table)}
                          {--pretty : Pretty print JSON output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect and display all available language locales';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $locales = Locales::raw()->available();

            if ($this->option('format') === 'table') {
                $this->displayTable($locales);
            } else {
                $this->displayJson($locales);
            }

            return Command::SUCCESS;
        } catch (JsonException $e) {
            $this->error("Failed to encode locales: {$e->getMessage()}");
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Display locales in table format.
     *
     * @param array $locales
     * @return void
     */
    private function displayTable(array $locales): void
    {
        $headers = ['Locale', 'Native Name', 'English Name'];
        $rows = [];

        foreach ($locales as $locale => $data) {
            $rows[] = [
                $locale,
                $data['native'] ?? 'N/A',
                $data['name'] ?? 'N/A'
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Display locales in JSON format.
     *
     * @param array $locales
     * @return void
     * @throws JsonException
     */
    private function displayJson(array $locales): void
    {
        $flags = JSON_THROW_ON_ERROR;
        if ($this->option('pretty')) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $this->line(json_encode($locales, $flags));
    }
}
