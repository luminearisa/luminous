<?php

namespace App\Console\Commands;

/**
 * Make Controller Command
 */
class MakeController extends Command
{
    public function execute(array $arguments): void
    {
        $name = $this->argument($arguments, 0);

        if (!$name) {
            $this->error('Controller name is required');
            $this->info('Usage: php lumi make:controller UserController');
            return;
        }

        $className = $this->studly($name);
        if (!str_ends_with($className, 'Controller')) {
            $className .= 'Controller';
        }

        $path = __DIR__ . "/../../Controllers/$className.php";

        $content = $this->getStub($className);

        $this->createFile($path, $content);
    }

    private function getStub(string $className): string
    {
        return <<<PHP
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/**
 * $className
 */
class $className extends Controller
{
    /**
     * Display a listing of the resource
     */
    public function index(Request \$request, Response \$response): void
    {
        \$response->success([
            'items' => []
        ]);
    }

    /**
     * Store a newly created resource
     */
    public function store(Request \$request, Response \$response): void
    {
        \$data = \$request->all();
        
        // Validation
        \$errors = \$this->validate(\$data, [
            'name' => 'required|string|min:3'
        ]);

        if (!empty(\$errors)) {
            \$response->validationError(\$errors);
            return;
        }

        // Create resource logic here
        
        \$response->created(['id' => 1], 'Resource created successfully');
    }

    /**
     * Display the specified resource
     */
    public function show(Request \$request, Response \$response, array \$params): void
    {
        \$id = \$params['id'] ?? null;
        
        if (!\$id) {
            \$response->error('ID is required');
            return;
        }

        // Fetch resource logic here
        
        \$response->success([
            'id' => \$id,
            'name' => 'Sample'
        ]);
    }

    /**
     * Update the specified resource
     */
    public function update(Request \$request, Response \$response, array \$params): void
    {
        \$id = \$params['id'] ?? null;
        \$data = \$request->all();
        
        if (!\$id) {
            \$response->error('ID is required');
            return;
        }

        // Update resource logic here
        
        \$response->success(['id' => \$id], 'Resource updated successfully');
    }

    /**
     * Remove the specified resource
     */
    public function destroy(Request \$request, Response \$response, array \$params): void
    {
        \$id = \$params['id'] ?? null;
        
        if (!\$id) {
            \$response->error('ID is required');
            return;
        }

        // Delete resource logic here
        
        \$response->success(null, 'Resource deleted successfully');
    }
}

PHP;
    }
}
