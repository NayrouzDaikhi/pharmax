<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users', name: 'admin_user_')]
class UserController extends AbstractController
{
    /**
     * List all users with filtering and sorting
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $q = $request->query->get('q');
        $role = $request->query->get('role');
        $status = $request->query->get('status');
        $sortField = $request->query->get('sort', 'lastName');
        $sortDir = $request->query->get('dir', 'ASC');

        $criteria = ['q' => $q, 'role' => $role, 'status' => $status];
        $users = $userRepository->searchUsers($criteria, [$sortField => $sortDir]);

        return $this->render('back/pages/user/index.html.twig', [
            'users' => $users,
            'criteria' => $criteria,
            'sort' => ['field' => $sortField, 'dir' => $sortDir],
        ]);
    }

    /**
     * Create new user - MUST come before {id} route to avoid conflict with /admin/users/new
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $currentUser = $this->getUser();
        $isSuperAdmin = $currentUser && in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles());

        $form = $this->createForm(UserType::class, $user, [
            'currentUser' => $currentUser,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $user->setCreatedAt(new \DateTime());
            $user->setUpdatedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User created successfully!');

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('back/pages/user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'isEdit' => false,
            'isSuperAdmin' => $isSuperAdmin,
            'canEditPassword' => true,
            'canEditRoles' => true,
        ]);
    }

    /**
     * Show single user with filterable users list
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        Request $request,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_user_index');
        }

        // Get filter/search/sort parameters from query string
        $q = $request->query->get('q');
        $status = $request->query->get('status');
        $role = $request->query->get('role');
        $sortField = $request->query->get('sort', 'lastName');
        $sortDir = $request->query->get('dir', 'ASC');

        // Build criteria and orderBy arrays
        $criteria = [
            'q' => $q,
            'status' => $status,
            'role' => $role,
        ];

        $orderBy = [$sortField => $sortDir];

        // Fetch filtered users using the repository method
        $users = $userRepository->searchUsers($criteria, $orderBy);

        // Check current user's role for permission-based display
        $currentUser = $this->getUser();
        $isSuperAdmin = $currentUser && in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles());
        $canShowPassword = $isSuperAdmin;

        return $this->render('back/pages/user/show.html.twig', [
            'user' => $user,
            'users' => $users,
            'criteria' => $criteria,
            'sort' => ['field' => $sortField, 'dir' => $sortDir],
            'canShowPassword' => $canShowPassword,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    /**
     * Edit existing user
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_user_index');
        }

        // Check current user's role and editing target's roles for permission-based restrictions
        $currentUser = $this->getUser();
        $isSuperAdmin = $currentUser && in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles());
        $targetIsSuperAdmin = in_array('ROLE_SUPER_ADMIN', $user->getRoles());
        
        // Normal admin cannot edit super admin's roles or password
        if (!$isSuperAdmin && $targetIsSuperAdmin) {
            $this->addFlash('error', 'You do not have permission to edit Super Admin users.');
            return $this->redirectToRoute('admin_user_index');
        }

        $form = $this->createForm(UserType::class, $user, [
            'currentUser' => $currentUser,
            'editingUser' => $user,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Only super admin can change passwords
            if ($isSuperAdmin) {
                $plainPassword = $form->get('password')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
            }

            $user->setUpdatedAt(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'User updated successfully!');

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('back/pages/user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'isEdit' => true,
            'isSuperAdmin' => $isSuperAdmin,
            'canEditPassword' => $isSuperAdmin,
            'canEditRoles' => $isSuperAdmin,
        ]);
    }

    /**
     * Delete user
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_user_index');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'User deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid security token.');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * Toggle user status (block/unblock)
     */
    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $em): Response
    {
        if ($user->isBlocked()) {
            $user->setStatus(User::STATUS_UNBLOCKED);
        } else {
            $user->setStatus(User::STATUS_BLOCKED);
        }

        $em->persist($user);
        $em->flush();

        // After blocking, user will be logged out on next request by BlockedUserSubscriber
        $this->addFlash('success', 'User status updated.');

        return $this->redirectToRoute('admin_user_index');
    }
}
