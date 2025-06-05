<?php

declare(strict_types=1);

namespace Worksofallen\LaravelCommand\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeApiCommand extends Command
{
    protected $signature = 'make:api {name}';
    protected $description = 'Generate a model, migration, API controller, and request validation with predefined rules';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $snakePlural = Str::snake(Str::plural($name));

        $migrationFileName = now()->format('Y_m_d_His') . "_create_{$snakePlural}_table.php";

        // List of files that will be created or modified
        $plannedFiles = [
            "app/Models/{$name}.php",
            "database/migrations/{$migrationFileName}",
            "app/Http/Requests/{$name}StoreRequest.php",
            "app/Http/Requests/{$name}UpdateRequest.php",
            "app/Http/Controllers/API/{$name}Controller.php"
        ];

        $this->info("The following files will be created or modified:");
        foreach ($plannedFiles as $file) {
            $this->line(" - {$file}");
        }

        if (!$this->confirm('Do you want to proceed?')) {
            $this->warn("❌ Operation cancelled.");
            return;
        }

        $createdFiles = [];

        // Model and Migration
        Artisan::call("make:model $name -m");
        $this->modifyModelFile($name);
        $createdFiles[] = "app/Models/{$name}.php";

        $migrationFile = $this->modifyMigrationFile($name);
        if ($migrationFile) {
            $createdFiles[] = "database/migrations/{$migrationFile}";
        }

        // Store and Update Request
        Artisan::call("make:request {$name}StoreRequest");
        $this->modifyRequestFile("{$name}StoreRequest");
        $createdFiles[] = "app/Http/Requests/{$name}StoreRequest.php";

        Artisan::call("make:request {$name}UpdateRequest");
        $this->modifyRequestFile("{$name}UpdateRequest");
        $createdFiles[] = "app/Http/Requests/{$name}UpdateRequest.php";

        // Controller
        Artisan::call("make:controller API/{$name}Controller --api");
        $this->modifyControllerFile($name);
        $createdFiles[] = "app/Http/Controllers/API/{$name}Controller.php";

        // Output final result
        $this->info("\n✅ All Template API resources for {$name} created successfully!");
        $this->info("📁 Files created or modified:");
        foreach ($createdFiles as $file) {
            $this->line(" - {$file}");
        }
    }

    protected function modifyMigrationFile($name)
    {
        $filesystem = new Filesystem();
        $migrationPath = database_path('migrations');
        $migrationFiles = $filesystem->files($migrationPath);

        $expectedFilename = "create_" . Str::snake(Str::plural($name)) . "_table";

        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), $expectedFilename)) {
                $migrationContent = $filesystem->get($file->getPathname());

                if (!Str::contains($migrationContent, "\$table->string('name');")) {
                    $migrationContent = str_replace(
                        "\$table->id();",
                        "\$table->id();\n            \$table->string('name');",
                        $migrationContent
                    );
                }

                if (!Str::contains($migrationContent, "\$table->softDeletes();")) {
                    $migrationContent = str_replace(
                        "\$table->timestamps();",
                        "\$table->timestamps();\n            \$table->softDeletes();",
                        $migrationContent
                    );
                }

                $filesystem->put($file->getPathname(), $migrationContent);
                break;
            }
        }
    }

    protected function modifyModelFile($name)
    {
        $filePath = app_path("Models/{$name}.php");
        $filesystem = new Filesystem();

        if ($filesystem->exists($filePath)) {
            $template = <<<PHP
            <?php

            namespace App\Models;

            use Illuminate\Database\Eloquent\Factories\HasFactory;
            use Illuminate\Database\Eloquent\Model;
            use Illuminate\Database\Eloquent\SoftDeletes;

            class {$name} extends Model
            {
                use HasFactory, SoftDeletes;

                protected \$fillable = [
                    'name'
                ];
            }

            PHP;

            $filesystem->put($filePath, $template);
        }
    }

    protected function modifyRequestFile($name)
    {
        $filePath = app_path("Http/Requests/{$name}.php");
        $filesystem = new Filesystem();

        if ($filesystem->exists($filePath)) {
            $template = <<<PHP
            <?php

            namespace App\Http\Requests;

            use Illuminate\Contracts\Validation\Validator;
            use Illuminate\Foundation\Http\FormRequest;
            use Illuminate\Http\Exceptions\HttpResponseException;

            class {$name} extends FormRequest
            {
                /**
                 * Determine if the user is authorized to make this request.
                 *
                 * @return bool
                 */
                public function authorize()
                {
                    return true;
                }

                /**
                 * Get the validation rules that apply to the request.
                 *
                 * @return array<string, mixed>
                 */
                public function rules()
                {
                    return [
                        'name' => 'required'
                    ];
                }

                public function failedValidation(Validator \$validator)
                {
                    \$response = response()->json([
                        'message' => \$validator->errors()->first(),
                        'details' => \$validator->errors()
                    ], 422);

                    throw new HttpResponseException(\$response);
                }

            }
            PHP;

            $filesystem->put($filePath, $template);
        }
    }

    protected function modifyControllerFile($name)
    {
        $filePath = app_path("Http/Controllers/API/{$name}Controller.php");
        $filesystem = new Filesystem();

        if ($filesystem->exists($filePath)) {
            $template = <<<PHP
            <?php

            namespace App\Http\Controllers\API;

            use App\Http\Controllers\Controller;
            use App\Http\Requests\\{$name}Request;
            use App\Http\Requests\\{$name}StoreRequest;
            use App\Http\Requests\\{$name}UpdateRequest;
            use App\Models\\{$name};
            use Illuminate\Http\Request;
            use Exception;

            class {$name}Controller extends Controller
            {
                public function index(Request \$request)
                {
                    try {

                        \$search = \$request->query('q');

                        return {$name}::when(!empty(\$search), function (\$q) use (\$search) {
                            \$q->where('name', 'LIKE', '%' . \$search . '%');
                        })
                        ->when(\$request->query('sortField') && \$request->query('sortOrder'), function (\$q) use (\$request) {
                            return \$q->orderBy(\$request->query('sortField'), \$request->query('sortOrder'));
                        })
                        ->paginate(\$request->query('sizePerPage', 25));

                    } catch(Exception \$error) {
                        return response()->json([
                            'message' => \$error->getMessage()
                        ], 400);
                    }
                }

                public function store({$name}StoreRequest \$request)
                {
                    return {$name}::create(\$request->validated());
                }

                public function show(\$id)
                {
                    \$model = {$name}::findOrFail(\$id);
                    return response()->json(\$model);
                }

                public function update({$name}UpdateRequest \$request, {$name} \$model)
                {
                    \$model = {\$name}::findOrFail(\$id);
                    \$model->update(\$request->validated());
                    return response()->json(\$model);
                }

                public function destroy(\$id)
                {
                    \$model = {\$name}::findOrFail(\$id);
                    \$model->delete();
                    return response()->json(['message' => 'Deleted successfully']);
                }
            }
            PHP;

            $filesystem->put($filePath, $template);
        }
    }
}
