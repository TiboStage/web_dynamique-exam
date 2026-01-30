<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', 0],
            LoginSuccessEvent::class => ['onLoginSuccess', 0],
        ];
    }

    /**
     * Vérifie si l'utilisateur est banni avant la connexion
     */
    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if ($user instanceof User && $user->isBanned()) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte a été suspendu. Contactez l\'administrateur pour plus d\'informations.'
            );
        }
    }

    /**
     * Met à jour la date de dernière connexion après un login réussi
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof User) {
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    }
}
