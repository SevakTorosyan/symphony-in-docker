<?php
/**
 * Created by PhpStorm.
 * User: sevak
 * Date: 2020-03-04
 * Time: 23:11
 */

namespace App\Controller;
use App\Entity\Plan;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
class PlansController extends AbstractController
{
    public function GetPlans()
    {
        $plans = $this->getDoctrine()
            ->getRepository(Plan::class)
            ->findAll();
        if (!$plans){
            return new Response("Таблица пуста");
        }
        foreach ($plans as $plan){
            var_dump($plan);
            echo '<br/>';
        }
        return new Response("Done");
    }

    public function GetPlan($id)
    {
        $plan = $this->getDoctrine()
            ->getRepository(Plan::class)
            ->find($id);

        if (!$plan) {
            return new Response('Плана подписки с идентификатором '.$id.' не существует');
        }else{
            $planData = $this->json([
                'plan' => [
                    [
                        "id"=> $id,
                        "name"=> $plan->getName(),
                        "description"=> $plan->getDescription(),
                        "subscriptionDayCount"=> $plan->getSubscriptionDayCount(),
                        "price"=> $plan->getPrice(),
                        "isActive"=> $plan->getIsActual()? 'true':'false'
                    ],
                ]
        ]);
        }
        return new Response('План: '.$planData->getContent());
    }

    public function PostPlan(Request $request):Response{
        $entityManager = $this->getDoctrine()->getManager();

        $plan = new Plan();
        $plan->setName($request->request->get('name'));
        $plan->setDescription($request->request->get('description'));
        $plan->setSubscriptionDayCount($request->request->get('dayCount'));
        $plan->setPrice($request->request->get('price'));
        $request->request->get('isActive')?
            $plan->setIsActual($request->request->get('isActive')):
            $plan->setIsActual(false);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($plan);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new plan with id '.$plan->getId());
    }

    public function PatchPlan($id, Request $request):Response{
        $entityManager = $this->getDoctrine()->getManager();
        $plan = $entityManager->getRepository(Plan::class)->find($id);

        if (!$plan) return new Response('План с такими идентификатором не найден');
        $plan->setName($request->request->get('name'));
        $plan->setDescription($request->request->get('description'));
        $plan->setSubscriptionDayCount($request->request->get('dayCount'));
        $plan->setPrice($request->request->get('price'));
        $request->request->get('isActive')?
            $plan->setIsActual($request->request->get('isActive')):
            $plan->setIsActual(false);
        $entityManager->flush();
        return new Response('План с идентиикатором '.$id.' изменен');
    }

    public function DeletePlan($id){
        $entityManager = $this->getDoctrine()->getManager();
        $plan = $entityManager->getRepository(Plan::class)->find($id);
        if (!$plan) return new Response('План с такими идентификатором не найден');
        $entityManager->remove($plan);
        $entityManager->flush();
        return $this->redirectToRoute('GetPlans');
    }

    public function indexPlan(){

        return $this->render('plan/indexPlan.html.twig');
    }
}