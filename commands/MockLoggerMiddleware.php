<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;

class MockLoggerMiddleware extends Command
{
    protected $signature = 'mocklogger:generate-middleware';
    protected $description = 'Generate and publish MockLogger middleware';

    public function handle()
    {
        $middlewareName = 'MockLoggerMiddleware'; // Change this to your desired middleware name

        // Generate the middleware file content
        $middlewareContent = "<?php\n\nnamespace App\Http\Middleware;\n\nclass $middlewareName\n{\n    // Your middleware logic here\n}\n";

        // Specify the middleware path
        $middlewarePath = app_path("Http/Middleware/$middlewareName.php");

        // Write the middleware content to the file
        file_put_contents($middlewarePath, $middlewareContent);

        $this->info("Middleware $middlewareName generated and published successfully!");
    }
}
