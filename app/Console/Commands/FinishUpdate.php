<?php

namespace App\Console\Commands;

use App\Events\Install\UpdateFinished;
use App\Models\Common\Company;
use Illuminate\Console\Command;

class FinishUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:finish {alias} {company} {new} {old}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finish the update process through CLI';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(900); // 15 minutes

        $this->info('Finishing update...');

        $this->call('cache:clear');

        $alias = $this->argument('alias');
        $company_id = $this->argument('company');
        $new = $this->argument('new');
        $old = $this->argument('old');

        // Check if file mirror was successful
        $version = ($alias == 'core') ? version('short') : module($alias)->get('version');
        if ($version != $new) {
            throw new \Exception(trans('modules.errors.finish', ['module' => $alias]));
        }

        // Set locale for modules
        if ($alias != 'core') {
            app()->setLocale(Company::find($company_id)->locale);
        }

        session(['company_id' => $company_id]);
        setting()->setExtraColumns(['company_id' => $company_id]);

        event(new UpdateFinished($alias, $new, $old));
    }
}
