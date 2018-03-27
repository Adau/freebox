<?php

require_once __DIR__ . '/vendor/autoload.php';
$config = parse_ini_file('config.ini');

// Accès local :
// $application = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
// $application->authorize();
// $application->openSession();

$application = new \alphayax\freebox\utils\Application('com.alphayax.freebox', 'Freebox PHP API', '0.0.1');
$application->setFreeboxApiHost($config['freebox_api_host']);
$application->setAppToken($config['freebox_app_token']);
$application->openSession();

$downloadService = new \alphayax\freebox\api\v3\services\download\Download($application);
$fileSystemListingService = new \alphayax\freebox\api\v3\services\FileSystem\FileSystemListing($application);
$fileSystemOperationService = new \alphayax\freebox\api\v3\services\FileSystem\FileSystemOperation($application);

$allocine = new AlloHelper;

foreach ($downloadService->getAll() as $task) {
    if (in_array($task->getStatus(), array('done', 'seeding'))) {
        $downloadDir = base64_decode($task->getDownloadDir());

        try {
            $fileName = $downloadDir . $task->getName();
            $fileInformation = $fileSystemListingService->getFileInformation($fileName);

            if ($fileInformation->getType() == 'file' && strpos($fileInformation->getMimetype(), 'video') === 0) {
                preg_match('/(.*?)\.*(\d{4})\.*(.*)/', $fileInformation->getName(), $matches);

                $movieTitle = str_replace('.', ' ', $matches[1]);
                $movieYear = $matches[2];

                $results = $allocine->search($movieTitle, 1, 10, false, array('movie'))->getArray();
                if ($results['totalResults']) {
                    foreach ($results['movie'] as $movie) {
                        if (strtolower($movie['originalTitle']) == strtolower($movieTitle) &&
                            $movie['productionYear'] == (int)$movieYear
                        ) {
                            // ToDo
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
