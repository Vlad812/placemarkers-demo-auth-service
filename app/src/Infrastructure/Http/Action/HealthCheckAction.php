<?php



declare(strict_types=1);



namespace App\Infrastructure\Http\Action;



use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;



#[Route(

    '/health',

    name: 'health_check',

    methods: ['GET'],

)]

final class HealthCheckAction

{

    public function __invoke(): Response

    {

        return new JsonResponse(['status' => 'ok']);

    }

}

