<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use JsonException;

class RunPythonScript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-python-script {symptoms}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute le script pyton pour predire les maladies problables d\'un patient';

    /**
     * Execute the console command.
     * @throws JsonException
     */
    public function handle()
    {
        $symptoms = $this->argument('symptoms');
        $pythonScriptPath = base_path('\python\model.py');
        $pythonPath = 'C:\ProgramData\anaconda3\python.exe';

        $input = json_encode(['symptoms' => explode(',', $symptoms)], JSON_THROW_ON_ERROR);
        $escapedInput = escapeshellarg($input);

        // Exécuter le script Python
        $output = shell_exec("$pythonPath $pythonScriptPath $escapedInput 2>&1");
        $this->info($output);
    }
}
