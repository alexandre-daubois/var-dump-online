<?php

namespace App\Controller;

use App\Entity\GlobalStats;
use App\Entity\UserVarDump;
use App\Entity\UserVarDumpModel;
use App\Form\Type\UserVarDumpFormType;
use App\Service\GlobalStatsManager;
use App\Service\UserVarDumpExporter;
use App\Service\UserVarDumpModelFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class HomeController
 * @package App\Controller
 *
 * @Route("/{_locale}", requirements={"_locale": "en|fr|es|de|it|nl"}, defaults={"_locale":"en"})
 */
class HomeController extends AbstractController
{
    /**
     * @Route(name="_home")
     *
     * @return Response
     */
    public function home(Request $request, UserVarDumpModelFormatter $formatter)
    {
        $form = $this->createForm(UserVarDumpFormType::class);
        $stat = $this->getDoctrine()->getRepository(GlobalStats::class)->findOneBy(['key' => GlobalStats::BEAUTIFIER_USE_KEY]);

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
            'dumpsCount' => $stat->getValue(),
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

        if (null === $dump) {
            $this->redirectToRoute('_home');
        }

        $dump->setSeen($dump->getSeen() + 1);
        $entityManager->flush();

        $model = (new UserVarDumpModel())
            ->setContent($dump->getContent());
        $root = $formatter->format($model);

        $form = $this->createForm(UserVarDumpFormType::class, $model);

        $stat = $this->getDoctrine()->getRepository(GlobalStats::class)->findOneBy(['key' => GlobalStats::BEAUTIFIER_USE_KEY]);

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
            'nodes' => $root,
            'dumpsCount' => $stat->getValue(),
        ]);
    }

    /**
     * @Route("/format", name="_format")
     *
     * @return Response
     */
    public function format(Request $request, UserVarDumpModelFormatter $formatter, GlobalStatsManager $globalStatsManager, EntityManagerInterface $em)
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $root = $formatter->format($userVarDumpModel);
                $globalStatsManager->incrementStat(GlobalStats::BEAUTIFIER_USE_KEY);
                $em->flush();
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
            } else {
                throw new AccessDeniedException();
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
    public function export(string $format, Request $request, UserVarDumpExporter $exporter, UserVarDumpModelFormatter $formatter, EntityManagerInterface $em)
    {
        if (!\in_array($format, UserVarDumpExporter::getSupportedFormats(), true)) {
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
                $serializedResult = $exporter->export($root, $format);
                $em->flush();
            }
        }

        if (null === $root) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(['exportResult' => $serializedResult]);
    }
}
