<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final class NotFoundSubscriber implements EventSubscriberInterface
{
    public function __construct(private Environment $twig) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        // Route inexistante => 404
        if (!$throwable instanceof NotFoundHttpException) {
            return;
        }

        $html = $this->twig->render('errors/404.html.twig'); // ton template à toi
        $event->setResponse(new Response($html, Response::HTTP_NOT_FOUND));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 100],
        ];
    }
}