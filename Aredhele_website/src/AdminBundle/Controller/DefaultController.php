<?php

namespace AdminBundle\Controller;

use DevLogBundle\Entity\Article;
use DevLogBundle\Entity\ArticleBlock;
use PortfolioBundle\Entity\Categorie;
use PortfolioBundle\Entity\Project;
use PortfolioBundle\Entity\ProjectCategoriesMm;
use PortfolioBundle\Entity\Skills;
use PortfolioBundle\Entity\SocialNetwork;
use PortfolioBundle\Entity\WorkExperience;
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
    private function checkConnexion(Request $request, $withTimeRestriction = true)
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
            if($date->h >= 1 && $withTimeRestriction)
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

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST"))
        {
            foreach ($request->get("contact") as $contact)
            {
                $c = $em->getRepository("PortfolioBundle:Contact")
                    ->findOneBy(array("id" => $contact));

                $c->setAnswered(true);
                $em->persist($c);
            }
            $em->flush();
            
        }

        $contacts = $em->getRepository("PortfolioBundle:Contact")
            ->findBy(array("answered" => false), array("createdAt" => "ASC"));

        return $this->render('admin/admin_dashboard.html.twig',["contacts" => $contacts]);
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
        $cv = "cv";

        if(!file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt'))
            touch($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt');
        if(!file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt'))
            touch($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt');
        if(!file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\gps.json'))
        {
            touch($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\gps.json');
            $gps = ["longitude" => 0.0,
                "latitude" => 0.0,
                "lieu" => "Santa House"];
            file_put_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\gps.json',json_encode($gps));
        }
        else
            $gps = json_decode(file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\gps.json'),true);

        if($request->isMethod("POST"))
        {
            file_put_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt',$request->get("content1"));
            file_put_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt',$request->get("content2"));

            $gps = ["longitude" => $request->get('longitude'),
                "latitude" => $request->get('latitude'),
                "lieu" => $request->get('lieu')];

            file_put_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\gps.json',json_encode($gps));

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
            if($_FILES["cv"]["name"] != "")
            {
                $uploadRes = $this->upload("cv", $cv, $this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\', ["application/pdf"]);
                if ($uploadRes["result"]) {
                    $session->getFlashBag()->add('success', "Upload CV réussie !");
                } else
                    $session->getFlashBag()->add('error', 'Upload CV error : ' . $uploadRes["message"]);
            }

        }

        $filenamephoto = "photo".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$photo);
        $filenamebackground1 = "background1".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$background1);
        $filenamebackground2 = "background2".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$background2);
        $filenamebackground3 = "background3".$this->getFileExtension($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$background3);
        $filenamecv = "cv.pdf";
        $content1 = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content1.txt');
        $content2 = file_get_contents($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\content2.txt');
        $photoExist = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamephoto);
        $photoExist2 = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamebackground1);
        $photoExist3 = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamebackground2);
        $photoExist4 = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\'.$filenamebackground3);
        $cvExist = file_exists($this->get('kernel')->getRootDir() . '\\..\\web\\TXT\\'.$filenamecv);

        $date = new \DateTime();
        $cvHash = sha1($date->format("Y-m-d")."TXTcv.pdf");

        return $this->render('admin/admin_content.html.twig', ["content1" => $content1,
            "content2" => $content2,
            "photo" => $filenamephoto,
            "photoExist" => $photoExist,
            "photoExist2" => $photoExist2,
            "photoExist3" => $photoExist3,
            "photoExist4" => $photoExist4,
            "cvExist" => $cvExist,
            "background1" => $filenamebackground1,
            "background2" => $filenamebackground2,
            "background3" => $filenamebackground3,
            "cv" => $cv,
            "latitude" => $gps["latitude"],
            "longitude" => $gps["longitude"],
            "lieu" => $gps["lieu"],
            "downloadLinkCV" => $cvHash]);
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

    /**
     * @Route("/workExperience", name="admin_workExperience")
     */
    public function workExperienceAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST"))
        {
            $experience = new WorkExperience();
            $experience->setCompany($request->get("company"));
            $experience->setPoste($request->get("poste"));
            $experience->setStart(new \DateTime($request->get("start")));
            if($request->get("end"))
                $experience->setEnd(new \DateTime($request->get("end")));
            $em->persist($experience);
            $em->flush();
        }

        $workExperiences = $em->getRepository("PortfolioBundle:WorkExperience")
            ->findAll();

        return $this->render('admin/admin_workExperience.html.twig', ["workExperiences" => $workExperiences]);
    }

    /**
     * @Route("/downloadFile/{hash}/{type}/{name}", name="downloadFile")
     */
    public function downloadFileAction($hash, $type, $name)
    {
        $date = new \DateTime();

        $hashBis = sha1($date->format("Y-m-d").$type.$name);

        if($hashBis != $hash)
            return $this->redirectToRoute('');

        $explode = explode(".",strtolower($name));
        $mime = "";
        switch($explode[count($explode)-1])
        {
            case 'jpg':
                $mime = "image/jpg";
                break;
            case 'png':
                $mime = "image/png";
                break;
            case 'jpeg':
                $mime = "image/jpeg";
                break;
            case 'gif':
                $mime = "image/gif";
                break;
            case 'zip':
                $mime = "application/zip";
                break;
            case 'pdf':
                $mime = "application/pdf";
                break;
        }

        header('Content-Type: '.$mime);
        header('Content-disposition: attachment;filename='.$name);
        readfile($this->get('kernel')->getRootDir() . '\\..\\web\\'.$type.'\\'.$name);
        exit();
    }

    /**
     * @Route("/projects", name="projects")
     */
    public function projectsAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST"))
        {
            $project = new Project();
            $project->setName($request->get("nom"));
            $project->setDescription($request->get("description"));

            if($_FILES["thumb"]["name"] != "")
            {
                $uploadRes = $this->upload("thumb", str_replace(' ','_',$request->get("nom")));
                if ($uploadRes["result"]) {
                    $session->getFlashBag()->add('success', "Upload photo réussie !");
                    $project->setThumb($uploadRes["filename"]);
                } else
                    $session->getFlashBag()->add('error', 'Upload photo error : ' . $uploadRes["message"]);
            }

            if($request->get("link"))
                $project->setLink($request->get("link"));
            if($request->get("video_link"))
                $project->setVideoLink($request->get("video_link"));

            $em->persist($project);
            $em->flush();

            foreach ($request->get("categorie") as $cat)
            {
                $mm = new ProjectCategoriesMm();
                $mm->setProjectId($project->getId());
                $mm->setCategoryId($cat);
                $em->persist($mm);
            }

            $em->flush();
        }

        $categories = $em->getRepository("PortfolioBundle:Categorie")
            ->findAll();

        $projects = $em->getRepository("PortfolioBundle:Project")
            ->findAll();

        return $this->render('admin/admin_project.html.twig', ["projects" => $projects, "categories" => $categories]);
    }

    /**
     * @Route("/categories", name="categories")
     */
    public function categorieAction(Request $request)
    {
        $retour = $this->checkConnexion($request);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST"))
        {
            $categorie = new Categorie();
            $categorie->setName($request->get("nom"));
            $categorie->setIsFilter($request->get("isFilter") == "on" ? true : false);

            $em->persist($categorie);
            $em->flush();
        }

        $categories = $em->getRepository("PortfolioBundle:Categorie")
            ->findAll();

        return $this->render('admin/admin_categorie.html.twig', ["categories" => $categories]);
    }

    /**
     * @Route("/article", name="article")
     */
    public function articleAction(Request $request)
    {
        $retour = $this->checkConnexion($request, false);
        if(!$retour[0])
            return $retour[1];

        $session = $request->getSession();

        $em = $this->getDoctrine()->getManager();

        if($request->isMethod("POST"))
        {
            $article = new Article();
            $article->setTitle($request->get("title"));
            $article->setCreatedAt(new \DateTime($request->get("createdAt")));
            $article->setAccroche($request->get("accroche"));
            $article->setPublished($request->get("published") == "on" ? true : false);

            $em->persist($article);
            $em->flush();

            $paragraphes = $request->get("paragraphe");
            $images = $request->get("image");
            $videos = $request->get("video");
            $quotes = $request->get("quote");
            $progs = $request->get("prog");
            $multimages = $request->get("multiImage");
            $blockQuotes = $request->get("blockQuote");
            $list = $request->get("list");

            foreach ($paragraphes as $paragraphe)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("paragraphe");
                $block->setOrdering(intval($paragraphe["order"]));

                $block->setContent($paragraphe["texte"]);
                $em->persist($block);
            }

            foreach ($images as $key => $image)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("paragraphe");
                $block->setOrdering($image["order"]);
                if($_FILES["image"]["name"][$key]["image"] != "")
                {
                    $uploadRes = $this->upload2("image", "article_".$article->getId().'_image_'.$key,$key,"image");
                    if ($uploadRes["result"]) {
                        $session->getFlashBag()->add('success', "Upload réussie !");
                    } else
                        $session->getFlashBag()->add('error', 'Upload error : ' . $uploadRes["message"]);

                    $content = '<div class="post-image margin-top-40 margin-bottom-40">
                        <img src="/IMG/'.$uploadRes["filename"].'" alt="">
                        <p>' . $image["legend"] . '</p>
                    </div>';
                }

                $block->setContent($content);
                $em->persist($block);
            }

            foreach ($videos as $key => $video)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("video");
                $block->setOrdering($video["order"]);
                if($_FILES["video"]["name"][$key]["thumb"] != "")
                {
                    $uploadRes = $this->upload2("video", "article_" . $article->getId() . '_video_' . $key, $key, "thumb");
                    if ($uploadRes["result"]) {
                        $session->getFlashBag()->add('success', "Upload réussie !");
                    } else
                        $session->getFlashBag()->add('error', 'Upload error : ' . $uploadRes["message"]);

                    $content = '<div class="video-box margin-top-40 margin-bottom-40">
                        <div class="video-tutorial">
                            <a class="video-popup" href="' . $video["url"] . '" title="' . $video["url"] . '">
                                <img src="/IMG/'.$uploadRes["filename"].'" alt="">
                            </a>
                        </div>
                        <p>' . $video["legend"] . '</p>
                    </div>';
                }

                $block->setContent($content);
                $em->persist($block);
            }

            foreach ($quotes as $quote)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("quote");
                $block->setOrdering($quote["order"]);

                $content = '<div class="post-quote margin-top-40 margin-bottom-40">
                    <blockquote>'.$quote["quote"].'</blockquote>
                </div>';

                $block->setContent($content);
                $em->persist($block);
            }

            foreach ($progs as $prog)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("prog");
                $block->setOrdering($prog["order"]);
                $content = '<div class="margin-top-40 margin-bottom-40">
                    <pre class="brush: '.$prog["langage"].'">
                        '.$prog["texte"].'
                    </pre>
                </div>';

                $block->setContent($content);
                $em->persist($block);
            }

            foreach ($blockQuotes as $blockQuote)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("blockQuote");
                $block->setOrdering($blockQuote["order"]);
                $content = '<blockquote class="margin-top-40 margin-bottom-40">
                    <p>'.$blockQuote["texte"].'</p>
                </blockquote>';

                $block->setContent($content);
                $em->persist($block);
            }

            foreach ($list as $l)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("liste");
                $block->setOrdering($l["order"]);
                $content = '<div class="list '.$l['style'].'">
                    <ul>';
                foreach (explode("\n",$l["items"]) as $item)
                        $content.= '<li>'.$item.'</li>';
                $content .= '</ul>
                </div>';

                $block->setContent($content);
                $em->persist($block);
            }

            foreach ($multimages as $key2 => $multimage)
            {
                $block = new ArticleBlock();
                $block->setArticleId($article->getId());
                $block->setType("multimage");
                $block->setOrdering($multimage["order"]);
                $content = '<div class="row margin-top-40 margin-bottom-40">';
                foreach ($multimage as $key => $value)
                {
                    if($key == "order")
                        continue;

                    if($_FILES["multiImage"]["name"][$key2][$key]["image"] != "")
                    {
                        $uploadRes = $this->upload3("multiImage", "article_" . $article->getId() . '_multiimage_' . $key2 . '_'.$key, $key2, $key, "image");
                        if ($uploadRes["result"]) {
                            $session->getFlashBag()->add('success', "Upload réussie !");
                        } else
                            $session->getFlashBag()->add('error', 'Upload error : ' . $uploadRes["message"]);


                        $content .= '<div class="col-md-4 col-sm-6 col-xs-12">
                            <a href="/IMG/' .$uploadRes["filename"].'" class="image-popup" title="' . $value['legend'] . '">
                                <img src="/IMG/' .$uploadRes["filename"].'" class="img-responsive" alt="">
                            </a>
                        </div>';
                    }
                }
                $content .= '</div>';

                $block->setContent($content);
                $em->persist($block);
            }
            $em->flush();
        }

        return $this->render('admin/admin_article.html.twig');
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
            case 'pdf':
                if (!in_array("application/pdf", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".pdf";
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

    private function upload2($inputName, $filename, $cpt, $subname, $uploaddir = "", $fileType = ["image/jpg","image/jpeg","image/png"])
    {
        if($uploaddir == "")
            $uploaddir = $this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\';

        $extension = "";
        $explode = explode(".",strtolower($_FILES[$inputName]["name"][$cpt][$subname]));
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
            case 'pdf':
                if (!in_array("application/pdf", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".pdf";
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

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'][$cpt][$subname], $uploaddir.$filename.$extension))
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

    private function upload3($inputName, $filename, $cpt, $cpt2, $subname, $uploaddir = "", $fileType = ["image/jpg","image/jpeg","image/png"])
    {
        if($uploaddir == "")
            $uploaddir = $this->get('kernel')->getRootDir() . '\\..\\web\\IMG\\';

        $extension = "";
        $explode = explode(".",strtolower($_FILES[$inputName]["name"][$cpt][$cpt2][$subname]));
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
            case 'pdf':
                if (!in_array("application/pdf", $fileType))
                    return [
                        "result" => false,
                        "message" => "Mauvais type de fichier ! "
                    ];
                $extension = ".pdf";
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

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'][$cpt][$cpt2][$subname], $uploaddir.$filename.$extension))
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
