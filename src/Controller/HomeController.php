<?php


namespace App\Controller;


use App\Entity\UserVarDumpModel;
use App\Form\Type\UserVarDumpFormType;
use App\Service\UserVarDumpModelProcesser;
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
     * @param UserVarDumpModelProcesser $processer
     * @return Response
     */
    public function home(Request $request, UserVarDumpModelProcesser $processer)
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Process content
                $processer->process($userVarDumpModel);
            }
        }

        return $this->render("home.html.twig", [
            'form' => $form->createView()
        ]);
    }
}