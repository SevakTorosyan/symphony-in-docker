<?php
/**
 * Created by PhpStorm.
 * User: sevak
 * Date: 2020-03-04
 * Time: 21:57
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Serial;
use App\Entity\Season;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SeasonsController extends AbstractController
{
    public function GetSeasons($serialID)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $seasons = $entityManager->getRepository(Season::class)->findBy(['serialID'=>$serialID]);
        if (!$seasons){
            return new Response("Таблица пуста");
        }
        foreach ($seasons as $season){
            echo $season->getName();
            echo '<br/>';
        }
        return new Response("That's all");
    }

    public function GetSeason($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $season = $entityManager->getRepository(Season::class)->find($id);
        if (!$season){
            return new Response("Сезон с ID: ".$id." не найден");
        }
        else{
            $userData = $this->json([
                'serial' => [
                    [
                        "id"=> $id,
                        'serial_id'=>$season->getSerialId(),
                        "name"=> $season->getName(),
                        "description"=> $season->getDescription(),
                        "logo"=> $season->getLogo(),
                        "seasonNumber"=> $season->getSeasonNumber(),
                    ],
                ]
            ]);
        }
        return new Response("Сезон с ID: ".$userData->getContent());
    }

    public function PostSeason(Request $request):Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $season = new Season();

        $serial = $this->getDoctrine()
            ->getRepository(Serial::class)
            ->find($request->request->get('serialId'));

        $season->setName($request->request->get('name'));
        $season->setSerialId($serial);
        $season->setLogo($request->request->get('logo'));
        $season->setDescription($request->request->get('description'));
        $season->setSeasonNumber($request->request->get('seriesNumber'));
        $entityManager->persist($season);
        $entityManager->flush();

        return new Response('Создан сезон с индентификатором '.$season->getId());
    }

    public function PutSeason($id,Request $request):Response
    {
        $isExist = true;
        $entityManager = $this->getDoctrine()->getManager();
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->find($id);
        if(!$season){
            $season = new Season();
            $isExist = false;
        }
        $serial = $this->getDoctrine()
            ->getRepository(Serial::class)
            ->find($request->request->get('serialId'));

        $season->setName($request->request->get('name'));
        $season->setSerialId($serial);
        $season->setLogo($request->request->get('logo'));
        $season->setDescription($request->request->get('description'));
        $season->setSeasonNumber($request->request->get('seriesNumber'));
        $entityManager->persist($season);
        $entityManager->flush();
        if ($isExist)
            return new Response('Изменен сезон с индентификатором '.$season->getId());
        else
            return new Response('Создан сезон с индентификатором '.$season->getId());
    }

    public function DeleteSeason($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $season = $entityManager->getRepository(Season::class)->find($id);
        if (!$season) return new Response('Сезон с такими идентификатором не найден');
        $entityManager->remove($season);
        $entityManager->flush();
        return new Response('Сезон c индентификатором '.$id.' был удален');
    }

    public function indexSeason(){
        $serials = $this->getDoctrine()
            ->getRepository(Serial::class)
            ->findAll();
        if (!$serials) return new Response("Сначала добавьте хотя бы 1 сериал");
        return $this->render('season/indexSeason.html.twig',['serials'=>$serials]);
    }
}
