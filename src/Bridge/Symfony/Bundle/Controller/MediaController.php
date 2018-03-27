<?php

declare(strict_types=1);

namespace Damax\Media\Bridge\Symfony\Bundle\Controller;

use Damax\Common\Bridge\Symfony\Bundle\Annotation\Command;
use Damax\Media\Application\Command\CreateMedia;
use Damax\Media\Application\Command\UploadMedia;
use Damax\Media\Application\Exception\MediaNotFound;
use Damax\Media\Application\Exception\MediaNotUploaded;
use Damax\Media\Application\Exception\MediaUploadFailure;
use Damax\Media\Application\Service\DownloadService;
use Damax\Media\Application\Service\MediaService;
use Damax\Media\Application\Service\UploadService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/media")
 */
class MediaController
{
    /**
     * @Method("POST")
     * @Route("")
     * @Command(CreateMedia::class)
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function createAction(Request $request, MediaService $service, CreateMedia $command, ValidatorInterface $validator)
    {
        $command->mimeType = $request->headers->get('X-Upload-Content-Type');
        $command->size = (int) $request->headers->get('X-Upload-Content-Length');

        foreach ($validator->validate($command) as $error) {
            throw new BadRequestHttpException(sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage()));
        }

        $service->create($command);
    }

    /**
     * @Method("PUT")
     * @Route("/{id}/upload")
     *
     * @throws LengthRequiredHttpException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function uploadAction(Request $request, string $id, UploadService $service, ValidatorInterface $validator)
    {
        if (!($length = $request->headers->get('Content-Length'))) {
            throw new LengthRequiredHttpException();
        }

        $command = new UploadMedia();
        $command->mediaId = $id;
        $command->stream = $request->getContent(true);
        $command->mimeType = $request->headers->get('Content-Type');
        $command->size = (int) $length;

        foreach ($validator->validate($command) as $error) {
            throw new BadRequestHttpException(sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage()));
        }

        try {
            $service->upload($command);
        } catch (MediaNotFound $e) {
            throw new NotFoundHttpException();
        } catch (MediaUploadFailure $e) {
            throw new BadRequestHttpException('Upload failure.');
        }
    }

    /**
     * @Method("GET")
     * @Route("/{id}/download")
     *
     * @throws NotFoundHttpException
     */
    public function downloadAction(string $id, DownloadService $service): Response
    {
        try {
            return $service->download($id);
        } catch (MediaNotFound | MediaNotUploaded $e) {
            throw new NotFoundHttpException();
        }
    }
}
