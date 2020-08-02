<?php


namespace App\Controller;


use App\Entity\UserVarDumpModel;
use App\Form\Type\UserVarDumpFormType;
use App\Service\UserVarDumpModelFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="_home")
     *
     * @param Request $request
     * @param UserVarDumpModelFormatter $formatter
     * @return Response
     */
    public function home(Request $request, UserVarDumpModelFormatter $formatter)
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Process content
                $root = $formatter->format($userVarDumpModel);
            }
        }

//        var_dump([
//            12,
//            "test" => -12,
//            "test \"super\"" => 0.000000034,
//            5.12,
//            -96.4,
//            "bonjour à tous \"c'est super\""
//        ]);
//        die;

        return $this->render("home.html.twig", [
            'form' => $form->createView(),
            'nodes' => $root
        ]);
    }
}