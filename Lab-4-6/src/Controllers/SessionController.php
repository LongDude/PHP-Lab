<?php

namespace src\Controllers;
use PDOException;
use src\Files\BaseUploader;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SessionController{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }
}
?>