<?php
session_start();

require_once '../app/Core/Database.php';
require_once '../app/Repositories/PublicationRepository.php';
require_once '../app/Controllers/GetLikersApiController.php';

$publicationRepository = new PublicationRepository(Database::getConnection());
$controller = new GetLikersApiController($publicationRepository);
$controller->handle();
?>