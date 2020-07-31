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
//            1 => [
//                2.3,
//                3
//            ],
//            2 => [
//                1 => [
//                    35
//                ]
//            ]
//        ]);
//        die;

        return $this->render("home.html.twig", [
            'form' => $form->createView(),
            'nodes' => $root
        ]);
    }
}