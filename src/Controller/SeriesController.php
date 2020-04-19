<?php
/**
 * Created by PhpStorm.
 * User: sevak
 * Date: 2020-03-04
 * Time: 22:19
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Series;
use App\Entity\Season;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SeriesController extends AbstractController
{
    public function GetSeriesBySeasonId($seasonID)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $series = $entityManager->getRepository(Series::class)->findBy(['seasonId'=>$seasonID]);
        if (!$series){
            return new Response("Таблица пуста");
        }
        foreach ($series as $serie){
            echo $serie->getName();
            echo '<br/>';
        }
        return new Response("That's all");
    }

    public function GetSeries($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $series = $entityManager->getRepository(Series::class)->find($id);
        if (!$series){
            return new Response("Серия с ID: ".$id." не найдена");
        }
        else{
            $userData = $this->json([
                'serial' => [
                    [
                        "id"=> $id,
                        'season_id'=>$series->getSeasonId(),
                        "name"=> $series->getName(),
                        "description"=> $series->getDescription(),
                        "seriesNumber"=> $series->getSeriesNumber(),
                    ],
                ]
            ]);
        }
        return new Response("Серия с ID: ".$userData->getContent());
    }

    public function PostSeries(Request $request):Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $series = new Series();

        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->find($request->request->get('seasonId'));

        $series->setName($request->request->get('name'));
        $series->setSeasonId($season);
        $series->setDescription($request->request->get('description'));
        $series->setSeriesNumber($request->request->get('seriesNumber'));
        $entityManager->persist($series);
        $entityManager->flush();

        return new Response('Создана серия с индентификатором '.$series->getId());
    }

    public function PutSeries($id, Request $request):Response
    {
        $isExist = true;
        $entityManager = $this->getDoctrine()->getManager();
        $series = $this->getDoctrine()
            ->getRepository(Series::class)
            ->find($id);
        if(!$series){
            $series = new Series();
            $isExist = false;
        }
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->find($request->request->get('seasonId'));

        $series->setName($request->request->get('name'));
        $series->setSeasonId($season);
        $series->setDescription($request->request->get('description'));
        $series->setSeriesNumber($request->request->get('seriesNumber'));
        $entityManager->persist($series);
        $entityManager->flush();
        if ($isExist)
            return new Response('Изменена серия с индентификатором '.$series->getId());
        else
            return new Response('Создана серия с индентификатором '.$series->getId());
    }

    public function DeleteSeries($id):Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $series = $entityManager->getRepository(Series::class)->find($id);
        if (!$series) return new Response('Серия с такими идентификатором не найдена');
        $entityManager->remove($series);
        $entityManager->flush();
        return new Response('Серия c индентификатором '.$id.' была удален');    }

    public function indexSeries(){
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findAll();
        if (!$seasons) return new Response('Сначала добавьте хотя бы 1 сезон');
        return $this->render('series/series.html.twig',['seasons'=>$seasons]);
    }
}