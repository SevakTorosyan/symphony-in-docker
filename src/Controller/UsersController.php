<?php
/**
 * Created by PhpStorm.
 * User: sevak
 * Date: 2020-03-04
 * Time: 22:38
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Plan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends AbstractController
{
    public function GetUsers()
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        if (!$users){
            return new Response("Таблица пуста");
        }
        foreach ($users as $user){
            var_dump($user);
            echo '<br/>';
        }
        return new Response("Done");
    }

    public function GetUsero($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            return new Response('Пользователя с идентификатором '.$id.' не существует');
        }else{
            $userData = $this->json([
                'user' => [
                    [
                        "id"=> $id,
                        "name"=> $user->getName(),
                        "planId"=> $user->getPlanId(),
                        "birthDate"=> $user->getBirthDate(),
                        "isActive"=> $user->getIsSubscribeActual()? 'true':'false'
                    ],
                ]
            ]);
        }
        return new Response('Пользователь: '.$userData->getContent());
    }

    public function PostUser(Request $request):Response{
        $entityManager = $this->getDoctrine()->getManager();
        $user = new User();
        if ($request->request->get('planType')) {
            $plan = $this->getDoctrine()
                ->getRepository(Plan::class)
                ->find($request->request->get('planType'));
            $user->setPlanId($plan);
        }

        $user->setName($request->request->get('name'));
        $user->setBirthDate(new \DateTime($request->request->get('birthDate')));
        $user->setCreatedTime(new \DateTime('now'));
        $request->request->get('isActive')?
            $user->setIsSubscribeActual($request->request->get('isActive')):
            $user->setIsSubscribeActual(false);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Создан новый пользователь с id '.$user->getId());
    }

    public function PutUser($id,Request $request):Response{
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        $isExist = true;
        if (!$user) {
            $user = new User();
            $isExist = false;
        }
        if ($request->request->get('planType')) {
            $plan = $this->getDoctrine()
                ->getRepository(Plan::class)
                ->find($request->request->get('planType'));
            $user->setPlanId($plan);
        }

        $user->setName($request->request->get('name'));
        $user->setBirthDate(new \DateTime($request->request->get('birthDate')));
        $isExist?
            $user->setUpdatedTime(new \DateTime('now')):
            $user->setCreatedTime(new \DateTime('now'));
        $request->request->get('isActive')?
            $user->setIsSubscribeActual($request->request->get('isActive')):
            $user->setIsSubscribeActual(false);
        $entityManager->persist($user);
        $entityManager->flush();

        if ($isExist) return new Response('Изменен пользователь с id '.$user->getId());
        return new Response('Создан пользователь с id '.$user->getId());
    }

    public function DeleteUser($id){
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) return new Response('Пользователь с такими идентификатором не найден');
        $entityManager->remove($user);
        $entityManager->flush();
        return new Response('Пользователь c индентификатором '.$id.' был удален');
    }

    public function indexUser(){
        $plans = $this->getDoctrine()
            ->getRepository(Plan::class)
            ->findAll();
        return $this->render('user/indexUser.html.twig',['plans'=>$plans]);
    }
}