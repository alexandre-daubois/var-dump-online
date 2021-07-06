<?php

namespace App\Controller;

use App\Entity\GlobalStats;
use App\Entity\UserVarDump;
use App\Entity\UserVarDumpModel;
use App\Exception\FormatterResultCheckFailedException;
use App\Exception\UnknownTypeException;
use App\Form\Type\UserVarDumpFormType;
use App\Service\GlobalStatsManager;
use App\Service\UserVarDumpExporter;
use App\Service\UserVarDumpModelFormatter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route("/{_locale}", requirements: ['_locale' => "en|fr|es|de|it|nl"], defaults: ['_locale' => 'en'])]
class HomeController extends AbstractController
{
    protected UserVarDumpModelFormatter $formatter;

    protected EntityManagerInterface $entityManager;

    protected GlobalStatsManager $globalStatsManager;

    public function __construct(UserVarDumpModelFormatter $formatter, EntityManagerInterface $entityManager, GlobalStatsManager $globalStatsManager)
    {
        $this->formatter = $formatter;
        $this->entityManager = $entityManager;
        $this->globalStatsManager = $globalStatsManager;
    }

    #[Route(name: '_home')]
    public function home(): Response
    {
        return $this->render('home.html.twig', [
            'form' => $this->createForm(UserVarDumpFormType::class)->createView(),
        ]);
    }

    #[Route('/shared/{token}', name: '_shared')]
    public function shared(string $token): Response
    {
        if (null === $dump = $this->entityManager->getRepository(UserVarDump::class)->findOneBy(['token' => $token])) {
            return $this->redirectToRoute('_home');
        }

        $dump->setSeen($dump->getSeen() + 1);
        $this->entityManager->flush();

        $model = (new UserVarDumpModel())
            ->setContent($dump->getContent());

        try {
            $root = $this->formatter->format($model);
        } catch (FormatterResultCheckFailedException) {
            // Shared links can't contain errors, but just in case
            return $this->redirectToRoute('_home');
        }

        return $this->render('home.html.twig', [
            'form' => $this->createForm(UserVarDumpFormType::class, $model)->createView(),
            'nodes' => $root,
        ]);
    }

    #[Route("/format", name: '_format')]
    public function format(Request $request): JsonResponse
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;
        $responsePayload = [];

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $root = $this->formatter->format($userVarDumpModel);
                } catch (FormatterResultCheckFailedException $e) {
                    $root = $e->root;
                    $responsePayload['error'] = true;
                } catch (UnknownTypeException) {
                    $responsePayload['error'] = true;
                }

                $this->globalStatsManager->incrementStat(GlobalStats::BEAUTIFIER_USE_KEY);
                $this->entityManager->flush();
            }
        }

        $responsePayload['html'] = $this->renderView('format.html.twig', [
            'form' => $form->createView(),
            'nodes' => $root,
        ]);

        return new JsonResponse($responsePayload);
    }

    #[Route('/share', name: '_share')]
    public function share(Request $request): JsonResponse
    {
        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $this->formatter->format($userVarDumpModel);
                } catch (UnknownTypeException | FormatterResultCheckFailedException) {
                    throw new BadRequestHttpException();
                }

                $dump = (new UserVarDump())
                    ->setSubmittedAt(new \DateTime('now'))
                    ->setContent($userVarDumpModel->getContent())
                    ->setToken(\bin2hex(\random_bytes(16)));

                $this->entityManager->persist($dump);
                $this->entityManager->flush();
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

    #[Route('/export/{format}', name: '_export')]
    public function export(string $format, Request $request, UserVarDumpExporter $exporter): JsonResponse
    {
        if (!\in_array($format, UserVarDumpExporter::getSupportedFormats(), true)) {
            throw new BadRequestHttpException();
        }

        $userVarDumpModel = new UserVarDumpModel();
        $form = $this->createForm(UserVarDumpFormType::class, $userVarDumpModel);
        $root = null;
        $serializedResult = null;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $root = $this->formatter->format($userVarDumpModel);
                $serializedResult = $exporter->export($root, $format);
                $this->entityManager->flush();
            }
        }

        if (null === $root) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(['exportResult' => $serializedResult]);
    }
}
