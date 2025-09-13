<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.email LIKE :search')
                ->orWhere('u.nom LIKE :search')
                ->orWhere('u.telephone LIKE :search')
                ->setParameter('search', "%$search%")
                ->orderBy('u.dateInscription', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $users = $userRepository->findBy([], ['dateInscription' => 'DESC']);
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
