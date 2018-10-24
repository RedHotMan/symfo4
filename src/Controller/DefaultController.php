<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route(path="/", methods={"GET"})
     */
    public function home()
    {
        return $this->render('/default/home.html.twig');
    }

    /**
     * @param $name
     * @return Response
     * @Route(path="/myname/{name}", methods={"GET"})
     */
    public function sayMyName($name) {
        return $this->render('/default/name.html.twig', [
            'name' => $name,
        ]);
    }

    /**
     * @Route(path="/users", methods={"GET"}, name="users_list")
     */
    public function listUsers() {
        $em = $this->getDoctrine();
        $users = $em->getRepository(User::class)->findAll();

        if (!$users) {
            throw $this->createNotFoundException("No user found");
        }
        return $this->render('user/users.html.twig', ['users' => $users]);
    }

    /**
     * @Route(path="/new", methods={"GET", "POST"}, name="new_user")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function new(Request $request) {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('users_list');
        }


        return $this->render('user/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route(path="/edit/{id}", name="edit_user")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editUser(Request $request, string $id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('users_list');
        }


        return $this->render('user/edit.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route(path="/users/{id}", name="show_user")
     * @param $id
     * @return Response
     */
    public function showUser(string $id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->redirectToRoute('user_list');
    }

    /**
     * @Route(path="/delete/{id}", methods={"GET"}, name="delete_user")
     * @param Request $request
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUser(Request $request, string $id) {
        if(!$this->isCsrfTokenValid('delete_user', $request->query->get('_token'))) {
            throw new AccessDeniedException('Erreur CSRF');
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('users_list');
    }
}