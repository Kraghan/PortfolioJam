<?php

namespace AdminBundle\Controller;

use PortfolioBundle\Entity\Skills;
use PortfolioBundle\Entity\SocialNetwork;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @Route("/admin")
 */
class DefaultController extends Controller
{
    private function checkConnexion(Request $request)
    {
        $session = $request->getSession();

        if(!$session->has("user"))
        {
            $session->getFlashBag()->add('error', 'You are not authorized (login is necessary) !');
            return [false,$this->redirectToRoute('admin_login')];
        }

        if($session->has("lastConnexion"))
        {
            $date = date_diff($session->get("lastConnexion"),new \DateTime());
            if($date->h >= 1)
            {
                $session->getFlashBag()->add('error', 'Log out !');
                $session->clear();
                return [false,$this->redirectToRoute('admin_login')];
            }
            else
                return [true];
        }

        $session->getFlashBag()->add('error', 'Error session !');
        $session->clear();
        return [false,$this->redirectToRoute('admin_login')];
    }

    /**
     * @Route("/", name="admin_login")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        if($session->has("user"))
        {
            $user = $em->getRepository("AdminBundle:User")->findOneByLogin($session->get("user")->getLogin());
            if($user)
            {
                $session->getFlashBag()->add('success', 'Already connected !');
                return $this->redirectToRoute('dashboard');
            }
        }

        if($request->isMethod("post"))
        {
            $user = $em->getRepository("AdminBundle:User")->findOneByLogin($request->get("login"));
            if($user && $user->getPassword() == sha1($request->get("password")))
            {
                $session->set("user",$user);
                $session->set("lastConnexion",new \DateTime());
                $session->getFlashBag()->add('success', 'Connected !');
                return $this->redirectToRoute('dashboard');
            }

            $session->getFlashBag()->add('error', 'Wrong login or password !');
        }

        return $this->render('admin/admin_login.html.twig');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('admin_login');
    }

    /**
     * @Route("/changepassword", name="changepassword")
     */
    public function changepasswordAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        if($request->isMethod("POST"))
        {
            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository("AdminBundle:User")->findOneById($session->get("user")->getId());

            $error = false;
            if(sha1($request->get("old")) != $user->getPassword())
            {
                $error = true;
                $session->getFlashBag()->add('error', 'Mot de passe incorrect !');
            }

            if($request->get("new") !=  $request->get("confirmation"))
            {
                $error = true;
                $session->getFlashBag()->add('error', 'Le nouveau mot de passe ne correspond pas à la confirmation');
            }

            if(!$error)
            {
                $user->setPassword(sha1($request->get("new")));
                $em->persist($user);
                $em->flush();

                $session->set("user", $user);
                $session->getFlashBag()->add('success', 'Mot de passe changé');
            }
        }

