<?php

namespace App\Console\Commands;

/**
 * Make Middleware Command
 */
class MakeMiddleware extends Command
{
    public function execute(array $arguments): void
    {
        $name = $this->argument($arguments, 0);

        if (!$name) {
            $this->error('Middleware name is required');
            $this->info('Usage: php lumi make:middleware CheckRole');
            return;
        }

        $className = $this->studly($name);
        if (!str_ends_with($className, 'Middleware')) {
            $className .= 'Middleware';
        }

        $path = __DIR__ . "/../../Middlewares/$className.php";

        $content = $this->getStub($className);

        $this->createFile($path, $content);
    }

    private function getStub(string $className): string
    {
        return <<<PHP
<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

/**
 * $className
 */
class $className
{
    /**
     * Handle the request
     * Return false to stop request processing
     */
    public function handle(Request \$request, Response \$response): bool
    {
        // Add your middleware logic here
        
        // Return true to continue processing
        // Return false to stop and send response
        return true;
    }
}

PHP;
    }
}
