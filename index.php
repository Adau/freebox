<?php

require_once __DIR__ . '/vendor/autoload.php';
$config = parse_ini_file('config.ini');

// Accès local :
// $application = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
// $application->authorize();
// $application->openSession();

// Accès distant
$application = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
$application->setFreeboxApiHost($config['freebox_api_host']);
$application->setAppToken($config['freebox_app_token']);
$application->openSession();

$fileSystemListingService = new \alphayax\freebox\api\v3\services\FileSystem\FileSystemListing($application);

$downloadService = new \alphayax\freebox\api\v3\services\download\Download($application);
$downloads = $downloadService->getAll();

foreach ($downloads as $task) {
    if (in_array($task->getStatus(), array('done', 'seeding'))) {
        $downloadDir = base64_decode($task->getDownloadDir());

        try {
            $fileName = $downloadDir . $task->getName();
            $fileInformation = $fileSystemListingService->getFileInformation($fileName);

            if ($fileInformation->getType() == 'file' && strpos($fileInformation->getMimetype(), 'video') === 0) {
                preg_match('/(.*?)\.*(\d{4})\.*(.*)/', $fileInformation->getName(), $matches);

                $movieTitle = str_replace('.', ' ', $matches[1]);
                $movieYear = $matches[2];

                var_dump($movieTitle, $movieYear);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