        return $this->render('admin/admin_changepassword.html.twig');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        return $this->render('admin/admin_base.html.twig');
    }

    /**
     * @Route("/socialNetwork", name="admin_socialnetwork")
     */
    public function socialNetworkAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        $availableSocialNetwork = [
            "facebook",
            "twitter",
            "google",
            "linkedin",
            "instagram",
            "youtube",
            "vimeo",
            "tumblr",
            "dribbble",
            "behance",
            "flickr"];

        if($request->isMethod("POST"))
        {
            if($request->get("nom") && trim($request->get("nom")) != ""
                && $request->get("url") && trim($request->get("url"))
                && $request->get("picto") && trim($request->get("picto")))
            {
                $social = new SocialNetwork();
                $social->setName($request->get("nom"));
                $social->setUrl($request->get("url"));
                $social->setPicto($request->get("picto"));
                $em->persist($social);
                $em->flush();

                $session->getFlashBag()->add('success', "Ajout du réseau social réussie !");
            }
            else
                $session->getFlashBag()->add('error', "Echec de l'ajout");

        }

        $socialNetwork = $em->getRepository("PortfolioBundle:SocialNetwork")
            ->findAll();

        return $this->render('admin/admin_socialnetwork.html.twig', ["socialNetwork" => $socialNetwork, "availables" => $availableSocialNetwork]);
    }

    /**
     * @Route("/content", name="admin_content")
     */
    public function contentAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $photo = "photo";
        $background1 = "background1";
        $background2 = "background2";
        $background3 = "background3";

        if(!file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt'))
            touch($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt');
        if(!file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt'))
            touch($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt');

        if($request->isMethod("POST"))
        {
            file_put_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt',$request->get("content1"));
            file_put_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt',$request->get("content2"));

            $session->getFlashBag()->add('success', "Modification du contenu réussie !");

            if($_FILES["photo"]["name"] != "")
            {
                $uploadRes = $this->upload("photo", $photo);
                if ($uploadRes["result"]) {
                    $session->getFlashBag()->add('success', "Upload photo réussie !");
                } else
                    $session->getFlashBag()->add('error', 'Upload photo error : ' . $uploadRes["message"]);
            }
            if($_FILES["background1"]["name"] != "")
            {
                $uploadRes = $this->upload("background1", $background1);
                if ($uploadRes["result"]) {
                    $session->getFlashBag()->add('success', "Upload background1 réussie !");
                } else
                    $session->getFlashBag()->add('error', 'Upload background1 error : ' . $uploadRes["message"]);
            }
            if($_FILES["background2"]["name"] != "")
            {
                $uploadRes = $this->upload("background2", $background2);
                if ($uploadRes["result"]) {
                    $session->getFlashBag()->add('success', "Upload background2 réussie !");
                } else
                    $session->getFlashBag()->add('error', 'Upload background2 error : ' . $uploadRes["message"]);
            }
            if($_FILES["background3"]["name"] != "")
            {
                $uploadRes = $this->upload("background3", $background3);
                if ($uploadRes["result"]) {
                    $session->getFlashBag()->add('success', "Upload background3 réussie !");
                } else
                    $session->getFlashBag()->add('error', 'Upload background3 error : ' . $uploadRes["message"]);
            }

        }

        $filenamephoto = "photo".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$photo);
        $filenamebackground1 = "background1".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$background1);
        $filenamebackground2 = "background2".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$background2);
        $filenamebackground3 = "background3".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$background3);
        $content1 = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt');
        $content2 = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt');
        $photoExist = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamephoto);
        $photoExist2 = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamebackground1);
        $photoExist3 = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamebackground2);
        $photoExist4 = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamebackground3);

        return $this->render('admin/admin_content.html.twig', ["content1" => $content1,
            "content2" => $content2, "photo" => $filenamephoto, "photoExist" => $photoExist,
            "photoExist2" => $photoExist2, "photoExist3" => $photoExist3, "photoExist4" => $photoExist4,
            "background1" => $filenamebackground1, "background2" => $filenamebackground2, "background3" => $filenamebackground3]);
    }

    /**
     * @Route("/skills", name="admin_skills")
     */
    public function skillsAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST"))
        {
            if($request->get("nom") && trim($request->get("nom")) != "")
            {
                $skill = new Skills();
                $skill->setName($request->get("nom"));
                $skill->setSkillProgress($request->get("pourcentage"));
                $skill->setDescription($request->get("description"));
                $em->persist($skill);
                $em->flush();

                $session->getFlashBag()->add('success', "Ajout de la compétence réussie !");
            }
            else
                $session->getFlashBag()->add('error', "Echec de l'ajout");
        }

        $skills = $em->getRepository("PortfolioBundle:Skills")
            ->findAll();

        return $this->render('admin/admin_skills.html.twig', ["skills" => $skills]);
    }


    private function upload($inputName, $filename, $uploaddir = "", $fileType = ["image/jpg","image/jpeg","image/png"])
    {
        if($uploaddir == "")
            $uploaddir = $this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\';

        $extension = "";
        $explode = explode(".",strtolower($_FILES[$inputName]["name"]));
        switch($explode[count($explode)-1])
        {
            case 'jpg':
                if (!in_array("image/jpg", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".jpg";
                break;
            case 'png':
                if (!in_array("image/png", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".png";
                break;
            case 'jpeg':
                if (!in_array("image/jpeg", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".jpeg";
                break;
            case 'gif':
                if (!in_array("image/gif", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".gif";
                break;
            case 'zip':
                if (!in_array("application/x-compressed", $fileType)
                    && !in_array("application/x-zip-compressed", $fileType)
                    && !in_array("application/zip", $fileType)
                    && !in_array("multipart/x-zip", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".zip";
                break;
            default:
                return [
                    "result" => false,
                    "message" => "Type de fichier inconnu ! "
                ];
                break;
        }

        if($ext = $this->getFileExtension($uploaddir.$filename))
            unlink($uploaddir.$filename.$ext);

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploaddir.$filename.$extension))
        {
            return [
                "result" => true,
                "message" => "Upload réussie ! ",
                "filename" => $filename.$extension
            ];
        }
        else
        {
            return [
                "result" => false,
                "message" => "Upload échouée ! "
            ];
        }
    }

    private function getFileExtension($filename)
    {
        $files = glob($filename.".*");

        return isset($files[0]) ? ".".pathinfo($files[0])["extension"] : false;
    }

    private function d($data)
    {
        echo "<pre>"; print_r($data); echo "</pre>";
    }
}
