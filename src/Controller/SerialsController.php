<?php
/**
 * Created by PhpStorm.
 * User: sevak
 * Date: 2020-03-04
 * Time: 20:46
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Serial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SerialsController extends AbstractController
{
    public function GetSerials()
    {
        $serials = $this->getDoctrine()
            ->getRepository(Serial::class)
            ->findAll();
        if (!$serials){
            return new Response("Таблица пуста");
        }
        foreach ($serials as $serial){
            var_dump($serial);
            echo '<br/>';
        }
        return new Response("Done");
    }

    public function GetSerial($id):Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $serial = $entityManager->getRepository(Serial::class)->find($id);
        if (!$serial){
            return new Response("Сериал с ID: ".$id." не найден");
        }
        else{
            $userData = $this->json([
                'serial' => [
                    [
                        "id"=> $id,
                        "name"=> $serial->getName(),
                        "description"=> $serial->getDescription(),
                        "logo"=> $serial->getLogo(),
                        "seasonNumber"=> $serial->getSeasonNumber(),
                        "genre"=>$serial->getGenre()
                    ],
                ]
            ]);
        }
        return new Response("Сериал с ID: ".$userData->getContent());
    }

    public function PostSerial(Request $request):Response{
        $entityManager = $this->getDoctrine()->getManager();

        $serial = new Serial();
        $serial->setName($request->request->get('name'));
        $serial->setDescription($request->request->get('description'));
        $serial->setSeasonNumber($request->request->get('seasonCount'));
        $serial->setLogo($request->request->get('logo'));
        $serial->setGenre($request->request->get('genre'));

        $entityManager->persist($serial);
        $entityManager->flush();

        return new Response('Saved new serial with id '.$serial->getId());
    }

    public function PutSerial($id, Request $request):Response{
        $entityManager = $this->getDoctrine()->getManager();
        $isExist = true;
        $serial = $entityManager->getRepository(Serial::class)->find($id);
        if (!$serial){
            $serial = new Serial();
            $isExist = false;
        }
        $serial->setName($request->request->get('name'));
        $serial->setDescription($request->request->get('description'));
        $serial->setSeasonNumber($request->request->get('seasonCount'));
        $serial->setLogo($request->request->get('logo'));
        $serial->setGenre($request->request->get('genre'));

        $entityManager->persist($serial);
        $entityManager->flush();
        if ($isExist) return new Response('Изменен сериал с идентификатором '.$serial->getId());
        return new Response('Создан сериал с идентификатором '.$serial->getId());
    }

    public function DeleteSerial($id){
        $entityManager = $this->getDoctrine()->getManager();
        $serial = $entityManager->getRepository(Serial::class)->find($id);
        if (!$serial) return new Response('Сериал с такими идентификатором не найден');
        $entityManager->remove($serial);
        $entityManager->flush();
        return new Response('Сериал c индентификатором '.$id.' был удален');
    }

    public function indexSerial(){
        return $this->render('serial/serial.html.twig');
    }
}