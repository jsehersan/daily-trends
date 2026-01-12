<?php

namespace App\Infrastructure\Controller\Web;

use App\Domain\Repository\FeedRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(Request $request, FeedRepositoryInterface $repository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 12; 
        
        $paginated = $repository->findAllPaginated($page, $limit, 'publishedAt', 'desc');

        return $this->render('dashboard/index.html.twig', [
            'news' => $paginated->items,
            'total' => $paginated->totalItems,
            'currentPage' => $page,
            'totalPages' => ceil($paginated->totalItems / $limit),
            'author' => 'Jose M. Hernandez'
        ]);
        
    }
}