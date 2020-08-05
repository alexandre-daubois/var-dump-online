<?php

namespace App\Controller;

use App\Entity\UserVarDump;
use App\Entity\UserVarDumpModel;
use App\Form\Type\UserVarDumpFormType;
use App\Service\UserVarDumpModelFormatter;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="_home")
     *
     * @return Response
     */
    public function home(Request $request, UserVarDumpModelFormatter $formatter)
    {
        $form = $this->createForm(UserVarDumpFormType::class);

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/shared/{token}", name="_shared")
     *
     * @param $token
     */
    public function shared($token, Request $request, UserVarDumpModelFormatter $formatter, EntityManagerInterface $entityManager)
    {
        /** @var UserVarDump $dump */
        $dump = $entityManager->getRepository(UserVarDump::class)->findOneBy(['token' => $token]);
        $model = (new UserVarDumpModel())
            ->setContent($dump->getContent());
        $root = $formatter->format($model);

        $form = $this->createForm(UserVarDumpFormType::class, $model);

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
            'nodes' => $root,
        ]);
    }

    /**
     * @Route("/format", name="_format")
     *
     * @return Response
     */
    public function format(Request $request, UserVarDumpModelFormatter $formatter)
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $root = $formatter->format($userVarDumpModel);
            }
        }

        return new JsonResponse([
            'html' => $this->renderView('format.html.twig', [
                    'form' => $form->createView(),
                    'nodes' => $root,
                ]),
        ]);
    }

    /**
     * @Route("/share", name="_share")
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function share(Request $request, EntityManagerInterface $entityManager)
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $dump = new UserVarDump();
                $dump->setSubmittedAt(new \DateTime('now'));
                $dump->setContent($userVarDumpModel->getContent());
                $dump->setToken(bin2hex(random_bytes(16)));

                $entityManager->persist($dump);
                $entityManager->flush();
            }
        } else {
            throw new AccessDeniedException();
        }

        return new JsonResponse([
            'link' => $this->generateUrl('_shared', ['token' => $dump->getToken()]),
        ]);
    }

    /**
     * @Route("/export/{format}", name="_export")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function export(string $format, Request $request, SerializerInterface $serializer, UserVarDumpModelFormatter $formatter)
    {
        if (!\in_array($format, ['json', 'xml'], true)) {
            throw new AccessDeniedException();
        }

        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;
        $serializedResult = null;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $root = $formatter->format($userVarDumpModel);
                $serializedResult = $serializer->serialize($root->getChildren()[0], $format);
            }
        }

        if (null === $root) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(['exportResult' => $serializedResult]);
    }
}
