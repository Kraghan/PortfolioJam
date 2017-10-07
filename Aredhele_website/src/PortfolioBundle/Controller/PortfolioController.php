<?php

namespace PortfolioBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PortfolioBundle\Entity\Contact;

class PortfolioController extends Controller
{
    /**
     * @Route("/{trad}", requirements={"trad":"en|fr"}, defaults={"trad" :"fr"}, name="homepage")
     */
    public function indexAction(Request $request)
    {
        $lang = $request->get("trad");

        $em = $this->getDoctrine()->getManager();

        $socialNetworks = $em->getRepository("PortfolioBundle:SocialNetwork")
            ->findAll();

        $skills = $em->getRepository("PortfolioBundle:Skills")
            ->findAll();

        $content = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt');

        $content2 = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt');

        $dataTrad = json_decode(file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\trads.json'),true);

        $dataTrad = $dataTrad[$lang];

        $filenamephoto = "photo".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\photo');
        $filenamebackground1 = "background1".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\background1');
        $filenamebackground2 = "background2".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\background2');
        $filenamebackground3 = "background3".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\background3');

        // replace this example code with whatever you need
        return $this->render('portfolio.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            "socialNetworks" => $socialNetworks, "skills" => $skills, "content" => $content, "content2" => $content2,
            "trad" => $dataTrad, "background1" => $filenamebackground1, "background2" => $filenamebackground2,
            "background3" => $filenamebackground3, "photo" => $filenamephoto
        ]);
    }

    /**
     * @Route("/send_message", name="send_message")
     */
    public function send_messageAction(Request $request)
    {
        if($request->isMethod("post"))
        {
            $em = $this->getDoctrine()->getManager();
            $contact = new Contact();
            $contact->setName($request->get("name"));
            $contact->setEmail($request->get("email"));
            $contact->setMessage($request->get("message"));
            $contact->setCreatedAt(new \DateTime());
            $em->persist($contact);
            $em->flush();

            echo json_encode(array("result" => true, "message" => "Sent !"));
            exit();
        }
        echo json_encode(array("result" => false, "message" => "Wrong method !"));
        exit();
    }

    private function getFileExtension($filename)
    {
        $files = glob($filename.".*");

        return isset($files[0]) ? ".".pathinfo($files[0])["extension"] : false;
    }
}