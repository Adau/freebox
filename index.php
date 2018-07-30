<?php

require_once __DIR__ . '/vendor/autoload.php';
$config = parse_ini_file('config.ini');

if (isset($_GET['user'])) {
    $config = $config[$_GET['user']];
}

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

                $movieTitle = preg_replace('/\W+/', ' ', $matches[1]);
                $movieYear = $matches[2];

                $results = $allocine->search($movieTitle, 1, 10, false, array('movie'))->getArray();
                if ($results['totalResults']) {
                    foreach ($results['movie'] as $movie) {
                        if (strtolower(preg_replace('/\W+/', ' ', $movie['originalTitle'])) == strtolower($movieTitle)
                            && date('Y', strtotime($movie['release']['releaseDate'])) == (int)$movieYear
                        ) {
                            $fileExtension = pathinfo($fileInformation->getName(), PATHINFO_EXTENSION);

                            $fileSystemOperationService->rename(
                                $fileInformation->getPath(),
                                sprintf('%s.%s', $movie['title'], $fileExtension)
                            );

                            $downloadService->deleteFromId($task->getId());

                            $freeMobileClient = new \Th3Mouk\FreeMobileSMSNotif\Client(
                                $config['freemobile_id'],
                                $config['freemobile_secret_key']
                            );

                            $message = sprintf(
                                "Nouveau film sur la Freebox :\n" .
                                "%s\n\n" .
                                "Infos et bande annonce :\n" .
                                "%s",
                                $movie['title'],
                                $movie['link'][0]['href']
                            );

                            $freeMobileClient->send($message);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
